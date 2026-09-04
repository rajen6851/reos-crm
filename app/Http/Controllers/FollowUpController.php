<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isBroker()) {
            return redirect()->route('dashboard');
        }

        $query = Lead::whereIn('status', ['new', 'contacted', 'follow_up', 'site_visit', 'negotiation'])
            ->with(['project', 'assignedTo', 'calls']);

        $callsQuery = LeadActivity::where('activity_type', 'call_logged')
            ->with(['lead', 'user']);

        if ($user->isSales()) {
            $query->where('assigned_to_user_id', $user->id);
            $callsQuery->where('user_id', $user->id);
        }

        $pendingFollowUps = $query->latest('updated_at')->get();
        $recentCalls = $callsQuery->latest()->take(20)->get();

        return view('follow_ups.index', compact('pendingFollowUps', 'recentCalls'));
    }
}
