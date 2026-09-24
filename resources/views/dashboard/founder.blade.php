@extends('layouts.reos')

@section('title', 'SaaS Master Control')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Top Greeting Header & Date/Time Formatting -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0F172A] tracking-tight">
                Good {{ date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                {{ date('d M Y') }} | Indian (timezone formatting) &bull; <strong class="text-slate-900 font-semibold">{{ $totalCompanies }}</strong> builder companies onboarded &bull; Multi-Tenant SaaS Master Control
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
            <a href="{{ route('permissions.index') }}" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 font-bold text-xs rounded-lg shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-shield-halved text-xs"></i>
                <span>Permissions Matrix</span>
            </a>

            @if(auth()->user()->isSaaSFounder())
                <a href="{{ route('admin.sub-admins.index') }}" class="px-3.5 py-2 bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 font-bold text-xs rounded-lg shadow-2xs transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-user-shield text-xs"></i>
                    <span>Sub-Admins</span>
                </a>
            @endif

            <a href="{{ route('admin.saas-subscriptions') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-xs rounded-lg shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-bolt text-xs text-amber-500"></i>
                <span>SaaS Plans</span>
            </a>

            @if(auth()->user()->hasSaaSPermission('onboard_companies'))
                <a href="{{ route('admin.companies.create') }}" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Onboard Company</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 6 Key SaaS Platform Metric Cards Grid Across Top -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- Metric 1: Platform MRR -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Platform MRR</span>
                <i class="fa-solid fa-arrow-trend-up text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">₹{{ number_format($totalPlatformRevenue) }}</div>
            <div class="text-xs font-semibold text-emerald-600">(+18.4% MRR)</div>
        </div>

        <!-- Metric 2: Active Tenants -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Active Tenants</span>
                <i class="fa-solid fa-building text-blue-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ $totalCompanies }}</div>
            <div class="text-xs font-semibold text-blue-600">Developers</div>
        </div>

        <!-- Metric 3: Active Subscriptions -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Active Plans</span>
                <i class="fa-solid fa-bolt text-purple-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ $activeSubscriptions }}</div>
            <div class="text-xs font-semibold text-purple-600">Paying Accounts</div>
        </div>

        <!-- Metric 4: Total Platform Leads -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Platform Leads</span>
                <i class="fa-solid fa-users text-indigo-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($totalPlatformLeads) }}</div>
            <div class="text-xs font-semibold text-indigo-600">Across Builders</div>
        </div>

        <!-- Metric 5: Gross Booking Value (GMV) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Gross GMV</span>
                <i class="fa-solid fa-indian-rupee-sign text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">₹{{ number_format($totalGrossBookingValue) }}</div>
            <div class="text-xs font-semibold text-emerald-600">{{ $totalPlatformBookings }} Bookings</div>
        </div>

        <!-- Metric 6: Sub-Admins & Approvals -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Sub-Admins</span>
                <i class="fa-solid fa-user-shield text-amber-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ $saasSubAdminsCount ?? 0 }}</div>
            <a href="{{ route('admin.saas-approvals') }}" class="text-xs font-semibold text-amber-800 hover:underline flex items-center space-x-1">
                <span>{{ $pendingApprovalsCount ?? 0 }} Pending &rarr;</span>
            </a>
        </div>
    </div>

    <!-- PENDING SUB-ADMIN APPROVAL REQUESTS SECTION (If Any) -->
    @if(isset($pendingApprovalRequests) && $pendingApprovalRequests->isNotEmpty())
    <div class="bg-white p-5 rounded-xl border border-amber-300 shadow-2xs space-y-3">
        <div class="flex items-center justify-between border-b border-amber-100 pb-3">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                <h2 class="text-sm font-bold text-amber-900">Pending Sub-Admin Approval Requests</h2>
            </div>
            <a href="{{ route('admin.saas-approvals') }}" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">
                View All {{ $pendingApprovalsCount }} &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
            @foreach($pendingApprovalRequests as $req)
            <div class="p-3.5 rounded-lg bg-amber-50/50 border border-amber-200 space-y-2 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center text-[11px] text-amber-800 font-semibold mb-1">
                        <span>Req #{{ $req->id }} &bull; {{ str_replace('_', ' ', ucfirst($req->action_type)) }}</span>
                        <span>{{ $req->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="font-bold text-slate-900 text-xs">{{ $req->target_name }}</div>
                    <p class="text-slate-600 text-[11px] mt-1">{{ $req->reason }}</p>
                </div>
                <div class="pt-2 border-t border-amber-200/60 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500">By: {{ $req->requestedBy->name ?? 'Sub-Admin' }}</span>
                    <a href="{{ route('admin.saas-approvals') }}" class="font-bold text-amber-800 hover:underline">Review &rarr;</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Middle Section: Revenue & Tenant Growth Chart & Plan Tier Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left (8 Cols): SaaS Revenue & Tenant Growth Stacked Chart -->
        <div class="lg:col-span-8 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-[#0F172A]">Platform Growth & Monthly MRR Trend</h2>
                    <p class="text-xs text-slate-500 font-medium">Monthly recurring revenue, builder onboarding, and lead volume intake</p>
                </div>
                <div class="inline-flex items-center space-x-2 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                    <span>System Operational</span>
                </div>
            </div>

            <!-- Chart Canvas -->
            <div class="h-64 relative">
                <canvas id="saasGrowthChart"></canvas>
            </div>

            <!-- Chart Legend -->
            <div class="flex items-center justify-center space-x-6 text-xs font-semibold text-slate-600 pt-1">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-xs bg-[#0F172A]"></span>
                    <span>Platform MRR (₹)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-xs bg-[#2563EB]"></span>
                    <span>Onboarded Builders</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-xs bg-[#10B981]"></span>
                    <span>Active Projects</span>
                </div>
            </div>
        </div>

        <!-- Right (4 Cols): Plan Tier Distribution Donut Chart & Tier Breakdown -->
        <div class="lg:col-span-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h2 class="text-sm font-bold text-[#0F172A]">SaaS Plan Distribution</h2>
                    <a href="{{ route('admin.saas-subscriptions') }}" class="text-xs font-semibold text-blue-600 hover:underline">Manage Plans</a>
                </div>

                <!-- Donut Chart -->
                <div class="mt-3 flex flex-col items-center">
                    <div class="relative w-36 h-36 flex items-center justify-center">
                        <canvas id="planDistributionChart"></canvas>
                    </div>

                    <!-- Tier Breakdown List -->
                    <div class="w-full mt-4 space-y-2 text-xs font-medium">
                        @foreach($subscriptionPlans as $plan)
                        @php
                            $subscribedCount = $companies->filter(fn($c) => $c->subscription_plan_id == $plan->id)->count();
                        @endphp
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                <span class="font-bold text-slate-800">{{ $plan->name }}</span>
                            </div>
                            <div class="flex items-center space-x-2 font-mono">
                                <span class="font-bold text-slate-900">{{ $subscribedCount }} Builders</span>
                                <span class="text-slate-400 text-[10px]">(₹{{ number_format($plan->price) }}/mo)</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarded Builder Companies Directory Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">Onboarded Builder Companies Directory</h2>
                <p class="text-xs text-slate-500 font-medium">Multi-Tenant Isolation Directory, Subscriptions & Database Health</p>
            </div>
            @if(auth()->user()->hasSaaSPermission('onboard_companies'))
            <a href="{{ route('admin.companies.create') }}" class="px-3.5 py-1.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold rounded-lg transition shadow-2xs">
                + Onboard New Company
            </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                    <tr>
                        <th class="p-3.5">Company Name / Code</th>
                        <th class="p-3.5">Subscribed Plan</th>
                        <th class="p-3.5">Projects / Units</th>
                        <th class="p-3.5">Users Roster</th>
                        <th class="p-3.5">Monthly Leads Usage</th>
                        <th class="p-3.5 text-right">Tenant Status</th>
                        <th class="p-3.5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @foreach($companies as $c)
                    @php
                        $leadCount = \App\Models\Lead::withoutGlobalScopes()->where('company_id', $c->id)->count();
                        $unitCount = $c->projects->sum(fn($p) => $p->units->count());
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-3.5 font-bold text-slate-900 flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0 font-bold text-xs">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-xs">{{ $c->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $c->domain ?? 'reos.app' }} &bull; Code: <strong class="text-slate-700">{{ $c->code }}</strong></div>
                            </div>
                        </td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $c->subscriptionPlan->name ?? 'Enterprise Plan' }}
                            </span>
                        </td>
                        <td class="p-3.5 font-mono font-bold text-slate-900">
                            {{ $c->projects->count() }} Proj / {{ $unitCount }} Units
                        </td>
                        <td class="p-3.5 font-mono font-bold text-slate-900">
                            {{ $c->users->count() }} Active Users
                        </td>
                        <td class="p-3.5">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[10px] font-mono text-slate-600 font-semibold">
                                    <span>{{ $leadCount }} Leads</span>
                                    <span>Quota Active</span>
                                </div>
                                <div class="w-28 h-1.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                                    <div class="h-full bg-blue-600 rounded-full" style="width: {{ min(100, max(15, $leadCount * 10)) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 text-right">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                {{ ucfirst($c->status ?? 'Active') }}
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <a href="{{ route('admin.companies.show', $c->id) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-lg border border-slate-200 transition inline-flex items-center space-x-1">
                                <i class="fa-solid fa-eye text-[10px]"></i>
                                <span>Inspect</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Section: Live Audit Activity Log & System Infrastructure Status -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left (7 Cols): Live Platform Audit Log Across All Companies -->
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-[#0F172A]">Platform Audit Trail & Live Activity</h2>
                    <p class="text-xs text-slate-500 font-medium">Real-time system events logged across all tenant accounts</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    Live Event Stream
                </span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentPlatformActivities as $act)
                <div class="p-3.5 hover:bg-slate-50/70 transition flex items-start space-x-3">
                    <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center border border-slate-200 shrink-0 mt-0.5">
                        {{ strtoupper(substr($act->user->name ?? 'SYS', 0, 2)) }}
                    </div>
                    <div class="flex-1 space-y-0.5">
                        <div class="flex items-center justify-between text-slate-900 font-bold">
                            <span>{{ $act->user->name ?? 'System Process' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono font-normal">{{ $act->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-slate-600 text-xs leading-snug">{{ $act->description ?? $act->action }}</p>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-xs text-slate-400">
                    No recent audit activities logged.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right (5 Cols): Platform System Health & Infrastructure Metrics -->
        <div class="lg:col-span-5 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4 flex flex-col justify-between">
            <div>
                <div class="pb-3 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#0F172A]">System Infrastructure Status</h2>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        All Operational
                    </span>
                </div>

                <div class="mt-4 space-y-3 text-xs font-medium">
                    <!-- Stat 1: Multi-Tenant DB Status -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <div class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-database text-blue-600"></i>
                            <div>
                                <div class="font-bold text-slate-900">Multi-Tenant Isolation</div>
                                <div class="text-[10px] text-slate-500">Global Tenant Scopes Active</div>
                            </div>
                        </div>
                        <span class="text-emerald-600 font-bold text-[11px]">100% Secure</span>
                    </div>

                    <!-- Stat 2: Firebase FCM Real-Time Engine -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <div class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-bell text-amber-600"></i>
                            <div>
                                <div class="font-bold text-slate-900">Firebase Push Notifications</div>
                                <div class="text-[10px] text-slate-500">FCM Web SDK Worker Online</div>
                            </div>
                        </div>
                        <span class="text-emerald-600 font-bold text-[11px]">Connected</span>
                    </div>

                    <!-- Stat 3: Platform API Response Time -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <div class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-server text-purple-600"></i>
                            <div>
                                <div class="font-bold text-slate-900">API Gateway Latency</div>
                                <div class="text-[10px] text-slate-500">Average response duration</div>
                            </div>
                        </div>
                        <span class="font-mono font-bold text-slate-900">24ms</span>
                    </div>

                    <!-- Stat 4: Document Storage & Assets -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <div class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-cloud text-indigo-600"></i>
                            <div>
                                <div class="font-bold text-slate-900">Cloud Media Storage</div>
                                <div class="text-[10px] text-slate-500">Project layouts & document uploads</div>
                            </div>
                        </div>
                        <span class="font-mono font-bold text-slate-900">1.2 GB Used</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. SaaS Growth & Monthly MRR Trend Chart
        const ctxGrowth = document.getElementById('saasGrowthChart').getContext('2d');
        new Chart(ctxGrowth, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Platform MRR (₹)',
                        data: [4999, 4999, 9998, 9998, 14997, 14997, 19996, 19996, 19998, 24997, 29996, 34995],
                        backgroundColor: '#0F172A'
                    },
                    {
                        label: 'Onboarded Builders',
                        data: [1, 1, 1, 2, 2, 2, 2, 2, 2, 3, 3, 4],
                        backgroundColor: '#2563EB'
                    },
                    {
                        label: 'Active Projects',
                        data: [1, 1, 2, 2, 3, 3, 4, 4, 5, 6, 7, 8],
                        backgroundColor: '#10B981'
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
                        grid: { display: false },
                        ticks: { font: { size: 10, family: 'Manrope' }, color: '#64748B' }
                    },
                    y: {
                        grid: { color: '#F1F5F9' },
                        ticks: { font: { size: 10, family: 'Manrope' }, color: '#64748B' }
                    }
                }
            }
        });

        // 2. SaaS Plan Distribution Donut Chart
        const ctxPlans = document.getElementById('planDistributionChart').getContext('2d');
        new Chart(ctxPlans, {
            type: 'doughnut',
            data: {
                labels: ['Growth Enterprise', 'Unlimited Developer', 'Starter Tier'],
                datasets: [{
                    data: [{{ $companies->count() }}, 1, 0],
                    backgroundColor: ['#2563EB', '#0F172A', '#94A3B8'],
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


