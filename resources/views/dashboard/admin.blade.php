@extends('layouts.reos')

@section('title', 'Admin Dashboard – REOS')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Top Hero Banner: Premium Real Estate Operations Greeting -->
    <div class="reos-card p-6 md:p-7 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white">
        <div class="space-y-1.5">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#059669] border border-emerald-200 uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-[#059669] animate-pulse"></span>
                <span>Builder Admin Dashboard</span>
            </div>
            <h1 class="page-heading flex items-center space-x-2">
                <span>Good {{ date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') }}, {{ strtok($user->name, ' ') }}</span>
                <i class="fa-solid fa-building-user text-emerald-600 text-2xl"></i>
            </h1>
            <p class="body-text">
                Overview for <strong class="text-[#0F172A] font-semibold">{{ $company->name }}</strong>: 
                <span class="text-[#059669] font-bold font-mono">{{ $availableUnits }}</span> units available & 
                <span class="text-[#059669] font-bold font-mono">{{ $totalLeads }}</span> CRM leads active across active projects.
            </p>
        </div>

        <div class="flex items-center space-x-3 shrink-0">
            <a href="{{ route('reports.index') }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-[#0F172A] btn-text rounded-xl border border-[#E2E8F0] shadow-2xs transition flex items-center space-x-2">
                <i class="fa-solid fa-download text-[#64748B] text-xs"></i>
                <span>Export Report</span>
            </a>
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-[#059669] hover:bg-[#047857] text-white btn-text rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add Team User</span>
            </a>
        </div>
    </div>

    <!-- Main Grid Row 1: Total Property Units Card + Property Inventory Donut Chart + Lead Summary Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Col 1: Left Stats + Mini KPI Grid (4 cols on lg) -->
        <div class="lg:col-span-4 space-y-4 flex flex-col justify-between">
            <!-- Total Property Units Card -->
            <a href="{{ route('projects.index') }}" class="reos-card p-6 flex-1 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer block">
                <div>
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="label-text">Total Property Units</span>
                            <h2 class="kpi-number mt-2 text-3xl">{{ number_format($totalUnits) }}</h2>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-[#0F172A] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            <i class="fa-solid fa-building"></i>
                        </div>
                    </div>
                    <div class="mt-4 inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#059669] border border-emerald-200">
                        <i class="fa-solid fa-arrow-trend-up text-[10px]"></i>
                        <span>{{ $availableUnits }} Units Ready for Booking →</span>
                    </div>
                    <p class="body-text text-xs mt-2">Active inventory across {{ $totalProjects }} developer projects</p>
                </div>
            </a>

            <!-- Mini 2x2 Metric Cards Grid -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Card 1: Available Units -->
                <a href="{{ route('projects.index') }}" class="reos-card p-4 space-y-2 hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer block">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#059669] flex items-center justify-center font-bold text-sm border border-emerald-100">
                            <i class="fa-solid fa-door-open"></i>
                        </div>
                        <span class="text-xs font-semibold text-[#059669] bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                            Available
                        </span>
                    </div>
                    <div>
                        <div class="label-text">Available Units</div>
                        <div class="text-xl font-bold text-[#0F172A] font-mono mt-0.5">{{ number_format($availableUnits) }} →</div>
                    </div>
                </a>

                <!-- Card 2: Occupancy Rate -->
                <a href="{{ route('bookings.index') }}" class="reos-card p-4 space-y-2 hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer block">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-[#D97706] flex items-center justify-center font-bold text-sm border border-amber-100">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <span class="text-xs font-semibold text-[#D97706] bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">
                            Booked
                        </span>
                    </div>
                    <div>
                        <div class="label-text">Sold Ratio</div>
                        @php
                            $conversionRate = $totalUnits > 0 ? round(($bookedUnits / $totalUnits) * 100, 1) : 0;
                        @endphp
                        <div class="text-xl font-bold text-[#0F172A] font-mono mt-0.5">{{ $conversionRate }}% →</div>
                    </div>
                </a>

                <!-- Card 3: CRM Leads -->
                <a href="{{ route('leads.index') }}" class="reos-card p-4 space-y-2 hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer block">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-[#4F46E5] flex items-center justify-center font-bold text-sm border border-indigo-100">
                            <i class="fa-solid fa-users text-xs"></i>
                        </div>
                        <span class="text-xs font-semibold text-[#4F46E5] bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">
                            Active
                        </span>
                    </div>
                    <div>
                        <div class="label-text">CRM Leads</div>
                        <div class="text-xl font-bold text-[#0F172A] font-mono mt-0.5">{{ number_format($totalLeads) }} →</div>
                    </div>
                </a>

                <!-- Card 4: Team Staff -->
                <a href="{{ route('users.index') }}" class="reos-card p-4 space-y-2 hover:shadow-md hover:-translate-y-0.5 transition transform cursor-pointer block">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm border border-purple-100">
                            <i class="fa-solid fa-user-tie text-xs"></i>
                        </div>
                        <span class="text-xs font-semibold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-100">
                            Staff
                        </span>
                    </div>
                    <div>
                        <div class="label-text">Active Team</div>
                        <div class="text-xl font-bold text-[#0F172A] font-mono mt-0.5">{{ number_format($totalUsers) }} →</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Col 2: Property Inventory Distribution Donut Chart (4 cols on lg) -->
        <div class="lg:col-span-4 reos-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                    <h2 class="section-heading">Inventory Distribution</h2>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200">
                        Live Data
                    </span>
                </div>

                <!-- Donut Chart & Legend Container -->
                <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-6">
                    <!-- Donut Canvas Container -->
                    <div class="relative w-40 h-40 flex items-center justify-center">
                        <canvas id="inventoryDonutChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                            <span class="text-2xl font-bold text-[#0F172A] font-mono leading-none">{{ $totalUnits }}</span>
                            <span class="label-text text-[10px] mt-1">Total Units</span>
                        </div>
                    </div>

                    <!-- Custom Legend Items -->
                    <div class="space-y-2.5 text-xs flex-1 w-full">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#0D9488]"></span>
                                <span class="table-text">Available</span>
                            </span>
                            <span class="font-bold text-[#0F172A] font-mono">{{ $availableUnits }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#F43F5E]"></span>
                                <span class="table-text">Booked / Sold</span>
                            </span>
                            <span class="font-bold text-[#0F172A] font-mono">{{ $bookedUnits }}</span>
                        </div>
                        @php
                            $holdUnits = max(0, $totalUnits - ($availableUnits + $bookedUnits));
                        @endphp
                        <div class="flex items-center justify-between">
                            <span class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span>
                                <span class="table-text">Reserved / Hold</span>
                            </span>
                            <span class="font-bold text-[#0F172A] font-mono">{{ $holdUnits }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Summary Meters Bar -->
            <div class="mt-6 pt-4 border-t border-[#E2E8F0] grid grid-cols-3 gap-2 text-center">
                <div class="bg-[#F8FAFC] p-2 rounded-lg border border-[#E2E8F0]">
                    <div class="label-text text-[10px]">Available</div>
                    <div class="text-base font-bold text-[#0D9488] font-mono mt-0.5">{{ $availableUnits }}</div>
                </div>
                <div class="bg-[#F8FAFC] p-2 rounded-lg border border-[#E2E8F0]">
                    <div class="label-text text-[10px]">Booked</div>
                    <div class="text-base font-bold text-[#F43F5E] font-mono mt-0.5">{{ $bookedUnits }}</div>
                </div>
                <div class="bg-[#F8FAFC] p-2 rounded-lg border border-[#E2E8F0]">
                    <div class="label-text text-[10px]">Hold</div>
                    <div class="text-base font-bold text-[#F59E0B] font-mono mt-0.5">{{ $holdUnits }}</div>
                </div>
            </div>
        </div>

        <!-- Col 3: Lead Activity Trend Bar Chart (4 cols on lg) -->
        <div class="lg:col-span-4 reos-card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E8F0]">
                    <h2 class="section-heading">Lead Summary</h2>
                    <a href="{{ route('leads.index') }}" class="text-xs text-[#4F46E5] hover:underline font-semibold flex items-center space-x-1">
                        <span>View Logs ›</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="p-3 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0]">
                        <span class="label-text text-[10px]">Total Leads</span>
                        <div class="text-xl font-bold text-[#0F172A] font-mono mt-0.5">{{ number_format($totalLeads) }}</div>
                        <span class="body-text text-[11px]">Active pipeline</span>
                    </div>
                    <div class="p-3 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0]">
                        <span class="label-text text-[10px]">Site Visits</span>
                        <div class="text-xl font-bold text-[#4F46E5] font-mono mt-0.5">{{ max(1, intval($totalLeads * 0.45)) }}</div>
                        <span class="body-text text-[11px]">Completed tours</span>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="label-text text-xs text-[#0F172A] mb-2">Weekly Lead Inquiries Trend</h3>
                    <div class="h-36 relative">
                        <canvas id="weeklyActivityBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Quick Action Dark Teal Banner (Dreams ERP Match) -->
            <div class="mt-4 p-4 rounded-xl bg-[#0D9488] text-white flex items-center justify-between shadow-xs">
                <div>
                    <div class="btn-text text-xs text-white">Run Cost Sheet & Booking</div>
                    <div class="body-text text-[11px] text-teal-100 opacity-90">Process monthly sales pay & booking</div>
                </div>
                <a href="{{ route('bookings.index') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-[#0D9488] btn-text rounded-full shadow-xs transition cursor-pointer">
                    Run Payroll
                </a>
            </div>
        </div>
    </div>

    <!-- Main Grid Row 2: Sales Pipeline Funnel & Navy Revenue Card -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Dark Navy Revenue Card with Gold Accent Highlight -->
        <div class="lg:col-span-4 bg-[#0F172A] text-white p-6 rounded-2xl shadow-md relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="label-text text-indigo-300">Total Portfolio Value</span>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded bg-[#C9A227] text-white uppercase tracking-wider shadow-2xs">
                        ★ VIP Portfolio
                    </span>
                </div>
                <div class="text-3xl font-extrabold font-mono tracking-tight text-white mt-2">
                    ₹{{ number_format($bookedUnits * 6500000 + $availableUnits * 5000000) }}
                </div>
                <p class="body-text text-xs text-slate-400 mt-1">Combined value of active developer property inventory</p>
            </div>

            <div class="my-6 space-y-2.5 border-t border-slate-800 pt-4">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">Booked Sales Revenue</span>
                    <span class="font-mono font-bold text-[#059669]">₹{{ number_format($bookedUnits * 6500000) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">Available Inventory Value</span>
                    <span class="font-mono font-bold text-indigo-300">₹{{ number_format($availableUnits * 5000000) }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                <a href="{{ route('reports.index') }}" class="w-full text-center py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text rounded-xl shadow-xs transition">
                    <i class="fa-solid fa-download text-xs mr-1.5"></i> Download Statement
                </a>
            </div>
        </div>

        <!-- Sales Pipeline Funnel (8 cols) -->
        <div class="lg:col-span-8 reos-card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
                <div>
                    <h2 class="section-heading">CRM Sales Pipeline</h2>
                    <p class="body-text text-xs">Live lead conversion stages across active projects</p>
                </div>
                <a href="{{ route('leads.index') }}" class="px-4 py-2 bg-[#0F172A] hover:bg-slate-800 text-white btn-text rounded-xl shadow-xs transition">
                    + Add New Lead
                </a>
            </div>

            <!-- Pipeline Cards Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Stage 1: New Leads -->
                <div class="p-4 rounded-xl bg-white border border-[#E2E8F0] border-l-4 border-l-[#2563EB] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="label-text">New Inquiries</span>
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#2563EB] flex items-center justify-center font-bold text-xs"><i class="fa-solid fa-inbox"></i></div>
                    </div>
                    <div class="text-2xl font-bold text-[#0F172A] font-mono mt-1">{{ number_format($totalLeads) }}</div>
                    <div class="body-text text-[11px]">Fresh captured leads</div>
                </div>

                <!-- Stage 2: Site Visit Scheduled -->
                <div class="p-4 rounded-xl bg-white border border-[#E2E8F0] border-l-4 border-l-[#D97706] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="label-text">Site Visits</span>
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-[#D97706] flex items-center justify-center font-bold text-xs"><i class="fa-solid fa-shoe-prints"></i></div>
                    </div>
                    <div class="text-2xl font-bold text-[#0F172A] font-mono mt-1">{{ max(1, intval($totalLeads * 0.45)) }}</div>
                    <div class="body-text text-[11px]">Property tours completed</div>
                </div>

                <!-- Stage 3: Bookings Converted -->
                <div class="p-4 rounded-xl bg-white border border-[#E2E8F0] border-l-4 border-l-[#059669] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="label-text">Bookings Converted</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-[#059669] flex items-center justify-center font-bold text-xs"><i class="fa-solid fa-circle-check"></i></div>
                    </div>
                    <div class="text-2xl font-bold text-[#0F172A] font-mono mt-1">{{ number_format($bookedUnits) }}</div>
                    <div class="body-text text-[11px]">Bookings & agreements</div>
                </div>
            </div>

            <!-- Recent Team Activity Table -->
            <div class="pt-2">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="label-text text-[#0F172A]">Active Team Roster</h3>
                    <a href="{{ route('users.index') }}" class="text-xs text-[#4F46E5] font-semibold hover:underline">Manage Team →</a>
                </div>
                <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs text-[#0F172A]">
                        <thead class="bg-[#F8FAFC] text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">Member</th>
                                <th class="py-2.5 px-3">Role</th>
                                <th class="py-2.5 px-3">Email</th>
                                <th class="py-2.5 px-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            @foreach($teamUsers->take(4) as $u)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2.5 px-3 font-semibold text-[#0F172A] flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-50 text-[#4F46E5] font-bold text-[10px] flex items-center justify-center border border-indigo-100">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <span class="table-text">{{ $u->name }}</span>
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-[#0F172A] border border-[#E2E8F0]">
                                        {{ $u->role->name ?? 'Staff' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 font-mono text-[#64748B] text-[11px]">{{ $u->email }}</td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200">Active</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Property Inventory Donut Chart
        const ctxDonut = document.getElementById('inventoryDonutChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Booked / Sold', 'Reserved / Hold'],
                datasets: [{
                    data: [{{ $availableUnits }}, {{ $bookedUnits }}, {{ $holdUnits }}],
                    backgroundColor: ['#0D9488', '#F43F5E', '#F59E0B'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + ' Units';
                            }
                        }
                    }
                }
            }
        });

        // 2. Weekly Activity Bar Chart
        const ctxBar = document.getElementById('weeklyActivityBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Leads & Inquiries',
                    data: [12, 19, 15, 22, 18, 25, 14],
                    backgroundColor: '#4F46E5',
                    borderRadius: 4,
                    barThickness: 14
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, family: 'Manrope' }, color: '#64748B' }
                    },
                    y: {
                        grid: { color: '#E2E8F0' },
                        ticks: { font: { size: 10, family: 'Manrope' }, color: '#64748B', stepSize: 5 }
                    }
                }
            }
        });
    });
</script>
@endsection
