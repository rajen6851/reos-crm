@extends('layouts.reos')

@section('title', 'Sales Executive Workspace – REOS')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Top Hero Banner: Humanized ERP Greeting & Action Bar -->
    <div class="erp-card p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden bg-gradient-to-br from-white via-slate-50/50 to-amber-50/30">
        <div class="space-y-2 z-10">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-800 border border-amber-500/20 tracking-wide uppercase">
                <span class="w-2 h-2 rounded-full bg-amber-600 animate-ping"></span>
                <span>Sales Executive Workspace</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center space-x-2">
                <span>Good {{ date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') }}, {{ strtok($user->name, ' ') }}</span>
                <i class="fa-solid fa-hand-wave text-amber-500 text-2xl"></i>
            </h1>
            <p class="text-xs md:text-sm text-slate-500 max-w-2xl leading-relaxed">
                You have <strong class="text-amber-600 font-bold font-mono">{{ $myLeadsCount }}</strong> assigned customer leads & 
                <strong class="text-sky-600 font-bold font-mono">{{ $mySiteVisitsCount }}</strong> scheduled site visits.
            </p>
        </div>

        <div class="flex items-center space-x-3 shrink-0 z-10">
            <a href="{{ route('leads.index') }}" class="px-4.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>+ Add Customer Lead</span>
            </a>
        </div>
    </div>

    <!-- Executive Stat Cards with Micro-Trends (Clickable Widgets) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Assigned Queue -->
        <a href="{{ route('leads.index') }}" class="erp-card p-5 space-y-2 hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer block">
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Assigned Queue</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-base border border-amber-500/20"><i class="fa-solid fa-list-check"></i></div>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($myLeadsCount) }} Leads</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                <i class="fa-solid fa-clock text-[9px]"></i>
                <span>Active Customer Leads →</span>
            </div>
        </a>

        <!-- Card 2: Site Visits -->
        <a href="{{ route('leads.index', ['status' => 'site_visit']) }}" class="erp-card p-5 space-y-2 hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer block">
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Site Visits</span>
                <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center font-bold text-base border border-sky-500/20"><i class="fa-solid fa-car"></i></div>
            </div>
            <div class="text-2xl font-bold text-sky-700 font-mono">{{ number_format($mySiteVisitsCount) }} Visits</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-200">
                <i class="fa-solid fa-location-dot text-[9px]"></i>
                <span>Scheduled Appointments →</span>
            </div>
        </a>

        <!-- Card 3: Converted Bookings -->
        <a href="{{ route('leads.index', ['status' => 'converted']) }}" class="erp-card p-5 space-y-2 hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer block">
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Converted Bookings</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-base border border-emerald-500/20"><i class="fa-solid fa-trophy"></i></div>
            </div>
            <div class="text-2xl font-bold text-emerald-700 font-mono">{{ number_format($myConvertedCount) }} Booked</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                <i class="fa-solid fa-circle-check text-[9px]"></i>
                <span>Closed Deals →</span>
            </div>
        </a>
    </div>

    <!-- Assigned Leads Queue Table -->
    <div class="erp-card p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Your Assigned Customer Leads</h2>
                <p class="text-xs text-slate-500">Update pipeline stage, log calls, and schedule follow-ups</p>
            </div>
            <a href="{{ route('leads.index') }}" class="px-3.5 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs rounded-xl border border-indigo-200/80 transition">
                Open Pipeline →
            </a>
        </div>

        @if($myLeads->isEmpty())
            <div class="p-8 text-center text-slate-400 text-xs rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                <div class="text-2xl text-slate-400"><i class="fa-solid fa-list-check"></i></div>
                <div class="font-bold text-slate-700">No leads currently assigned in your queue.</div>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-slate-200/80">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4">Customer Lead</th>
                            <th class="py-3.5 px-4">Phone</th>
                            <th class="py-3.5 px-4">Project</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($myLeads as $lead)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <a href="{{ route('leads.show', $lead->id) }}" class="hover:underline flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-500/10 text-indigo-700 font-bold text-xs flex items-center justify-center border border-indigo-500/20 shrink-0">
                                        {{ strtoupper(substr($lead->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ $lead->first_name }} {{ $lead->last_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $lead->lead_code }}</div>
                                    </div>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $lead->phone }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $lead->project->name ?? 'N/A' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full bg-slate-100 border border-slate-200 text-slate-800">
                                    {{ str_replace('_', ' ', $lead->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('leads.show', $lead->id) }}" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 transition">
                                    View →
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
