<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\LeaveRequest;
use App\Models\SalarySlip;
use App\Models\User;
use Illuminate\Http\Request;

class HrmsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = date('Y-m-d');
        $currentMonth = date('Y-m');

        if ($user->isSaaSFounder()) {
            // SaaS Owner Master HRMS Scope Across All Onboarded Tenant Companies
            $tenantCompanies = Company::withCount(['users', 'projects'])->get();
            $totalPlatformStaff = User::whereNotNull('company_id')->count();
            $todayPlatformCheckIns = Attendance::where('date', $today)->count();
            $pendingLeaveRequestsCount = LeaveRequest::where('status', 'pending')->count();
            $generatedSalarySlipsCount = SalarySlip::count();

            // All tenant rosters and leave applications for SaaS Founder review
            $todayRoster = Attendance::where('date', $today)->with(['user', 'company'])->get();
            $leaveRequests = LeaveRequest::with(['user', 'company', 'approver'])->orderByDesc('created_at')->take(20)->get();
            $salarySlips = SalarySlip::with(['user', 'company'])->orderByDesc('created_at')->take(20)->get();
            $staffUsers = User::all();
            $myTodayAttendance = null;
            $monthlyAttendance = collect();

            return view('hrms.index', compact(
                'user',
                'tenantCompanies',
                'totalPlatformStaff',
                'todayPlatformCheckIns',
                'pendingLeaveRequestsCount',
                'generatedSalarySlipsCount',
                'todayRoster',
                'leaveRequests',
                'salarySlips',
                'staffUsers',
                'today',
                'myTodayAttendance',
                'monthlyAttendance'
            ));
        }

        // Builder Tenant Staff / Admin Scope
        $myTodayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $todayRoster = Attendance::where('company_id', $user->company_id)
            ->where('date', $today)
            ->with('user')
            ->get();

        $leaveRequests = LeaveRequest::where('company_id', $user->company_id)
            ->with(['user', 'approver'])
            ->orderByDesc('created_at')
            ->get();

        $salarySlips = SalarySlip::where('company_id', $user->company_id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $staffUsers = User::where('company_id', $user->company_id)->get();

        // Fetch monthly attendance history for visual Calendar Grid
        $monthlyAttendance = Attendance::where('user_id', $user->id)
            ->where('date', 'like', $currentMonth . '%')
            ->get()
            ->keyBy('date');

        return view('hrms.index', compact(
            'user',
            'myTodayAttendance',
            'todayRoster',
            'leaveRequests',
            'salarySlips',
            'staffUsers',
            'today',
            'currentMonth',
            'monthlyAttendance'
        ));
    }

    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $today = date('Y-m-d');

        $attendance = Attendance::firstOrCreate(
            [
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'clock_in' => date('H:i:s'),
                'work_location' => $request->input('work_location', 'office'),
                'status' => 'present',
            ]
        );

        return redirect()->route('hrms.index')->with('success', 'Clocked in successfully for today!');
    }

    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $today = date('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            $attendance->update([
                'clock_out' => date('H:i:s'),
            ]);
        }

        return redirect()->route('hrms.index')->with('success', 'Clocked out successfully! Have a great evening.');
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

        return redirect()->route('hrms.index')->with('success', 'Leave application submitted for approval!');
    }

    public function updateLeaveStatus(Request $request, LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        if (!$user->isCompanyAdmin() && !$user->isManager() && !$user->isSaaSFounder()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $leaveRequest->update([
            'status' => $request->input('status', 'approved'),
            'approved_by_user_id' => $user->id,
        ]);

        return redirect()->route('hrms.index')->with('success', 'Leave request status updated!');
    }

    public function generateSalarySlip(Request $request)
    {
        $user = auth()->user();

        if (!$user->isCompanyAdmin() && !$user->isManager() && !$user->isSaaSFounder()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

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

        return redirect()->route('hrms.index')->with('success', 'Monthly salary slip generated successfully!');
    }
}
