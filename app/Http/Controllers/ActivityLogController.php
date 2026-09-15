<?php

namespace App\Http\Controllers;

use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Activity logs are visible to platform admins, company admins, and managers.
        if (!Auth::user()->can('view-activity-logs')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. Activity Audit Logs are reserved for Admins.');
        }

        if ($user->is_super_admin) {
            $query = LeadActivity::withoutGlobalScopes()->with(['lead', 'user'])->latest();
            $teamUsers = User::withoutGlobalScopes()->with('role')->get();
        } else {
            $query = LeadActivity::with(['lead', 'user'])->latest();
            $teamUsers = User::where('company_id', $user->company_id)
                ->when($user->isManager(), function ($q) use ($user) {
                    $q->where(function ($q) use ($user) {
                        $q->whereKey($user->id)
                            ->orWhere('reporting_manager_id', $user->id);
                    });
                })
                ->with('role')
                ->get();

            if ($user->isManager()) {
                $teamUserIds = $teamUsers->pluck('id');
                $query->where(function ($q) use ($user, $teamUserIds) {
                    $q->whereIn('user_id', $teamUserIds)
                        ->orWhereHas('lead', function ($leadQuery) use ($user, $teamUserIds) {
                            $leadQuery->where('assigned_to_manager_id', $user->id)
                                ->orWhereIn('assigned_to_user_id', $teamUserIds);
                        });
                });
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereHas('lead', function ($leadQuery) use ($user) {
                            $leadQuery->where('company_id', $user->company_id);
                        });
                });
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('lead', function ($l) use ($search) {
                      $l->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('lead_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $activities = $query->paginate(15);

        return view('activity_logs.index', compact('activities', 'teamUsers'));
    }
}
