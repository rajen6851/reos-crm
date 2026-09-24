@extends('layouts.reos')

@section('title', 'Sales Executive Workspace')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Top Greeting Header & Date/Time Formatting -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0F172A] tracking-tight">
                Good {{ date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                {{ date('d M Y') }} | Indian (timezone formatting) &bull; <strong class="text-slate-900 font-semibold">{{ $myLeadsCount }}</strong> assigned customer leads &bull; <strong class="text-slate-900 font-semibold">{{ $mySiteVisitsCount }}</strong> site visits scheduled
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Segmented Period Selector -->
            @php $currentPeriod = request('period', 'all'); @endphp
            <div class="inline-flex items-center bg-[#E2E8F0]/70 p-1 rounded-lg text-xs font-semibold text-slate-600">
                <a href="{{ route('dashboard', ['period' => 'today']) }}" class="px-3 py-1.5 rounded-md transition {{ $currentPeriod === 'today' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'hover:text-slate-900' }}">Today</a>
                <a href="{{ route('dashboard', ['period' => 'this_week']) }}" class="px-3 py-1.5 rounded-md transition {{ $currentPeriod === 'this_week' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'hover:text-slate-900' }}">This Week</a>
                <a href="{{ route('dashboard', ['period' => 'this_month']) }}" class="px-3 py-1.5 rounded-md transition {{ $currentPeriod === 'this_month' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'hover:text-slate-900' }}">This Month</a>
                <a href="{{ route('dashboard', ['period' => 'all']) }}" class="px-3 py-1.5 rounded-md transition {{ $currentPeriod === 'all' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'hover:text-slate-900' }}">All Time</a>
            </div>

            <!-- Action Button -->
            <a href="{{ route('leads.index') }}" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Customer Lead</span>
            </a>
        </div>
    </div>

    <!-- Executive 3 KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Assigned Queue -->
        <a href="{{ route('leads.index') }}" class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-semibold uppercase tracking-wider">
                <span>Assigned Queue</span>
                <i class="fa-solid fa-list-check text-amber-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($myLeadsCount) }} Leads</div>
            <div class="text-xs font-semibold text-amber-600">Active Queue Inquiries</div>
        </a>

        <!-- Card 2: Site Visits -->
        <a href="{{ route('leads.index', ['status' => 'site_visit']) }}" class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-semibold uppercase tracking-wider">
                <span>Site Visits</span>
                <i class="fa-solid fa-location-dot text-blue-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-blue-700 font-mono">{{ number_format($mySiteVisitsCount) }} Visits</div>
            <div class="text-xs font-semibold text-blue-600">Scheduled Tours</div>
        </a>

        <!-- Card 3: Converted Bookings -->
        <a href="{{ route('leads.index', ['status' => 'converted']) }}" class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-2xs hover:border-slate-300 transition space-y-1 block">
            <div class="flex items-center justify-between text-xs text-slate-600 font-semibold uppercase tracking-wider">
                <span>Converted Bookings</span>
                <i class="fa-solid fa-trophy text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-700 font-mono">{{ number_format($myConvertedCount) }} Booked</div>
            <div class="text-xs font-semibold text-emerald-600">Closed Deals</div>
        </a>
    </div>

    <!-- Assigned Customer Leads Queue Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">Your Assigned Customer Leads</h2>
                <p class="text-xs text-slate-500 font-medium">Update pipeline stage, log call notes, and schedule follow-ups</p>
            </div>
            <a href="{{ route('leads.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs rounded-lg border border-slate-200 transition">
                Open Pipeline &rarr;
            </a>
        </div>

        @if($myLeads->isEmpty())
            <div class="p-8 text-center text-slate-400 text-xs bg-slate-50 border border-slate-200 m-4 rounded-xl space-y-1">
                <div class="text-2xl text-slate-400"><i class="fa-solid fa-list-check"></i></div>
                <div class="font-bold text-slate-700">No leads currently assigned in your queue.</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                        <tr>
                            <th class="p-3.5">Customer Lead</th>
                            <th class="p-3.5">Phone</th>
                            <th class="p-3.5">Project</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($myLeads as $lead)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3.5 font-bold text-slate-900">
                                <a href="{{ route('leads.show', $lead->id) }}" class="hover:underline flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center border border-blue-200 shrink-0">
                                        {{ strtoupper(substr($lead->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">{{ $lead->first_name }} {{ $lead->last_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $lead->lead_code }}</div>
                                    </div>
                                </a>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-slate-900">{{ $lead->phone }}</td>
                            <td class="p-3.5 font-semibold text-slate-700">{{ $lead->project->name ?? 'N/A' }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase rounded-full bg-slate-100 border border-slate-200 text-slate-800">
                                    {{ str_replace('_', ' ', $lead->status) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-right">
                                <a href="{{ route('leads.show', $lead->id) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded border border-slate-200 transition inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                    <span>View</span>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

