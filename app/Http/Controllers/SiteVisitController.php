<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteVisitController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $query = Lead::whereIn('status', ['site_visit', 'negotiation', 'converted', 'booked'])
            ->with(['project', 'assignedTo']);

        $logsQuery = LeadActivity::where('activity_type', 'site_visit_logged')
            ->with(['lead', 'user']);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
            $logsQuery->where('user_id', $user->id);
        }

        $siteVisits = $query->latest()->get();
        $recentVisitLogs = $logsQuery->latest()->take(15)->get();

        return view('site_visits.index', compact('siteVisits', 'recentVisitLogs'));
    }
}
