@extends('layouts.reos')

@section('title', 'SaaS Subscriptions & Tenant Control – REOS SuperAdmin')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <!-- Top Hero Banner: Clean Light Header -->
    <div class="p-8 rounded-3xl bg-white border border-slate-200 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-[11px] font-black bg-indigo-50 text-indigo-700 uppercase tracking-widest border border-indigo-200">
                    <i class="fa-solid fa-crown text-purple-600"></i>
                    <span>Dedicated SaaS Subscriptions & Tenant Control Tower</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">SaaS Subscription Plans & Tenant Companies</h1>
                <p class="text-xs text-slate-600 max-w-xl">SuperAdmin Management Center: Create/Delete SaaS plans, override company subscription packages, and activate or suspend builder tenant access.</p>
            </div>
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white text-xl font-bold">
                    ₹
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Monthly MRR</div>
                    <div class="text-2xl font-black text-emerald-700 font-mono">₹{{ number_format($totalPlatformRevenue, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Feedback Messages -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs">
        <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold shadow-xs">
        <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start">
                <div class="text-xs font-extrabold uppercase text-indigo-700 tracking-wider">Registered Companies</div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-building"></i></div>
            </div>
            <div class="text-4xl font-black text-slate-900 mt-3 font-mono">{{ $totalCompanies }}</div>
            <div class="text-xs text-slate-500 mt-2 font-medium">Real-Estate Builder Tenants</div>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start">
                <div class="text-xs font-extrabold uppercase text-emerald-700 tracking-wider">Active Subscriptions</div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-bolt"></i></div>
            </div>
            <div class="text-4xl font-black text-emerald-700 mt-3 font-mono">{{ $activeSubscriptions }}</div>
            <div class="text-xs text-slate-500 mt-2 font-medium">Growth & Starter Enterprise Plans</div>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start">
                <div class="text-xs font-extrabold uppercase text-purple-700 tracking-wider">SaaS Plans Available</div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-gem"></i></div>
            </div>
            <div class="text-4xl font-black text-purple-700 mt-3 font-mono">{{ $subscriptionPlans->count() }}</div>
            <div class="text-xs text-slate-500 mt-2 font-medium">Configured SaaS Packages</div>
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

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-700 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4 rounded-l-xl">Company Name</th>
                        <th class="p-4">Tenant Code</th>
                        <th class="p-4">Source</th>
                        <th class="p-4">Assigned SaaS Plan</th>
                        <th class="p-4">Tenant Access Status</th>
                        <th class="p-4 rounded-r-xl">SuperAdmin Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($companies as $comp)
                    @php
                        $isFounderCreated = ($comp->settings['onboarding_source'] ?? '') === 'founder_created';
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-900 flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center font-black text-sm">
                                {{ strtoupper(substr($comp->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-black">{{ $comp->name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $comp->email }}</div>
                            </div>
                        </td>
                        <td class="p-4 font-mono font-bold text-indigo-700">
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-200">{{ $comp->code }}</span>
                        </td>
                        <td class="p-4">
                            @if($isFounderCreated)
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-purple-100 text-purple-900 border border-purple-300 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-crown text-purple-600 mr-1"></i><span>Founder</span>
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-sky-100 text-sky-900 border border-sky-300 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-globe text-sky-600 mr-1"></i><span>Self</span>
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-xs font-bold">
                            @if($comp->subscriptionPlan)
                                <span class="text-emerald-700 font-mono font-bold">{{ $comp->subscriptionPlan->name }}</span>
                                <div class="text-[10px] text-slate-500">₹{{ number_format($comp->subscriptionPlan->price) }}/mo</div>
                            @else
                                <span class="text-amber-700 font-bold">No Plan Assigned</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($comp->status === 'active')
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center space-x-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    <span>Active</span>
                                </span>
                            @elseif($comp->status === 'suspended')
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-300 inline-flex items-center space-x-1">
                                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                    <span>Suspended</span>
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-900 border border-amber-300 inline-flex items-center space-x-1">
                                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                                    <span>Pending Selection</span>
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            <!-- Founder Direct Quick Update Form -->
                            <form action="{{ route('admin.companies.subscription', $comp->id) }}" method="POST" class="flex items-center space-x-2">
                                @csrf
                                <select name="subscription_plan_id" class="bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-indigo-600">
                                    @foreach($subscriptionPlans as $pl)
                                    <option value="{{ $pl->id }}" {{ ($comp->subscription_plan_id == $pl->id) ? 'selected' : '' }}>
                                        {{ $pl->name }} (₹{{ number_format($pl->price) }})
                                    </option>
                                    @endforeach
                                </select>

                                <select name="status" class="bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1 text-xs font-bold text-slate-800 focus:outline-none focus:border-indigo-600">
                                    <option value="active" {{ $comp->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ $comp->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="pending_subscription" {{ $comp->status === 'pending_subscription' ? 'selected' : '' }}>Pending Selection</option>
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

    <!-- Create New SaaS Subscription Plan Section -->
    <div class="p-6 md:p-8 rounded-3xl bg-indigo-50/50 border border-indigo-200 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] font-black uppercase text-indigo-700 tracking-wider"><i class="fa-solid fa-bolt text-indigo-600 mr-1"></i>Create SaaS Package</span>
                <h2 class="text-xl font-black text-slate-900">Create New SaaS Subscription Plan</h2>
                <p class="text-xs text-slate-600">Define custom pricing, team user limits, active projects, and monthly lead quotas for tenant companies.</p>
            </div>
        </div>

        <form action="{{ route('admin.saas-plans.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Plan Name *</label>
                <input type="text" name="name" required placeholder="Enterprise Platinum"
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Monthly Price (₹) *</label>
                <input type="number" name="price" step="0.01" required placeholder="24999"
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Max Team Users</label>
                <input type="number" name="max_users" placeholder="50 (Leave blank for unlimited)"
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Max Active Projects</label>
                <input type="number" name="max_projects" placeholder="20 (Leave blank for unlimited)"
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Monthly CRM Leads Quota</label>
                <input type="number" name="max_leads_per_month" placeholder="10000 (Leave blank for unlimited)"
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Feature Badges (Comma Separated)</label>
                <input type="text" name="features" placeholder="CRM, Custom Domain, Dedicated RM, WhatsApp"
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-600">
            </div>

            <div class="md:col-span-3 flex justify-end pt-2">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition">
                    + Create & Publish SaaS Plan
                </button>
            </div>
        </form>
    </div>

    <!-- Platform SaaS Plan Master Catalog Reference with Delete Action -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900">REOS Platform SaaS Plans & Quotas Master</h2>
                <p class="text-xs text-slate-500">Configured subscription packages and user/lead entitlements</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($subscriptionPlans as $plan)
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-4 relative">
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-black text-slate-900 text-lg">{{ $plan->name }}</h4>
                        <div class="text-2xl font-black text-emerald-700 font-mono mt-0.5">₹{{ number_format($plan->price) }} <span class="text-xs text-slate-500 font-sans font-normal">/{{ $plan->billing_cycle }}</span></div>
                    </div>
                    
                    <form action="{{ route('admin.saas-plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete the {{ $plan->name }} plan?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-2.5 py-1 bg-rose-100 hover:bg-rose-200 text-rose-800 font-bold text-[11px] rounded-lg border border-rose-300 transition">
                            <i class="fa-solid fa-trash-can text-rose-600 mr-1"></i>Delete
                        </button>
                    </form>
                </div>

                <div class="space-y-2 text-xs text-slate-700 pt-3 border-t border-slate-200 font-medium">
                    <div class="flex justify-between"><span><i class="fa-solid fa-users text-slate-500 mr-1"></i>Max Team Users:</span> <strong class="text-slate-900 font-mono">{{ $plan->max_users ?? 'Unlimited' }}</strong></div>
                    <div class="flex justify-between"><span><i class="fa-solid fa-building text-slate-500 mr-1"></i>Max Active Projects:</span> <strong class="text-slate-900 font-mono">{{ $plan->max_projects ?? 'Unlimited' }}</strong></div>
                    <div class="flex justify-between"><span><i class="fa-solid fa-chart-line text-slate-500 mr-1"></i>Monthly CRM Leads:</span> <strong class="text-slate-900 font-mono">{{ $plan->max_leads_per_month ?? 'Unlimited' }}</strong></div>
                </div>

                @if(is_array($plan->features) && count($plan->features) > 0)
                <div class="pt-2 border-t border-slate-200 flex flex-wrap gap-1">
                    @foreach($plan->features as $feat)
                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold border border-indigo-200"><i class="fa-solid fa-bolt text-indigo-600 mr-1"></i>{{ $feat }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
