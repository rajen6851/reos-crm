@extends('layouts.reos')

@section('title', 'HRMS & Staff Attendance System – REOS')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header Banner & Breadcrumb -->
    <div class="reos-card p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">HRMS & Attendance</span>
            </div>
            <h1 class="page-heading text-2xl">
                @if(auth()->user()->isSaaSFounder())
                    SaaS Founder Master HRMS Command Center
                @else
                    HRMS & Staff Attendance Command Center
                @endif
            </h1>
            <p class="body-text text-xs mt-0.5">
                @if(auth()->user()->isSaaSFounder())
                    Platform-wide HR monitoring, company tenant staff check-ins, leave approvals, and payroll overview.
                @else
                    Track daily staff clock-in/out, field visit tagging, monthly attendance calendar grid, leave workflows, and salary slips.
                @endif
            </p>
        </div>

        <div class="flex items-center space-x-3 shrink-0">
            @if(!auth()->user()->isSaaSFounder())
            <button onclick="document.getElementById('applyLeaveModal').classList.remove('hidden')" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-[#0F172A] btn-text text-xs rounded-xl border border-[#E2E8F0] shadow-2xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-calendar-plus text-[#4F46E5] text-xs"></i>
                <span>Apply for Leave</span>
            </button>
            @endif

            @if(auth()->user()->isCompanyAdmin() || auth()->user()->isSaaSFounder())
            <button onclick="document.getElementById('generateSalarySlipModal').classList.remove('hidden')" class="px-5 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-file-invoice-dollar text-xs"></i>
                <span>+ Generate Salary Slip</span>
            </button>
            @endif
        </div>
    </div>

    @if(auth()->user()->isSaaSFounder())
    <!-- SAAS FOUNDER MASTER HR OVERVIEW CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="reos-card p-5 bg-white space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <span>Platform Total Staff</span>
                <i class="fa-solid fa-users-gear text-indigo-600"></i>
            </div>
            <div class="text-3xl font-extrabold text-[#0F172A] font-mono">{{ $totalPlatformStaff }}</div>
            <p class="body-text text-[11px]">Across all onboarded builder tenants</p>
        </div>

        <div class="reos-card p-5 bg-white space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <span>Today's Live Check-ins</span>
                <i class="fa-solid fa-stopwatch text-[#059669]"></i>
            </div>
            <div class="text-3xl font-extrabold text-[#059669] font-mono">{{ $todayPlatformCheckIns }}</div>
            <p class="body-text text-[11px]">Active staff on field or desk today</p>
        </div>

        <div class="reos-card p-5 bg-white space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <span>Pending Approvals</span>
                <i class="fa-solid fa-clock-rotate-left text-[#D97706]"></i>
            </div>
            <div class="text-3xl font-extrabold text-[#D97706] font-mono">{{ $pendingLeaveRequestsCount }}</div>
            <p class="body-text text-[11px]">Leave requests awaiting review</p>
        </div>

        <div class="reos-card p-5 bg-white space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <span>Generated Salary Slips</span>
                <i class="fa-solid fa-file-invoice-dollar text-[#DC2626]"></i>
            </div>
            <div class="text-3xl font-extrabold text-[#DC2626] font-mono">{{ $generatedSalarySlipsCount }}</div>
            <p class="body-text text-[11px]">Monthly payroll slips issued</p>
        </div>
    </div>

    <!-- TENANT BUILDER COMPANIES HR SUMMARY TABLE -->
    <div class="reos-card p-6 bg-white space-y-4">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
            <div>
                <h2 class="section-heading text-lg">Builder Tenant Companies HR Matrix</h2>
                <p class="body-text text-xs">Staff strength and active check-ins per tenant company</p>
            </div>
            <span class="px-2.5 py-0.5 text-xs font-mono font-bold rounded-full bg-indigo-50 text-[#4F46E5] border border-indigo-200">
                {{ $tenantCompanies->count() }} Builder Companies
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
            <table class="w-full text-left text-xs text-[#0F172A]">
                <thead class="bg-[#F8FAFC] text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Builder Company</th>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Staff Count</th>
                        <th class="py-3 px-4">Projects Count</th>
                        <th class="py-3 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @foreach($tenantCompanies as $tc)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-[#0F172A] flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-[#4F46E5] border border-indigo-200 flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($tc->name, 0, 1)) }}
                            </div>
                            <span>{{ $tc->name }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-[#4F46E5]">{{ $tc->code }}</td>
                        <td class="py-3 px-4 font-mono font-bold">{{ $tc->users_count }} Users</td>
                        <td class="py-3 px-4 font-mono font-bold">{{ $tc->projects_count }} Projects</td>
                        <td class="py-3 px-4 text-right">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200">Active Tenant</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else

    <!-- BUILDER TENANT DAILY CLOCK-IN & ATTENDANCE ROSTER -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Daily Clock-In / Clock-Out Hero Widget (5 cols) -->
        <div class="lg:col-span-5 bg-[#0F172A] text-white p-6 rounded-2xl shadow-md flex flex-col justify-between space-y-6">
            <div>
                <div class="flex items-center justify-between">
                    <span class="label-text text-indigo-300">My Daily Attendance</span>
                    <span class="text-xs font-bold font-mono text-slate-300 bg-slate-800 px-3 py-1 rounded-full border border-slate-700">
                        {{ date('D, M j, Y') }}
                    </span>
                </div>

                <div class="mt-4 space-y-1">
                    <div class="text-xs text-slate-400 font-semibold">Today's Shift Status</div>
                    @if($myTodayAttendance && $myTodayAttendance->clock_in)
                        <div class="text-2xl font-extrabold text-emerald-400 font-mono flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>Clocked In: {{ date('h:i A', strtotime($myTodayAttendance->clock_in)) }}</span>
                        </div>
                        <div class="text-xs text-slate-300 font-medium">Work Mode: <strong class="uppercase text-indigo-300">{{ str_replace('_', ' ', $myTodayAttendance->work_location) }}</strong></div>
                    @else
                        <div class="text-2xl font-extrabold text-amber-400 font-mono">Not Clocked In Yet</div>
                        <div class="text-xs text-slate-400">Click below to start today's attendance log</div>
                    @endif
                </div>
            </div>

            <!-- Action Form -->
            <div class="pt-4 border-t border-slate-800 space-y-3">
                @if(!$myTodayAttendance || !$myTodayAttendance->clock_in)
                <form action="{{ route('hrms.clock-in') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="label-text text-slate-300 text-[11px] mb-1">Work Location Tag</label>
                        <select name="work_location" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-[#4F46E5]">
                            <option value="office">Office Desk</option>
                            <option value="field_visit">Property Site Visit / Field</option>
                            <option value="wfh">Work From Home</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 bg-[#059669] hover:bg-emerald-700 text-white btn-text rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                        <i class="fa-solid fa-stopwatch text-sm"></i>
                        <span>Start Shift (Clock-In)</span>
                    </button>
                </form>
                @else
                <form action="{{ route('hrms.clock-out') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white btn-text rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>
                        <span>End Shift (Clock-Out)</span>
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Today's Attendance Roster (7 cols) -->
        <div class="lg:col-span-7 reos-card p-6 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
                <div>
                    <h2 class="section-heading text-base">Today's Staff Check-in Roster</h2>
                    <p class="body-text text-xs">Live check-in log for {{ date('M j, Y') }}</p>
                </div>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-50 text-[#4F46E5] border border-indigo-200">
                    {{ $todayRoster->count() }} Checked In
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                <table class="w-full text-left text-xs text-[#0F172A]">
                    <thead class="bg-[#F8FAFC] text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-2.5 px-3">Staff Member</th>
                            <th class="py-2.5 px-3">Location</th>
                            <th class="py-2.5 px-3">Clock In</th>
                            <th class="py-2.5 px-3">Clock Out</th>
                            <th class="py-2.5 px-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($todayRoster as $att)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-3 font-semibold text-[#0F172A] flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-50 text-[#4F46E5] font-bold text-[10px] flex items-center justify-center border border-indigo-100">
                                    {{ strtoupper(substr($att->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <span class="table-text">{{ $att->user->name ?? 'Staff User' }}</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-[#0F172A] border border-[#E2E8F0] uppercase">
                                    {{ str_replace('_', ' ', $att->work_location) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-emerald-700 font-bold">
                                {{ $att->clock_in ? date('h:i A', strtotime($att->clock_in)) : '-' }}
                            </td>
                            <td class="py-2.5 px-3 font-mono text-rose-700 font-bold">
                                {{ $att->clock_out ? date('h:i A', strtotime($att->clock_out)) : 'Active' }}
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200">Present</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-xs text-slate-400 font-medium">No staff members have clocked in today yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MONTHLY ATTENDANCE VISUAL CALENDAR GRID (Visualizing Monthly Present/Leaves) -->
    <div class="reos-card p-6 bg-white space-y-4">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
            <div>
                <h2 class="section-heading text-base flex items-center space-x-2">
                    <i class="fa-regular fa-calendar-days text-[#4F46E5]"></i>
                    <span>My Monthly Attendance Visual Calendar Grid</span>
                </h2>
                <p class="body-text text-xs">Visual log for <strong class="text-[#0F172A]">{{ date('F Y') }}</strong></p>
            </div>

            <!-- Legend Pills -->
            <div class="hidden sm:flex items-center space-x-2 text-[11px] font-semibold">
                <span class="px-2 py-0.5 rounded bg-emerald-50 text-[#059669] border border-emerald-200">Present</span>
                <span class="px-2 py-0.5 rounded bg-indigo-50 text-[#4F46E5] border border-indigo-200">Field Visit</span>
                <span class="px-2 py-0.5 rounded bg-amber-50 text-[#D97706] border border-amber-200">On Leave</span>
                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 border border-slate-200">- Off / Pending</span>
            </div>
        </div>

        @php
            $daysInMonth = date('t');
            $startDayOfWeek = date('w', strtotime(date('Y-m-01')));
        @endphp
        <div class="grid grid-cols-7 gap-2 text-center text-xs">
            <div class="font-extrabold text-[#64748B] py-1 text-[11px]">SUN</div>
            <div class="font-extrabold text-[#64748B] py-1 text-[11px]">MON</div>
            <div class="font-extrabold text-[#64748B] py-1 text-[11px]">TUE</div>
            <div class="font-extrabold text-[#64748B] py-1 text-[11px]">WED</div>
            <div class="font-extrabold text-[#64748B] py-1 text-[11px]">THU</div>
            <div class="font-extrabold text-[#64748B] py-1 text-[11px]">FRI</div>
            <div class="font-extrabold text-[#64748B] py-1 text-[11px]">SAT</div>

            <!-- Empty slots before 1st day -->
            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="p-2 rounded-xl bg-slate-50/50 border border-transparent"></div>
            @endfor

            <!-- Month Days 1 to 31 -->
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr = sprintf('%s-%02d', date('Y-m'), $day);
                    $attRecord = $monthlyAttendance[$dateStr] ?? null;
                    $isToday = ($dateStr === date('Y-m-d'));
                @endphp
                <div class="p-2.5 rounded-xl border flex flex-col items-center justify-between min-h-[54px] transition {{ $isToday ? 'ring-2 ring-[#4F46E5] bg-indigo-50/30' : 'bg-white' }} {{ $attRecord ? 'border-emerald-200' : 'border-[#E2E8F0]' }}">
                    <span class="font-bold font-mono text-[11px] {{ $isToday ? 'text-[#4F46E5]' : 'text-[#0F172A]' }}">{{ $day }}</span>

                    @if($attRecord)
                        @if($attRecord->work_location === 'field_visit')
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-[#4F46E5] border border-indigo-200">Field</span>
                        @elseif($attRecord->work_location === 'wfh')
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-200">WFH</span>
                        @else
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-[#059669] border border-emerald-200">{{ date('h:i A', strtotime($attRecord->clock_in)) }}</span>
                        @endif
                    @else
                        <span class="text-[10px] text-slate-300 font-mono">-</span>
                    @endif
                </div>
            @endfor
        </div>
    </div>
    @endif

    <!-- Row 2: Leave Requests Workflow & Payroll Salary Slips Directory -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Leave Requests Table (6 cols) -->
        <div class="lg:col-span-6 reos-card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
                <div>
                    <h2 class="section-heading text-base">Leave Applications</h2>
                    <p class="body-text text-xs">Staff leave requests & manager approval status</p>
                </div>
                @if(!auth()->user()->isSaaSFounder())
                <button onclick="document.getElementById('applyLeaveModal').classList.remove('hidden')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] btn-text text-xs rounded-xl border border-indigo-200 transition">
                    + Apply
                </button>
                @endif
            </div>

            <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                <table class="w-full text-left text-xs text-[#0F172A]">
                    <thead class="bg-[#F8FAFC] text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-2.5 px-3">Applicant</th>
                            <th class="py-2.5 px-3">Type / Dates</th>
                            <th class="py-2.5 px-3">Days</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($leaveRequests as $lr)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-3 font-semibold text-[#0F172A]">
                                <div class="table-text font-bold">{{ $lr->user->name ?? 'User' }}</div>
                                @if(auth()->user()->isSaaSFounder())
                                    <div class="text-[10px] text-indigo-600 font-mono">{{ $lr->company->name ?? '' }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-[#4F46E5] uppercase text-[10px]">{{ $lr->leave_type }}</div>
                                <div class="text-[10px] text-[#64748B] font-mono">{{ $lr->start_date }} to {{ $lr->end_date }}</div>
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold">{{ $lr->total_days }}d</td>
                            <td class="py-2.5 px-3">
                                @if($lr->status === 'approved')
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200">Approved</span>
                                @elseif($lr->status === 'rejected')
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-rose-50 text-[#DC2626] border border-rose-200">Rejected</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 text-[#D97706] border border-amber-200">Pending</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                @if($lr->status === 'pending' && (auth()->user()->isCompanyAdmin() || auth()->user()->isManager() || auth()->user()->isSaaSFounder()))
                                <div class="flex items-center justify-end space-x-1">
                                    <form action="{{ route('hrms.leave-requests.status', $lr->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-[10px]"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                    <form action="{{ route('hrms.leave-requests.status', $lr->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="px-2 py-0.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded text-[10px]">✕</button>
                                    </form>
                                </div>
                                @else
                                <span class="text-[10px] text-slate-400 font-mono">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-xs text-slate-400 font-medium">No leave requests submitted yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Monthly Salary Slips Directory (6 cols) -->
        <div class="lg:col-span-6 reos-card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
                <div>
                    <h2 class="section-heading text-base">Salary Slips & Payroll</h2>
                    <p class="body-text text-xs">Generated monthly pay slips & commission payouts</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                <table class="w-full text-left text-xs text-[#0F172A]">
                    <thead class="bg-[#F8FAFC] text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-2.5 px-3">Staff Member</th>
                            <th class="py-2.5 px-3">Month</th>
                            <th class="py-2.5 px-3">Net Salary</th>
                            <th class="py-2.5 px-3 text-right">Action / Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @forelse($salarySlips as $slip)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-3 font-semibold text-[#0F172A]">
                                <div class="table-text font-bold">{{ $slip->user->name ?? 'User' }}</div>
                                @if(auth()->user()->isSaaSFounder())
                                    <div class="text-[10px] text-indigo-600 font-mono">{{ $slip->company->name ?? '' }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-[#4F46E5]">{{ $slip->month }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-[#059669]">₹{{ number_format($slip->net_salary) }}</td>
                            <td class="py-2.5 px-3 text-right">
                                <a href="{{ route('hrms.salary-slips.show', $slip->id) }}" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-[#047857] font-bold text-[10px] rounded border border-emerald-200 transition inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-file-invoice"></i>
                                    <span>View Payslip</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-xs text-slate-400 font-medium">No salary slips generated yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Apply for Leave -->
<div id="applyLeaveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-6 border border-[#E2E8F0] shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-[#E2E8F0]">
            <h3 class="section-heading text-base">Apply for Staff Leave</h3>
            <button onclick="document.getElementById('applyLeaveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form action="{{ route('hrms.leave-requests.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="form-label">Leave Type *</label>
                <select name="leave_type" required class="form-input">
                    <option value="casual">Casual Leave (CL)</option>
                    <option value="sick">Sick Leave (SL)</option>
                    <option value="earned">Earned Leave (EL)</option>
                    <option value="loss_of_pay">Loss of Pay (LOP)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" required class="form-input">
                </div>
                <div>
                    <label class="form-label">End Date *</label>
                    <input type="date" name="end_date" required class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Reason for Leave</label>
                <textarea name="reason" rows="2" class="form-input" placeholder="Enter reason..."></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                <button type="button" onclick="document.getElementById('applyLeaveModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] btn-text rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Generate Salary Slip -->
<div id="generateSalarySlipModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white max-w-md w-full rounded-3xl p-6 border border-[#E2E8F0] shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-[#E2E8F0]">
            <h3 class="section-heading text-base">Generate Salary Slip</h3>
            <button onclick="document.getElementById('generateSalarySlipModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form action="{{ route('hrms.salary-slips.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="form-label">Select Staff Member *</label>
                <select name="user_id" required class="form-input">
                    @foreach($staffUsers as $su)
                        <option value="{{ $su->id }}">{{ $su->name }} ({{ $su->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Month *</label>
                <input type="month" name="month" required value="{{ date('Y-m') }}" class="form-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Basic Salary (₹) *</label>
                    <input type="number" name="basic_salary" required value="35000" class="form-input">
                </div>
                <div>
                    <label class="form-label">Allowances (₹)</label>
                    <input type="number" name="allowances" value="5000" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Sales Commission (₹)</label>
                    <input type="number" name="commission_earned" value="10000" class="form-input">
                </div>
                <div>
                    <label class="form-label">Deductions (₹)</label>
                    <input type="number" name="deductions" value="2000" class="form-input">
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                <button type="button" onclick="document.getElementById('generateSalarySlipModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-[#0F172A] btn-text rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#059669] hover:bg-emerald-700 text-white btn-text rounded-xl shadow-xs">Generate Salary Slip</button>
            </div>
        </form>
    </div>
</div>
@endsection
