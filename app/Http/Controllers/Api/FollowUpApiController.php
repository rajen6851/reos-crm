<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Http\Request;

class FollowUpApiController extends Controller
{
    protected function queryForUser($user)
    {
        $query = FollowUp::where('company_id', $user->company_id)->with(['lead', 'user']);

        if ($user->isSales()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isManager()) {
            $teamIds = User::where('company_id', $user->company_id)
                ->where(fn ($q) => $q->whereKey($user->id)->orWhere('reporting_manager_id', $user->id))
                ->pluck('id');
            $query->whereIn('user_id', $teamIds);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->queryForUser($request->user());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('scheduled_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('scheduled_at', '<=', $request->date('to'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->latest('scheduled_at')->paginate(min((int) $request->get('per_page', 20), 100)),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,missed,cancelled',
            'notes' => 'nullable|string|max:2000',
        ]);

        $followUp = $this->queryForUser($request->user())->whereKey($id)->firstOrFail();
        $followUp->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $followUp->notes,
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Follow-up status updated.',
            'data' => $followUp->fresh(['lead', 'user']),
        ]);
    }
}
