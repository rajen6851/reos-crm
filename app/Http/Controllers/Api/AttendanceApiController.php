<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_if($user->isBroker(), 403, 'Attendance is available for internal staff only.');
        $query = Attendance::where('company_id', $user->company_id);

        if ($user->isManager()) {
            $teamIds = $user->teamExecutives()->pluck('id')->push($user->id);
            $query->whereIn('user_id', $teamIds);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date('date'));
        }

        return response()->json(['status' => 'success', 'data' => $query->latest('date')->paginate(31)]);
    }

    public function clockIn(Request $request)
    {
        abort_if($request->user()->isBroker(), 403, 'Attendance is available for internal staff only.');
        $validated = $request->validate([
            'work_location' => 'required|in:office,field_visit,wfh',
            'selfie' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);
        $user = $request->user();
        $attendance = Attendance::firstOrCreate(
            ['company_id' => $user->company_id, 'user_id' => $user->id, 'date' => now()->toDateString()],
            [
                'clock_in' => now()->format('H:i:s'),
                'work_location' => $validated['work_location'],
                'status' => 'present',
                'notes' => $validated['notes'] ?? null,
                'selfie_path' => $request->file('selfie')->store('attendance-selfies', 'public'),
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Attendance clock-in recorded.', 'data' => $attendance], 201);
    }

    public function clockOut(Request $request)
    {
        abort_if($request->user()->isBroker(), 403, 'Attendance is available for internal staff only.');
        $attendance = Attendance::where('company_id', $request->user()->company_id)
            ->where('user_id', $request->user()->id)
            ->whereDate('date', now()->toDateString())
            ->firstOrFail();
        $attendance->update(['clock_out' => now()->format('H:i:s')]);

        return response()->json(['status' => 'success', 'message' => 'Attendance clock-out recorded.', 'data' => $attendance->fresh()]);
    }
}
