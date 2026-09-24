@extends('layouts.reos')

@section('title', 'SaaS Subscriptions & Tenant Control – UrbanProperty SuperAdmin')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <div class="space-y-1">
                <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#2563EB]">Home</a>
                    <span>›</span>
                    <span class="text-[#0F172A] font-bold">SaaS Subscriptions</span>
                </div>
                <h1 class="page-heading text-2xl font-extrabold text-slate-900 tracking-tight">SaaS Subscription Plans & Tenant Companies</h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">SuperAdmin Management Center: Create/Delete SaaS plans, override company subscription packages, and activate or suspend builder tenant access.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 flex items-center space-x-3 shrink-0">
                <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white text-lg font-bold">
                    ₹
                </div>
                <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Monthly MRR</div>
                    <div class="text-xl font-bold text-emerald-700 font-mono">₹{{ number_format($totalPlatformRevenue, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full group-hover:scale-150 transition duration-500"></div>
            <div class="relative z-10">
                <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">Registered Companies</div>
                <div class="text-3xl font-black text-slate-900">{{ $totalCompanies }}</div>
                <div class="text-xs text-slate-500 font-medium mt-1">Real-Estate Builder Tenants</div>
            </div>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-50 rounded-full group-hover:scale-150 transition duration-500"></div>
            <div class="relative z-10">
                <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">Active Subscriptions</div>
                <div class="text-3xl font-black text-slate-900">{{ $activeSubscriptions }}</div>
                <div class="text-xs text-slate-500 font-medium mt-1">Growth & Starter Enterprise Plans</div>
            </div>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-indigo-50 rounded-full group-hover:scale-150 transition duration-500"></div>
            <div class="relative z-10">
                <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-2">SaaS Plans Available</div>
                <div class="text-3xl font-black text-slate-900">{{ count($subscriptionPlans) }}</div>
                <div class="text-xs text-slate-500 font-medium mt-1">Configured SaaS Packages</div>
            </div>
        </div>
    </div>

    <!-- Configuration Section (Single Source of Truth for Pricing Limits) -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900">Configure SaaS Subscription Tiers</h2>
                <p class="text-xs text-slate-500 mt-1">Create and manage pricing plans and team limits (e.g., 10, 100, 1000 Users).</p>
            </div>
        </div>

        <!-- Create New Plan Form -->
        <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="bg-slate-50 p-5 rounded-2xl border border-slate-200 flex flex-wrap gap-4 items-end">
            @csrf
            <div class="flex-1 min-w-[150px]">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Plan Name</label>
                <input type="text" name="name" placeholder="e.g. Starter (10 Users)" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-indigo-600">
            </div>
            <div class="w-24">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Price (&#8377;)</label>
                <input type="number" name="price" value="0" min="0" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-indigo-600">
            </div>
            <div class="w-28">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Max Users</label>
                <input type="number" name="max_users" value="10" min="1" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-indigo-600">
            </div>
            <div class="w-28">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Max Projects</label>
                <input type="number" name="max_projects" value="5" min="1" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-indigo-600">
            </div>
            <div class="w-28">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Billing Cycle</label>
                <select name="billing_cycle" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-indigo-600">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition">
                + Create Plan
            </button>
        </form>

        <!-- Existing Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($subscriptionPlans as $plan)
            <div class="p-4 rounded-xl border {{ $plan->is_active ? 'border-indigo-200 bg-indigo-50' : 'border-slate-200 bg-slate-50' }}">
                <div class="flex justify-between items-start mb-2">
                    <div class="font-black text-slate-900">{{ $plan->name }}</div>
                    <div class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </div>
                </div>
                <div class="text-xl font-black text-indigo-700 font-mono mb-3">&#8377;{{ number_format($plan->price) }} <span class="text-xs text-slate-500 font-sans">/ {{ $plan->billing_cycle }}</span></div>
                <div class="space-y-1 text-xs text-slate-600 font-medium">
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-users text-indigo-400 w-4"></i> <span>Up to <strong>{{ $plan->max_users }}</strong> Users</span></div>
                    <div class="flex items-center space-x-2"><i class="fa-solid fa-building text-indigo-400 w-4"></i> <span>Up to <strong>{{ $plan->max_projects }}</strong> Projects</span></div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-6 text-slate-500 text-xs font-semibold">
                No SaaS plans created yet. Use the form above to create 10, 100, and 1000 user plans.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Onboarded Tenant Real-Estate Companies Control Center Table -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900">Tenant Companies & SaaS Subscriptions Control</h2>
                <p class="text-xs text-slate-500 mt-1">SuperAdmin control to activate/suspend tenant access and override SaaS subscription plans</p>
            </div>
            <a href="{{ route('admin.companies.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Onboard New Builder Company</span>
            </a>
        </div>

        <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-slate-200 text-[10px] uppercase tracking-wider text-slate-500 font-bold">
                        <th class="p-4">Company Name</th>
                        <th class="p-4">Tenant Code</th>
                        <th class="p-4">Founder Email</th>
                        <th class="p-4">Users</th>
                        <th class="p-4">SaaS Package</th>
                        <th class="p-4">Account Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($companies as $comp)
                    <tr class="hover:bg-indigo-50/30 transition">
                        <td class="p-4 text-xs font-bold text-slate-900">{{ $comp->name }}</td>
                        <td class="p-4 text-xs font-mono font-bold text-indigo-600">{{ $comp->code ?? 'N/A' }}</td>
                        <td class="p-4 text-xs text-slate-600">{{ $comp->email }}</td>
                        <td class="p-4 text-xs font-medium text-slate-700">
                            <i class="fa-solid fa-users text-slate-400 mr-1"></i> {{ $comp->users_count }}
                        </td>
                        <td class="p-4 text-xs font-bold">
                            @if($comp->subscriptionPlan)
                                <span class="text-emerald-700 font-mono font-bold">{{ $comp->subscriptionPlan->name }}</span>
                                <div class="text-[10px] text-slate-500">&#8377;{{ number_format($comp->subscriptionPlan->price) }}/mo</div>
                            @else
                                <span class="text-amber-700 font-bold">No Plan Assigned</span>
                            @endif
                        </td>
                        <td class="p-4 text-xs">
                            @if($comp->status === 'active')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <svg class="mr-1 h-2 w-2 text-emerald-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                    <svg class="mr-1 h-2 w-2 text-rose-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                    {{ ucfirst($comp->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-xs text-right">
                            <form action="{{ route('admin.companies.subscription', $comp->id) }}" method="POST" class="inline-flex items-center space-x-2">
                                @csrf
                                <select name="subscription_plan_id" class="bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-indigo-600">
                                    @foreach($subscriptionPlans as $pl)
                                    <option value="{{ $pl->id }}" {{ ($comp->subscription_plan_id == $pl->id) ? 'selected' : '' }}>
                                        {{ $pl->name }} (&#8377;{{ number_format($pl->price) }})
                                    </option>
                                    @endforeach
                                </select>
                                
                                <select name="status" class="bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-indigo-600">
                                    <option value="active" {{ $comp->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ $comp->status === 'suspended' ? 'selected' : '' }}>Suspend</option>
                                </select>

                                <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                    Save
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
