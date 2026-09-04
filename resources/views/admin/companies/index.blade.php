@extends('layouts.reos')

@section('title', 'Builder Tenant Companies Directory – REOS SaaS SuperAdmin')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Breadcrumb & Top Action Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-[#E2E8F0]">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B]">
                <a href="{{ route('dashboard') }}" class="hover:text-[#4F46E5]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Companies</span>
            </div>
            <h1 class="page-heading text-2xl flex items-center space-x-2">
                <span>Builder Tenant Companies</span>
                <span class="px-2.5 py-0.5 text-xs font-mono font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-200">
                    {{ $companies->count() }} Accounts
                </span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter & Search Controls -->
            <form method="GET" action="{{ route('admin.companies.index') }}" class="flex items-center space-x-2">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search company, domain, email..." class="bg-white border border-[#E2E8F0] text-xs rounded-xl pl-9 pr-4 py-2.5 text-[#0F172A] font-medium w-64 focus:outline-none focus:border-[#4F46E5] shadow-2xs">
                </div>
                <button type="submit" class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-[#0F172A] border border-[#E2E8F0] text-xs font-bold rounded-xl transition shadow-2xs flex items-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter text-slate-400 text-xs"></i>
                    <span>Filter</span>
                </button>
            </form>

            <!-- View Mode Selector (Grid / Table) -->
            <div class="bg-slate-100 p-1 rounded-xl border border-[#E2E8F0] flex items-center space-x-1">
                <button type="button" id="btnCompanyGridView" onclick="switchCompanyView('grid')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-[#0F172A] shadow-xs transition cursor-pointer flex items-center space-x-1">
                    <i class="fa-solid fa-grip-vertical"></i>
                </button>
                <button type="button" id="btnCompanyTableView" onclick="switchCompanyView('table')" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-900 transition cursor-pointer flex items-center space-x-1">
                    <i class="fa-solid fa-list"></i>
                </button>
            </div>

            <!-- Add Company Primary Button -->
            <a href="{{ route('admin.companies.create') }}" class="px-5 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>+ Add Company</span>
            </a>
        </div>
    </div>

    <!-- AI Account Intelligence Banner Card -->
    <div class="reos-card p-6 bg-white space-y-4">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
            <div class="flex items-center space-x-2">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-[#4F46E5] flex items-center justify-center font-bold text-xs">✨</span>
                <div>
                    <h2 class="section-heading text-base flex items-center space-x-2">
                        <span>AI Account Intelligence</span>
                    </h2>
                    <p class="body-text text-xs">Across <strong class="text-[#0F172A] font-semibold">{{ $companies->count() }}</strong> active customer builder accounts</p>
                </div>
            </div>
            <button class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-[#0F172A] btn-text text-xs rounded-xl border border-[#E2E8F0] shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                <span>View Account Insights</span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            </button>
        </div>

        <!-- 3 Key AI Intelligence Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-1 text-xs">
            <!-- 1. Average Account Health -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-[#64748B] font-semibold">
                    <i class="fa-solid fa-shield-halved text-[#059669]"></i>
                    <span>Average Account Health</span>
                </div>
                <div class="text-2xl font-extrabold text-[#0F172A] font-mono">71 / 100</div>
                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden border border-slate-200">
                    <div class="h-full bg-amber-500 rounded-full" style="width: 71%"></div>
                </div>
            </div>

            <!-- 2. Revenue Opportunity -->
            <div class="space-y-1">
                <div class="flex items-center space-x-2 text-[#64748B] font-semibold">
                    <i class="fa-solid fa-dollar-sign text-[#059669]"></i>
                    <span>Revenue Opportunity</span>
                </div>
                <div class="text-xl font-bold text-[#0F172A] font-mono">₹{{ number_format($totalCompanies * 45000) }} in {{ max(1, intval($totalCompanies * 0.6)) }} Accounts</div>
                <p class="body-text text-[11px]">At or above 85% seat utilisation</p>
            </div>

            <!-- 3. Customer Risk -->
            <div class="space-y-1">
                <div class="flex items-center space-x-2 text-[#64748B] font-semibold">
                    <i class="fa-solid fa-heart-pulse text-[#DC2626]"></i>
                    <span>Customer Risk</span>
                </div>
                <div class="text-xl font-bold text-[#0F172A] font-mono">1 Account · ₹{{ number_format(15000) }} ARR</div>
                <p class="body-text text-[11px]">Usage decline or elevated support volume</p>
            </div>
        </div>
    </div>

    <!-- GRID VIEW MODE (Exact Match to User Reference Screenshot with Red 3-Dots Button) -->
    <div id="companyGridContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($companies as $comp)
        @php
            $isFounderCreated = ($comp->settings['onboarding_source'] ?? '') === 'founder_created';
            $adminUser = $comp->users->firstWhere('role.slug', 'admin') ?? $comp->users->first();
            $planName = $comp->subscriptionPlan->name ?? 'Standard Plan';
            $initials = strtoupper(substr($comp->name, 0, 2));
            $avatarBgColors = ['bg-indigo-600', 'bg-emerald-600', 'bg-amber-600', 'bg-rose-600', 'bg-purple-600', 'bg-sky-600'];
            $bgPick = $avatarBgColors[$loop->index % count($avatarBgColors)];
        @endphp
        <div class="reos-card p-5 bg-white space-y-4 flex flex-col justify-between hover:shadow-md transition duration-200 relative">
            <div class="space-y-3">
                <!-- Card Header: Company Logo Avatar, Name, Rating & Red 3-Dots Menu -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $bgPick }} text-white font-extrabold text-xs flex items-center justify-center shadow-xs shrink-0">
                            {{ $initials }}
                        </div>
                        <div>
                            <a href="{{ route('admin.companies.show', $comp->id) }}" class="font-extrabold text-[#0F172A] text-sm leading-tight hover:text-[#4F46E5] transition cursor-pointer block">
                                {{ $comp->name }}
                            </a>
                            <div class="flex items-center space-x-1 mt-0.5">
                                <span class="text-amber-400 text-xs">★</span>
                                <span class="text-xs font-bold text-[#64748B] font-mono">4.5</span>
                            </div>
                        </div>
                    </div>

                    <!-- Red 3-Dots Vertical Options Button (Exact Match to User Reference Screenshot) -->
                    <div class="relative">
                        <button type="button" onclick="event.stopPropagation(); toggleCompanyMenu({{ $comp->id }});" class="w-8 h-9 rounded-xl bg-[#DC2626] hover:bg-[#B91C1C] text-white flex items-center justify-center transition shadow-md cursor-pointer active:scale-95" title="More Options">
                            <i class="fa-solid fa-ellipsis-vertical text-base pointer-events-none"></i>
                        </button>

                        <div id="companyMenu_{{ $comp->id }}" class="hidden absolute right-0 mt-2 w-36 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 z-50 text-xs space-y-1">
                            <!-- Option 1: Edit -->
                            <button type="button" onclick="event.stopPropagation(); openEditCompanyModal({{ json_encode($comp) }}); hideAllCompanyMenus();" class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-100 rounded-xl font-bold flex items-center space-x-2 transition cursor-pointer">
                                <i class="fa-solid fa-pen-to-square text-slate-500 text-xs"></i>
                                <span>Edit</span>
                            </button>

                            <!-- Option 2: Delete -->
                            <form method="POST" action="{{ route('admin.companies.destroy', $comp->id) }}" onsubmit="return confirm('Delete tenant company {{ $comp->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-3 py-2 text-rose-600 hover:bg-rose-50 rounded-xl font-bold flex items-center space-x-2 transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-rose-500 text-xs"></i>
                                    <span>Delete</span>
                                </button>
                            </form>

                            <!-- Option 3: Preview (Opens 360 Company History & Profile Blade Page) -->
                            <a href="{{ route('admin.companies.show', $comp->id) }}" class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-100 rounded-xl font-bold flex items-center space-x-2 transition block">
                                <i class="fa-solid fa-eye text-slate-500 text-xs"></i>
                                <span>Preview</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Rows -->
                <div class="space-y-1.5 text-xs text-[#64748B] pt-1">
                    <div class="flex items-center space-x-2 truncate">
                        <i class="fa-regular fa-envelope text-slate-400 text-xs w-4"></i>
                        <span class="truncate font-medium text-[#0F172A]">{{ $comp->email }}</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-phone text-slate-400 text-xs w-4"></i>
                        <span class="font-mono font-medium text-[#0F172A]">{{ $adminUser->phone ?? '+91 989757485' }}</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-globe text-slate-400 text-xs w-4"></i>
                        <span class="font-medium text-[#0F172A]">{{ $comp->domain ?? 'India' }}</span>
                    </div>
                </div>

                <!-- Tag Badges -->
                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-[#059669] border border-emerald-200">
                        {{ $planName }}
                    </span>
                    @if($isFounderCreated)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        Founder Account
                    </span>
                    @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-[#D97706] border border-amber-200">
                        Self Registered
                    </span>
                    @endif
                </div>
            </div>

            <!-- Card Footer: Quick Actions + Assigned Owner Avatar Thumbnail -->
            <div class="pt-3 border-t border-[#E2E8F0] flex items-center justify-between">
                <div class="flex items-center space-x-3 text-slate-500 text-xs">
                    <a href="mailto:{{ $comp->email }}" class="hover:text-[#4F46E5] transition" title="Send Email">
                        <i class="fa-regular fa-envelope"></i>
                    </a>
                    <a href="tel:{{ $adminUser->phone ?? '' }}" class="hover:text-[#059669] transition" title="Call Admin">
                        <i class="fa-solid fa-phone"></i>
                    </a>
                    <a href="#" class="hover:text-[#4F46E5] transition" title="Chat">
                        <i class="fa-regular fa-comment"></i>
                    </a>
                </div>

                <!-- Assigned Admin Owner Avatar -->
                <div class="w-6 h-6 rounded-full bg-slate-900 text-white font-bold text-[10px] flex items-center justify-center border border-white shadow-2xs" title="Admin: {{ $adminUser->name ?? 'Admin' }}">
                    {{ strtoupper(substr($adminUser->name ?? 'A', 0, 1)) }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- TABLE VIEW MODE -->
    <div id="companyTableContainer" class="hidden reos-card p-6 bg-white space-y-4">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-3">
            <h2 class="section-heading">Tenant Companies Table Directory</h2>
            <span class="text-xs font-mono font-bold text-[#64748B]">Total {{ $companies->count() }} Builder Accounts</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
            <table class="w-full text-left text-xs text-[#0F172A]">
                <thead class="bg-[#F8FAFC] text-[#64748B] font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Company Name</th>
                        <th class="py-3 px-4">Code / Domain</th>
                        <th class="py-3 px-4">Onboarding Source</th>
                        <th class="py-3 px-4">Assigned Plan</th>
                        <th class="py-3 px-4">Projects / Staff</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @foreach($companies as $comp)
                    @php
                        $isFounderCreated = ($comp->settings['onboarding_source'] ?? '') === 'founder_created';
                        $adminUser = $comp->users->firstWhere('role.slug', 'admin') ?? $comp->users->first();
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-semibold text-[#0F172A] flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-[#4F46E5] border border-indigo-200 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($comp->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="table-text font-bold">{{ $comp->name }}</div>
                                <div class="text-[10px] text-[#64748B] font-mono">{{ $comp->email }}</div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-[#4F46E5]">
                            {{ $comp->code }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($isFounderCreated)
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-purple-50 text-purple-700 border border-purple-200">Founder Created</span>
                            @else
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 text-[#D97706] border border-amber-200">Self Registered</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-[#059669]">
                            {{ $comp->subscriptionPlan->name ?? 'Standard Plan' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold">
                            {{ $comp->projects->count() }} Projects / {{ $comp->users->count() }} Users
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button type="button" onclick="openEditCompanyModal({{ json_encode($comp) }})" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center space-x-1 cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square text-amber-700"></i>
                                    <span>Edit</span>
                                </button>
                                <button type="button" onclick="openSubscriptionModal({{ json_encode($comp) }})" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-[#4F46E5] rounded-xl text-xs font-bold border border-indigo-200 transition">
                                    Subscription
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- RIGHT SLIDE-OVER DRAWER PANEL: Edit Company Details -->
    <div id="editCompanyModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop Blur -->
        <div onclick="document.getElementById('editCompanyModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-[#E2E8F0]">
            <!-- Header -->
            <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200 text-[#4F46E5] flex items-center justify-center font-extrabold text-sm shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="section-heading text-lg">Edit Builder Account Specs</h3>
                        <p class="body-text text-xs text-[#64748B]">Update company details & subscription tier</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('editCompanyModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <!-- Drawer Form Body (Scrollable) -->
            <form id="editCompanyForm" method="POST" action="" class="p-6 overflow-y-auto flex-1 space-y-5 text-xs">
                @csrf
                @method('PUT')

                <!-- Section 1: Company Profile -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#4F46E5] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building text-xs"></i>
                        <span>1. Company Profile</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Company Name *</label>
                        <input type="text" id="edit_comp_name" name="name" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Company Email *</label>
                            <input type="email" id="edit_comp_email" name="email" required class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Phone Number</label>
                            <input type="tel" id="edit_comp_phone" name="phone" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5]">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Subscription & Account Status -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#0F172A] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-[#059669] fa-shield-halved text-xs text-emerald-600"></i>
                        <span>2. Subscription & Status</span>
                    </div>

                    <div>
                        <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Assigned Subscription Plan</label>
                        <select id="edit_comp_plan" name="subscription_plan_id" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs font-bold text-[#0F172A]">
                            @foreach($subscriptionPlans as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->name }} (₹{{ number_format($sp->price_monthly) }}/mo)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Account Access Status</label>
                        <select id="edit_comp_status" name="status" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-3 py-2 text-xs font-bold text-[#0F172A]">
                            <option value="active">Active (Full Access)</option>
                            <option value="suspended">Suspended (Access Blocked)</option>
                            <option value="pending_subscription">Pending Subscription</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="p-6 border-t border-[#E2E8F0] bg-slate-50/80 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('editCompanyModal').classList.add('hidden')" class="px-5 py-2.5 bg-white border border-[#E2E8F0] text-[#0F172A] btn-text rounded-xl hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" form="editCompanyForm" class="px-6 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text rounded-xl shadow-xs transition cursor-pointer">Save Company Specs</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Subscription & Preview Details -->
    <div id="subscriptionModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="bg-white w-full max-w-md p-6 rounded-3xl space-y-4 border border-slate-200 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900" id="preview_comp_name">Company Preview</h3>
                <button type="button" onclick="document.getElementById('subscriptionModal').classList.add('hidden')" class="text-slate-400 font-bold hover:text-slate-600">✕</button>
            </div>

            <form id="subscriptionForm" method="POST" action="" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Subscription Plan</label>
                    <select id="preview_plan_select" name="subscription_plan_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 font-bold text-slate-900">
                        @foreach($subscriptionPlans as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->name }} (₹{{ number_format($sp->price_monthly) }}/mo)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Account Access Status</label>
                    <select id="preview_status_select" name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 font-bold text-slate-900">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="pending_subscription">Pending Subscription</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('subscriptionModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded-xl">Close</button>
                    <button type="submit" class="px-5 py-2 bg-[#4F46E5] hover:bg-[#4338CA] text-white font-bold rounded-xl shadow-xs">Save Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchCompanyView(mode) {
        const gridCont = document.getElementById('companyGridContainer');
        const tableCont = document.getElementById('companyTableContainer');
        const btnGrid = document.getElementById('btnCompanyGridView');
        const btnTable = document.getElementById('btnCompanyTableView');

        if (mode === 'grid') {
            gridCont.classList.remove('hidden');
            tableCont.classList.add('hidden');
            btnGrid.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-[#0F172A] shadow-xs transition cursor-pointer flex items-center space-x-1';
            btnTable.className = 'px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-900 transition cursor-pointer flex items-center space-x-1';
        } else {
            gridCont.classList.add('hidden');
            tableCont.classList.remove('hidden');
            btnTable.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-white text-[#0F172A] shadow-xs transition cursor-pointer flex items-center space-x-1';
            btnGrid.className = 'px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-900 transition cursor-pointer flex items-center space-x-1';
        }
    }

    function toggleCompanyMenu(id) {
        const targetMenu = document.getElementById('companyMenu_' + id);
        if (!targetMenu) return;
        
        const isCurrentlyHidden = targetMenu.classList.contains('hidden');
        hideAllCompanyMenus();
        
        if (isCurrentlyHidden) {
            targetMenu.classList.remove('hidden');
        }
    }

    function hideAllCompanyMenus() {
        const allMenus = document.querySelectorAll('[id^="companyMenu_"]');
        allMenus.forEach(menu => {
            menu.classList.add('hidden');
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="companyMenu_"]') && !e.target.closest('button[onclick*="toggleCompanyMenu"]')) {
            hideAllCompanyMenus();
        }
    });

    function openEditCompanyModal(company) {
        document.getElementById('editCompanyForm').action = "/admin/companies/" + company.id;
        document.getElementById('edit_comp_name').value = company.name || '';
        document.getElementById('edit_comp_email').value = company.email || '';
        document.getElementById('edit_comp_phone').value = company.phone || '';
        if (company.subscription_plan_id) {
            document.getElementById('edit_comp_plan').value = company.subscription_plan_id;
        }
        if (company.status) {
            document.getElementById('edit_comp_status').value = company.status;
        }
        document.getElementById('editCompanyModal').classList.remove('hidden');
    }

    function openSubscriptionModal(company) {
        document.getElementById('subscriptionForm').action = "/admin/companies/" + company.id + "/subscription";
        document.getElementById('preview_comp_name').innerText = company.name + " - Subscription & Access";
        if (company.subscription_plan_id) {
            document.getElementById('preview_plan_select').value = company.subscription_plan_id;
        }
        if (company.status) {
            document.getElementById('preview_status_select').value = company.status;
        }
        document.getElementById('subscriptionModal').classList.remove('hidden');
    }
</script>
@endsection
