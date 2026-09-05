<?php

namespace App\Http\Controllers;

use App\Models\LeadActivity;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LeadActivity::with(['lead', 'user'])->latest();

        if ($user->isSales()) {
            $assignedLeadIds = \App\Models\Lead::where('assigned_to_user_id', $user->id)->pluck('id')->toArray();
            $query->whereIn('lead_id', $assignedLeadIds)->orWhere('user_id', $user->id);
        } elseif ($user->isBroker()) {
            $broker = \App\Models\Broker::where('user_id', $user->id)->first();
            $brokerLeadIds = $broker ? \App\Models\Lead::where('broker_id', $broker->id)->pluck('id')->toArray() : [];
            $query->whereIn('lead_id', $brokerLeadIds);
        }

        $notifications = $query->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Broadcast Firebase FCM Push Notification & Realtime Announcement to users
     */
    public function sendBroadcast(Request $request)
    {
        $currentUser = $request->user();

        if (!$currentUser->isCompanyAdmin() && !$currentUser->isSaaSFounder()) {
            return back()->with('error', 'Unauthorized access. Only Company Admins and SaaS Founders can send broadcast notifications.');
        }

        $request->validate([
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:500',
            'target_audience' => 'required|string|in:all,admin,manager,executive,broker',
        ]);

        $currentUser = $request->user();

        // Build recipient query
        $query = User::query();
        if (!$currentUser->isSaaSFounder()) {
            $query->where('company_id', $currentUser->company_id);
        }

        $target = $request->target_audience;
        if ($target !== 'all') {
            $query->whereHas('role', function ($q) use ($target) {
                if ($target === 'admin') {
                    $q->whereIn('slug', ['founder', 'director', 'admin']);
                } elseif ($target === 'manager') {
                    $q->whereIn('slug', ['manager', 'sales_manager']);
                } elseif ($target === 'executive') {
                    $q->whereIn('slug', ['sales_executive', 'executive']);
                } elseif ($target === 'broker') {
                    $q->where('slug', 'broker');
                }
            });
        }

        $recipients = $query->get();

        // 1. Dispatch Firebase Cloud Messaging (FCM) Push Notifications
        $fcmService = app(FirebaseNotificationService::class);
        $sentFcmCount = $fcmService->sendToUsers(
            $recipients,
            $request->title,
            $request->message,
            [
                'type' => 'broadcast_announcement',
                'click_action' => route('notifications.index'),
            ]
        );

        // 2. Dispatch Firebase Realtime Database (RTDB) sync for live app listening
        $databaseUrl = rtrim((string) config('firebase.database_url', ''), '/');
        if (!empty($databaseUrl)) {
            try {
                $timestamp = now()->timestamp * 1000;
                $serverKey = config('firebase.server_key', '');
                $endpoint = "{$databaseUrl}/announcements/{$timestamp}.json";
                $url = !empty($serverKey) && !str_contains($serverKey, 'dummy')
                    ? "{$endpoint}?auth={$serverKey}"
                    : $endpoint;

                Http::timeout(3)->put($url, [
                    'id' => (string) $timestamp,
                    'title' => $request->title,
                    'message' => $request->message,
                    'sender' => $currentUser->name,
                    'target' => $request->target_audience,
                    'timestamp' => $timestamp,
                    'created_at' => now()->format('d M Y, h:i A'),
                ]);
            } catch (\Throwable $e) {
                Log::warning('[RTDB BROADCAST WARN] ' . $e->getMessage());
            }
        }

        Log::info(sprintf(
            '[FCM BROADCAST DISPATCH] User #%s (%s) broadcasted "%s" to %d user(s) (FCM Devices: %d)',
            $currentUser->id,
            $currentUser->name,
            $request->title,
            $recipients->count(),
            $sentFcmCount
        ));

        return back()->with('success', "Broadcast Push Notification '{$request->title}' sent successfully to {$recipients->count()} user account(s)!");
    }
}
