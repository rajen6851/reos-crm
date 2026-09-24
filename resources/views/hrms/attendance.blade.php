@extends('layouts.reos')

@section('title', 'Attendance & Time Tracking – UrbanProperty')

@section('content')
<div class="space-y-6 pb-12 font-sans">

    {{-- Clock-In Widget (non-managers only) --}}
    @if(!auth()->user()->isSaaSFounder() && !auth()->user()->isCompanyFounder() && !auth()->user()->isDirector() && !auth()->user()->isCompanyAdmin())
    <div class="bg-gradient-to-r from-emerald-800 to-teal-900 text-white p-6 rounded-3xl shadow-lg flex flex-col justify-between space-y-6 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white rounded-full blur-3xl opacity-10"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-extrabold text-emerald-100 uppercase tracking-wider">My Daily Shift</span>
                <span class="text-xs font-bold font-mono text-emerald-50 bg-black/20 px-3 py-1 rounded-full border border-white/10">{{ date('D, M j, Y') }}</span>
            </div>
            <div class="mt-4 space-y-1">
                @if($myTodayAttendance && $myTodayAttendance->clock_in)
                    <div class="text-3xl font-black text-white font-mono flex items-center space-x-3">
                        <span class="relative flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-white"></span>
                        </span>
                        <span>{{ date('h:i A', strtotime($myTodayAttendance->clock_in)) }}</span>
                    </div>
                    <div class="text-xs text-emerald-100 font-semibold mt-2 bg-black/20 inline-block px-3 py-1 rounded-full border border-white/10">
                        Mode: <span class="uppercase text-white">{{ str_replace('_', ' ', $myTodayAttendance->work_location) }}</span>
                    </div>
                @else
                    <div class="text-3xl font-black text-amber-300 font-mono">Off the clock</div>
                    <div class="text-xs text-emerald-100 font-semibold mt-1">Click below to start today's attendance.</div>
                @endif
            </div>
        </div>
        <div class="pt-4 border-t border-white/10 space-y-3 relative z-10">
            @if(!$myTodayAttendance || !$myTodayAttendance->clock_in)
            <form id="web-clock-in-form" action="{{ route('hrms.clock-in') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="latitude" id="clock-in-lat">
                <input type="hidden" name="longitude" id="clock-in-lon">
                <select name="work_location" class="w-full bg-black/20 border border-white/20 rounded-xl p-3 text-sm font-semibold text-white focus:outline-none focus:border-white transition">
                    <option value="office" class="text-slate-900">Office Desk</option>
                    <option value="field_visit" class="text-slate-900">Property Site Visit / Field</option>
                    <option value="wfh" class="text-slate-900">Work From Home</option>
                </select>
                <button type="button" id="clock-in-btn" class="w-full py-3.5 bg-white hover:bg-emerald-50 text-emerald-900 font-black rounded-xl shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-stopwatch"></i>
                    <span>Start Shift (Clock-In)</span>
                </button>
            </form>
            <script>
                document.getElementById('clock-in-btn').addEventListener('click', function() {
                    const btn = this;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Getting Location...</span>';
                    btn.disabled = true;
                    if (!navigator.geolocation) {
                        document.getElementById('web-clock-in-form').submit(); return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (p) => {
                            document.getElementById('clock-in-lat').value = p.coords.latitude;
                            document.getElementById('clock-in-lon').value = p.coords.longitude;
                            document.getElementById('web-clock-in-form').submit();
                        },
                        (error) => { 
                            alert("Location access is required for attendance. Please enable location services in your browser/device settings and try again.");
                            btn.innerHTML = '<i class="fa-solid fa-stopwatch"></i> <span>Start Shift (Clock-In)</span>';
                            btn.disabled = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                });
            </script>
            @elseif($myTodayAttendance && !$myTodayAttendance->clock_out)
            <form action="{{ route('hrms.clock-out') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3.5 bg-rose-500 hover:bg-rose-600 text-white font-black rounded-xl shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>End Shift (Clock-Out)</span>
                </button>
            </form>
            @else
            <div class="text-white font-bold font-mono text-sm bg-black/20 p-3 rounded-xl text-center border border-white/10">
                <i class="fa-solid fa-check-circle mr-2 text-emerald-300"></i> Shift Completed for Today. See you tomorrow!
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Global Date Filter -->
    <div class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-slate-100">
        <h3 class="text-sm font-bold text-slate-800">Showing logs for: <span class="text-emerald-600">{{ date('d M Y', strtotime($today)) }}</span></h3>
        <form method="GET" action="{{ route('hrms.attendance') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $today }}" max="{{ date('Y-m-d') }}" class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:border-emerald-500 font-medium">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition">Filter</button>
        </form>
    </div>

    <!-- 1. Daily Summary List -->
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-slate-800">Daily Summary List</h2>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" placeholder="Search" class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-emerald-500 w-48">
                </div>
                <button class="bg-[#3BB75E] hover:bg-[#2fa04c] text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                    Customs view <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>
                <button class="border border-slate-200 text-slate-600 bg-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 hover:bg-slate-50 transition">
                    Monthly <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>
                <div class="flex border border-slate-200 rounded-lg overflow-hidden">
                    <button class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-600 border-r border-slate-200 transition">
                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                    </button>
                    <button class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-600 transition">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-[#F8F9FA] text-slate-700 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap">Department</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Employee</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Male</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Female</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Present</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Present%</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Late</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Late%</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Absent</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Absent%</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Leave</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Leave%</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Offday</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Offday%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $tEmp = 0; $tMale = 0; $tFemale = 0; $tPresent = 0;
                        $tLate = 0; $tAbsent = 0; $tLeave = 0; $tOffday = 0;
                    @endphp
                    @forelse($departmentStats as $stat)
                    @php
                        $tEmp += $stat->employees; $tMale += $stat->male; $tFemale += $stat->female;
                        $tPresent += $stat->present; $tLate += $stat->late; $tAbsent += $stat->absent;
                        $tLeave += $stat->leave; $tOffday += $stat->offday;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-4 font-medium text-[#3BB75E] border-r border-slate-100 whitespace-nowrap"><a href="#" class="underline underline-offset-2">{{ $stat->name }}</a></td>
                        <td class="px-4 py-4 text-center text-slate-700 font-semibold border-r border-slate-100">{{ $stat->employees }}</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100">{{ $stat->male }}</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100">{{ $stat->female }}</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100">{{ $stat->employees > 0 ? $stat->employees : 0 }}</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100 font-medium">{{ $stat->present_pct }}%</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100">{{ $stat->late }}</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100 font-medium">{{ $stat->late_pct }}%</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100">{{ $stat->absent }}</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100 font-medium">{{ $stat->absent_pct }}%</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100">{{ $stat->leave }}</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100 font-medium">{{ $stat->leave_pct }}%</td>
                        <td class="px-4 py-4 text-center text-slate-700 border-r border-slate-100">{{ $stat->offday }}</td>
                        <td class="px-4 py-4 text-center text-slate-700 font-medium">{{ $stat->offday_pct }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="14" class="px-4 py-8 text-center text-slate-500">No department data found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-white border-t border-slate-200 text-[11px] font-bold text-slate-600">
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">Total Department : {{ $departmentStats->count() }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">T. Emp : {{ $tEmp }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">T. Male : {{ $tMale }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">T. Female : {{ $tFemale }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap" colspan="2">T. Present : {{ $tPresent }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap" colspan="2">T. Late : {{ $tLate }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap" colspan="2">T. Absent : {{ $tAbsent }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap" colspan="2">T. Leave : {{ $tLeave }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap" colspan="2">T. Offday : {{ $tOffday }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- 2. Daily Attendance List -->
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-slate-800">Daily Attendance List</h2>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" placeholder="Search" class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-emerald-500 w-48">
                </div>
                <button class="bg-[#3BB75E] hover:bg-[#2fa04c] text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                    Customs view <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>
                <button class="border border-slate-200 text-slate-600 bg-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 hover:bg-slate-50 transition">
                    Monthly <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>
                <div class="flex border border-slate-200 rounded-lg overflow-hidden">
                    <button class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-600 border-r border-slate-200 transition">
                        <i class="fa-solid fa-arrow-up-from-bracket"></i>
                    </button>
                    <button class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-600 transition">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-white text-slate-800 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap w-16 text-center">ID</th>
                        <th class="px-4 py-4 whitespace-nowrap">Employee Name</th>
                        <th class="px-4 py-4 whitespace-nowrap">Status</th>
                        <th class="px-4 py-4 whitespace-nowrap">Check In</th>
                        <th class="px-4 py-4 whitespace-nowrap">Location</th>
                        <th class="px-4 py-4 whitespace-nowrap">Check Out</th>
                        <th class="px-4 py-4 whitespace-nowrap">Shift</th>
                        <th class="px-4 py-4 whitespace-nowrap text-center">Worked</th>
                        <th class="px-6 py-4 whitespace-nowrap">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dailyAttendanceList as $index => $emp)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-center text-slate-500 font-medium border-r border-slate-100">{{ $index + 1 }}</td>
                        <td class="px-4 py-4 border-r border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ substr($emp->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="text-slate-800 font-medium">{{ $emp->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $emp->role }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 border-r border-slate-100">
                            @if($emp->status === 'Present')
                                <span class="text-slate-700 font-medium">{{ $emp->status }}</span>
                            @elseif($emp->status === 'Absent')
                                <span class="text-rose-500 font-medium">{{ $emp->status }}</span>
                            @elseif($emp->status === 'Late')
                                <span class="text-amber-500 font-medium">{{ $emp->status }}</span>
                            @else
                                <span class="text-slate-500 font-medium">{{ $emp->status }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 border-r border-slate-100 font-medium text-slate-700">{{ $emp->check_in }}</td>
                        <td class="px-4 py-4 border-r border-slate-100 font-medium text-slate-700">
                            {{ $emp->location }}
                            @if($emp->lat && $emp->lon)
                            <a href="https://maps.google.com/?q={{ $emp->lat }},{{ $emp->lon }}" target="_blank" class="text-blue-500 hover:underline ml-1"><i class="fa-solid fa-map-location-dot"></i></a>
                            @endif
                        </td>
                        <td class="px-4 py-4 border-r border-slate-100 font-medium text-slate-700">{{ $emp->check_out }}</td>
                        <td class="px-4 py-4 border-r border-slate-100">
                            <div class="bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded text-[11px] font-semibold inline-block">
                                {{ $emp->shift }}
                                <div class="text-[9px] text-indigo-400 mt-0.5"><i class="fa-regular fa-clock"></i> 9 hr Shift : A</div>
                            </div>
                        </td>
                        <td class="px-4 py-4 border-r border-slate-100 text-center font-medium text-slate-700">{{ $emp->worked }}</td>
                        <td class="px-6 py-4 text-slate-500 text-[13px]">{{ $emp->remarks }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-500">No attendance data for today.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
