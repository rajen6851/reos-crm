<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    protected function queryForUser(User $user)
    {
        $query = LeadActivity::with(['lead', 'user'])
            ->where('company_id', $user->company_id)
            ->latest();

        if ($user->isSales()) {
            $query->where(function ($activityQuery) use ($user) {
                $activityQuery->where('user_id', $user->id)
                    ->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('assigned_to_user_id', $user->id));
            });
        } elseif ($user->isManager()) {
            $teamUserIds = User::where('company_id', $user->company_id)
                ->where(fn ($teamQuery) => $teamQuery
                    ->whereKey($user->id)
                    ->orWhere('reporting_manager_id', $user->id))
                ->pluck('id');

            $query->where(function ($activityQuery) use ($user, $teamUserIds) {
                $activityQuery->whereIn('user_id', $teamUserIds)
                    ->orWhereHas('lead', fn ($leadQuery) => $leadQuery
                        ->where('assigned_to_manager_id', $user->id)
                        ->orWhereIn('assigned_to_user_id', $teamUserIds));
            });
        } elseif ($user->isBroker()) {
            $broker = Broker::where('user_id', $user->id)->first();
            $query->when($broker, function ($activityQuery) use ($broker) {
                $activityQuery->whereHas('lead', fn ($leadQuery) => $leadQuery->where('broker_id', $broker->id));
            }, function ($activityQuery) {
                $activityQuery->whereRaw('1 = 0');
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = $this->queryForUser($user);

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(min((int) $request->get('per_page', 20), 100));

        return response()->json([
            'status' => 'success',
            'unread_count' => $this->queryForUser($user)->whereNull('read_at')->count(),
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, int $id)
    {
        $notification = $this->queryForUser($request->user())->whereKey($id)->firstOrFail();
        $notification->forceFill(['read_at' => now()])->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }
}
