@extends('layouts.reos')

@section('title', 'Permissions & Access Control Center')

@section('content')
<div x-data="{ 
    activeMainTab: '{{ auth()->user()->isSaaSAdmin() ? 'saas_platform' : 'company_roles' }}',
    selectedRoleId: '{{ collect($companyRoles)->first()?->id ?? 1 }}',
    roleSearch: ''
}" class="space-y-6 pb-12">

    <!-- Top Greeting Header & Page Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
        <div>
            <h1 class="text-xl font-bold text-[#0F172A] tracking-tight flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-lg shadow-2xs">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <span>Permissions & Access Control Center</span>
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Manage SaaS platform privileges, configure builder company role permissions, and inspect staff user access.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            @if(auth()->user()->isSaaSAdmin())
            <span class="px-3 py-1.5 bg-purple-50 text-purple-800 border border-purple-200 rounded-full font-bold text-xs flex items-center space-x-1.5 shadow-2xs">
                <i class="fa-solid fa-crown text-purple-600"></i>
                <span>SaaS Master Control</span>
            </span>
            @endif
            @if(auth()->user()->isDirectorOrFounder() || auth()->user()->isCompanyAdmin())
            <span class="px-3 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full font-bold text-xs flex items-center space-x-1.5 shadow-2xs">
                <i class="fa-solid fa-building-user text-emerald-600"></i>
                <span>Builder Admin</span>
            </span>
            @endif
        </div>
    </div>

    <!-- MAIN TOP-LEVEL NAVIGATION TABS -->
    <div class="bg-white rounded-xl border border-slate-200 p-1.5 shadow-2xs flex flex-wrap items-center gap-1.5">
        @if(auth()->user()->isSaaSAdmin())
        <button type="button" @click="activeMainTab = 'saas_platform'"
            :class="activeMainTab === 'saas_platform' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-100 font-semibold'"
            class="px-4 py-2 text-xs rounded-lg transition flex items-center space-x-2 cursor-pointer">
            <i class="fa-solid fa-crown text-xs"></i>
            <span>1. SaaS Sub-Admin Platform Privileges</span>
        </button>
        @endif

        {{-- @if(auth()->user()->company_id || auth()->user()->isCompanyAdmin() || auth()->user()->isDirectorOrFounder())
        <button type="button" @click="activeMainTab = 'company_roles'"
            :class="activeMainTab === 'company_roles' ? 'bg-[#0F172A] text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-100 font-semibold'"
            class="px-4 py-2 text-xs rounded-lg transition flex items-center space-x-2 cursor-pointer">
            <i class="fa-solid fa-table-cells text-xs"></i>
            <span>{{ auth()->user()->isSaaSAdmin() ? '2.' : '1.' }} Company Role & Feature Matrix</span>
        </button>

        <button type="button" @click="activeMainTab = 'staff_inspector'"
            :class="activeMainTab === 'staff_inspector' ? 'bg-[#0F172A] text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-100 font-semibold'"
            class="px-4 py-2 text-xs rounded-lg transition flex items-center space-x-2 cursor-pointer">
            <i class="fa-solid fa-users-gear text-xs"></i>
            <span>{{ auth()->user()->isSaaSAdmin() ? '3.' : '2.' }} Staff Access Inspector</span>
        </button>
        @endif --}}
    </div>

    <!-- TAB 1: SAAS SUB-ADMIN PLATFORM PERMISSIONS (Only visible when activeMainTab === 'saas_platform') -->
    @if(auth()->user()->isSaaSAdmin())
    <div x-show="activeMainTab === 'saas_platform'" class="space-y-4" x-cloak>
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] flex items-center space-x-2">
                        <i class="fa-solid fa-user-shield text-purple-600"></i>
                        <span>SaaS Sub-Admin Platform Privileges Matrix</span>
                    </h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        Assign module privileges for SaaS administrative team members across all builder tenants.
                    </p>
                </div>
                <a href="{{ route('admin.sub-admins.index') }}" class="px-3.5 py-2 bg-purple-600 text-white hover:bg-purple-700 font-bold text-xs rounded-lg border border-purple-700 transition shadow-2xs flex items-center space-x-1.5 w-fit">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Manage / Add Sub-Admins</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($saasSubAdmins as $admin)
                <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-4 flex flex-col justify-between hover:border-purple-200 transition">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-sm flex items-center justify-center shadow-2xs">
                                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $admin->name }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $admin->email }}</div>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $admin->is_super_admin ? 'bg-purple-100 text-purple-800 border border-purple-300' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                {{ $admin->is_super_admin ? 'SaaS Founder' : 'Sub-Admin' }}
                            </span>
                        </div>

                        @if(!$admin->is_super_admin)
                        <form action="{{ route('permissions.saas-users.update', $admin->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Assigned Platform Privileges:</div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-700 font-medium">
                                @foreach($availableSaasPermissions as $permSlug => $permInfo)
                                @php
                                    $isChecked = in_array($permSlug, $admin->saas_permissions ?? []);
                                @endphp
                                <label class="flex items-center space-x-2.5 p-2 rounded-lg bg-white border border-slate-200 hover:border-purple-300 transition cursor-pointer">
                                    <input type="checkbox" name="saas_permissions[]" value="{{ $permSlug }}" {{ $isChecked ? 'checked' : '' }} class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-xs font-semibold text-slate-800">{{ $permInfo['name'] }}</span>
                                </label>
                                @endforeach
                            </div>

                            <input type="hidden" name="is_active" value="{{ $admin->is_active ? 1 : 0 }}">

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-lg transition shadow-2xs flex items-center space-x-1.5 cursor-pointer">
                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                    <span>Save Sub-Admin Privileges</span>
                                </button>
                            </div>
                        </form>
                        @else
                        <div class="p-3 rounded-lg bg-purple-50 border border-purple-200 text-purple-900 font-semibold text-xs flex items-center space-x-2">
                            <i class="fa-solid fa-lock text-purple-600 text-sm"></i>
                            <span>Full Unrestricted SaaS Super-Admin Master Access Granted</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 2: BUILDER COMPANY ROLE & PERMISSIONS MATRIX (Only visible when activeMainTab === 'company_roles') -->
    @if(auth()->user()->company_id || auth()->user()->isCompanyAdmin() || auth()->user()->isDirectorOrFounder())
    <div x-show="activeMainTab === 'company_roles'" class="space-y-4" x-cloak>
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] flex items-center space-x-2">
                        <i class="fa-solid fa-shield-cat text-blue-600"></i>
                        <span>Company Roles & Feature Permissions Matrix</span>
                    </h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        Select a company role from the roster to customize exact module & feature capabilities.
                    </p>
                </div>

                @if(auth()->user()->isSaaSAdmin())
                <form method="GET" action="{{ route('permissions.index') }}" class="flex items-center space-x-2 bg-purple-50 p-2 rounded-xl border border-purple-200">
                    <label class="text-[11px] font-bold text-purple-900 shrink-0"><i class="fa-solid fa-building mr-1 text-purple-600"></i>Select Tenant:</label>
                    <select name="company_id" onchange="this.form.submit()" class="bg-white border border-purple-300 text-xs font-bold rounded-lg px-2.5 py-1 text-purple-950 focus:ring-purple-500 focus:border-purple-500 cursor-pointer">
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ ($selectedCompanyId ?? $companyRoles->first()?->company_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Column (4 cols): Roles List Selection -->
                <div class="lg:col-span-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Company Roles Roster</div>
                        <span class="text-[11px] font-bold text-slate-400 font-mono">{{ count($companyRoles) }} Roles</span>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($companyRoles as $role)
                        <button type="button" @click="selectedRoleId = '{{ $role->id }}'" 
                                :class="selectedRoleId == '{{ $role->id }}' ? 'border-blue-600 bg-blue-50/70 ring-2 ring-blue-500/20' : 'border-slate-200 bg-slate-50 hover:bg-white'"
                                class="w-full text-left p-3.5 rounded-xl border transition flex items-center justify-between cursor-pointer group">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg font-bold text-xs flex items-center justify-center transition"
                                     :class="selectedRoleId == '{{ $role->id }}' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700 group-hover:bg-slate-300'">
                                    <i class="fa-solid fa-user-tag text-xs"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $role->name }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ $role->slug }}</div>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-mono font-bold bg-white text-slate-700 border border-slate-200 shadow-2xs">
                                {{ $role->permissions->count() }} Perms
                            </span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Right Column (8 cols): Permission Toggles for Selected Role -->
                <div class="lg:col-span-8 bg-slate-50/50 p-5 rounded-xl border border-slate-200 space-y-4">
                    @foreach($companyRoles as $role)
                    <div x-show="selectedRoleId == '{{ $role->id }}'" class="space-y-4">
                        <form action="{{ route('permissions.roles.update', $role->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200 bg-white p-3.5 rounded-xl border">
                                <div>
                                    <h3 class="text-sm font-bold text-[#0F172A] flex items-center space-x-2">
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 font-mono text-[11px] rounded-md">{{ $role->slug }}</span>
                                        <span>Permissions: {{ $role->name }}</span>
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $role->description ?? 'Role permissions matrix configuration' }}</p>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg shadow-2xs transition cursor-pointer flex items-center space-x-1.5">
                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                    <span>Save {{ $role->name }} Matrix</span>
                                </button>
                            </div>

                            <div class="space-y-4 text-xs">
                                @foreach($allPermissionsGrouped as $moduleName => $permissionsList)
                                <div class="bg-white p-4 rounded-xl border border-slate-200 space-y-3">
                                    <div class="font-bold text-slate-900 text-xs uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <i class="fa-solid fa-folder-open text-blue-600"></i>
                                            <span>Module: {{ $moduleName }}</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono font-normal">{{ count($permissionsList) }} Features</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-800">
                                        @foreach($permissionsList as $perm)
                                        @php
                                            $hasPerm = $role->permissions->contains('id', $perm->id);
                                        @endphp
                                        <label class="flex items-start space-x-2.5 p-2.5 rounded-lg bg-slate-50/50 hover:bg-blue-50/40 border border-slate-200/80 hover:border-blue-200 transition cursor-pointer">
                                            <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" {{ $hasPerm ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <div>
                                                <div class="font-bold text-slate-900 text-xs">{{ $perm->name }}</div>
                                                <div class="text-[10px] text-slate-500 font-normal leading-tight mt-0.5">{{ $perm->description }}</div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-5 py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg shadow-xs transition cursor-pointer flex items-center space-x-1.5">
                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                    <span>Save {{ $role->name }} Matrix</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ─── COMPANY STAFF CONTROL PANEL (Director / Admin / Founder) ─────────────── --}}
    {{-- Mirrors the SaaS Sub-Admin Panel but for company-level staff management      --}}
    @if(!auth()->user()->isSaaSAdmin() && (auth()->user()->isDirectorOrFounder() || auth()->user()->isCompanyAdmin()))
    <div x-show="activeMainTab === 'company_roles'" class="space-y-0" x-cloak>
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5 space-y-5">

            {{-- Panel Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] flex items-center space-x-2">
                        <i class="fa-solid fa-users-gear text-blue-600"></i>
                        <span>Company Staff Access Control</span>
                    </h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        Manage individual staff members — reassign roles and toggle account access.
                    </p>
                </div>
                <a href="{{ route('users.index') }}"
                   class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-lg border border-slate-200 transition shadow-2xs flex items-center space-x-1.5 w-fit">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Manage / Add Staff &rarr;</span>
                </a>
            </div>

            {{-- Filter: only staff whose role is in Director's manageable scope --}}
            @php
                $assignableSlugs = $assignableRoles->pluck('slug')->toArray();
                $managedUsers    = $companyUsers->filter(fn($u) => in_array($u->role?->slug, $assignableSlugs));
            @endphp

            @if($managedUsers->isEmpty())
                <div class="py-10 text-center text-slate-400 text-sm font-semibold">
                    <i class="fa-solid fa-users-slash text-3xl mb-3 block text-slate-300"></i>
                    No staff members found under your management scope.
                </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($managedUsers as $staff)
                @php
                    $staffPerms = $staff->role?->permissions ?? collect();
                    $isActive   = $staff->is_active;
                @endphp
                <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-4 flex flex-col justify-between hover:border-blue-200 transition">
                    <div class="space-y-3">
                        {{-- Card Header: User Info + Badges --}}
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-[#0F172A] text-white font-bold text-sm flex items-center justify-center shadow-2xs">
                                    {{ strtoupper(substr($staff->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $staff->name }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $staff->email }}</div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full {{ $isActive ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }}">
                                    {{ $isActive ? 'Active' : 'Disabled' }}
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-800 border border-blue-200">
                                    {{ $staff->role?->name ?? 'No Role' }}
                                </span>
                            </div>
                        </div>

                        {{-- Active Permissions from Role --}}
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Active Permissions via Role:
                        </div>
                        @if($staffPerms->isEmpty())
                            <div class="text-xs text-slate-400 italic">No permissions assigned to this role yet.</div>
                        @else
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($staffPerms as $perm)
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $perm->name }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Control Form: Change Role + Status --}}
                    <form action="{{ route('permissions.users.update', $staff->id) }}" method="POST"
                          class="pt-3 border-t border-slate-200 space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wide block mb-1">Assign Role</label>
                                <select name="role_id" required
                                        class="w-full bg-white border border-slate-200 text-xs font-bold rounded-lg px-2.5 py-1.5 text-slate-800 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                                    @foreach($assignableRoles as $r)
                                        <option value="{{ $r->id }}" {{ $staff->role_id == $r->id ? 'selected' : '' }}>
                                            {{ $r->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wide block mb-1">Account Status</label>
                                <select name="is_active" required
                                        class="w-full bg-white border border-slate-200 text-xs font-bold rounded-lg px-2.5 py-1.5 text-slate-800 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                                    <option value="1" {{ $isActive ? 'selected' : '' }}>✅ Active</option>
                                    <option value="0" {{ !$isActive ? 'selected' : '' }}>🚫 Disabled</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs rounded-lg transition shadow-2xs flex items-center space-x-1.5 cursor-pointer">
                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                <span>Update Access</span>
                            </button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- TAB 3: STAFF ACCESS INSPECTOR (Only visible when activeMainTab === 'staff_inspector') -->
    <div x-show="activeMainTab === 'staff_inspector'" class="space-y-4" x-cloak>
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-[#0F172A] flex items-center space-x-2">
                        <i class="fa-solid fa-users-gear text-blue-600"></i>
                        <span>Staff User Access Inspector & Role Assignment</span>
                    </h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        Inspect active permissions for all staff users and quickly update assigned company roles.
                    </p>
                </div>
                <a href="{{ route('users.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-lg border border-slate-200 transition shadow-2xs flex items-center space-x-1.5 w-fit">
                    <i class="fa-solid fa-user-gear text-xs"></i>
                    <span>Manage Staff Roster &rarr;</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                        <tr>
                            <th class="p-3.5">Staff User</th>
                            <th class="p-3.5">Contact Email / Phone</th>
                            <th class="p-3.5">Assigned Role</th>
                            <th class="p-3.5">Active Permissions Count</th>
                            <th class="p-3.5 text-right">Status</th>
                            <th class="p-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($companyUsers as $staff)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3.5 font-bold text-slate-900 flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-[#0F172A] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                    {{ strtoupper(substr($staff->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $staff->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">ID: #{{ $staff->id }}</div>
                                </div>
                            </td>
                            <td class="p-3.5 font-mono text-slate-700">
                                <div>{{ $staff->email }}</div>
                                <div class="text-[10px] text-slate-400">{{ $staff->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-blue-50 text-blue-800 border border-blue-200">
                                    {{ $staff->role->name ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-slate-900">
                                {{ $staff->role ? $staff->role->permissions->count() : 0 }} Permissions
                            </td>
                            <td class="p-3.5 text-right">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full {{ $staff->is_active ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }}">
                                    {{ $staff->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <button onclick="document.getElementById('editStaffModal-{{ $staff->id }}').classList.remove('hidden')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-lg border border-slate-200 transition inline-flex items-center space-x-1.5 cursor-pointer shadow-2xs">
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                    <span>Change Access</span>
                                </button>

                                <!-- Staff Modal -->
                                <div id="editStaffModal-{{ $staff->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
                                    <div class="bg-white max-w-md w-full rounded-xl p-5 border border-slate-200 shadow-2xl text-left space-y-4">
                                        <div class="flex justify-between items-center pb-3 border-b border-slate-200">
                                            <h3 class="text-sm font-bold text-[#0F172A]">Edit Access: {{ $staff->name }}</h3>
                                            <button onclick="document.getElementById('editStaffModal-{{ $staff->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                                        </div>

                                        <form action="{{ route('permissions.users.update', $staff->id) }}" method="POST" class="space-y-3 text-xs">
                                            @csrf
                                            <div>
                                                <label class="form-label">Assigned Role</label>
                                                <select name="role_id" required class="form-input">
                                                    @foreach($assignableRoles as $r)
                                                        <option value="{{ $r->id }}" {{ $staff->role_id == $r->id ? 'selected' : '' }}>{{ $r->name }} ({{ $r->slug }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="form-label">Account Status</label>
                                                <select name="is_active" required class="form-input">
                                                    <option value="1" {{ $staff->is_active ? 'selected' : '' }}>Active Access Granted</option>
                                                    <option value="0" {{ !$staff->is_active ? 'selected' : '' }}>Disabled / Suspended</option>
                                                </select>
                                            </div>

                                            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                                                <button type="button" onclick="document.getElementById('editStaffModal-{{ $staff->id }}').classList.add('hidden')" class="px-3.5 py-1.5 bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200">Cancel</button>
                                                <button type="submit" class="px-4 py-1.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold rounded-lg shadow-xs">Update Access</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
