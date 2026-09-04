@extends('layouts.reos')

@section('title', 'Onboard New Builder Company – REOS SuperAdmin')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Navigation Breadcrumb & Back Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <div class="inline-flex items-center space-x-2 text-xs font-bold text-indigo-700">
                <a href="{{ route('admin.saas-subscriptions') }}" class="hover:underline">SaaS Subscriptions</a>
                <span>/</span>
                <span class="text-slate-500">Onboard Company</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900"><i class="fa-solid fa-building text-indigo-600 mr-1"></i>Onboard New Builder Tenant Company</h1>
            <p class="text-xs text-slate-500">Register new real-estate company profile, create initial Admin account, and assign SaaS subscription plan.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.saas-subscriptions') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-200 transition">
                ← Cancel & Return
            </a>
        </div>
    </div>

    <!-- Error Validation Alert -->
    @if ($errors->any())
    <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-xs">
        <div class="font-black text-sm"><i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1"></i>Please correct the following errors:</div>
        <ul class="list-disc list-inside space-y-0.5 font-semibold">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Main Form Card -->
    <form id="onboardCompanyForm" method="POST" action="{{ route('admin.companies.store') }}" onsubmit="return validateCompanyForm(event)" class="space-y-8">
        @csrf

        <!-- Section 1: Company Profile Information -->
        <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">1. Company Profile & Details</h3>
                    <p class="text-xs text-slate-500">Official business name and unique tenant code</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Company Legal Name *</label>
                    <input type="text" id="company_name" name="name" value="{{ old('name') }}" required minlength="2" placeholder="e.g. Shree Ganesh Realty Pvt Ltd"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-600 font-bold">
                    <span id="name_error" class="text-[11px] text-rose-600 font-bold mt-1 hidden">Please enter a valid company name (at least 2 characters).</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Company Short Code (Prefix) *</label>
                    <input type="text" id="company_code" name="code" value="{{ old('code') }}" required maxlength="10" placeholder="e.g. SGR"
                        oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-indigo-900 font-mono font-bold uppercase focus:outline-none focus:border-indigo-600">
                    <span class="text-[10px] text-slate-400 mt-1 block">Short prefix used for lead codes and unit identifiers.</span>
                    <span id="code_error" class="text-[11px] text-rose-600 font-bold mt-1 hidden">Short code is required (letters & numbers only).</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Company Admin Profile -->
        <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">2. Initial Company Admin User Account</h3>
                    <p class="text-xs text-slate-500">Account login credentials for the company owner or administrator</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Admin Full Name *</label>
                    <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required minlength="2" placeholder="Ramesh Gupta"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-600 font-bold">
                    <span id="owner_error" class="text-[11px] text-rose-600 font-bold mt-1 hidden">Admin full name is required.</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Contact Phone Number (10 Digits) *</label>
                    <input type="text" id="admin_phone" name="phone" value="{{ old('phone') }}" required maxlength="10" placeholder="9876543210"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-600 font-mono font-bold">
                    <span id="phone_error" class="text-[11px] text-rose-600 font-bold mt-1 hidden">Please enter a valid 10-digit mobile number.</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Company Admin Email (Login ID) *</label>
                    <input type="email" id="admin_email" name="email" value="{{ old('email') }}" required placeholder="admin@shreeganesh.com"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-600 font-bold">
                    <span id="email_error" class="text-[11px] text-rose-600 font-bold mt-1 hidden">Please enter a valid email address.</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Initial Password (Min 6 Chars) *</label>
                    <input type="password" id="admin_password" name="password" required minlength="6" value="password123"
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-slate-900 focus:outline-none focus:border-indigo-600 font-mono">
                    <span class="text-[10px] text-slate-400 mt-1 block">Default initial password (User can change after login).</span>
                    <span id="password_error" class="text-[11px] text-rose-600 font-bold mt-1 hidden">Password must be at least 6 characters.</span>
                </div>
            </div>
        </div>

        <!-- Section 3: Initial SaaS Subscription Package Selection -->
        <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">3. SaaS Subscription Package Entitlement</h3>
                    <p class="text-xs text-slate-500">Select initial SaaS plan and active feature limits for this builder tenant</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($subscriptionPlans as $pl)
                <label class="p-5 rounded-2xl border border-slate-200 bg-slate-50 hover:border-indigo-500 transition cursor-pointer flex items-start space-x-3">
                    <input type="radio" name="subscription_plan_id" value="{{ $pl->id }}" {{ $loop->first ? 'checked' : '' }} class="mt-1 text-indigo-600 focus:ring-indigo-500">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center w-full">
                            <span class="font-black text-slate-900 text-sm">{{ $pl->name }}</span>
                            <span class="font-mono font-bold text-emerald-700 text-sm">₹{{ number_format($pl->price) }}/mo</span>
                        </div>
                        <p class="text-xs text-slate-500">Users: {{ $pl->max_users ?? 'Unlimited' }} | Projects: {{ $pl->max_projects ?? 'Unlimited' }} | Leads: {{ $pl->max_leads_per_month ?? 'Unlimited' }}/mo</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Submission Controls -->
        <div class="flex items-center justify-end space-x-4 pt-4">
            <a href="{{ route('admin.companies.index') }}" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-2xl border border-slate-200 transition">
                Cancel
            </a>
            <button type="submit" id="submitBtn" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs rounded-2xl shadow-md transition flex items-center space-x-2">
                <span id="btnText"><i class="fa-solid fa-rocket mr-1"></i>Complete Onboarding & Activate Company</span>
            </button>
        </div>
    </form>

    <script>
        function validateCompanyForm(event) {
            let isValid = true;

            // Reset errors
            document.querySelectorAll('[id$="_error"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('input').forEach(el => el.classList.remove('ring-2', 'ring-rose-500'));

            // Validate Company Name
            const name = document.getElementById('company_name').value.trim();
            if (name.length < 2) {
                document.getElementById('name_error').classList.remove('hidden');
                document.getElementById('company_name').classList.add('ring-2', 'ring-rose-500');
                isValid = false;
            }

            // Validate Company Code
            const code = document.getElementById('company_code').value.trim();
            if (code.length < 2) {
                document.getElementById('code_error').classList.remove('hidden');
                document.getElementById('company_code').classList.add('ring-2', 'ring-rose-500');
                isValid = false;
            }

            // Validate Admin Full Name
            const owner = document.getElementById('owner_name').value.trim();
            if (owner.length < 2) {
                document.getElementById('owner_error').classList.remove('hidden');
                document.getElementById('owner_name').classList.add('ring-2', 'ring-rose-500');
                isValid = false;
            }

            // Validate 10-digit Phone
            const phone = document.getElementById('admin_phone').value.trim();
            if (!/^[0-9]{10}$/.test(phone)) {
                document.getElementById('phone_error').classList.remove('hidden');
                document.getElementById('admin_phone').classList.add('ring-2', 'ring-rose-500');
                isValid = false;
            }

            // Validate Email Regex
            const email = document.getElementById('admin_email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('email_error').classList.remove('hidden');
                document.getElementById('admin_email').classList.add('ring-2', 'ring-rose-500');
                isValid = false;
            }

            // Validate Password
            const password = document.getElementById('admin_password').value;
            if (password.length < 6) {
                document.getElementById('password_error').classList.remove('hidden');
                document.getElementById('admin_password').classList.add('ring-2', 'ring-rose-500');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
                return false;
            }

            // Disable submit button during processing
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.innerText = '⏳ Onboarding Builder Company...';

            return true;
        }
    </script>

    <!-- Onboarded Tenant Companies List (Founder Created vs Self Registered Breakdown) -->
    <div class="p-6 md:p-8 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-[10px] font-black bg-purple-50 text-purple-700 uppercase tracking-widest border border-purple-200">
                    <i class="fa-solid fa-list-check text-purple-600 mr-1"></i><span>Onboarding Directory & Audit</span>
                </div>
                <h2 class="text-xl font-black text-slate-900 mt-1">Tenant Companies Onboarding History</h2>
                <p class="text-xs text-slate-500">Track companies created directly by SaaS Founder vs self-registered via website sign-up.</p>
            </div>

            @php
                $founderCount = $companies->filter(fn($c) => ($c->settings['onboarding_source'] ?? '') === 'founder_created')->count();
                $selfCount = $companies->count() - $founderCount;
            @endphp
            <div class="flex items-center space-x-3 text-xs font-mono">
                <span class="px-3.5 py-1.5 rounded-xl bg-purple-50 text-purple-900 border border-purple-200 font-bold">
                    <i class="fa-solid fa-crown text-purple-600 mr-1"></i>Founder Created: <strong>{{ $founderCount }}</strong>
                </span>
                <span class="px-3.5 py-1.5 rounded-xl bg-sky-50 text-sky-900 border border-sky-200 font-bold">
                    <i class="fa-solid fa-globe text-sky-600 mr-1"></i>Self Registered: <strong>{{ $selfCount }}</strong>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-800 font-extrabold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="p-4 rounded-l-xl">Company Name</th>
                        <th class="p-4">Tenant Code</th>
                        <th class="p-4">Onboarding Source</th>
                        <th class="p-4">Assigned Plan</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 rounded-r-xl">Joined Date</th>
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
                                <span class="px-3 py-1 text-xs font-black rounded-full bg-purple-100 text-purple-900 border border-purple-300 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-crown text-purple-600 mr-1"></i>
                                    <span>Founder Created</span>
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-black rounded-full bg-sky-100 text-sky-900 border border-sky-300 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-globe text-sky-600 mr-1"></i>
                                    <span>Self Registered</span>
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
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">Active</span>
                            @elseif($comp->status === 'suspended')
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-300">Suspended</span>
                            @else
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-900 border border-amber-300">Pending</span>
                            @endif
                        </td>
                        <td class="p-4 text-xs font-mono font-medium text-slate-600">
                            {{ $comp->created_at->format('d M Y, h:i A') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
