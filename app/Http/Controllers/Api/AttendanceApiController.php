<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
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

        // Reverse Geocoding via Google Maps API
        if (empty($validated['address']) && !empty($validated['latitude']) && !empty($validated['longitude'])) {
            try {
                $apiKey = env('GOOGLE_MAPS_API_KEY');
                if ($apiKey) {
                    $response = \Illuminate\Support\Facades\Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                        'latlng' => $validated['latitude'] . ',' . $validated['longitude'],
                        'key' => $apiKey
                    ]);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        if (!empty($data['results'][0]['formatted_address'])) {
                            $validated['address'] = $data['results'][0]['formatted_address'];
                        }
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning('Google Maps API key not set for reverse geocoding.');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Google Geocoding failed: ' . $e->getMessage());
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

    public function leaves(Request $request)
    {
        abort_if($request->user()->isBroker(), 403, 'HRMS is available for internal staff only.');
        
        $leaves = LeaveRequest::where('company_id', $request->user()->company_id)
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return response()->json(['status' => 'success', 'data' => $leaves]);
    }

    public function storeLeaveRequest(Request $request)
    {
        abort_if($request->user()->isBroker(), 403, 'HRMS is available for internal staff only.');
        
        $validated = $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $start = strtotime($validated['start_date']);
        $end = strtotime($validated['end_date']);
        $totalDays = max(1, round(($end - $start) / 86400) + 1);

        $leave = LeaveRequest::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Leave application submitted for approval!', 'data' => $leave], 201);
    }
}
