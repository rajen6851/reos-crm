@extends('layouts.reos')

@section('title', 'Team & Staff Management - REOS')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ activeRoleTab: 'all' }">
    <!-- Header Banner -->
    <div class="bg-white rounded-3xl p-6 md:p-8 border border-[#E2E8F0] shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-[#64748B] mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#DC2626]">Home</a>
                <span>›</span>
                <span class="text-[#0F172A] font-bold">Team Users</span>
            </div>
            @if(auth()->user()->isManager())
                <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Sales Executives Management</h1>
                <p class="body-text text-xs text-[#64748B] mt-0.5">Add and manage internal Sales Executives for {{ auth()->user()->company->name ?? 'Company' }}</p>
            @else
                <h1 class="page-heading text-2xl font-extrabold text-[#0F172A]">Company Team & Staff Management</h1>
                <p class="body-text text-xs text-[#64748B] mt-0.5">Add and manage internal Sales Managers, Sales Executives, and Support Staff for {{ auth()->user()->company->name ?? 'Company' }}</p>
            @endif
        </div>
        <div>
            <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="px-5 py-3 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-user-plus text-white text-xs"></i>
                <span>{{ auth()->user()->isManager() ? '+ Add Sales Executive' : '+ Add Staff Member' }}</span>
            </button>
        </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Total Active Staff</span>
                <div class="text-2xl font-extrabold text-[#0F172A] mt-1 font-mono">{{ $users->count() }} Members</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-users text-[#4F46E5]"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Sales Executives</span>
                <div class="text-2xl font-extrabold text-[#059669] mt-1 font-mono">{{ $users->filter(fn($u) => str_contains(strtolower($u->role->name ?? ''), 'executive') || str_contains(strtolower($u->role->name ?? ''), 'sales'))->count() }} Executives</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#059669] flex items-center justify-center text-lg border border-emerald-200"><i class="fa-solid fa-user-tie text-[#059669]"></i></span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-[#E2E8F0] shadow-2xs flex justify-between items-center">
            <div>
                <span class="label-text text-[#64748B]">Team Converted Leads</span>
                <div class="text-2xl font-extrabold text-[#4F46E5] mt-1 font-mono">{{ $users->sum('converted_leads') }} Bookings</div>
            </div>
            <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-[#4F46E5] flex items-center justify-center text-lg border border-indigo-100"><i class="fa-solid fa-chart-line text-[#4F46E5]"></i></span>
        </div>
    </div>

    <!-- Role Filter Tabs -->
    <div class="flex items-center space-x-2 bg-white p-2 rounded-2xl border border-[#E2E8F0] shadow-2xs overflow-x-auto text-xs font-semibold">
        <button @click="activeRoleTab = 'all'" :class="activeRoleTab === 'all' ? 'bg-[#FEF2F2] text-[#DC2626] border-[#FEE2E2]' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-users"></i>
            <span>All Staff ({{ $users->count() }})</span>
        </button>

        <button @click="activeRoleTab = 'admin'" :class="activeRoleTab === 'admin' ? 'bg-amber-50 text-amber-900 border-amber-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-user-shield text-amber-600"></i>
            <span>Admins & Directors ({{ $users->filter(fn($u) => str_contains(strtolower($u->role->name ?? ''), 'admin') || str_contains(strtolower($u->role->name ?? ''), 'director') || str_contains(strtolower($u->role->name ?? ''), 'founder'))->count() }})</span>
        </button>

        <button @click="activeRoleTab = 'manager'" :class="activeRoleTab === 'manager' ? 'bg-sky-50 text-sky-800 border-sky-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-briefcase text-sky-600"></i>
            <span>Sales Managers ({{ $users->filter(fn($u) => str_contains(strtolower($u->role->name ?? ''), 'manager'))->count() }})</span>
        </button>

        <button @click="activeRoleTab = 'executive'" :class="activeRoleTab === 'executive' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'text-[#475569] border-transparent hover:bg-slate-50'" class="px-4 py-2 rounded-xl border transition flex items-center space-x-2 cursor-pointer shrink-0">
            <i class="fa-solid fa-headset text-emerald-600"></i>
            <span>Sales Executives ({{ $users->filter(fn($u) => str_contains(strtolower($u->role->name ?? ''), 'executive') || str_contains(strtolower($u->role->name ?? ''), 'sales'))->count() }})</span>
        </button>
    </div>

    <!-- Internal Users Directory Table -->
    <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] flex justify-between items-center">
            <h3 class="section-heading text-base">Internal Team Members Directory</h3>
            <span class="text-xs text-[#64748B] font-medium">Sorted by creation date</span>
        </div>

        @if($users->isEmpty())
            <div class="p-8 text-center text-slate-500 font-medium text-xs">No internal staff accounts found.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[#64748B] font-bold border-b border-[#E2E8F0] uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="p-4">Staff Name & Avatar</th>
                            <th class="p-4">Internal System Role</th>
                            <th class="p-4">Org, Branch & Dept</th>
                            <th class="p-4">Contact Info</th>
                            <th class="p-4">Leads Performance</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 font-mono">Joined Date</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] font-medium">
                        @foreach($users as $u)
                        @php
                            $roleSlug = strtolower($u->role->name ?? '');
                            $tabRole = 'executive';
                            if (str_contains($roleSlug, 'admin') || str_contains($roleSlug, 'director') || str_contains($roleSlug, 'founder')) {
                                $tabRole = 'admin';
                            } elseif (str_contains($roleSlug, 'manager')) {
                                $tabRole = 'manager';
                            }
                        @endphp
                        <tr x-show="activeRoleTab === 'all' || activeRoleTab === '{{ $tabRole }}'" class="hover:bg-slate-50/80 transition">
                            <td class="p-4 font-bold text-slate-900 flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-indigo-50 border border-indigo-200 text-[#4F46E5] flex items-center justify-center font-extrabold text-xs shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-[#0F172A] font-extrabold text-sm">{{ $u->name }}</div>
                                    @if($u->id === auth()->id())
                                        <span class="text-[10px] text-[#059669] font-bold uppercase tracking-wider">(Current Account)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-indigo-50 text-[#4F46E5] border border-indigo-200 inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-user-tie text-[#4F46E5] mr-1"></i>
                                    <span>{{ $u->role->name ?? 'Internal Staff' }}</span>
                                </span>
                            </td>
                            <td class="p-4 text-xs font-medium text-slate-700">
                                <div class="font-bold text-[#0F172A]">{{ $u->branch ?? 'Head Office' }}</div>
                                <div class="text-[11px] text-[#64748B]">{{ $u->department ?? 'Sales' }} • {{ $u->designation ?? 'Executive' }}</div>
                            </td>
                            <td class="p-4 text-xs font-mono">
                                <div class="text-[#0F172A] font-bold">{{ $u->email }}</div>
                                <div class="text-[#64748B] flex items-center space-x-1.5 mt-0.5">
                                    <span>{{ $u->phone }}</span>
                                    @if($u->phone)
                                    <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $u->phone) }}" target="_blank" class="px-1.5 py-0.5 rounded bg-emerald-50 text-[#059669] border border-emerald-200 text-[10px] font-bold hover:bg-emerald-100 transition">
                                        WA
                                    </a>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-xs">
                                <div class="font-mono font-bold text-[#0F172A]">{{ $u->total_leads ?? 0 }} Assigned Leads</div>
                                <div class="text-[11px] text-[#059669] font-bold">{{ $u->converted_leads ?? 0 }} Converted Bookings</div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-[#059669] border border-emerald-200">Active</span>
                            </td>
                            <td class="p-4 text-xs text-[#64748B] font-mono font-medium">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button onclick="openEditUserModal({{ json_encode($u) }})" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center space-x-1 cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-amber-700"></i>
                                        <span>Edit Specs</span>
                                    </button>

                                    @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $u->id) }}" onsubmit="return confirm('Are you sure you want to delete staff account {{ $u->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-[#DC2626] transition" title="Delete Staff Account">
                                            <i class="fa-solid fa-trash-can text-rose-500"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- RIGHT SLIDE-OVER DRAWER PANEL 1: Add Staff Member -->
    <div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop Blur -->
        <div onclick="document.getElementById('addUserModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-[#E2E8F0]">
            <!-- Header -->
            <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-200 text-[#DC2626] flex items-center justify-center font-extrabold text-sm shrink-0">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="section-heading text-lg">Add Staff Member</h3>
                        <p class="body-text text-xs text-[#64748B]">Create new internal employee account</p>
                    </div>
                </div>
                <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <!-- Form Body (Scrollable) -->
            <form id="addUserForm" method="POST" action="{{ route('users.store') }}" class="p-6 overflow-y-auto flex-1 space-y-5 text-xs">
                @csrf

                <!-- Section 1: Personal Info -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#4F46E5] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-user text-xs"></i>
                        <span>1. Personal Information</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Rohan Sharma" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" name="email" required placeholder="rohan@company.com" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Phone Number *</label>
                            <input type="tel" name="phone" required placeholder="9876543210" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Organization Specs -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#0F172A] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building-user text-xs text-slate-500"></i>
                        <span>2. Organization Specs</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Branch</label>
                            <input type="text" name="branch" value="Head Office" placeholder="Head Office" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-2.5 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Department</label>
                            <input type="text" name="department" value="Sales" placeholder="Sales" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-2.5 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Designation</label>
                            <input type="text" name="designation" value="Sr. Executive" placeholder="Sr. Exec" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-2.5 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Access Role & Security -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#059669] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                        <span>3. Access Role & Security</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">System Access Role *</label>
                        <select name="role_id" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Initial Password *</label>
                        <input type="password" name="password" required value="password123" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>
                </div>
            </form>

            <!-- Footer Actions -->
            <div class="p-6 border-t border-[#E2E8F0] bg-slate-50/80 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-5 py-2.5 bg-white border border-[#E2E8F0] text-[#0F172A] btn-text rounded-xl hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" form="addUserForm" class="px-6 py-2.5 bg-[#DC2626] hover:bg-[#B91C1C] text-white btn-text rounded-xl shadow-xs transition cursor-pointer">Create Staff Account</button>
            </div>
        </div>
    </div>

    <!-- RIGHT SLIDE-OVER DRAWER PANEL 2: Edit Staff Account Details (Fixed Form Styling) -->
    <div id="editUserModal" class="hidden fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop Blur -->
        <div onclick="document.getElementById('editUserModal').classList.add('hidden')" class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl z-50 flex flex-col justify-between transform transition-transform duration-300 ease-in-out border-l border-[#E2E8F0]">
            <!-- Ultra Premium Drawer Header -->
            <div class="p-6 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50/80">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200 text-[#4F46E5] flex items-center justify-center font-extrabold text-sm shrink-0">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div>
                        <h3 class="section-heading text-lg">Edit Staff Account Specs</h3>
                        <p class="body-text text-xs text-[#64748B]">Update employee profile, contact & system role</p>
                    </div>
                </div>
                <button onclick="document.getElementById('editUserModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <!-- Drawer Form Body (Scrollable with Section Dividers & Fixed Block Inputs) -->
            <form id="editUserForm" method="POST" action="" class="p-6 overflow-y-auto flex-1 space-y-5 text-xs">
                @csrf
                @method('PUT')

                <!-- Section 1: Basic Info -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#4F46E5] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-user text-xs"></i>
                        <span>1. Employee Profile Details</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Full Name *</label>
                        <input type="text" id="edit_name" name="name" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" id="edit_email" name="email" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">Phone Number *</label>
                            <input type="tel" id="edit_phone" name="phone" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Organization Specs -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-[#E2E8F0] space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#0F172A] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-building-user text-xs text-slate-500"></i>
                        <span>2. Organization & Department</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Branch</label>
                            <input type="text" id="edit_branch" name="branch" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-2.5 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Department</label>
                            <input type="text" id="edit_department" name="department" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-2.5 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                        </div>
                        <div>
                            <label class="block font-bold text-[10px] text-[#475569] uppercase mb-1">Designation</label>
                            <input type="text" id="edit_designation" name="designation" class="w-full bg-white border border-[#CBD5E1] rounded-xl px-2.5 py-2 text-xs text-[#0F172A] font-semibold focus:outline-none focus:border-[#4F46E5]">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Access Role & Security -->
                <div class="space-y-3">
                    <div class="text-[11px] font-extrabold uppercase text-[#059669] tracking-wider flex items-center space-x-1.5">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                        <span>3. Access Control & Password Reset</span>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">System Access Role *</label>
                        <select id="edit_role_id" name="role_id" required class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-extrabold focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-[#475569] uppercase tracking-wider mb-1.5">New Password (Optional)</label>
                        <input type="password" name="password" placeholder="••••••••" class="w-full bg-slate-50 border border-[#CBD5E1] rounded-xl px-3.5 py-2.5 text-xs text-[#0F172A] font-mono focus:outline-none focus:border-[#4F46E5] focus:bg-white transition">
                        <p class="text-[10px] text-[#64748B] mt-1 font-medium">Leave blank to keep current password unchanged.</p>
                    </div>
                </div>
            </form>

            <!-- Ultra Premium Drawer Footer Actions -->
            <div class="p-6 border-t border-[#E2E8F0] bg-slate-50/80 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="px-5 py-2.5 bg-white border border-[#E2E8F0] text-[#0F172A] btn-text rounded-xl hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" form="editUserForm" class="px-6 py-2.5 bg-[#4F46E5] hover:bg-[#4338CA] text-white btn-text rounded-xl shadow-xs transition cursor-pointer">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditUserModal(user) {
        document.getElementById('editUserForm').action = "/users/" + user.id;
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_branch').value = user.branch || 'Head Office';
        document.getElementById('edit_department').value = user.department || 'Sales';
        document.getElementById('edit_designation').value = user.designation || 'Executive';
        document.getElementById('edit_role_id').value = user.role_id || '';
        document.getElementById('editUserModal').classList.remove('hidden');
    }
</script>
@endsection
