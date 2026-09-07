@extends('layouts.reos')

@section('title', 'SaaS Sub-Admin Management – REOS Platform')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <span>SaaS Platform Control</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-emerald-700">Sub-Admins & Permissions</span>
            </div>
            <h1 class="page-heading">SaaS Sub-Admins & Granular Access Control</h1>
            <p class="body-text mt-1">Create multiple SaaS Sub-Admins with restricted permissions and approval-gated sensitive actions.</p>
        </div>
        <div class="flex items-center space-x-3">
            @if($pendingApprovalsCount > 0)
                <a href="{{ route('admin.saas-approvals') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-sm transition flex items-center space-x-2 animate-pulse">
                    <i class="fa-solid fa-bell"></i>
                    <span>{{ $pendingApprovalsCount }} Pending SaaS Approvals</span>
                </a>
            @endif
            <button onclick="document.getElementById('createSubAdminModal').classList.remove('hidden')" class="px-4 py-2.5 bg-[#059669] hover:bg-[#047857] text-white rounded-xl font-bold text-xs shadow-sm transition flex items-center space-x-2 cursor-pointer">
                <i class="fa-solid fa-user-plus"></i>
                <span>Create New SaaS Sub-Admin</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-xs font-bold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-bold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    <!-- Available Permissions Reference Guide -->
    <div class="reos-card p-6 bg-slate-900 text-white">
        <h3 class="text-sm font-bold text-slate-100 flex items-center space-x-2 mb-3">
            <i class="fa-solid fa-shield-halved text-emerald-400"></i>
            <span>SaaS Sub-Admin Permission Modules Reference</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($availablePermissions as $slug => $perm)
                <div class="bg-slate-800/80 border border-slate-700 p-3 rounded-xl">
                    <div class="flex items-center space-x-2 text-xs font-bold text-emerald-400 mb-1">
                        <i class="fa-solid {{ $perm['icon'] }}"></i>
                        <span>{{ $perm['name'] }}</span>
                    </div>
                    <p class="text-[11px] text-slate-300 leading-snug">{{ $perm['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Sub-Admins Directory Table -->
    <div class="reos-card overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <h2 class="section-heading text-base flex items-center space-x-2">
                <i class="fa-solid fa-users-gear text-emerald-600"></i>
                <span>Active SaaS Sub-Admin Accounts ({{ $subAdmins->count() }})</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-extrabold uppercase text-slate-500 tracking-wider">
                        <th class="py-3 px-4">Sub-Admin User</th>
                        <th class="py-3 px-4">Contact Email & Phone</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Assigned SaaS Permissions</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-800">
                    @forelse($subAdmins as $admin)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 font-extrabold flex items-center justify-center border border-purple-200 text-xs">
                                        {{ strtoupper(substr($admin->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $admin->name }}</div>
                                        <div class="text-[10px] font-mono text-slate-400">ID: #SA-{{ $admin->id }} • Created {{ $admin->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-700">{{ $admin->email }}</div>
                                <div class="text-[11px] text-slate-400">{{ $admin->phone ?? 'No phone provided' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($admin->is_active)
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center space-x-1 w-max">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>Active Sub-Admin</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200 flex items-center space-x-1 w-max">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        <span>Suspended</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1 max-w-md">
                                    @php
                                        $perms = $admin->saas_permissions ?? [];
                                    @endphp
                                    @forelse($perms as $pSlug)
                                        @if(isset($availablePermissions[$pSlug]))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $availablePermissions[$pSlug]['badge'] }}">
                                                <i class="fa-solid {{ $availablePermissions[$pSlug]['icon'] }} text-[9px] mr-1"></i>
                                                {{ $availablePermissions[$pSlug]['name'] }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border bg-slate-100 text-slate-700 border-slate-200">
                                                {{ $pSlug }}
                                            </span>
                                        @endif
                                    @empty
                                        <span class="text-[11px] text-slate-400 italic">No permissions assigned (View only)</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button onclick="editSubAdmin({{ json_encode($admin) }})" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition border border-slate-200 cursor-pointer">
                                        <i class="fa-solid fa-sliders mr-1"></i> Edit Permissions
                                    </button>

                                    <form method="POST" action="{{ route('admin.sub-admins.destroy', $admin) }}" onsubmit="return confirm('Are you sure you want to remove sub-admin {{ $admin->name }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg text-xs transition border border-rose-200 cursor-pointer">
                                            <i class="fa-solid fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 font-semibold">
                                <i class="fa-solid fa-user-shield text-3xl text-slate-300 mb-2 block"></i>
                                No SaaS Sub-Admins created yet. Click "Create New SaaS Sub-Admin" to onboard staff accounts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Sub-Admin -->
<div id="createSubAdminModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full border border-slate-200 shadow-xl overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-900 text-base flex items-center space-x-2">
                <i class="fa-solid fa-user-plus text-emerald-600"></i>
                <span>Create New SaaS Sub-Admin Account</span>
            </h3>
            <button onclick="document.getElementById('createSubAdminModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.sub-admins.store') }}" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Rahul Sharma" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="rahul@reos.in" class="form-input">
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" placeholder="+91 98765 43210" class="form-input">
                </div>
                <div>
                    <label class="form-label">Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required placeholder="••••••••" class="form-input">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Confirm Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="form-input">
                </div>
            </div>

            <!-- Granular Permissions Checklist -->
            <div class="border-t border-slate-200 pt-4">
                <label class="form-label text-slate-900 mb-2">Assign SaaS Permissions & Access Modules</label>
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    @foreach($availablePermissions as $slug => $perm)
                        <label class="flex items-start space-x-3 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                            <input type="checkbox" name="saas_permissions[]" value="{{ $slug }}" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="text-xs font-bold text-slate-900 flex items-center space-x-1.5">
                                    <i class="fa-solid {{ $perm['icon'] }} text-emerald-600 text-[11px]"></i>
                                    <span>{{ $perm['name'] }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $perm['description'] }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-4">
                <button type="button" onclick="document.getElementById('createSubAdminModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs rounded-xl transition shadow-sm">Save Sub-Admin Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Sub-Admin -->
<div id="editSubAdminModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full border border-slate-200 shadow-xl overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-900 text-base flex items-center space-x-2">
                <i class="fa-solid fa-sliders text-emerald-600"></i>
                <span>Edit SaaS Sub-Admin Permissions</span>
            </h3>
            <button onclick="document.getElementById('editSubAdminModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="editSubAdminForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" id="edit_name" name="name" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" id="edit_email" name="email" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" id="edit_phone" name="phone" class="form-input">
                </div>
                <div>
                    <label class="form-label">Account Status</label>
                    <select id="edit_is_active" name="is_active" class="form-input">
                        <option value="1">Active</option>
                        <option value="0">Suspended / Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Edit Permissions Checklist -->
            <div class="border-t border-slate-200 pt-4">
                <label class="form-label text-slate-900 mb-2">Configure Granted SaaS Permissions</label>
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    @foreach($availablePermissions as $slug => $perm)
                        <label class="flex items-start space-x-3 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                            <input type="checkbox" id="edit_perm_{{ $slug }}" name="saas_permissions[]" value="{{ $slug }}" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="text-xs font-bold text-slate-900 flex items-center space-x-1.5">
                                    <i class="fa-solid {{ $perm['icon'] }} text-emerald-600 text-[11px]"></i>
                                    <span>{{ $perm['name'] }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $perm['description'] }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-4">
                <button type="button" onclick="document.getElementById('editSubAdminModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs rounded-xl transition shadow-sm">Update Permissions</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editSubAdmin(admin) {
        const form = document.getElementById('editSubAdminForm');
        form.action = `/admin/sub-admins/${admin.id}`;
        document.getElementById('edit_name').value = admin.name;
        document.getElementById('edit_email').value = admin.email;
        document.getElementById('edit_phone').value = admin.phone || '';
        document.getElementById('edit_is_active').value = admin.is_active ? '1' : '0';

        // Reset permissions checkboxes
        const checkboxes = form.querySelectorAll('input[name="saas_permissions[]"]');
        checkboxes.forEach(cb => cb.checked = false);

        // Check assigned permissions
        const perms = admin.saas_permissions || [];
        perms.forEach(slug => {
            const cb = document.getElementById(`edit_perm_${slug}`);
            if (cb) cb.checked = true;
        });

        document.getElementById('editSubAdminModal').classList.remove('hidden');
    }
</script>
@endsection
