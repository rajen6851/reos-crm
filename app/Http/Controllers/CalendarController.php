<?php

namespace App\Http\Controllers;

use App\Models\SiteVisit;
use App\Models\FollowUp;
use App\Models\Booking;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Display the Master Interactive CRM Calendar.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $companyId = $user->company_id;

        // Fetch Site Visits for Calendar Events
        $siteVisitsQuery = SiteVisit::with(['lead', 'project', 'assignedTo']);
        if (!$user->isSaaSFounder()) {
            $siteVisitsQuery->where('company_id', $companyId);
        }
        if ($user->isSales()) {
            $siteVisitsQuery->where(function($q) use ($user) {
                $q->where('assigned_to_user_id', $user->id)
                  ->orWhereHas('lead', fn($l) => $l->where('assigned_to_user_id', $user->id));
            });
        }
        $siteVisits = $siteVisitsQuery->whereNotNull('scheduled_at')->get();

        // Fetch Follow-ups for Calendar Events
        $followUpsQuery = FollowUp::with('lead');
        if (!$user->isSaaSFounder()) {
            $followUpsQuery->where('company_id', $companyId);
        }
        if ($user->isSales()) {
            $followUpsQuery->where('user_id', $user->id);
        }
        $followUps = $followUpsQuery->whereNotNull('scheduled_at')->get();

        // Format Events JSON for FullCalendar JS
        $events = [];

        // 1. Site Visits (Emerald Green Badge)
        foreach ($siteVisits as $sv) {
            $events[] = [
                'id' => 'sv_' . $sv->id,
                'title' => '🚗 Site Visit: ' . ($sv->lead->name ?? 'Buyer') . ' @ ' . ($sv->project->name ?? 'Project'),
                'start' => date('Y-m-d\TH:i:s', strtotime($sv->scheduled_at)),
                'backgroundColor' => '#059669',
                'borderColor' => '#047857',
                'textColor' => '#ffffff',
                'url' => route('site-visits.index'),
                'extendedProps' => [
                    'category' => 'Site Visit',
                    'lead_name' => $sv->lead->name ?? 'N/A',
                    'location' => $sv->project->name ?? 'Site Location',
                ]
            ];
        }

        // 2. Follow-up Calls & Meetings (Brand Indigo Badge)
        foreach ($followUps as $fu) {
            $events[] = [
                'id' => 'fu_' . $fu->id,
                'title' => '📞 Follow-up: ' . ($fu->lead->name ?? 'Lead') . ' (' . ucfirst($fu->type ?? 'call') . ')',
                'start' => date('Y-m-d\TH:i:s', strtotime($fu->scheduled_at)),
                'backgroundColor' => '#4F46E5',
                'borderColor' => '#4338CA',
                'textColor' => '#ffffff',
                'url' => route('follow-ups.index'),
                'extendedProps' => [
                    'category' => 'Follow-up Call',
                    'lead_name' => $fu->lead->name ?? 'N/A',
                    'notes' => $fu->notes ?? '',
                ]
            ];
        }

        // Upcoming Events Summary List for Left Panel
        $upcomingEvents = collect($events)
            ->sortBy('start')
            ->filter(fn($e) => strtotime($e['start']) >= strtotime('-1 day'))
            ->take(6)
            ->values();

        return view('calendar.index', compact('events', 'upcomingEvents'));
    }
}
