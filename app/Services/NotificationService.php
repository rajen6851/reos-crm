<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Dispatch in-app notification, email notification, and multi-channel triggers.
     */
    public function notify(User $user, string $type, string $title, string $message, ?string $url = null): void
    {
        // 1. Log notification dispatch
        Log::info("Notification [{$type}] sent to User ID {$user->id} ({$user->email}): {$title} - {$message}");

        // 2. Dispatch Firebase FCM Push Notification
        try {
            $fcmService = app(\App\Services\FirebaseNotificationService::class);
            $fcmService->sendToUser($user, $title, $message, [
                'type' => $type,
                'url' => $url ?? route('notifications.index'),
                'click_action' => $url ?? route('notifications.index'),
            ]);
        } catch (\Throwable $e) {
            Log::warning("[FCM NOTIFICATION ERROR] " . $e->getMessage());
        }

        // 3. Dispatch Firebase Realtime Database (RTDB) sync for live app listening
        $databaseUrl = rtrim((string) config('firebase.database_url', ''), '/');
        if (!empty($databaseUrl)) {
            try {
                $timestamp = now()->timestamp * 1000;
                $serverKey = config('firebase.server_key', '');
                $endpoint = "{$databaseUrl}/user_notifications/{$user->id}/{$timestamp}.json";
                $requestUrl = !empty($serverKey) && !str_contains($serverKey, 'dummy')
                    ? "{$endpoint}?auth={$serverKey}"
                    : $endpoint;

                \Illuminate\Support\Facades\Http::timeout(3)->put($requestUrl, [
                    'id' => (string) $timestamp,
                    'user_id' => (string) $user->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                    'timestamp' => $timestamp,
                    'created_at' => now()->format('d M Y, h:i A'),
                ]);
                Log::info("[FIREBASE RTDB NOTIFICATION SUCCESS] Synced alert '{$title}' for User #{$user->id}");
            } catch (\Throwable $e) {
                Log::warning("[RTDB NOTIFICATION WARN] " . $e->getMessage());
            }
        }

        // 4. In-App Database Activity Record
        try {
            \App\Models\LeadActivity::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'activity_type' => $type,
                'description' => "{$title}: {$message}",
                'metadata' => [
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning("[DB ACTIVITY FEED WARN] " . $e->getMessage());
        }

        // 5. Multi-channel Email Notification
        if (!empty($user->email)) {
            try {
                $emailBody = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8fafc; color: #1e293b;'>
                        <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0;'>
                            <div style='border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px;'>
                                <span style='background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: bold; text-transform: uppercase;'>REOS Real Estate Alert</span>
                                <h2 style='color: #0f172a; margin-top: 8px; font-size: 20px;'>{$title}</h2>
                            </div>
                            <p style='font-size: 14px; line-height: 1.5; color: #334155;'>Hello <strong>{$user->name}</strong>,</p>
                            <p style='font-size: 14px; line-height: 1.5; color: #334155;'>{$message}</p>
                            " . ($url ? "<div style='margin-top: 24px;'><a href='{$url}' style='background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 12px; font-weight: bold; font-size: 13px; display: inline-block;'>View Details on REOS Dashboard →</a></div>" : "") . "
                            <div style='margin-top: 32px; border-top: 1px solid #f1f5f9; padding-top: 16px; font-size: 11px; color: #94a3b8;'>
                                Sent automatically by REOS – Real Estate Operating System SaaS Platform.
                            </div>
                        </div>
                    </div>
                ";

                Mail::html($emailBody, function ($mail) use ($user, $title) {
                    $mail->to($user->email)
                         ->subject("REOS Alert: {$title}");
                });
            } catch (\Throwable $e) {
                Log::error("Failed to send email notification to {$user->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Send direct HTML email to any email address (e.g. Customers).
     */
    public function sendDirectEmail(string $email, string $recipientName, string $title, string $message, ?string $url = null): void
    {
        if (empty($email)) {
            return;
        }

        try {
            $emailBody = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8fafc; color: #1e293b;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0;'>
                        <div style='border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px;'>
                            <span style='background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: bold; text-transform: uppercase;'>Property Booking Confirmation</span>
                            <h2 style='color: #0f172a; margin-top: 8px; font-size: 20px;'>{$title}</h2>
                        </div>
                        <p style='font-size: 14px; line-height: 1.5; color: #334155;'>Dear <strong>{$recipientName}</strong>,</p>
                        <p style='font-size: 14px; line-height: 1.5; color: #334155;'>{$message}</p>
                        " . ($url ? "<div style='margin-top: 24px;'><a href='{$url}' style='background-color: #059669; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 12px; font-weight: bold; font-size: 13px; display: inline-block;'>Download Official Booking Receipt →</a></div>" : "") . "
                        <div style='margin-top: 32px; border-top: 1px solid #f1f5f9; padding-top: 16px; font-size: 11px; color: #94a3b8;'>
                            Sent by REOS Real Estate Operating System on behalf of Builder & Operations Team.
                        </div>
                    </div>
                </div>
            ";

            Mail::html($emailBody, function ($mail) use ($email, $title) {
                $mail->to($email)
                     ->subject("{$title}");
            });

            Log::info("Direct Email sent successfully to {$email}: {$title}");
        } catch (\Throwable $e) {
            Log::error("Failed to send direct email to {$email}: " . $e->getMessage());
        }
    }
}
