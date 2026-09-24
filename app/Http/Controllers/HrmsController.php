<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\LeaveRequest;
use App\Models\SalarySlip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HrmsController extends Controller
{
    private function checkAccess()
    {
        if (auth()->user()->isBroker()) {
            abort(403, 'Unauthorized access. HRMS is reserved for internal company staff members only.');
        }
    }

    public function dashboard(\Illuminate\Http\Request $request)
    {
        $this->checkAccess();
        $user = auth()->user();
        $today = date('Y-m-d');
        $mapDate = $request->query('map_date', $today);

        if ($user->isSaaSFounder()) {
            $totalPlatformStaff = User::whereNotNull('company_id')->count();
            $todayPlatformCheckIns = Attendance::where('date', $today)->count();
            $pendingLeaveRequestsCount = LeaveRequest::where('status', 'pending')->count();
            $generatedSalarySlipsCount = SalarySlip::count();
            $todayRoster = Attendance::where('date', $mapDate)->with(['user', 'company'])->get();

            return view('hrms.dashboard', compact(
                'user', 'totalPlatformStaff', 'todayPlatformCheckIns',
                'pendingLeaveRequestsCount', 'generatedSalarySlipsCount', 'todayRoster', 'mapDate'
            ));
        }

        $myTodayAttendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        
        if ($user->hasPermission('manage-hrms')) {
            $todayRoster = Attendance::where('company_id', $user->company_id)->where('date', $mapDate)->with('user')->get();
        } elseif ($user->isManager()) {
            $teamIds = $user->teamExecutives()->pluck('id')->push($user->id);
            $todayRoster = Attendance::whereIn('user_id', $teamIds)->where('date', $mapDate)->with('user')->get();
        } else {
            $todayRoster = Attendance::where('user_id', $user->id)->where('date', $mapDate)->with('user')->get();
        }

        return view('hrms.dashboard', compact('user', 'myTodayAttendance', 'todayRoster', 'mapDate'));
    }

    public function staff()
    {
        $this->checkAccess();
        $user = auth()->user();

        if ($user->isSaaSFounder()) {
            $staffUsers = User::all();
        } elseif ($user->hasPermission('manage-hrms')) {
            $staffUsers = User::where('company_id', $user->company_id)->get();
        } elseif ($user->isManager()) {
            $teamIds = $user->teamExecutives()->pluck('id')->push($user->id);
            $staffUsers = User::whereIn('id', $teamIds)->get();
        } else {
            $staffUsers = User::where('id', $user->id)->get();
        }

        return view('hrms.staff', compact('staffUsers'));
    }

    public function attendance(\Illuminate\Http\Request $request)
    {
        $this->checkAccess();
        $user = auth()->user();
        $today = $request->query('date', date('Y-m-d'));
        $currentMonth = date('Y-m', strtotime($today));

        // Original logic for specific cases (if needed) and My Today Attendance
        $myTodayAttendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        // New Logic: Fetch User list based on access
        $usersQuery = User::with('role');
        if ($user->isSaaSFounder()) {
            // all
        } elseif ($user->hasPermission('manage-hrms') || $user->isCompanyAdmin()) {
            $usersQuery->where('company_id', $user->company_id);
        } elseif ($user->isManager()) {
            $teamIds = $user->teamExecutives()->pluck('id')->push($user->id);
            $usersQuery->whereIn('id', $teamIds);
        } else {
            $usersQuery->where('id', $user->id);
        }

        $allUsers = $usersQuery->get();
        $userIds = $allUsers->pluck('id')->toArray();

        $todayAttendances = Attendance::whereIn('user_id', $userIds)->where('date', $today)->get()->keyBy('user_id');
        $todayLeaves = LeaveRequest::whereIn('user_id', $userIds)->where('start_date', '<=', $today)->where('end_date', '>=', $today)->where('status', 'approved')->get()->keyBy('user_id');

        $departmentStats = collect();
        $dailyAttendanceList = collect();

        $departments = $allUsers->groupBy(function ($u) {
            return $u->department ?: 'Unassigned';
        });

        $isOffday = (date('N', strtotime($today)) == 7); // Assuming Sunday is offday

        foreach ($departments as $deptName => $deptUsers) {
            $totalEmployees = $deptUsers->count();
            // Estimating Male/Female for UI as we don't track gender natively
            $male = (int) ceil($totalEmployees * 0.6);
            $female = $totalEmployees - $male;
            
            $present = 0;
            $late = 0;
            $absent = 0;
            $leave = 0;
            $offday = 0;

            foreach ($deptUsers as $emp) {
                $att = $todayAttendances->get($emp->id);
                $onLeave = $todayLeaves->get($emp->id);

                $status = 'Absent';
                $checkIn = 'Null';
                $checkOut = 'Null';
                $worked = '0 hr 00 min';
                $remarks = 'Fixed Attendance'; 

                if ($onLeave) {
                    $status = 'Leave';
                    $leave++;
                } elseif ($isOffday) {
                    $status = 'Offday';
                    $offday++;
                } elseif ($att) {
                    $status = 'Present';
                    $present++;
                    
                    $checkInTime = strtotime($att->clock_in);
                    $checkIn = date('h:i a', $checkInTime);
                    if ($checkInTime > strtotime($today . ' 09:30:00')) {
                        $status = 'Late';
                        $late++; 
                    }

                    if ($att->clock_out) {
                        $checkOut = date('h:i a', strtotime($att->clock_out));
                        $diff = strtotime($att->clock_out) - strtotime($att->clock_in);
                        $hours = floor($diff / 3600);
                        $mins = floor(($diff % 3600) / 60);
                        $worked = "{$hours} hr {$mins} min";
                    } else {
                        $checkOut = 'Working...';
                    }
                } else {
                    $absent++;
                }

                $location = 'N/A';
                if ($att) {
                    $location = $att->address ?? str_replace('_', ' ', ucwords($att->work_location ?? 'Office', '_'));
                    if ($att->latitude && $att->longitude) {
                        $location .= " (Map)";
                    }
                }

                $checkout_location = 'Null';
                if ($att && $att->clock_out) {
                    $checkout_location = $att->checkout_address ?? 'Checked Out';
                    if ($att->checkout_latitude && $att->checkout_longitude) {
                        $checkout_location .= " (Map)";
                    }
                }

                $dailyAttendanceList->push((object)[
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'role' => $emp->role->name ?? 'Staff',
                    'status' => $status,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'shift' => '9 am - 6 pm',
                    'worked' => $worked,
                    'location' => $location,
                    'checkout_location' => $checkout_location,
                    'lat' => $att->latitude ?? null,
                    'lon' => $att->longitude ?? null,
                    'checkout_lat' => $att->checkout_latitude ?? null,
                    'checkout_lon' => $att->checkout_longitude ?? null,
                    'remarks' => $remarks
                ]);
            }

            if ($isOffday) {
                $offday = $totalEmployees;
                $present = 0; $absent = 0; $late = 0; $leave = 0;
            }

            $departmentStats->push((object)[
                'name' => $deptName,
                'employees' => $totalEmployees,
                'male' => $male,
                'female' => $female,
                'present' => $present,
                'present_pct' => $totalEmployees > 0 ? round(($present / $totalEmployees) * 100, 1) : 0,
                'late' => $late,
                'late_pct' => $totalEmployees > 0 ? round(($late / $totalEmployees) * 100, 1) : 0,
                'absent' => $absent,
                'absent_pct' => $totalEmployees > 0 ? round(($absent / $totalEmployees) * 100, 1) : 0,
                'leave' => $leave,
                'leave_pct' => $totalEmployees > 0 ? round(($leave / $totalEmployees) * 100, 1) : 0,
                'offday' => $offday,
                'offday_pct' => $totalEmployees > 0 ? round(($offday / $totalEmployees) * 100, 1) : 0,
            ]);
        }

        return view('hrms.attendance', compact('myTodayAttendance', 'currentMonth', 'departmentStats', 'dailyAttendanceList', 'today'));
    }

    public function leaves()
    {
        $this->checkAccess();
        $user = auth()->user();

        if ($user->isSaaSFounder()) {
            $leaveRequests = LeaveRequest::with(['user', 'company', 'approver'])->orderByDesc('created_at')->get();
        } elseif ($user->hasPermission('manage-hrms')) {
            $leaveRequests = LeaveRequest::where('company_id', $user->company_id)->with(['user', 'approver'])->orderByDesc('created_at')->get();
        } elseif ($user->isManager()) {
            $teamIds = $user->teamExecutives()->pluck('id')->push($user->id);
            $leaveRequests = LeaveRequest::whereIn('user_id', $teamIds)->with(['user', 'approver'])->orderByDesc('created_at')->get();
        } else {
            $leaveRequests = LeaveRequest::where('user_id', $user->id)->with(['user', 'approver'])->orderByDesc('created_at')->get();
        }

        return view('hrms.leaves', compact('leaveRequests'));
    }

    public function payroll()
    {
        $this->checkAccess();
        $user = auth()->user();

        if ($user->isSaaSFounder()) {
            $salarySlips = SalarySlip::with(['user', 'company'])->orderByDesc('created_at')->get();
            $staffUsers = User::all();
        } elseif ($user->hasPermission('manage-hrms')) {
            $salarySlips = SalarySlip::where('company_id', $user->company_id)->with('user')->orderByDesc('created_at')->get();
            $staffUsers = User::where('company_id', $user->company_id)->get();
        } elseif ($user->isManager()) {
            $salarySlips = SalarySlip::where('user_id', $user->id)->with('user')->orderByDesc('created_at')->get();
            $teamIds = $user->teamExecutives()->pluck('id')->push($user->id);
            $staffUsers = User::whereIn('id', $teamIds)->get();
        } else {
            $salarySlips = SalarySlip::where('user_id', $user->id)->with('user')->orderByDesc('created_at')->get();
            $staffUsers = User::where('id', $user->id)->get();
        }

        return view('hrms.payroll', compact('salarySlips', 'staffUsers'));
    }

    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $today = date('Y-m-d');

        $validated = $request->validate([
            'work_location' => 'required|string|in:office,field_visit,wfh',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if (in_array($validated['work_location'], ['office', 'field_visit'])) {
            $company = $user->company;
            $baseLat = $company->settings['latitude'] ?? null;
            $baseLon = $company->settings['longitude'] ?? null;
            $radiusKm = $company->settings['attendance_radius_km'] ?? 30;

            if ($baseLat && $baseLon && !empty($validated['latitude']) && !empty($validated['longitude'])) {
                $earthRadius = 6371000;
                $latFrom = deg2rad($validated['latitude']);
                $lonFrom = deg2rad($validated['longitude']);
                $latTo = deg2rad($baseLat);
                $lonTo = deg2rad($baseLon);

                $latDelta = $latTo - $latFrom;
                $lonDelta = $lonTo - $lonFrom;

                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                $distanceKm = ($angle * $earthRadius) / 1000;

                if ($distanceKm > (float) $radiusKm) {
                    return redirect()->back()->with('error', "You are outside the allowed location. Distance: " . round($distanceKm, 2) . "km.");
                }
            }
        }

        $address = null;
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
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
                            $address = $data['results'][0]['formatted_address'];
                        }
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning('Google Maps API key not set for reverse geocoding.');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Google Geocoding failed: ' . $e->getMessage());
            }
        }

        Attendance::firstOrCreate(
            [
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'clock_in' => date('H:i:s'),
                'work_location' => $validated['work_location'],
                'status' => 'present',
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'address' => $address,
            ]
        );

        return redirect()->route('hrms.attendance')->with('success', 'Clocked in successfully for today!');
    }

    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $today = date('Y-m-d');

        $validated = $request->validate([
            'checkout_latitude' => 'nullable|numeric',
            'checkout_longitude' => 'nullable|numeric',
        ]);

        $address = null;
        if (!empty($validated['checkout_latitude']) && !empty($validated['checkout_longitude'])) {
            try {
                $apiKey = env('GOOGLE_MAPS_API_KEY');
                if ($apiKey) {
                    $response = \Illuminate\Support\Facades\Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                        'latlng' => $validated['checkout_latitude'] . ',' . $validated['checkout_longitude'],
                        'key' => $apiKey
                    ]);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        if (!empty($data['results'][0]['formatted_address'])) {
                            $address = $data['results'][0]['formatted_address'];
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Google Geocoding failed on checkout: ' . $e->getMessage());
            }
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            $attendance->update([
                'clock_out' => date('H:i:s'),
                'checkout_latitude' => $validated['checkout_latitude'] ?? null,
                'checkout_longitude' => $validated['checkout_longitude'] ?? null,
                'checkout_address' => $address,
            ]);
        }

        return redirect()->route('hrms.attendance')->with('success', 'Clocked out successfully! Have a great evening.');
    }

    public function storeLeaveRequest(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $start = strtotime($request->start_date);
        $end = strtotime($request->end_date);
        $totalDays = max(1, round(($end - $start) / 86400) + 1);

        LeaveRequest::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('hrms.leaves')->with('success', 'Leave application submitted for approval!');
    }

    public function updateLeaveStatus(Request $request, LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        Gate::authorize('manage-hrms');

        $leaveRequest->update([
            'status' => $request->input('status', 'approved'),
            'approved_by_user_id' => $user->id,
        ]);

        return redirect()->route('hrms.leaves')->with('success', 'Leave request status updated!');
    }

    public function generateSalarySlip(Request $request)
    {
        $user = auth()->user();

        Gate::authorize('manage-hrms');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|string',
            'basic_salary' => 'required|numeric',
        ]);

        $basic = $request->basic_salary;
        $allowances = $request->input('allowances', 0);
        $commission = $request->input('commission_earned', 0);
        $deductions = $request->input('deductions', 0);
        $net = ($basic + $allowances + $commission) - $deductions;

        $targetUser = User::find($request->user_id);

        SalarySlip::create([
            'company_id' => $targetUser->company_id ?? $user->company_id,
            'user_id' => $targetUser->id,
            'month' => $request->month,
            'working_days' => 26,
            'present_days' => 24,
            'leave_days' => 2,
            'basic_salary' => $basic,
            'allowances' => $allowances,
            'commission_earned' => $commission,
            'deductions' => $deductions,
            'net_salary' => $net,
            'status' => 'generated',
        ]);

        return redirect()->route('hrms.payroll')->with('success', 'Monthly salary slip generated successfully!');
    }

    public function showSalarySlip($id)
    {
        $user = auth()->user();
        $salarySlip = SalarySlip::with(['user', 'company'])->findOrFail($id);

        if (!$user->hasPermission('manage-hrms') && $salarySlip->user_id !== $user->id) {
            return redirect()->route('hrms.dashboard')->with('error', 'Unauthorized access to salary slip.');
        }

        return view('hrms.payslip', compact('salarySlip'));
    }

    public function requestLeaveDeletion($id)
    {
        $this->checkAccess();
        $leave = LeaveRequest::findOrFail($id);
        $user = auth()->user();

        if ($user->isCompanyFounder() || $user->isSaaSFounder()) {
            $leave->delete();
            return redirect()->back()->with('success', 'Leave request deleted instantly by Founder.');
        } elseif ($user->isCompanyAdmin()) {
            $leave->update([
                'deletion_requested_by' => $user->id,
                'deletion_reason' => 'Admin requested deletion.',
                'status' => 'pending_deletion',
            ]);
            return redirect()->back()->with('success', 'Deletion requested. Pending Founder approval.');
        }

        return redirect()->back()->with('error', 'You do not have permission to delete this.');
    }

    public function approveLeaveDeletion($id, \Illuminate\Http\Request $request)
    {
        $this->checkAccess();
        $leave = LeaveRequest::findOrFail($id);
        $user = auth()->user();

        if (!$user->isCompanyFounder() && !$user->isSaaSFounder()) {
            return redirect()->back()->with('error', 'Unauthorized. Only Founders can approve deletions.');
        }

        if ($request->action == 'approve') {
            $leave->delete();
            return redirect()->back()->with('success', 'Deletion approved and record removed.');
        } else {
            $leave->update([
                'deletion_requested_by' => null,
                'deletion_reason' => null,
                'status' => 'pending',
            ]);
            return redirect()->back()->with('warning', 'Deletion rejected. Record restored.');
        }
    }

    public function requestSalarySlipDeletion($id)
    {
        $this->checkAccess();
        $slip = SalarySlip::findOrFail($id);
        $user = auth()->user();

        if ($user->isCompanyFounder() || $user->isSaaSFounder()) {
            $slip->delete();
            return redirect()->back()->with('success', 'Salary slip deleted instantly by Founder.');
        } elseif ($user->isCompanyAdmin()) {
            $slip->update([
                'deletion_requested_by' => $user->id,
                'deletion_reason' => 'Admin requested deletion.',
                'status' => 'pending_deletion',
            ]);
            return redirect()->back()->with('success', 'Deletion requested. Pending Founder approval.');
        }

        return redirect()->back()->with('error', 'You do not have permission to delete this.');
    }

    public function approveSalarySlipDeletion($id, \Illuminate\Http\Request $request)
    {
        $this->checkAccess();
        $slip = SalarySlip::findOrFail($id);
        $user = auth()->user();

        if (!$user->isCompanyFounder() && !$user->isSaaSFounder()) {
            return redirect()->back()->with('error', 'Unauthorized. Only Founders can approve deletions.');
        }

        if ($request->action == 'approve') {
            $slip->delete();
            return redirect()->back()->with('success', 'Deletion approved and record removed.');
        } else {
            $slip->update([
                'deletion_requested_by' => null,
                'deletion_reason' => null,
                'status' => 'generated',
            ]);
            return redirect()->back()->with('warning', 'Deletion rejected. Record restored.');
        }
    }
}

