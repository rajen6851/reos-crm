<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display Roles & Permissions Matrix for SaaS Founder and Company Admin.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin() && !$user->isCompanyAdmin() && !$user->isDirectorOrFounder()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to Permissions Control Center.');
        }

        // Initialize Standard Permissions in DB if empty
        $this->ensureDefaultPermissionsExist();

        // 1. SaaS Owner / Platform Super Admin Context
        $saasSubAdmins = [];
        $availableSaasPermissions = [];
        if ($user->isSaaSAdmin()) {
            $saasSubAdmins = User::where(function($q) {
                $q->where('is_saas_sub_admin', true)->orWhere('is_super_admin', true);
            })->latest()->get();

            $availableSaasPermissions = [
                'view_companies' => [
                    'name' => 'View Builder Companies',
                    'description' => 'Access and inspect registered builder tenant companies',
                    'module' => 'Platform',
                    'icon' => 'fa-building',
                ],
                'onboard_companies' => [
                    'name' => 'Onboard New Companies',
                    'description' => 'Create and onboard new tenant builder accounts',
                    'module' => 'Platform',
                    'icon' => 'fa-plus-circle',
                ],
                'edit_companies' => [
                    'name' => 'Edit Company Details',
                    'description' => 'Modify builder company settings and credentials',
                    'module' => 'Platform',
                    'icon' => 'fa-edit',
                ],
                'delete_companies' => [
                    'name' => 'Delete Builder Company',
                    'description' => 'Delete builder tenant company (Sub-Admins require SaaS Founder approval)',
                    'module' => 'Platform',
                    'icon' => 'fa-trash-alt',
                ],
                'manage_subscriptions' => [
                    'name' => 'Subscription Management',
                    'description' => 'Modify company SaaS plans and subscription statuses',
                    'module' => 'Subscriptions',
                    'icon' => 'fa-bolt',
                ],
                'delete_plans' => [
                    'name' => 'Delete SaaS Plans',
                    'description' => 'Delete SaaS pricing plans (Sub-Admins require SaaS Founder approval)',
                    'module' => 'Subscriptions',
                    'icon' => 'fa-trash-can',
                ],
                'manage_subadmins' => [
                    'name' => 'Manage SaaS Sub-Admins',
                    'description' => 'Create and configure other SaaS Sub-Admin accounts & permissions',
                    'module' => 'Security',
                    'icon' => 'fa-user-shield',
                ],
            ];
        }

        // 2. Builder Company Admin / Director & SaaS Admin Context
        $companies = Company::orderBy('name')->get();
        $companyRoles = collect();
        $companyUsers = collect();
        $allPermissionsGrouped = collect();

        $selectedCompanyId = $request->get('company_id');

        if ($user->isSaaSAdmin()) {
            // SaaS Admin: default to first company if not specified
            $targetCompanyId = $selectedCompanyId ?: ($companies->first()?->id ?? 1);
            
            $companyRoles = Role::where('company_id', $targetCompanyId)->with('permissions')->get();
            $companyUsers = User::where('company_id', $targetCompanyId)
                ->where('is_super_admin', false)
                ->with(['role.permissions'])
                ->latest()
                ->get();
        } else {
            // Builder Company Admin / Director
            $targetCompanyId = $user->company_id;
            
            $companyRoles = Role::where('company_id', $targetCompanyId)->with('permissions')->get();
            $companyUsers = User::where('company_id', $targetCompanyId)
                ->where('is_super_admin', false)
                ->with(['role.permissions'])
                ->latest()
                ->get();
        }

        $allPermissionsGrouped = Permission::all()->groupBy('module');

        return view('permissions.index', compact(
            'user',
            'companies',
            'selectedCompanyId',
            'saasSubAdmins',
            'availableSaasPermissions',
            'companyRoles',
            'companyUsers',
            'allPermissionsGrouped'
        ));
    }

    /**
     * Update permissions attached to a specific Company Role.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $user = Auth::user();

        if (!$user->isCompanyAdmin() && !$user->isDirectorOrFounder() && !$user->isSaaSAdmin()) {
            return back()->with('error', 'Unauthorized. Only Company Founder/Director or Admin can update role permissions.');
        }

        if ($user->company_id && $role->company_id && $role->company_id !== $user->company_id) {
            return back()->with('error', 'Unauthorized. Cannot modify roles of another company.');
        }

        $validated = $request->validate([
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $permissionIds = $validated['permission_ids'] ?? [];

        // Sync role permissions in pivot table
        $role->permissions()->sync($permissionIds);

        AuditLogService::log(
            'role_permissions_updated',
            "User {$user->name} updated permission matrix for role '{$role->name}' (Assigned: " . count($permissionIds) . " permissions).",
            $role
        );

        return back()->with('success', "Permissions for role '{$role->name}' updated successfully!");
    }

    /**
     * Update specified Staff User role and custom access rights.
     */
    public function updateUserPermissions(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isCompanyAdmin() && !$currentUser->isDirectorOrFounder() && !$currentUser->isSaaSAdmin()) {
            return back()->with('error', 'Unauthorized. Only Company Founder/Director or Admin can edit staff roles and permissions.');
        }

        if ($currentUser->company_id && $user->company_id !== $currentUser->company_id) {
            return back()->with('error', 'Unauthorized. Cannot modify staff of another company.');
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'required|boolean',
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $user->update([
            'role_id' => $role->id,
            'is_active' => $validated['is_active'],
        ]);

        AuditLogService::log(
            'user_role_updated',
            "User {$currentUser->name} updated role for staff member '{$user->name}' to '{$role->name}'.",
            $user
        );

        return back()->with('success', "Access permissions for staff member '{$user->name}' updated to '{$role->name}'!");
    }

    /**
     * Update specified SaaS Sub-Admin platform permissions.
     */
    public function updateSaasPermissions(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSaaSFounder()) {
            return back()->with('error', 'Only the main SaaS Founder can modify SaaS Sub-Admin permissions.');
        }

        if (!$user->is_saas_sub_admin) {
            return back()->with('error', 'Specified user is not a SaaS Sub-Admin.');
        }

        $validated = $request->validate([
            'saas_permissions' => 'nullable|array',
            'saas_permissions.*' => 'string',
            'is_active' => 'required|boolean',
        ]);

        $user->update([
            'saas_permissions' => $validated['saas_permissions'] ?? [],
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('success', "SaaS Sub-Admin '{$user->name}' platform permissions updated successfully!");
    }

    /**
     * Seed default permissions in `permissions` table if missing.
     */
    private function ensureDefaultPermissionsExist(): void
    {
        $defaultPermissions = [
            // Customer & Leads
            ['name' => 'View & Manage Customer Leads', 'slug' => 'manage-leads', 'module' => 'Leads', 'description' => 'View, create, and update customer inquiry leads'],
            ['name' => 'Assign Leads to Sales Team', 'slug' => 'assign-leads', 'module' => 'Leads', 'description' => 'Distribute and reallocate customer leads to sales executives'],
            ['name' => 'Delete / Archive Leads', 'slug' => 'delete-leads', 'module' => 'Leads', 'description' => 'Delete or archive customer leads'],
            ['name' => 'Export Leads Data (Excel/CSV)', 'slug' => 'export-leads', 'module' => 'Leads', 'description' => 'Download and export customer leads database'],

            // Properties & Inventory
            ['name' => 'Manage Projects & Buildings', 'slug' => 'manage-projects', 'module' => 'Inventory', 'description' => 'Create, edit, and update real estate projects & layout plans'],
            ['name' => 'Manage Unit Inventory & Pricing', 'slug' => 'manage-units', 'module' => 'Inventory', 'description' => 'Configure unit pricing, facing, carpet area, and availability status'],

            // Bookings & Agreements
            ['name' => 'Approve Unit Booking Locks', 'slug' => 'approve-bookings', 'module' => 'Bookings', 'description' => 'Authorize and approve customer unit booking tokens'],
            ['name' => 'Approve Agreement Step Skips', 'slug' => 'approve-agreement-skips', 'module' => 'Bookings', 'description' => 'Grant approval to bypass mandatory agreement workflow steps'],

            // Commissions & Finance
            ['name' => 'Manage Broker Commissions', 'slug' => 'manage-commissions', 'module' => 'Finance', 'description' => 'Configure partner commission slabs and calculate earned amounts'],
            ['name' => 'Process & Approve Payouts', 'slug' => 'process-payouts', 'module' => 'Finance', 'description' => 'Authorize and disburse commission payouts to channel partners'],

            // Team & Security
            ['name' => 'Manage Team Users & Roles', 'slug' => 'manage-users', 'module' => 'Users', 'description' => 'Onboard, edit, and manage team members and designations'],
            ['name' => 'Access Broker Channel Partner Portal', 'slug' => 'broker-access', 'module' => 'Channel Partners', 'description' => 'Access external broker portal features'],

            // Reports & Settings
            ['name' => 'View System Reports & Analytics', 'slug' => 'view-reports', 'module' => 'Reports', 'description' => 'Access executive sales reports, conversion analytics, and audit logs'],
            ['name' => 'Edit Company Settings & Branding', 'slug' => 'company-settings', 'module' => 'Settings', 'description' => 'Modify builder company tax info, address, logo, and settings'],
        ];

        foreach ($defaultPermissions as $perm) {
            Permission::firstOrCreate(
                ['slug' => $perm['slug']],
                $perm
            );
        }
    }
}
