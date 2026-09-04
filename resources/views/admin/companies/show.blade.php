@extends('layouts.reos')

@section('title', $company->name . ' – Builder Tenant 360° Profile & Full History - REOS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ activeTab: 'projects' }">
    <!-- Breadcrumb & Top Action Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-[#E2E8F0]">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B]">
                <a href="{{ route('dashboard') }}" class="hover:text-[#4F46E5]">Home</a>
                <span>›</span>
                <a href="{{ route('admin.companies.index') }}" class="hover:text-[#4F46E5]">Companies</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">{{ $company->name }}</span>
            </div>
            <h1 class="page-heading text-2xl flex items-center space-x-3">
                <span>{{ $company->name }}</span>
                <span class="px-2.5 py-0.5 text-xs font-mono font-bold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200 uppercase">
                    {{ $company->status }}
                </span>
            </h1>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.companies.index') }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-[#0F172A] border border-[#E2E8F0] text-xs font-bold rounded-xl transition shadow-2xs flex items-center space-x-1.5">
                <i class="fa-solid fa-arrow-left text-slate-400 text-xs"></i>
                <span>Back to Directory</span>
            </a>

            <button type="button" onclick="openEditCompanyModal({{ json_encode($company) }})" class="px-5 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
                <span>Edit Specs</span>
            </button>
        </div>
    </div>

    <!-- 360° Hero Profile & Corporate Legal Information Card -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex items-start space-x-4">
                <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-[#4F46E5] to-[#6366F1] text-white font-extrabold text-2xl flex items-center justify-center shadow-md shrink-0 mt-1">
                    {{ strtoupper(substr($company->name, 0, 2)) }}
                </div>
                <div class="space-y-1">
                    <h2 class="text-2xl font-extrabold text-[#0F172A]">{{ $company->name }}</h2>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] font-semibold">
                        <span class="font-mono text-[#4F46E5] bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-100">{{ $company->code }}</span>
                        <span>•</span>
                        <span class="font-mono text-[#0F172A]"><i class="fa-regular fa-envelope text-slate-400 mr-1"></i>{{ $company->email }}</span>
                        <span>•</span>
                        <span class="font-mono text-[#0F172A]"><i class="fa-solid fa-phone text-slate-400 mr-1"></i>{{ $company->phone ?? 'Phone N/A' }}</span>
                    </div>

                    <!-- Corporate Office Address & RERA / Tax Details -->
                    <div class="pt-2 text-xs text-slate-600 flex flex-wrap items-center gap-3">
                        @if($company->address)
                        <div><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i>{{ $company->address }}</div>
                        @endif
                        @if($company->tax_number)
                        <div><i class="fa-solid fa-file-invoice text-emerald-600 mr-1"></i>Tax/GSTIN: <strong class="font-mono text-slate-900">{{ $company->tax_number }}</strong></div>
                        @endif
                        @if(!empty($company->settings['rera_number']))
                        <div><i class="fa-solid fa-certificate text-amber-500 mr-1"></i>RERA: <strong class="font-mono text-slate-900">{{ $company->settings['rera_number'] }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Active Plan & Days Remaining Card -->
            <div class="flex flex-col items-end space-y-2 shrink-0">
                <div class="flex items-center space-x-3 bg-slate-50 p-3.5 rounded-2xl border border-[#E2E8F0]">
                    <div class="text-right text-xs space-y-0.5">
                        <div class="text-[#64748B] font-semibold">Subscription Tier</div>
                        <div class="font-extrabold text-[#059669] text-base font-mono">{{ $company->subscriptionPlan->name ?? 'Standard Plan' }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-[#059669] border border-emerald-200 flex items-center justify-center font-bold text-lg shadow-2xs">
                        <i class="fa-solid fa-gem"></i>
                    </div>
                </div>
                <div class="text-[11px] text-slate-500 font-medium">
                    Onboarded: <strong class="text-slate-900 font-mono">{{ $company->created_at->format('d M Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- SaaS Plan Quota Limits Progress Bar Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-[#E2E8F0]">
            <!-- Quota 1: Team Users -->
            @php
                $uLimit = $usageSummary['plan']['max_users'] ?? 100;
                $uCurr = $usageSummary['usage']['users']['current'] ?? 0;
                $uPct = $uLimit ? min(100, round(($uCurr / $uLimit) * 100)) : 0;
            @endphp
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-600">Team Users Capacity</span>
                    <span class="font-mono text-[#4F46E5]">{{ $uCurr }} / {{ $uLimit ?: '∞' }}</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#4F46E5] h-2 rounded-full" style="width: {{ $uPct }}%"></div>
                </div>
            </div>

            <!-- Quota 2: Projects Capacity -->
            @php
                $pLimit = $usageSummary['plan']['max_projects'] ?? 50;
                $pCurr = $usageSummary['usage']['projects']['current'] ?? 0;
                $pPct = $pLimit ? min(100, round(($pCurr / $pLimit) * 100)) : 0;
            @endphp
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-600">Developer Projects Capacity</span>
                    <span class="font-mono text-indigo-700">{{ $pCurr }} / {{ $pLimit ?: '∞' }}</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $pPct }}%"></div>
                </div>
            </div>

            <!-- Quota 3: Monthly Leads Quota -->
            @php
                $lLimit = $usageSummary['plan']['max_leads_per_month'] ?? 1000;
                $lCurr = $usageSummary['usage']['monthly_leads']['current'] ?? 0;
                $lPct = $lLimit ? min(100, round(($lCurr / $lLimit) * 100)) : 0;
            @endphp
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-600">Monthly Lead Intake Limit</span>
                    <span class="font-mono text-emerald-700">{{ $lCurr }} / {{ $lLimit ?: '∞' }}</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#059669] h-2 rounded-full" style="width: {{ $lPct }}%"></div>
                </div>
            </div>
        </div>

        <!-- 6 Stat Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 text-xs pt-2">
            <!-- 1. Projects -->
            <div class="p-3.5 rounded-2xl bg-slate-50/90 border border-[#E2E8F0] space-y-1">
                <div class="text-[#64748B] font-semibold text-[11px]">Projects</div>
                <div class="text-xl font-extrabold text-[#0F172A] font-mono">{{ $company->projects->count() }}</div>
                <div class="text-[10px] text-slate-500">Developer Enclaves</div>
            </div>

            <!-- 2. Staff Team Users -->
            <div class="p-3.5 rounded-2xl bg-slate-50/90 border border-[#E2E8F0] space-y-1">
                <div class="text-[#64748B] font-semibold text-[11px]">Staff Users</div>
                <div class="text-xl font-extrabold text-[#4F46E5] font-mono">{{ $company->users->count() }}</div>
                <div class="text-[10px] text-slate-500">Team Roster</div>
            </div>

            <!-- 3. Total Intake Leads -->
            <div class="p-3.5 rounded-2xl bg-slate-50/90 border border-[#E2E8F0] space-y-1">
                <div class="text-[#64748B] font-semibold text-[11px]">Intake Leads</div>
                <div class="text-xl font-extrabold text-[#0F172A] font-mono">{{ number_format($totalLeads) }}</div>
                <div class="text-[10px] text-slate-500">Total Referrals</div>
            </div>

            <!-- 4. Converted Deals -->
            <div class="p-3.5 rounded-2xl bg-slate-50/90 border border-[#E2E8F0] space-y-1">
                <div class="text-[#64748B] font-semibold text-[11px]">Converted Deals</div>
                <div class="text-xl font-extrabold text-emerald-700 font-mono">{{ number_format($convertedLeads) }}</div>
                <div class="text-[10px] text-emerald-600 font-semibold">Closed Clients</div>
            </div>

            <!-- 5. Site Visits -->
            <div class="p-3.5 rounded-2xl bg-slate-50/90 border border-[#E2E8F0] space-y-1">
                <div class="text-[#64748B] font-semibold text-[11px]">Site Visits</div>
                <div class="text-xl font-extrabold text-amber-600 font-mono">{{ number_format($siteVisitsCount) }}</div>
                <div class="text-[10px] text-amber-600 font-semibold">Property Tours</div>
            </div>

            <!-- 6. Total Booked Token Revenue -->
            <div class="p-3.5 rounded-2xl bg-slate-50/90 border border-[#E2E8F0] space-y-1 col-span-2 sm:col-span-1">
                <div class="text-[#64748B] font-semibold text-[11px]">Token Revenue</div>
                <div class="text-xl font-extrabold text-[#059669] font-mono truncate">₹{{ number_format($totalRevenue) }}</div>
                <div class="text-[10px] text-emerald-700 font-semibold truncate">{{ $totalBookings }} Units Locked</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Projects, Staff Roster, Customer Leads, Bookings Ledger, Channel Brokers, Billing, Activity) -->
    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs overflow-hidden">
        <div class="p-2 bg-slate-50 border-b border-[#E2E8F0] flex items-center space-x-1 text-xs font-bold overflow-x-auto">
            <button type="button" @click="activeTab = 'projects'" :class="activeTab === 'projects' ? 'bg-white text-[#4F46E5] shadow-2xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-slate-900'" class="px-4 py-2.5 rounded-xl transition flex items-center space-x-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-city"></i>
                <span>Projects ({{ $company->projects->count() }})</span>
            </button>
            <button type="button" @click="activeTab = 'team'" :class="activeTab === 'team' ? 'bg-white text-[#4F46E5] shadow-2xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-slate-900'" class="px-4 py-2.5 rounded-xl transition flex items-center space-x-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-users"></i>
                <span>Staff Roster ({{ $company->users->count() }})</span>
            </button>
            <button type="button" @click="activeTab = 'leads'" :class="activeTab === 'leads' ? 'bg-white text-[#4F46E5] shadow-2xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-slate-900'" class="px-4 py-2.5 rounded-xl transition flex items-center space-x-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-chart-line"></i>
                <span>Leads ({{ $totalLeads }})</span>
            </button>
            <button type="button" @click="activeTab = 'bookings'" :class="activeTab === 'bookings' ? 'bg-white text-[#4F46E5] shadow-2xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-slate-900'" class="px-4 py-2.5 rounded-xl transition flex items-center space-x-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-file-contract"></i>
                <span>Bookings ({{ $totalBookings }})</span>
            </button>
            <button type="button" @click="activeTab = 'brokers'" :class="activeTab === 'brokers' ? 'bg-white text-[#4F46E5] shadow-2xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-slate-900'" class="px-4 py-2.5 rounded-xl transition flex items-center space-x-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-handshake"></i>
                <span>Brokers ({{ $companyBrokers->count() }})</span>
            </button>
            <button type="button" @click="activeTab = 'billing'" :class="activeTab === 'billing' ? 'bg-white text-[#4F46E5] shadow-2xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-slate-900'" class="px-4 py-2.5 rounded-xl transition flex items-center space-x-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-gem"></i>
                <span>Subscription Plan</span>
            </button>
            <button type="button" @click="activeTab = 'activity'" :class="activeTab === 'activity' ? 'bg-white text-[#4F46E5] shadow-2xs border border-[#E2E8F0]' : 'text-[#64748B] hover:text-slate-900'" class="px-4 py-2.5 rounded-xl transition flex items-center space-x-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Audit Logs</span>
            </button>
        </div>

        <!-- TAB 1: Projects Inventory Breakdown -->
        <div x-show="activeTab === 'projects'" class="p-6 space-y-4">
            <h3 class="section-heading text-base">Projects & Property Inventory Matrix</h3>

            @if($company->projects->isEmpty())
                <div class="p-6 text-center text-xs text-slate-400 font-medium bg-slate-50 rounded-2xl">
                    No projects created yet for this builder company.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($company->projects as $proj)
                    <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-extrabold text-[#0F172A] text-sm">{{ $proj->name }}</h4>
                                <p class="text-[11px] text-[#64748B] font-mono">{{ $proj->code }} • {{ $proj->city }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-50 text-[#4F46E5] uppercase">
                                {{ $proj->project_type }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-3 text-xs text-[#64748B] pt-2 border-t border-slate-200">
                            <span><strong class="text-[#0F172A]">{{ $proj->buildings->count() }}</strong> Towers</span>
                            <span>•</span>
                            <span><strong class="text-[#0F172A]">{{ $proj->units->count() }}</strong> Total Units</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- TAB 2: Company Team Roster -->
        <div x-show="activeTab === 'team'" class="p-6 space-y-4">
            <h3 class="section-heading text-base">Company Staff Team Roster</h3>

            <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="p-3.5">Staff Member</th>
                            <th class="p-3.5">System Access Role</th>
                            <th class="p-3.5">Email</th>
                            <th class="p-3.5">Phone</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] font-medium">
                        @foreach($company->users as $u)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-extrabold text-[#0F172A]">
                                {{ $u->name }}
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-[#4F46E5] border border-indigo-200">
                                    {{ $u->role->name ?? 'Staff User' }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono text-[#0F172A]">{{ $u->email }}</td>
                            <td class="p-3.5 font-mono text-[#0F172A]">{{ $u->phone ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: Recent Customer Leads -->
        <div x-show="activeTab === 'leads'" class="p-6 space-y-4">
            <h3 class="section-heading text-base">Recent Intake Leads</h3>

            @if($companyLeads->isEmpty())
                <div class="p-6 text-center text-xs text-slate-400 font-medium bg-slate-50 rounded-2xl">
                    No leads registered under this company yet.
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="p-3.5">Customer Name</th>
                                <th class="p-3.5">Phone</th>
                                <th class="p-3.5">Pipeline Stage</th>
                                <th class="p-3.5">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] font-medium">
                            @foreach($companyLeads as $ld)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3.5 font-extrabold text-[#0F172A]">{{ $ld->name }}</td>
                                <td class="p-3.5 font-mono text-[#0F172A]">{{ $ld->phone }}</td>
                                <td class="p-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-[#059669] border border-emerald-200 uppercase">
                                        {{ $ld->status }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-mono text-slate-500">{{ $ld->created_at->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- TAB 4: Unit Bookings & Revenue Ledger -->
        <div x-show="activeTab === 'bookings'" class="p-6 space-y-4">
            <h3 class="section-heading text-base">Confirmed Unit Bookings & Token Revenue</h3>

            @if($companyBookings->isEmpty())
                <div class="p-6 text-center text-xs text-slate-400 font-medium bg-slate-50 rounded-2xl">
                    No unit bookings recorded yet for this company.
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="p-3.5">Booking Code</th>
                                <th class="p-3.5">Buyer Name</th>
                                <th class="p-3.5">Unit Number</th>
                                <th class="p-3.5">Token Amount</th>
                                <th class="p-3.5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] font-medium">
                            @foreach($companyBookings as $bk)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3.5 font-mono font-bold text-[#4F46E5]">{{ $bk->booking_code }}</td>
                                <td class="p-3.5 font-extrabold text-[#0F172A]">{{ $bk->customer_name }}</td>
                                <td class="p-3.5 font-mono font-bold text-[#0F172A]">Unit {{ $bk->unit->unit_number ?? 'N/A' }}</td>
                                <td class="p-3.5 font-mono font-bold text-[#059669]">₹{{ number_format($bk->booking_amount) }}</td>
                                <td class="p-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-[#4F46E5] border border-indigo-200 uppercase">
                                        {{ $bk->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- TAB 5: Brokers Network -->
        <div x-show="activeTab === 'brokers'" class="p-6 space-y-4">
            <h3 class="section-heading text-base">Brokers Network</h3>

            @if($companyBrokers->isEmpty())
                <div class="p-6 text-center text-xs text-slate-400 font-medium bg-slate-50 rounded-2xl">
                    No brokers registered under this builder company yet.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($companyBrokers as $brk)
                    <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] flex items-center justify-between">
                        <div class="space-y-1">
                            <div class="font-extrabold text-[#0F172A] text-sm">{{ $brk->name }}</div>
                            <div class="text-xs text-[#64748B] font-mono">{{ $brk->firm_name ?? 'Individual Broker' }} • {{ $brk->phone }}</div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                            {{ $brk->category ?? 'Partner' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- TAB 6: Subscription Tier & Billing -->
        <div x-show="activeTab === 'billing'" class="p-6 space-y-4">
            <h3 class="section-heading text-base">Subscription Tier & Feature Entitlements</h3>

            <div class="p-5 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-4 max-w-xl">
                <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                    <span class="text-slate-600 font-bold">Assigned Plan Name:</span>
                    <span class="font-extrabold text-[#4F46E5] text-sm">{{ $company->subscriptionPlan->name ?? 'Standard Plan' }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                    <span class="text-slate-600 font-bold">Monthly Recurring Price:</span>
                    <span class="font-mono font-bold text-[#059669]">₹{{ number_format($company->subscriptionPlan->price_monthly ?? 0) }}/mo</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                    <span class="text-slate-600 font-bold">Subscription Expiry Date:</span>
                    <span class="font-mono font-bold text-[#0F172A]">{{ $company->subscription_expires_at ? \Carbon\Carbon::parse($company->subscription_expires_at)->format('d M Y') : 'Lifetime / Active' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-600 font-bold">Account Access Status:</span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#059669] border border-emerald-200 uppercase">
                        {{ $company->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- TAB 7: System Audit Activity Logs -->
        <div x-show="activeTab === 'activity'" class="p-6 space-y-4">
            <h3 class="section-heading text-base">System Audit Activity Log Trail</h3>

            @if($activities->isEmpty())
                <div class="p-6 text-center text-xs text-slate-400 font-medium bg-slate-50 rounded-2xl">
                    No recent activity audit logs recorded for this company.
                </div>
            @else
                <div class="space-y-2">
                    @foreach($activities as $act)
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1">
                        <div class="flex justify-between items-center font-bold text-slate-900">
                            <span><i class="fa-solid fa-bolt text-amber-500 mr-1.5"></i>{{ ucwords(str_replace('_', ' ', $act->event ?? 'Audit Activity')) }} by {{ $act->user->name ?? $act->user_name ?? 'User' }}</span>
                            <span class="text-[10px] font-mono text-slate-400">{{ $act->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <p class="text-slate-600 font-medium">{{ $act->description }}</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- RIGHT SLIDE-OVER DRAWER PANEL: Edit Company Specs -->
    <div id="editCompanyModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <div onclick="document.getElementById('editCompanyModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-[#E2E8F0]">
            <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200 text-[#4F46E5] flex items-center justify-center font-extrabold text-sm shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="section-heading text-lg">Edit Builder Specs</h3>
                        <p class="body-text text-xs text-[#64748B]">Update profile & plan settings</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('editCompanyModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <form id="editCompanyForm" method="POST" action="{{ route('admin.companies.update', $company->id) }}" class="p-6 overflow-y-auto flex-1 space-y-5 text-xs">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase mb-1.5">Company Name *</label>
                        <input type="text" id="edit_comp_name" name="name" value="{{ $company->name }}" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5]">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Company Email *</label>
                            <input type="email" id="edit_comp_email" name="email" value="{{ $company->email }}" required class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Phone Number</label>
                            <input type="tel" id="edit_comp_phone" name="phone" value="{{ $company->phone }}" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5]">
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                    <div>
                        <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Assigned Subscription Plan</label>
                        <select id="edit_comp_plan" name="subscription_plan_id" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs font-bold text-[#0F172A]">
                            @foreach($subscriptionPlans as $sp)
                                <option value="{{ $sp->id }}" {{ $company->subscription_plan_id == $sp->id ? 'selected' : '' }}>{{ $sp->name }} (₹{{ number_format($sp->price_monthly) }}/mo)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Account Access Status</label>
                        <select id="edit_comp_status" name="status" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs font-bold text-[#0F172A]">
                            <option value="active" {{ $company->status === 'active' ? 'selected' : '' }}>Active (Full Access)</option>
                            <option value="suspended" {{ $company->status === 'suspended' ? 'selected' : '' }}>Suspended (Access Blocked)</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="p-6 border-t border-[#E2E8F0] bg-slate-50/80 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('editCompanyModal').classList.add('hidden')" class="px-5 py-2.5 bg-white border border-[#E2E8F0] text-[#0F172A] btn-text rounded-xl hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" form="editCompanyForm" class="px-6 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text rounded-xl shadow-xs transition cursor-pointer">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditCompanyModal(company) {
        document.getElementById('editCompanyModal').classList.remove('hidden');
    }
</script>
@endsection
