@extends('layouts.reos')

@section('title', 'Manager Command Center')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Top Greeting Header & Date/Time Formatting -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0F172A] tracking-tight">
                Good {{ date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                {{ date('d M Y') }} | Indian (timezone formatting) &bull; <strong class="text-slate-900 font-semibold">{{ $newLeadsCount }}</strong> new unassigned leads &bull; <strong class="text-slate-900 font-semibold">{{ $pendingBookings->count() }}</strong> pending booking approvals
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Segmented Period Selector -->
            <div class="inline-flex items-center bg-[#E2E8F0]/70 p-1 rounded-lg text-xs font-semibold text-slate-600">
                <button class="px-3 py-1.5 rounded-md bg-white text-slate-900 shadow-2xs font-bold transition">Today</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">This Week</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">This Month</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">Custom</button>
            </div>

            <!-- Action Buttons -->
            @can('manage-users')
            <a href="{{ route('users.index') }}" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-lg border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-users text-xs text-slate-500"></i>
                <span>Executives</span>
            </a>
            @endcan

            <a href="{{ route('calendar.index') }}" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-lg border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-regular fa-calendar-days text-xs text-emerald-600"></i>
                <span>Calendar</span>
            </a>

            <a href="{{ route('leads.index') }}" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Lead</span>
            </a>
        </div>
    </div>

    <!-- Manager 5 KPI Metrics Matrix Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- Metric 1: Total Leads -->
        <a href="{{ route('leads.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Total Pipeline</span>
                <i class="fa-solid fa-arrow-trend-up text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($totalLeads) }}</div>
            <div class="text-xs font-semibold text-emerald-600">(+12.5%)</div>
        </a>

        <!-- Metric 2: New Inquiries -->
        <a href="{{ route('leads.index', ['status' => 'new']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>New Inquiries</span>
                <i class="fa-solid fa-inbox text-indigo-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-indigo-600 font-mono">{{ number_format($newLeadsCount) }}</div>
            <div class="text-xs font-semibold text-indigo-600">Pending Assign</div>
        </a>

        <!-- Metric 3: Site Visits -->
        <a href="{{ route('leads.index', ['status' => 'site_visit']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Site Visits</span>
                <i class="fa-solid fa-location-dot text-amber-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-amber-700 font-mono">{{ number_format($siteVisitsCount) }}</div>
            <div class="text-xs font-semibold text-amber-600">Scheduled Tours</div>
        </a>

        <!-- Metric 4: Negotiation -->
        <a href="{{ route('leads.index', ['status' => 'negotiation']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Negotiations</span>
                <i class="fa-solid fa-file-invoice-dollar text-purple-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-purple-700 font-mono">{{ number_format($negotiationCount) }}</div>
            <div class="text-xs font-semibold text-purple-600">Cost Sheets Sent</div>
        </a>

        <!-- Metric 5: Booked Deals -->
        <a href="{{ route('bookings.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Booked Deals</span>
                <i class="fa-solid fa-trophy text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-700 font-mono">{{ number_format($convertedCount) }}</div>
            <div class="text-xs font-semibold text-emerald-600">Units Locked</div>
        </a>
    </div>

    <!-- PENDING UNIT BOOKING APPROVALS SECTION (If Any) -->
    @if($pendingBookings->isNotEmpty())
    <div class="bg-white p-5 rounded-xl border border-amber-300 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-amber-100 pb-3">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                <h2 class="text-sm font-bold text-amber-900">Pending Unit Booking Approvals</h2>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">
                {{ $pendingBookings->count() }} Locks Awaiting Review
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            @foreach($pendingBookings as $bk)
            <div class="p-4 rounded-lg bg-amber-50/50 border border-amber-200 space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <span class="font-mono font-bold text-indigo-700 text-xs">{{ $bk->booking_code }}</span>
                        <span class="font-mono font-bold text-emerald-700">â‚¹{{ number_format($bk->booking_amount) }} Token</span>
                    </div>
                    <div class="font-bold text-slate-900 text-sm">{{ $bk->customer_name }}</div>
                    <div class="text-xs text-slate-500">Unit {{ $bk->unit->unit_number ?? 'N/A' }} in {{ $bk->project->name ?? 'Project' }}</div>
                </div>

                <div class="pt-2.5 border-t border-amber-200/60 flex items-center justify-end space-x-2">
                    <form method="POST" action="{{ route('bookings.reject', $bk->id) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-white text-rose-700 hover:bg-rose-50 border border-rose-200 rounded-md font-bold text-xs transition cursor-pointer">
                            Reject
                        </button>
                    </form>
                    <form method="POST" action="{{ route('bookings.approve', $bk->id) }}">
                        @csrf
                        <button type="submit" class="px-3.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-bold text-xs transition shadow-2xs cursor-pointer flex items-center space-x-1">
                            <i class="fa-solid fa-check text-[10px]"></i>
                            <span>Approve Booking</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SALES EXECUTIVES PERFORMANCE MATRIX -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">Sales Team Executives Performance Leaderboard</h2>
                <p class="text-xs text-slate-500 font-medium">Active executive workload, assigned lead volume, & deal conversion ratio</p>
            </div>
            @can('manage-users')
            <a href="{{ route('users.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs rounded-lg border border-slate-200 transition">
                Manage Team Roster &rarr;
            </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            @forelse($salesExecutives as $exec)
            @php
                $ratio = $exec->total_assigned_leads > 0 ? round(($exec->converted_leads_count / $exec->total_assigned_leads) * 100, 1) : 0;
            @endphp
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 transition space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($exec->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-xs">{{ $exec->name }}</div>
                            <div class="text-[11px] text-slate-500 font-medium">{{ $exec->role->name ?? $exec->designation ?? 'Sales Executive' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200 text-center">
                        <div class="bg-white p-2 rounded-lg border border-slate-200">
                            <div class="text-sm font-bold font-mono text-slate-900">{{ $exec->total_assigned_leads }}</div>
                            <div class="text-[10px] text-slate-500 font-medium">Assigned</div>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-slate-200">
                            <div class="text-sm font-bold font-mono text-emerald-600">{{ $exec->converted_leads_count }}</div>
                            <div class="text-[10px] text-slate-500 font-medium">Booked</div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between text-[11px] border-t border-slate-200/60">
                    <span class="text-slate-500 font-semibold">Conversion Rate:</span>
                    <span class="font-bold font-mono text-emerald-600">{{ $ratio }}%</span>
                </div>
            </div>
            @empty
            <div class="col-span-4 p-6 text-center text-xs text-slate-500 rounded-xl bg-slate-50 border border-slate-200">
                No sales executives assigned to your company yet.
            </div>
            @endforelse
        </div>
    </div>

    <!-- RECENT CRM LEADS PIPELINE TABLE -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">Recent Leads Pipeline</h2>
                <p class="text-xs text-slate-500 font-medium">Real-time customer inquiry status & executive assignment</p>
            </div>
            <a href="{{ route('leads.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs rounded-lg border border-slate-200 transition">
                Open Full Pipeline &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                    <tr>
                        <th class="p-3">Customer Lead</th>
                        <th class="p-3">Contact Phone</th>
                        <th class="p-3">Interested Property</th>
                        <th class="p-3">Assigned Executive</th>
                        <th class="p-3">Stage Status</th>
                        <th class="p-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @foreach($recentLeads as $lead)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-3 font-bold text-slate-900">
                            <a href="{{ route('leads.show', $lead->id) }}" class="hover:underline flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200 shrink-0">
                                    {{ strtoupper(substr($lead->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">{{ $lead->first_name }} {{ $lead->last_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $lead->lead_code }}</div>
                                </div>
                            </a>
                        </td>
                        <td class="p-3 font-mono">
                            <div class="flex items-center space-x-1.5">
                                <span class="font-bold text-slate-900">{{ $lead->phone }}</span>
                                @if($lead->phone)
                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-bold hover:bg-emerald-100 transition">
                                    WA
                                </a>
                                @endif
                            </div>
                        </td>
                        <td class="p-3 font-semibold text-slate-700">{{ $lead->project->name ?? 'General Inquiry' }}</td>
                        <td class="p-3 font-semibold text-indigo-600">{{ $lead->assignedTo->name ?? 'Unassigned' }}</td>
                        <td class="p-3">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase rounded-full bg-slate-100 border border-slate-200 text-slate-800">
                                {{ str_replace('_', ' ', $lead->status) }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <a href="{{ route('leads.show', $lead->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded border border-slate-200 transition inline-flex items-center space-x-1">
                                <i class="fa-solid fa-eye text-[10px]"></i>
                                <span>View</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

