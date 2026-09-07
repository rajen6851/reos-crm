<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SaasApprovalRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SaasSubAdminController extends Controller
{
    /**
     * Display a listing of SaaS Sub-Admins and permission management.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized. SaaS Admin access required.');
        }

        if (!$user->hasSaaSPermission('manage_subadmins')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to manage SaaS Sub-Admins.');
        }

        $subAdmins = User::where('is_saas_sub_admin', true)->latest()->get();
        $pendingApprovalsCount = SaasApprovalRequest::where('status', 'pending')->count();

        $availablePermissions = [
            'view_companies' => [
                'name' => 'View Builder Companies',
                'description' => 'View all registered builder companies and details',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icon' => 'fa-building',
            ],
            'onboard_companies' => [
                'name' => 'Onboard New Companies',
                'description' => 'Register and onboard new tenant builder companies',
                'badge' => 'bg-sky-50 text-sky-700 border-sky-200',
                'icon' => 'fa-plus-circle',
            ],
            'edit_companies' => [
                'name' => 'Edit Company Details',
                'description' => 'Update company information and details',
                'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'icon' => 'fa-edit',
            ],
            'delete_companies' => [
                'name' => 'Delete Company (Approval Required)',
                'description' => 'Request deletion of a tenant company (Requires SaaS Founder Approval)',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                'icon' => 'fa-trash-alt',
            ],
            'manage_subscriptions' => [
                'name' => 'Subscription Management',
                'description' => 'View, assign, and modify company SaaS subscriptions and plans',
                'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                'icon' => 'fa-gem',
            ],
            'delete_plans' => [
                'name' => 'Delete SaaS Plans (Approval Required)',
                'description' => 'Request deletion of SaaS subscription plans (Requires SaaS Founder Approval)',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon' => 'fa-trash-can',
            ],
            'manage_subadmins' => [
                'name' => 'Manage SaaS Sub-Admins',
                'description' => 'Create and configure other SaaS Sub-Admin accounts and permissions',
                'badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                'icon' => 'fa-user-shield',
            ],
        ];

        return view('admin.sub_admins.index', compact('subAdmins', 'availablePermissions', 'pendingApprovalsCount'));
    }

    /**
     * Store a newly created SaaS Sub-Admin in database.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSaaSAdmin() || !$currentUser->hasSaaSPermission('manage_subadmins')) {
            return back()->with('error', 'Unauthorized to create SaaS Sub-Admin accounts.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'saas_permissions' => 'nullable|array',
            'saas_permissions.*' => 'string',
        ]);

        $subAdmin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'company_id' => null,
            'role_id' => null,
            'is_active' => true,
            'is_super_admin' => false,
            'is_saas_sub_admin' => true,
            'saas_permissions' => $validated['saas_permissions'] ?? [],
        ]);

        return back()->with('success', "SaaS Sub-Admin '{$subAdmin->name}' created successfully!");
    }

    /**
     * Update specified SaaS Sub-Admin permissions or details.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSaaSAdmin() || !$currentUser->hasSaaSPermission('manage_subadmins')) {
            return back()->with('error', 'Unauthorized to modify SaaS Sub-Admins.');
        }

        if (!$user->is_saas_sub_admin) {
            return back()->with('error', 'User is not a SaaS Sub-Admin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
            'saas_permissions' => 'nullable|array',
            'saas_permissions.*' => 'string',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'],
            'saas_permissions' => $validated['saas_permissions'] ?? [],
        ]);

        return back()->with('success', "SaaS Sub-Admin '{$user->name}' updated successfully!");
    }

    /**
     * Remove or request deletion of a SaaS Sub-Admin.
     */
    public function destroy(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSaaSAdmin() || !$currentUser->hasSaaSPermission('manage_subadmins')) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($user->is_super_admin) {
            return back()->with('error', 'Cannot delete the main SaaS Founder / Super Admin account.');
        }

        // If performed by Sub-Admin, request approval
        if ($currentUser->isSaaSSubAdmin()) {
            SaasApprovalRequest::create([
                'requested_by_user_id' => $currentUser->id,
                'action_type' => 'delete_subadmin',
                'target_type' => User::class,
                'target_id' => $user->id,
                'target_name' => $user->name . " ({$user->email})",
                'payload' => ['user_id' => $user->id],
                'reason' => $request->input('reason', 'Sub-admin requested account removal.'),
                'status' => 'pending',
            ]);

            return back()->with('success', "Approval request submitted to SaaS Founder to delete sub-admin '{$user->name}'.");
        }

        // Super Admin can delete immediately
        $name = $user->name;
        $user->delete();

        return back()->with('success', "SaaS Sub-Admin '{$name}' deleted successfully.");
    }

    /**
     * List all SaaS Pending & Processed Approval Requests.
     */
    public function pendingApprovals()
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized.');
        }

        $pendingRequests = SaasApprovalRequest::with(['requestedBy', 'reviewedBy'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $historicalRequests = SaasApprovalRequest::with(['requestedBy', 'reviewedBy'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->take(30)
            ->get();

        return view('admin.approvals.index', compact('pendingRequests', 'historicalRequests'));
    }

    /**
     * Approve a SaaS Approval Request and execute payload.
     */
    public function approveRequest(Request $request, SaasApprovalRequest $approvalRequest, NotificationService $notificationService)
    {
        $user = Auth::user();

        if (!$user->isSaaSFounder()) {
            return back()->with('error', 'Only the main SaaS Founder can approve critical platform requests.');
        }

        if ($approvalRequest->status !== 'pending') {
            return back()->with('error', 'This approval request has already been processed.');
        }

        $reviewerNotes = $request->input('reviewer_notes', 'Approved by SaaS Founder');

        try {
            // Execute payload based on action type
            $this->executePayloadAction($approvalRequest);

            $approvalRequest->update([
                'status' => 'approved',
                'reviewed_by_user_id' => $user->id,
                'reviewer_notes' => $reviewerNotes,
                'executed_at' => now(),
            ]);

            // Notify sub-admin
            if ($approvalRequest->requestedBy) {
                $notificationService->notify(
                    $approvalRequest->requestedBy,
                    'saas_approval_success',
                    "Request Approved: {$approvalRequest->action_label}",
                    "Your request to {$approvalRequest->action_label} ({$approvalRequest->target_name}) was APPROVED by SaaS Founder. Notes: {$reviewerNotes}",
                    route('admin.saas-approvals')
                );
            }

            return back()->with('success', "Approval Request #{$approvalRequest->id} ('{$approvalRequest->action_label}') APPROVED & EXECUTED successfully!");
        } catch (\Throwable $e) {
            Log::error("[SAAS APPROVAL EXECUTION FAILED] " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', "Failed to execute request action: " . $e->getMessage());
        }
    }

    /**
     * Reject a SaaS Approval Request.
     */
    public function rejectRequest(Request $request, SaasApprovalRequest $approvalRequest, NotificationService $notificationService)
    {
        $user = Auth::user();

        if (!$user->isSaaSFounder()) {
            return back()->with('error', 'Only the main SaaS Founder can reject critical platform requests.');
        }

        if ($approvalRequest->status !== 'pending') {
            return back()->with('error', 'This approval request has already been processed.');
        }

        $reviewerNotes = $request->input('reviewer_notes', 'Rejected by SaaS Founder');

        $approvalRequest->update([
            'status' => 'rejected',
            'reviewed_by_user_id' => $user->id,
            'reviewer_notes' => $reviewerNotes,
        ]);

        // Notify sub-admin
        if ($approvalRequest->requestedBy) {
            $notificationService->notify(
                $approvalRequest->requestedBy,
                'saas_approval_rejected',
                "Request Rejected: {$approvalRequest->action_label}",
                "Your request to {$approvalRequest->action_label} ({$approvalRequest->target_name}) was REJECTED by SaaS Founder. Reason: {$reviewerNotes}",
                route('admin.saas-approvals')
            );
        }

        return back()->with('success', "Approval Request #{$approvalRequest->id} REJECTED.");
    }

    /**
     * Execute payload of an approved request.
     */
    private function executePayloadAction(SaasApprovalRequest $approvalRequest): void
    {
        $payload = $approvalRequest->payload ?? [];

        switch ($approvalRequest->action_type) {
            case 'delete_company':
                $companyId = $payload['company_id'] ?? $approvalRequest->target_id;
                $company = Company::find($companyId);
                if ($company) {
                    $company->delete();
                }
                break;

            case 'destroy_plan':
                $planId = $payload['plan_id'] ?? $approvalRequest->target_id;
                $plan = SubscriptionPlan::find($planId);
                if ($plan) {
                    $plan->delete();
                }
                break;

            case 'update_company_status':
                $companyId = $payload['company_id'] ?? $approvalRequest->target_id;
                $company = Company::find($companyId);
                if ($company) {
                    $company->update([
                        'status' => $payload['status'] ?? 'active',
                        'subscription_plan_id' => $payload['subscription_plan_id'] ?? $company->subscription_plan_id,
                        'subscription_expires_at' => isset($payload['subscription_plan_id']) ? now()->addDays(30) : $company->subscription_expires_at,
                    ]);
                }
                break;

            case 'delete_subadmin':
                $userId = $payload['user_id'] ?? $approvalRequest->target_id;
                $subAdminUser = User::find($userId);
                if ($subAdminUser && !$subAdminUser->is_super_admin) {
                    $subAdminUser->delete();
                }
                break;

            default:
                throw new \InvalidArgumentException("Unknown action type: {$approvalRequest->action_type}");
        }
    }
}
