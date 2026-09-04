@extends('layouts.reos')

@section('title', "Broker {$broker->agency_name} – Profile & Ledger – REOS")

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header Navigation & Action Bar -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <a href="{{ route('brokers.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600">← Back to Brokers Directory</a>
                <span class="text-slate-300">•</span>
                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono font-bold text-[11px]">{{ $broker->broker_code }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ $broker->agency_name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Contact: <strong class="text-slate-800">{{ $broker->user->name ?? 'N/A' }}</strong> • Phone: <span class="font-mono text-slate-800 font-bold">{{ $broker->phone }}</span> • Email: <span class="font-mono text-slate-800">{{ $broker->email }}</span></p>
        </div>

        <div class="flex items-center space-x-2.5">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                Commission Rate: {{ $broker->commission_rate }}%
            </span>
        </div>
    </div>

    <!-- Metrics Cards Strip -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[11px] font-medium text-slate-500">Total Leads Submitted</span>
            <div class="text-xl font-bold font-mono text-slate-900 mt-0.5">{{ $broker->brokerLeads->count() }}</div>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[11px] font-medium text-slate-500">Converted Bookings</span>
            <div class="text-xl font-bold font-mono text-emerald-600 mt-0.5">
                {{ $broker->brokerLeads->whereIn('broker_visible_status', ['Booked', 'converted', 'BOOKED', 'CONVERTED'])->count() }}
            </div>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[11px] font-medium text-slate-500">Total Commission</span>
            <div class="text-xl font-bold font-mono text-amber-600 mt-0.5">₹{{ number_format($totalCommissions) }}</div>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[11px] font-medium text-slate-500">Approved Commission</span>
            <div class="text-xl font-bold font-mono text-emerald-600 mt-0.5">₹{{ number_format($approvedCommissions) }}</div>
        </div>
    </div>

    <!-- Submitted Leads Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-2xs space-y-4">
        <h2 class="text-base font-bold text-slate-900">Submitted Customers & Leads History</h2>

        @if($broker->brokerLeads->isEmpty())
            <div class="p-4 text-center text-xs text-slate-400 font-medium bg-slate-50 rounded-xl border border-slate-200">
                No leads submitted by this broker yet.
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/80 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Lead Code</th>
                            <th class="py-3 px-4">Customer Name</th>
                            <th class="py-3 px-4">Phone</th>
                            <th class="py-3 px-4">Project</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($broker->brokerLeads as $bl)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-700">{{ $bl->lead->lead_code ?? 'N/A' }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $bl->lead->first_name ?? '' }} {{ $bl->lead->last_name ?? '' }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-800">{{ $bl->lead->phone ?? 'N/A' }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $bl->project->name ?? 'N/A' }}</td>
                            <td class="py-3.5 px-4 font-bold">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 border border-slate-200 text-slate-800">
                                    {{ $bl->broker_visible_status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Commission Payout Ledger -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-2xs space-y-4">
        <h2 class="text-base font-bold text-slate-900">Commission Payout Ledger</h2>

        @if($broker->commissions->isEmpty())
            <div class="p-4 text-center text-xs text-slate-400 font-medium bg-slate-50 rounded-xl border border-slate-200">
                No commission entries generated yet.
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/80 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Booking Code</th>
                            <th class="py-3 px-4">Rate (%)</th>
                            <th class="py-3 px-4">Total Commission</th>
                            <th class="py-3 px-4">Payout Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($broker->commissions as $comm)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $comm->booking->booking_code ?? 'N/A' }}</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">{{ $comm->commission_rate }}%</td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">₹{{ number_format($comm->total_commission_amount) }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $comm->status }}
                                </span>
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
