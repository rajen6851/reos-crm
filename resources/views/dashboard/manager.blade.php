@extends('layouts.reos')

@section('title', 'Manager Command Center – REOS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Top Hero Banner: Manager Command Center -->
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-[#059669] border border-emerald-200 uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-[#059669] animate-ping"></span>
                <span>Manager Command Center</span>
            </div>
            <h1 class="page-heading text-2xl md:text-3xl font-extrabold text-[#0F172A] tracking-tight flex items-center space-x-2">
                <span>Welcome back, {{ strtok($user->name, ' ') }}!</span>
                <i class="fa-solid fa-user-tie text-[#059669] text-2xl"></i>
            </h1>
            <p class="body-text text-xs md:text-sm text-[#64748B] max-w-2xl leading-relaxed">
                You have <strong class="text-[#059669] font-extrabold font-mono">{{ $newLeadsCount }}</strong> unassigned inquiries, 
                <strong class="text-amber-600 font-extrabold font-mono">{{ $siteVisitsCount }}</strong> site visits scheduled, & 
                <strong class="text-[#059669] font-extrabold font-mono">{{ $pendingBookings->count() }}</strong> pending unit booking approvals for <span class="font-bold text-[#0F172A]">{{ $company->name ?? 'Real Estate' }}</span>.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            @can('manage-users')
            <a href="{{ route('users.index') }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-[#0F172A] btn-text text-xs rounded-xl border border-[#E2E8F0] shadow-2xs transition flex items-center space-x-2">
                <i class="fa-solid fa-users text-slate-500 text-xs"></i>
                <span>Team Executives</span>
            </a>
            @endcan

            <a href="{{ route('calendar.index') }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-[#0F172A] btn-text text-xs rounded-xl border border-[#E2E8F0] shadow-2xs transition flex items-center space-x-2">
                <i class="fa-regular fa-calendar-days text-[#059669] text-xs"></i>
                <span>Calendar Schedule</span>
            </a>

            <a href="{{ route('leads.index') }}" class="px-5 py-2.5 bg-[#059669] hover:bg-[#047857] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Customer Lead</span>
            </a>
        </div>
    </div>

    <!-- Manager 5 KPI Metrics Matrix Cards (Clickable Widgets) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-xs">
        <!-- Metric 1: Total Leads -->
        <a href="{{ route('leads.index') }}" class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer space-y-2 block">
            <div class="flex justify-between items-center">
                <span class="label-text text-[#64748B]">Total Pipeline</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm border border-sky-200"><i class="fa-solid fa-list-check"></i></div>
            </div>
            <div class="text-2xl font-extrabold text-[#0F172A] font-mono">{{ number_format($totalLeads) }}</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-200">
                <i class="fa-solid fa-chart-line text-[9px]"></i>
                <span>All Intake Channels →</span>
            </div>
        </a>

        <!-- Metric 2: New Leads -->
        <a href="{{ route('leads.index', ['status' => 'new']) }}" class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer space-y-2 block">
            <div class="flex justify-between items-center">
                <span class="label-text text-[#64748B]">New Inquiries</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center font-bold text-sm border border-indigo-200"><i class="fa-solid fa-inbox"></i></div>
            </div>
            <div class="text-2xl font-extrabold text-[#4F46E5] font-mono">{{ number_format($newLeadsCount) }}</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-[#4F46E5] bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                <i class="fa-solid fa-clock text-[9px]"></i>
                <span>Pending Assign →</span>
            </div>
        </a>

        <!-- Metric 3: Site Visits -->
        <a href="{{ route('leads.index', ['status' => 'site_visit']) }}" class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer space-y-2 block">
            <div class="flex justify-between items-center">
                <span class="label-text text-[#64748B]">Site Visits</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm border border-amber-200"><i class="fa-solid fa-car"></i></div>
            </div>
            <div class="text-2xl font-extrabold text-amber-700 font-mono">{{ number_format($siteVisitsCount) }}</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                <i class="fa-solid fa-location-dot text-[9px]"></i>
                <span>Property Tours →</span>
            </div>
        </a>

        <!-- Metric 4: Negotiation -->
        <a href="{{ route('leads.index', ['status' => 'negotiation']) }}" class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer space-y-2 block">
            <div class="flex justify-between items-center">
                <span class="label-text text-[#64748B]">Negotiations</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm border border-purple-200"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            </div>
            <div class="text-2xl font-extrabold text-purple-700 font-mono">{{ number_format($negotiationCount) }}</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">
                <i class="fa-solid fa-calculator text-[9px]"></i>
                <span>Cost Sheets Sent →</span>
            </div>
        </a>

        <!-- Metric 5: Booked Deals -->
        <a href="{{ route('bookings.index') }}" class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer space-y-2 block">
            <div class="flex justify-between items-center">
                <span class="label-text text-[#64748B]">Booked Deals</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#059669] flex items-center justify-center font-bold text-sm border border-emerald-200"><i class="fa-solid fa-trophy"></i></div>
            </div>
            <div class="text-2xl font-extrabold text-[#059669] font-mono">{{ number_format($convertedCount) }}</div>
            <div class="inline-flex items-center space-x-1 text-[10px] font-bold text-[#059669] bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                <i class="fa-solid fa-circle-check text-[9px]"></i>
                <span>Units Locked →</span>
            </div>
        </a>
    </div>

    <!-- PENDING UNIT BOOKING APPROVALS SECTION (If Any) -->
    @if($pendingBookings->isNotEmpty())
    <div class="bg-white p-6 rounded-3xl border border-amber-200 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-amber-100 pb-3">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                <h2 class="section-heading text-base text-amber-900">Pending Unit Booking Approvals</h2>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                {{ $pendingBookings->count() }} Booking Locks Awaiting Review
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            @foreach($pendingBookings as $bk)
            <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200 space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <span class="font-mono font-extrabold text-[#4F46E5] text-sm">{{ $bk->booking_code }}</span>
                        <span class="font-mono font-bold text-[#059669]">₹{{ number_format($bk->booking_amount) }} Token</span>
                    </div>
                    <div class="font-extrabold text-[#0F172A] text-sm">{{ $bk->customer_name }}</div>
                    <div class="text-xs text-[#64748B]">Unit {{ $bk->unit->unit_number ?? 'N/A' }} in {{ $bk->project->name ?? 'Project' }}</div>
                </div>

                <div class="pt-3 border-t border-amber-200/60 flex items-center justify-end space-x-2">
                    <form method="POST" action="{{ route('bookings.reject', $bk->id) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-white text-rose-700 hover:bg-rose-50 border border-rose-200 rounded-xl font-bold transition cursor-pointer">
                            Reject
                        </button>
                    </form>
                    <form method="POST" action="{{ route('bookings.approve', $bk->id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-1.5 bg-[#059669] hover:bg-emerald-700 text-white rounded-xl font-bold transition shadow-xs cursor-pointer flex items-center space-x-1">
                            <i class="fa-solid fa-check text-xs mr-1"></i>
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
    <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
            <div>
                <h2 class="section-heading text-base">Sales Team Executives Performance Leaderboard</h2>
                <p class="body-text text-xs text-[#64748B]">Active executive workload, assigned lead volume, & deal conversion ratio</p>
            </div>
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-indigo-50 text-[#4F46E5] hover:bg-indigo-100 btn-text text-xs rounded-xl border border-indigo-200 transition">
                Manage Team Roster →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            @forelse($salesExecutives as $exec)
            @php
                $ratio = $exec->total_assigned_leads > 0 ? round(($exec->converted_leads_count / $exec->total_assigned_leads) * 100, 1) : 0;
            @endphp
            <div class="p-5 rounded-2xl bg-slate-50 border border-[#E2E8F0] hover:border-indigo-300 hover:bg-white transition space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center shadow-xs shrink-0">
                            {{ strtoupper(substr($exec->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="font-extrabold text-[#0F172A] text-sm">{{ $exec->name }}</div>
                            <div class="text-[11px] text-[#64748B] font-medium">{{ $exec->designation ?? 'Sales Executive' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[#E2E8F0] text-center">
                        <div class="bg-white p-2 rounded-xl border border-[#E2E8F0]">
                            <div class="text-sm font-extrabold font-mono text-[#0F172A]">{{ $exec->total_assigned_leads }}</div>
                            <div class="text-[10px] text-[#64748B] font-medium">Assigned</div>
                        </div>
                        <div class="bg-white p-2 rounded-xl border border-[#E2E8F0]">
                            <div class="text-sm font-extrabold font-mono text-[#059669]">{{ $exec->converted_leads_count }}</div>
                            <div class="text-[10px] text-[#64748B] font-medium">Booked</div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between text-[11px]">
                    <span class="text-[#64748B] font-semibold">Conversion Rate:</span>
                    <span class="font-extrabold font-mono text-[#059669]">{{ $ratio }}%</span>
                </div>
            </div>
            @empty
            <div class="col-span-4 p-6 text-center text-xs text-slate-500 rounded-2xl bg-slate-50 border border-[#E2E8F0]">
                No sales executives assigned to your company yet. Add sales team members in Team Users.
            </div>
            @endforelse
        </div>
    </div>

    <!-- RECENT CRM LEADS PIPELINE TABLE -->
    <div class="bg-white p-6 rounded-3xl border border-[#E2E8F0] shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
            <div>
                <h2 class="section-heading text-base">Recent Leads Pipeline</h2>
                <p class="body-text text-xs text-[#64748B]">Real-time customer inquiry status & executive assignment</p>
            </div>
            <a href="{{ route('leads.index') }}" class="px-4 py-2 bg-indigo-50 text-[#4F46E5] hover:bg-indigo-100 btn-text text-xs rounded-xl border border-indigo-200 transition">
                Open Full Pipeline →
            </a>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0]">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-[#64748B] font-bold border-b border-[#E2E8F0] uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">Customer Lead</th>
                        <th class="py-3.5 px-4">Contact Phone</th>
                        <th class="py-3.5 px-4">Interested Property</th>
                        <th class="py-3.5 px-4">Assigned Executive</th>
                        <th class="py-3.5 px-4">Stage Status</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] font-medium">
                    @foreach($recentLeads as $lead)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-extrabold text-[#0F172A]">
                            <a href="{{ route('leads.show', $lead->id) }}" class="hover:underline flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-full bg-indigo-50 text-[#4F46E5] font-extrabold text-xs flex items-center justify-center border border-indigo-200 shrink-0">
                                    {{ strtoupper(substr($lead->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-extrabold text-[#0F172A]">{{ $lead->first_name }} {{ $lead->last_name }}</div>
                                    <div class="text-[10px] text-[#64748B] font-mono font-normal">{{ $lead->lead_code }}</div>
                                </div>
                            </a>
                        </td>
                        <td class="py-3.5 px-4 font-mono">
                            <div class="flex items-center space-x-1.5">
                                <span class="font-bold text-[#0F172A]">{{ $lead->phone }}</span>
                                @if($lead->phone)
                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="px-1.5 py-0.5 rounded bg-emerald-50 text-[#059669] border border-emerald-200 text-[10px] font-bold hover:bg-emerald-100 transition">
                                    WA
                                </a>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-[#0F172A]">{{ $lead->project->name ?? 'General Inquiry' }}</td>
                        <td class="py-3.5 px-4 font-semibold text-[#4F46E5]">{{ $lead->assignedTo->name ?? 'Unassigned' }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-slate-100 border border-slate-200 text-[#0F172A]">
                                {{ str_replace('_', ' ', $lead->status) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('leads.show', $lead->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] btn-text rounded-xl border border-indigo-200 transition inline-flex items-center space-x-1">
                                <i class="fa-solid fa-eye text-xs mr-1"></i>
                                <span>View Details</span>
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
