@extends('layouts.reos')

@section('title', 'SaaS Platform Master Control – REOS Founder')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Top Hero Banner: Premium Real Estate Operations Greeting -->
    <div class="reos-card p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white">
        <div class="space-y-2">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#059669] border border-emerald-200 uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-[#059669] animate-pulse"></span>
                <span>SuperAdmin Platform Scope</span>
            </div>
            <h1 class="page-heading flex items-center space-x-2">
                <span>Good {{ date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') }}, {{ strtok($user->name, ' ') }}</span>
                <i class="fa-solid fa-crown text-amber-500 text-2xl"></i>
            </h1>
            <p class="body-text">
                Platform SaaS metrics: <strong class="text-[#0F172A] font-semibold">{{ $totalCompanies }}</strong> builder companies onboarded across active SaaS subscription plans.
            </p>
        </div>

        <div class="flex items-center space-x-3 shrink-0">
            <a href="{{ route('admin.companies.create') }}" class="px-5 py-2.5 bg-[#059669] hover:bg-[#047857] text-white btn-text rounded-xl shadow-sm transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Onboard New Company</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards Matrix with Micro-Trend Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Metric 1: Monthly MRR -->
        <div class="reos-card p-5 space-y-2">
            <div class="flex justify-between items-center">
                <span class="label-text">Platform MRR</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#059669] flex items-center justify-center font-bold text-base border border-emerald-100">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
            </div>
            <div class="kpi-number text-2xl">₹{{ number_format($totalPlatformRevenue) }}</div>
            <div class="inline-flex items-center space-x-1 text-xs font-semibold text-[#059669] bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                <i class="fa-solid fa-arrow-trend-up text-[10px]"></i>
                <span>Monthly Revenue</span>
            </div>
        </div>

        <!-- Metric 2: Registered Companies -->
        <div class="reos-card p-5 space-y-2">
            <div class="flex justify-between items-center">
                <span class="label-text">Active Tenants</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#059669] flex items-center justify-center font-bold text-base border border-emerald-200">
                    <i class="fa-solid fa-building"></i>
                </div>
            </div>
            <div class="kpi-number text-2xl">{{ $totalCompanies }} Tenants</div>
            <div class="inline-flex items-center space-x-1 text-xs font-semibold text-[#047857] bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                <i class="fa-solid fa-check text-[10px]"></i>
                <span>Real Estate Developers</span>
            </div>
        </div>

        <!-- Metric 3: Active Subscriptions -->
        <div class="reos-card p-5 space-y-2">
            <div class="flex justify-between items-center">
                <span class="label-text">Active Plans</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-[#7C3AED] flex items-center justify-center font-bold text-base border border-purple-100">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>
            <div class="kpi-number text-2xl">{{ $activeSubscriptions }} Active</div>
            <div class="inline-flex items-center space-x-1 text-xs font-semibold text-[#7C3AED] bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">
                <i class="fa-solid fa-sparkles text-[10px]"></i>
                <span>Paying Accounts</span>
            </div>
        </div>

        <!-- Metric 4: SaaS Tier Plans -->
        <div class="reos-card p-5 space-y-2">
            <div class="flex justify-between items-center">
                <span class="label-text">Tier Catalog</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-[#D97706] flex items-center justify-center font-bold text-base border border-amber-100">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
            <div class="kpi-number text-2xl">{{ $subscriptionPlans->count() }} Plans</div>
            <div class="inline-flex items-center space-x-1 text-xs font-semibold text-[#D97706] bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                <i class="fa-solid fa-gem text-[10px]"></i>
                <span>Configured Tiers</span>
            </div>
        </div>
    </div>

    <!-- Onboarded Companies Table Grid -->
    <div class="reos-card p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
            <div>
                <h2 class="section-heading">Onboarded Builder Companies</h2>
                <p class="body-text text-xs">Multi-Tenant Isolation Directory & Subscription Status</p>
            </div>
            <a href="{{ route('admin.companies.create') }}" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-[#047857] btn-text rounded-xl border border-emerald-200 transition">
                + Onboard Company
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
            <table class="w-full text-left text-xs text-[#0F172A]">
                <thead class="bg-[#F8FAFC] text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">Company Name</th>
                        <th class="py-3.5 px-4">Subscribed Plan</th>
                        <th class="py-3.5 px-4">Projects / Units</th>
                        <th class="py-3.5 px-4">Staff Members</th>
                        <th class="py-3.5 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @foreach($companies as $c)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-semibold text-[#0F172A] flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#059669] flex items-center justify-center border border-emerald-100 shrink-0">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <div>
                                <div class="table-text font-semibold">{{ $c->name }}</div>
                                <div class="text-[10px] text-[#64748B] font-mono font-normal">{{ $c->domain ?? 'reos.app' }}</div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-3 py-1 text-[11px] font-semibold rounded-full bg-emerald-50 text-[#047857] border border-emerald-200">
                                {{ $c->subscriptionPlan->name ?? 'Enterprise Plan' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-[#0F172A]">
                            {{ $c->projects->count() }} Projects / {{ $c->projects->sum(fn($p) => $p->units->count()) }} Units
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-[#0F172A]">
                            {{ $c->users->count() }} Users
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200">Active Tenant</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
