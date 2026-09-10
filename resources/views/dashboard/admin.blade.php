@extends('layouts.reos')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Top Greeting Header & Date/Time Formatting -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0F172A] tracking-tight">Good Morning, {{ explode(' ', auth()->user()->name)[0] }}</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ date('d M Y') }} | Indian (timezone formatting)</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Segmented Period Selector (Today, This Week, This Month, Custom) -->
            <div class="inline-flex items-center bg-[#E2E8F0]/70 p-1 rounded-lg text-xs font-semibold text-slate-600">
                <button class="px-3 py-1.5 rounded-md bg-white text-slate-900 shadow-2xs font-bold transition">Today</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">This Week</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">This Month</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">Custom</button>
            </div>

            <!-- Permissions Matrix Link Button -->
            <a href="{{ route('permissions.index') }}" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 font-bold text-xs rounded-lg shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-shield-halved text-xs"></i>
                <span>Permissions Matrix</span>
            </a>
        </div>
    </div>

    <!-- 6 KPI Metric Cards Grid Across Top -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- Card 1: Total Leads -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Total Leads</span>
                <i class="fa-solid fa-arrow-trend-up text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">1,284</div>
            <div class="text-xs font-semibold text-emerald-600">(+12.8%)</div>
        </div>

        <!-- Card 2: Active Properties -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Active Properties</span>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">248</div>
            <div class="text-xs font-semibold text-emerald-600">(+6.4%)</div>
        </div>

        <!-- Card 3: Site Visits -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Site Visits</span>
                <i class="fa-solid fa-arrow-trend-up text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">186</div>
            <div class="text-xs font-semibold text-emerald-600">(+18.2%)</div>
        </div>

        <!-- Card 4: Deals Closed -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Deals Closed</span>
                <i class="fa-solid fa-arrow-trend-up text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">42</div>
            <div class="text-xs font-semibold text-emerald-600">(+9.6%)</div>
        </div>

        <!-- Card 5: Revenue -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Revenue</span>
                <i class="fa-solid fa-arrow-trend-up text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">₹2.84 Cr</div>
            <div class="text-xs font-semibold text-emerald-600">(+14.5%)</div>
        </div>

        <!-- Card 6: Pending Follow-ups -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Pending Follow-ups</span>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">73</div>
            <div class="text-xs font-semibold text-rose-500">(-8.2%)</div>
        </div>
    </div>

    <!-- Middle Section: Lead & Deal Velocity Chart, Sales Funnel, Today's Agenda -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left (6 Cols): Lead & Deal Velocity Dual Y-Axis Stacked Chart -->
        <div class="lg:col-span-6 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-[#0F172A]">Lead & Deal Velocity</h2>
            </div>

            <!-- Chart Canvas -->
            <div class="h-64 relative">
                <canvas id="velocityStackedChart"></canvas>
            </div>

            <!-- Chart Legend -->
            <div class="flex items-center justify-center space-x-5 text-xs font-semibold text-slate-600 pt-1">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-xs bg-[#0F172A]"></span>
                    <span>Leads</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-xs bg-[#2563EB]"></span>
                    <span>Lead Stage</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-xs bg-[#F97316]"></span>
                    <span>Deal</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-xs bg-[#94A3B8]"></span>
                    <span>Deal Stage</span>
                </div>
            </div>
        </div>

        <!-- Middle (3 Cols): Sales Funnel (Matching Screenshot Stages) -->
        <div class="lg:col-span-3 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-[#0F172A]">Sales Funnel</h2>
                <span class="text-[11px] text-slate-400 font-semibold">Conversion %</span>
            </div>

            <!-- Funnel Inverted Pyramid Stack -->
            <div class="py-2 space-y-1 flex flex-col items-center justify-center flex-1 text-white text-[11px] font-bold">
                <!-- Stage 1: Prospecting (1,284) -->
                <div class="w-full bg-[#0F172A] py-2.5 px-3 rounded-t-sm flex items-center justify-between shadow-2xs">
                    <span class="font-mono">1,284</span>
                    <span class="text-[10px]">Prospecting</span>
                </div>

                <!-- Stage 2: Contacted (890) -->
                <div class="w-[88%] bg-[#1E3A8A] py-2 px-3 flex items-center justify-between shadow-2xs">
                    <span class="font-mono">890</span>
                    <span class="text-[10px]">Contacted</span>
                </div>

                <!-- Stage 3: Qualified (412) -->
                <div class="w-[76%] bg-[#2563EB] py-2 px-3 flex items-center justify-between shadow-2xs">
                    <span class="font-mono">412</span>
                    <span class="text-[10px]">Qualified (412)</span>
                </div>

                <!-- Stage 4: Site Visit (186) -->
                <div class="w-[62%] bg-[#3B82F6] py-1.5 px-2.5 flex items-center justify-between shadow-2xs">
                    <span class="font-mono">186</span>
                    <span class="text-[10px]">Site Visit (186)</span>
                </div>

                <!-- Stage 5: Negotiation (64) -->
                <div class="w-[48%] bg-[#6366F1] py-1.5 px-2 flex items-center justify-between shadow-2xs">
                    <span class="font-mono">64</span>
                    <span class="text-[10px]">Negotiation (64)</span>
                </div>

                <!-- Stage 6: Won (42) -->
                <div class="w-[36%] bg-[#10B981] py-1.5 px-2 rounded-b-sm flex items-center justify-between shadow-2xs">
                    <span class="font-mono">42</span>
                    <span class="text-[10px]">Won (42)</span>
                </div>
            </div>
        </div>

        <!-- Right (3 Cols): Today's Agenda & Site Visits -->
        <div class="lg:col-span-3 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-[#0F172A]">Today's Agenda & Site Visits</h2>
            </div>

            <!-- Agenda Events List -->
            <div class="space-y-4 flex-1 text-xs py-2">
                <!-- Event 1 -->
                <div class="flex items-start space-x-3">
                    <span class="font-mono text-slate-500 text-[11px] w-16 pt-0.5 shrink-0">10:30 AM</span>
                    <div class="space-y-0.5">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="font-bold text-slate-900">Site Visit:</span>
                        </div>
                        <div class="text-slate-600 pl-4">Skyline Residency</div>
                    </div>
                </div>

                <!-- Event 2 -->
                <div class="flex items-start space-x-3">
                    <span class="font-mono text-slate-500 text-[11px] w-16 pt-0.5 shrink-0">02:00 PM</span>
                    <div class="space-y-0.5">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-slate-800"></span>
                            <span class="font-bold text-slate-900">Negotiation Call:</span>
                        </div>
                        <div class="text-slate-600 pl-4">Mr. Gupta</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Recent High-Priority Leads Table & Property Inventory Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left (8 Cols): Recent High-Priority Leads Table -->
        <div class="lg:col-span-8 bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-bold text-[#0F172A]">Recent High-Priority Leads</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                        <tr>
                            <th class="p-3 w-8"><input type="checkbox" class="rounded border-slate-300"></th>
                            <th class="p-3">Customer Name <span class="text-[9px] text-slate-400">↕</span></th>
                            <th class="p-3">Phone <span class="text-[9px] text-slate-400">↕</span></th>
                            <th class="p-3">Property Name <span class="text-[9px] text-slate-400">↕</span></th>
                            <th class="p-3">Assigned Agent <span class="text-[9px] text-slate-400">↕</span></th>
                            <th class="p-3">Status <span class="text-[9px] text-slate-400">↕</span></th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        <!-- Row 1 -->
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3"><input type="checkbox" class="rounded border-slate-300"></td>
                            <td class="p-3 font-bold text-slate-900">Vikram Malhotra</td>
                            <td class="p-3 font-mono text-slate-600 flex items-center space-x-1.5">
                                <span>+91 98260...</span>
                                <span class="text-xs" title="India">🇮🇳</span>
                            </td>
                            <td class="p-3 text-slate-700">Skyline Residency 3BHK</td>
                            <td class="p-3 text-slate-700 flex items-center space-x-2">
                                <div class="w-5 h-5 rounded-full bg-slate-300 text-slate-700 font-bold text-[9px] flex items-center justify-center">RM</div>
                                <span class="truncate">Rajesh Malh...</span>
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Qualified
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <button class="p-1 text-slate-500 hover:text-blue-600 rounded border border-slate-200 hover:bg-slate-50" title="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                    <button class="p-1 text-slate-500 hover:text-emerald-600 rounded border border-slate-200 hover:bg-slate-50" title="Call"><i class="fa-solid fa-phone text-[10px]"></i></button>
                                    <button class="p-1 text-slate-500 hover:text-indigo-600 rounded border border-slate-200 hover:bg-slate-50" title="Email"><i class="fa-regular fa-envelope text-[10px]"></i></button>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 2 -->
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3"><input type="checkbox" class="rounded border-slate-300"></td>
                            <td class="p-3 font-bold text-slate-900">Sunita Rao</td>
                            <td class="p-3 font-mono text-slate-600 flex items-center space-x-1.5">
                                <span>+91 98260...</span>
                                <span class="text-xs" title="India">🇮🇳</span>
                            </td>
                            <td class="p-3 text-slate-700">Green Valley Villa</td>
                            <td class="p-3 text-slate-700 flex items-center space-x-2">
                                <div class="w-5 h-5 rounded-full bg-slate-300 text-slate-700 font-bold text-[9px] flex items-center justify-center">MG</div>
                                <span class="truncate">Mr. Gupta</span>
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    Site Visit
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <button class="p-1 text-slate-500 hover:text-blue-600 rounded border border-slate-200 hover:bg-slate-50" title="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                    <button class="p-1 text-slate-500 hover:text-emerald-600 rounded border border-slate-200 hover:bg-slate-50" title="Call"><i class="fa-solid fa-phone text-[10px]"></i></button>
                                    <button class="p-1 text-slate-500 hover:text-indigo-600 rounded border border-slate-200 hover:bg-slate-50" title="Email"><i class="fa-regular fa-envelope text-[10px]"></i></button>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 3 -->
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3"><input type="checkbox" class="rounded border-slate-300"></td>
                            <td class="p-3 font-bold text-slate-900">Amitabh Verma</td>
                            <td class="p-3 font-mono text-slate-600 flex items-center space-x-1.5">
                                <span>+91 98260...</span>
                                <span class="text-xs" title="India">🇮🇳</span>
                            </td>
                            <td class="p-3 text-slate-700">Croteckivii Property</td>
                            <td class="p-3 text-slate-700 flex items-center space-x-2">
                                <div class="w-5 h-5 rounded-full bg-slate-300 text-slate-700 font-bold text-[9px] flex items-center justify-center">RM</div>
                                <span class="truncate">Rajesh Malh...</span>
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-900 border border-amber-200">
                                    Negotiation
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <button class="p-1 text-slate-500 hover:text-blue-600 rounded border border-slate-200 hover:bg-slate-50" title="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                    <button class="p-1 text-slate-500 hover:text-emerald-600 rounded border border-slate-200 hover:bg-slate-50" title="Call"><i class="fa-solid fa-phone text-[10px]"></i></button>
                                    <button class="p-1 text-slate-500 hover:text-indigo-600 rounded border border-slate-200 hover:bg-slate-50" title="Email"><i class="fa-regular fa-envelope text-[10px]"></i></button>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 4 -->
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3"><input type="checkbox" class="rounded border-slate-300"></td>
                            <td class="p-3 font-bold text-slate-900">Amitabh Verma</td>
                            <td class="p-3 font-mono text-slate-600 flex items-center space-x-1.5">
                                <span>+91 98260...</span>
                                <span class="text-xs" title="India">🇮🇳</span>
                            </td>
                            <td class="p-3 text-slate-700">Skylie Residency 3BHK</td>
                            <td class="p-3 text-slate-700 flex items-center space-x-2">
                                <div class="w-5 h-5 rounded-full bg-slate-300 text-slate-700 font-bold text-[9px] flex items-center justify-center">RM</div>
                                <span class="truncate">Rajesh Malh...</span>
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    Contacted
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <button class="p-1 text-slate-500 hover:text-blue-600 rounded border border-slate-200 hover:bg-slate-50" title="Edit"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                    <button class="p-1 text-slate-500 hover:text-emerald-600 rounded border border-slate-200 hover:bg-slate-50" title="Call"><i class="fa-solid fa-phone text-[10px]"></i></button>
                                    <button class="p-1 text-slate-500 hover:text-indigo-600 rounded border border-slate-200 hover:bg-slate-50" title="Email"><i class="fa-regular fa-envelope text-[10px]"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right (4 Cols): Property Inventory Breakdown Donut Chart -->
        <div class="lg:col-span-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h2 class="text-sm font-bold text-[#0F172A]">Property Inventory Breakdown</h2>
                </div>

                <!-- Donut Chart -->
                <div class="mt-4 flex flex-col items-center">
                    <div class="relative w-40 h-40 flex items-center justify-center">
                        <canvas id="inventoryBreakdownChart"></canvas>
                    </div>

                    <!-- Chart Legend -->
                    <div class="w-full mt-5 flex items-center justify-center space-x-4 text-xs font-semibold text-slate-600">
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded-xs bg-[#0F172A]"></span>
                            <span>Available: <strong>164</strong></span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded-xs bg-[#2563EB]"></span>
                            <span>Reserved: <strong>32</strong></span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3 h-3 rounded-xs bg-[#94A3B8]"></span>
                            <span>Sold: <strong>52</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Lead & Deal Velocity Dual Y-Axis Stacked Chart
        const ctxVelocity = document.getElementById('velocityStackedChart').getContext('2d');
        new Chart(ctxVelocity, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Leads',
                        data: [120, 130, 160, 175, 145, 190, 150, 210, 200, 235, 220, 210],
                        backgroundColor: '#0F172A'
                    },
                    {
                        label: 'Lead Stage',
                        data: [75, 80, 125, 95, 95, 135, 95, 165, 135, 165, 145, 155],
                        backgroundColor: '#2563EB'
                    },
                    {
                        label: 'Deal',
                        data: [35, 40, 50, 50, 40, 55, 45, 65, 50, 70, 60, 65],
                        backgroundColor: '#F97316'
                    },
                    {
                        label: 'Deal Stage',
                        data: [35, 35, 55, 40, 45, 55, 45, 65, 40, 70, 50, 65],
                        backgroundColor: '#94A3B8'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 10, family: 'Manrope' }, color: '#64748B' }
                    },
                    y: {
                        stacked: true,
                        grid: { color: '#F1F5F9' },
                        ticks: { font: { size: 10, family: 'Manrope' }, color: '#64748B' },
                        max: 600
                    }
                }
            }
        });

        // 2. Property Inventory Breakdown Donut Chart
        const ctxInventory = document.getElementById('inventoryBreakdownChart').getContext('2d');
        new Chart(ctxInventory, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Reserved', 'Sold'],
                datasets: [{
                    data: [164, 32, 52],
                    backgroundColor: ['#0F172A', '#2563EB', '#94A3B8'],
                    borderWidth: 0,
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection
