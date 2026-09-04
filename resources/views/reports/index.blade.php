@extends('layouts.reos')

@section('title', 'Executive Reports & Analytics - REOS')

@section('content')
<div class="space-y-6">
    <div class="reos-card p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Reports & Analytics</span>
            </div>
            <h1 class="page-heading text-2xl">Executive Sales & Analytics Report</h1>
            <p class="body-text text-xs mt-0.5">Real-time pipeline performance, inventory conversion rates, and sales team efficiency</p>
        </div>
        <div class="flex items-center space-x-3">
            @can('manage-users')
            <a href="{{ route('users.index') }}" class="px-4 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2">
                <i class="fa-solid fa-user-tie text-white"></i>
                <span>Manage Sales Executives</span>
            </a>
            @endcan
            <div class="flex items-center space-x-2 text-xs font-bold text-[#4F46E5] bg-indigo-50 border border-indigo-200 px-3.5 py-2.5 rounded-xl shadow-2xs">
                <i class="fa-solid fa-bolt text-[#4F46E5]"></i>
                <span>Live Real-time Sync Active</span>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total CRM Leads</div>
            <div class="text-3xl font-black text-slate-900 font-mono mt-2">{{ $totalLeads }}</div>
            <div class="text-xs text-emerald-600 font-bold mt-1">Conversions: {{ $convertedLeads }}</div>
        </div>
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Site Visits Conducted</div>
            <div class="text-3xl font-black text-indigo-600 font-mono mt-2">{{ $siteVisits }}</div>
            <div class="text-xs text-slate-600 font-bold mt-1">Scheduled & Conducted</div>
        </div>
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Unit Bookings</div>
            <div class="text-3xl font-black text-emerald-600 font-mono mt-2">{{ $totalBookings }}</div>
            <div class="text-xs text-slate-600 font-bold mt-1">Units Secured</div>
        </div>
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Token Revenue Collected</div>
            <div class="text-3xl font-black text-purple-600 font-mono mt-2">₹{{ number_format($totalRevenue) }}</div>
            <div class="text-xs text-purple-700 font-bold mt-1">Token Payments</div>
        </div>
    </div>

    <!-- Sales Executive Performance Table -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-slate-900">Sales Executive Performance Breakdown</h2>
            @can('manage-users')
            <a href="{{ route('users.index') }}" class="text-xs font-extrabold text-indigo-600 hover:text-indigo-800 transition">
                + Add / Manage Executives →
            </a>
            @endcan
        </div>
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4">Sales Executive</th>
                        <th class="p-4">Assigned Leads</th>
                        <th class="p-4">Site Visits</th>
                        <th class="p-4">Converted Bookings</th>
                        <th class="p-4">Conversion Rate</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($salesPerformance as $exec)
                    @php
                        $rate = $exec->total_leads > 0 ? round(($exec->converted_leads / $exec->total_leads) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-900 flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center font-black text-xs">
                                {{ strtoupper(substr($exec->name, 0, 2)) }}
                            </div>
                            <div>
                                <div>{{ $exec->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $exec->email }}</div>
                            </div>
                        </td>
                        <td class="p-4 font-mono font-bold text-slate-900">{{ $exec->total_leads }}</td>
                        <td class="p-4 font-mono font-bold text-indigo-600">{{ $exec->site_visit_leads }}</td>
                        <td class="p-4 font-mono font-bold text-emerald-600">{{ $exec->converted_leads }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 text-xs font-black rounded-full bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $rate }}%
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center space-x-2">
                                @can('manage-users')
                                <a href="{{ route('users.index') }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 border border-indigo-200 rounded-xl text-xs font-extrabold transition flex items-center space-x-1">
                                    <i class="fa-solid fa-gear text-indigo-600 mr-1"></i><span>Manage Executive</span>
                                </a>
                                @endcan
                                <a href="{{ route('leads.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 rounded-xl text-xs font-bold transition flex items-center space-x-1">
                                    <i class="fa-solid fa-list text-slate-600 mr-1"></i><span>View Leads</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500 font-medium text-xs">No sales performance data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
