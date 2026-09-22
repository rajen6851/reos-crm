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

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function clockIn(Request $request)
    {
        abort_if($request->user()->isBroker(), 403, 'Attendance is available for internal staff only.');
        $validated = $request->validate([
            'work_location' => 'required|in:office,field_visit,wfh',
            'project_id' => 'required_if:work_location,field_visit|exists:projects,id',
            'selfie' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:500',
            'latitude' => 'required_unless:work_location,wfh|numeric',
            'longitude' => 'required_unless:work_location,wfh|numeric',
            'address' => 'nullable|string|max:500',
        ]);
        $user = $request->user();

        // Geofencing Check
        if (in_array($validated['work_location'], ['office', 'field_visit'])) {
            $company = $user->company;
            $baseLat = $company->settings['latitude'] ?? null;
            $baseLon = $company->settings['longitude'] ?? null;
            $radiusKm = $company->settings['attendance_radius_km'] ?? 30; // default 30 km

            if ($baseLat && $baseLon) {
                $distance = $this->calculateDistance($validated['latitude'], $validated['longitude'], $baseLat, $baseLon);
                $distanceKm = $distance / 1000;

                if ($distanceKm > (float) $radiusKm) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => "Aap allowed location ke bahar hain. Distance: " . round($distanceKm, 2) . "km. Maximum allowed is {$radiusKm}km."
                    ], 403);
                }
            }
        }

        $attendance = Attendance::firstOrCreate(
            ['company_id' => $user->company_id, 'user_id' => $user->id, 'date' => now()->toDateString()],
            [
                'clock_in' => now()->format('H:i:s'),
                'work_location' => $validated['work_location'],
                'status' => 'present',
                'notes' => $validated['notes'] ?? null,
                'selfie_path' => $request->file('selfie')->store('attendance-selfies', 'public'),
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'address' => $validated['address'] ?? null,
            ]
        );

        $msg = 'Attendance clock-in recorded.';
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $msg .= " Location Captured: Lat: {$validated['latitude']}, Lon: {$validated['longitude']}";
        }

        return response()->json(['status' => 'success', 'message' => $msg, 'data' => $attendance], 201);
    }

    public function clockOut(Request $request)
    {
        abort_if($request->user()->isBroker(), 403, 'Attendance is available for internal staff only.');
        
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string|max:500',
        ]);

        $attendance = Attendance::where('company_id', $request->user()->company_id)
            ->where('user_id', $request->user()->id)
            ->whereDate('date', now()->toDateString())
            ->firstOrFail();
            
        $attendance->update([
            'clock_out' => now()->format('H:i:s'),
            'latitude' => $attendance->latitude ?? $validated['latitude'] ?? null,
            'longitude' => $attendance->longitude ?? $validated['longitude'] ?? null,
            'address' => $attendance->address ?? $validated['address'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Attendance clock-out recorded.', 'data' => $attendance->fresh()]);
    }
}
