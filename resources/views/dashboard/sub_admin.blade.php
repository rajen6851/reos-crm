@extends('layouts.reos')

@section('title', 'Sub-Admin Control Panel')

@section('content')
<div class="space-y-6 pb-12">

    <!-- Top Greeting Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0F172A] tracking-tight">
                Good {{ date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                {{ date('d M Y') }}
                &bull; <strong class="text-slate-900">SaaS Sub-Admin</strong> &mdash; Delegated Platform Monitor
                &bull; <strong class="text-slate-900">{{ $totalCompanies }}</strong> builder tenants in system
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Period Selector -->
            <div class="inline-flex items-center bg-[#E2E8F0]/70 p-1 rounded-lg text-xs font-semibold text-slate-600">
                <button class="px-3 py-1.5 rounded-md bg-white text-slate-900 shadow-2xs font-bold transition">Today</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">This Week</button>
                <button class="px-3 py-1.5 rounded-md hover:text-slate-900 transition">This Month</button>
            </div>

            <!-- Tenant Companies Link -->
            @if(auth()->user()->hasSaaSPermission('view_companies'))
            <a href="{{ route('admin.companies.index') }}" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 font-bold text-xs rounded-lg shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-city text-xs"></i>
                <span>Tenant Companies</span>
            </a>
            @endif

            <!-- Approvals Link -->
            <a href="{{ route('admin.saas-approvals') }}" class="relative px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-xs rounded-lg shadow-2xs transition flex items-center space-x-1.5">
                <i class="fa-solid fa-bell text-xs"></i>
                <span>Approvals</span>
                @if($pendingApprovalsCount > 0)
                <span class="w-4 h-4 rounded-full bg-amber-500 text-white text-[9px] font-bold flex items-center justify-center absolute -top-1.5 -right-1.5">{{ $pendingApprovalsCount }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- My Delegated Permissions Badges -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">My Delegated SaaS Permissions</h2>
                <p class="text-xs text-slate-500 font-medium">Permissions granted by SaaS Founder to your account</p>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">Sub-Admin Account</span>
        </div>

        @php
        $allSaasPermissions = [
            'view_companies'        => ['label' => 'View Tenants',       'icon' => 'fa-eye',          'color' => 'blue'],
            'onboard_companies'     => ['label' => 'Onboard Companies',  'icon' => 'fa-plus-circle',  'color' => 'emerald'],
            'manage_subscriptions'  => ['label' => 'Manage Plans',       'icon' => 'fa-bolt',         'color' => 'amber'],
            'approve_requests'      => ['label' => 'Approve Requests',   'icon' => 'fa-check-circle', 'color' => 'green'],
            'update_company_status' => ['label' => 'Update Status',      'icon' => 'fa-toggle-on',    'color' => 'indigo'],
            'delete_companies'      => ['label' => 'Delete Companies',   'icon' => 'fa-trash',        'color' => 'rose'],
            'delete_plans'          => ['label' => 'Delete Plans',       'icon' => 'fa-trash-can',    'color' => 'red'],
        ];
        @endphp

        <div class="flex flex-wrap gap-2">
            @foreach($allSaasPermissions as $permSlug => $perm)
            @php $hasIt = in_array($permSlug, $myPermissions); @endphp
            <div class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold transition
                {{ $hasIt ? 'bg-slate-100 text-slate-800 border-slate-300' : 'bg-slate-50 text-slate-400 border-slate-200 opacity-50' }}">
                <i class="fa-solid {{ $perm['icon'] }} text-[10px] {{ $hasIt ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                <span>{{ $perm['label'] }}</span>
                @if(!$hasIt)
                <i class="fa-solid fa-lock text-[9px] text-slate-400"></i>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- 4 Key Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Card 1: Total Tenants -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Total Tenants</span>
                <i class="fa-solid fa-city text-blue-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ $totalCompanies }}</div>
            <div class="text-xs font-semibold text-blue-600">Builder Companies</div>
        </div>

        <!-- Card 2: Active Tenants -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Active Tenants</span>
                <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-700 font-mono">{{ $activeCompanies }}</div>
            <div class="text-xs font-semibold text-emerald-600">Active Subscriptions</div>
        </div>

        <!-- Card 3: Platform Leads -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-1">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Platform Leads</span>
                <i class="fa-solid fa-users text-indigo-600 text-xs"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900 font-mono">{{ number_format($totalPlatformLeads) }}</div>
            <div class="text-xs font-semibold text-indigo-600">Across All Builders</div>
        </div>

        <!-- Card 4: Pending Approvals -->
        <a href="{{ route('admin.saas-approvals') }}" class="bg-white p-4 rounded-xl border {{ $pendingApprovalsCount > 0 ? 'border-amber-300' : 'border-slate-200' }} shadow-2xs space-y-1 block hover:border-amber-400 transition">
            <div class="flex items-center justify-between text-xs text-slate-600 font-medium">
                <span>Pending Approvals</span>
                <i class="fa-solid fa-bell {{ $pendingApprovalsCount > 0 ? 'text-amber-500 animate-pulse' : 'text-slate-400' }} text-xs"></i>
            </div>
            <div class="text-2xl font-bold {{ $pendingApprovalsCount > 0 ? 'text-amber-700' : 'text-slate-900' }} font-mono">{{ $pendingApprovalsCount }}</div>
            <div class="text-xs font-semibold {{ $pendingApprovalsCount > 0 ? 'text-amber-600' : 'text-slate-500' }}">
                {{ $pendingApprovalsCount > 0 ? 'Needs Founder Review &rarr;' : 'All Clear' }}
            </div>
        </a>
    </div>

    <!-- Pending Approval Requests Section -->
    @if($pendingApprovalRequests->isNotEmpty())
    <div class="bg-white p-5 rounded-xl border border-amber-300 shadow-2xs space-y-3">
        <div class="flex items-center justify-between border-b border-amber-100 pb-3">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                <h2 class="text-sm font-bold text-amber-900">Pending Approval Queue &mdash; Awaiting Founder Review</h2>
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
                    <span class="font-bold text-amber-700">Pending Founder</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- My Submitted Requests -->
    @if($myPendingRequests->isNotEmpty())
    <div class="bg-white p-5 rounded-xl border border-indigo-200 shadow-2xs space-y-3">
        <div class="flex items-center justify-between border-b border-indigo-100 pb-3">
            <div>
                <h2 class="text-sm font-bold text-indigo-900">My Submitted Approval Requests</h2>
                <p class="text-xs text-slate-500 font-medium">Actions you have submitted for Founder approval</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">{{ $myPendingRequests->count() }} Pending</span>
        </div>
        <div class="divide-y divide-slate-100 text-xs">
            @foreach($myPendingRequests as $req)
            <div class="py-3 flex items-center justify-between">
                <div class="space-y-0.5">
                    <div class="font-bold text-slate-900">{{ $req->target_name }}</div>
                    <div class="text-[11px] text-slate-500">{{ str_replace('_', ' ', ucfirst($req->action_type)) }} &bull; {{ $req->created_at->diffForHumans() }}</div>
                </div>
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Pending</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Tenant Companies Table (View Only) -->
    @if(auth()->user()->hasSaaSPermission('view_companies'))
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">Monitored Builder Tenant Companies</h2>
                <p class="text-xs text-slate-500 font-medium">Read-only view &mdash; destructive actions require Founder approval</p>
            </div>
            @if(auth()->user()->hasSaaSPermission('onboard_companies'))
            <a href="{{ route('admin.companies.create') }}" class="px-3.5 py-1.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold rounded-lg transition shadow-2xs">
                + Onboard Company
            </a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                    <tr>
                        <th class="p-3.5">Company Name / Code</th>
                        <th class="p-3.5">Subscribed Plan</th>
                        <th class="p-3.5">Users</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @foreach($companies as $c)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-3.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                                    <i class="fa-solid fa-building text-xs"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $c->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono font-normal">Code: <strong class="text-slate-700">{{ $c->code }}</strong></div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $c->subscriptionPlan->name ?? 'No Plan' }}
                            </span>
                        </td>
                        <td class="p-3.5 font-mono font-bold text-slate-900">{{ $c->users->count() }} Users</td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full
                                {{ $c->status === 'active' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
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
    @else
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-10 text-center space-y-2">
        <i class="fa-solid fa-lock text-4xl text-slate-300"></i>
        <div class="font-bold text-slate-700 text-sm">Company Viewing Permission Not Granted</div>
        <p class="text-xs text-slate-400">Ask the SaaS Founder to grant you <strong>view_companies</strong> permission.</p>
    </div>
    @endif

    <!-- Recent Platform Activity Log -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-[#0F172A]">Recent Platform Activity</h2>
                <p class="text-xs text-slate-500 font-medium">System events across all tenant accounts</p>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600 border border-slate-200">Read-Only Log</span>
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
            <div class="p-6 text-center text-xs text-slate-400">No recent activity logged.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection
