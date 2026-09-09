<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Role;
use App\Models\SaasApprovalRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-users');

        $user = Auth::user();
        
        $query = User::where('company_id', $user->company_id)
            ->where('is_super_admin', false)
            ->whereHas('role', function ($q) {
                $q->where('slug', '!=', 'broker');
            });

        // Sales Managers can only manage Sales Executives
        if ($user->isManager()) {
            $query->whereHas('role', function ($q) {
                $q->whereIn('slug', ['sales_executive', 'executive']);
            });
        }

        $users = $query->with(['role', 'reportingManager'])
            ->withCount([
                'leads as total_leads',
                'leads as converted_leads' => function ($q) {
                    $q->whereIn('status', ['converted', 'booked']);
                }
            ])
            ->latest()
            ->get();

        $rolesQuery = Role::where(function ($q) use ($user) {
            $q->whereNull('company_id')->orWhere('company_id', $user->company_id);
        })->whereNotIn('slug', ['broker', 'support_team', 'field_team']);

        if ($user->isManager()) {
            $rolesQuery->whereIn('slug', ['sales_executive', 'executive']);
        }

        $roles = $rolesQuery->get();

        $managers = User::where('company_id', $user->company_id)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['manager', 'sales_manager']);
            })->get();

        // Fetch Pending Critical Approval Requests for Director / Founder / Main Owner
        $pendingUserApprovals = SaasApprovalRequest::where('company_id', $user->company_id)
            ->whereIn('action_type', ['delete_user', 'promote_user', 'create_admin_user', 'update_user_role'])
            ->where('status', 'pending')
            ->with(['requestedBy'])
            ->latest()
            ->get();

        return view('users.index', compact('users', 'roles', 'pendingUserApprovals', 'managers'));
    }

    public function store(Request $request, NotificationService $notificationService)
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'branch' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'reporting_manager_id' => 'nullable|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:6',
            'agency_name' => 'nullable|string|max:150',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $currentUser = Auth::user();
        $role = Role::findOrFail($validated['role_id']);

        if (in_array($role->slug, ['broker', 'support_team', 'field_team'])) {
            return back()->with('error', 'The selected role cannot be assigned to new staff members.');
        }

        if ($currentUser->isManager() && !in_array($role->slug, ['sales_executive', 'executive'])) {
            return back()->with('error', 'Managers can only create Sales Executive accounts.');
        }

        $isCriticalRole = in_array($role->slug, ['admin', 'director', 'founder']);

        // CRITICAL APPROVAL FLOW: If non-Director tries to create Admin/Director role, request approval from Founder/Director
        if (!$currentUser->isDirectorOrFounder() && $isCriticalRole) {
            $approval = SaasApprovalRequest::create([
                'company_id' => $currentUser->company_id,
                'requested_by_user_id' => $currentUser->id,
                'action_type' => 'create_admin_user',
                'target_name' => $validated['name'] . " (" . $validated['email'] . ")",
                'payload' => array_merge($validated, ['role_slug' => $role->slug]),
                'reason' => "Admin {$currentUser->name} requested to create new {$role->name} account.",
                'status' => 'pending',
            ]);

            // Notify Directors / Founders in company
            $directors = User::where('company_id', $currentUser->company_id)
                ->whereHas('role', fn($q) => $q->whereIn('slug', ['director', 'founder']))
                ->get();

            foreach ($directors as $director) {
                $notificationService->notify(
                    $director,
                    'critical_approval_request',
                    "🚨 Critical Approval Needed: Create {$role->name} Account",
                    "Admin {$currentUser->name} requested to create a new {$role->name} account for '{$validated['name']}'. Please review and approve.",
                    route('users.index')
                );
            }

            return redirect()->route('users.index')->with('warning', "⚠️ Approval Request Submitted! Creating an Admin/Director user requires Company Director / Main Owner verification. Request sent to Director/Founder.");
        }

        // Direct Execution for Directors/Founders or standard staff creation
        $newUser = User::create([
            'company_id' => $currentUser->company_id,
            'role_id' => $role->id,
            'reporting_manager_id' => $validated['reporting_manager_id'] ?? ($currentUser->isManager() ? $currentUser->id : null),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'branch' => $validated['branch'] ?? 'Head Office',
            'department' => $validated['department'] ?? 'Sales',
            'designation' => $validated['designation'] ?? 'Executive',
            'password' => Hash::make($validated['password']),
        ]);

        // Dispatch Welcome Email with Login Credentials
        try {
            \Illuminate\Support\Facades\Mail::to($newUser->email)
                ->send(new \App\Mail\UserWelcomeCredentialsMail($newUser, $validated['password']));
        } catch (\Throwable $e) {
            Log::warning("[WELCOME MAIL ERROR] Failed to send credentials email: " . $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', "Team user {$newUser->name} created successfully!");
    }

    public function update(Request $request, User $user, NotificationService $notificationService)
    {
        Gate::authorize('manage-users');

        $currentUser = Auth::user();
        if ($currentUser->isManager() && !in_array($user->role?->slug, ['sales_executive', 'executive'])) {
            return back()->with('error', 'Managers can only edit Sales Executive accounts.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'branch' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'reporting_manager_id' => 'nullable|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:6',
        ]);

        $targetRole = Role::findOrFail($validated['role_id']);
        $isCriticalTargetRole = in_array($targetRole->slug, ['admin', 'director', 'founder']) || in_array($user->role?->slug, ['admin', 'director', 'founder']);

        // CRITICAL APPROVAL FLOW: If non-Director tries to edit Admin/Director role or user, send approval request
        if (!$currentUser->isDirectorOrFounder() && $isCriticalTargetRole) {
            SaasApprovalRequest::create([
                'company_id' => $currentUser->company_id,
                'requested_by_user_id' => $currentUser->id,
                'action_type' => 'update_user_role',
                'target_type' => User::class,
                'target_id' => $user->id,
                'target_name' => $user->name . " (" . $user->email . ")",
                'payload' => array_merge($validated, ['user_id' => $user->id]),
                'reason' => "Admin {$currentUser->name} requested to modify role/permissions of '{$user->name}'.",
                'status' => 'pending',
            ]);

            $directors = User::where('company_id', $currentUser->company_id)
                ->whereHas('role', fn($q) => $q->whereIn('slug', ['director', 'founder']))
                ->get();

            foreach ($directors as $director) {
                $notificationService->notify(
                    $director,
                    'critical_approval_request',
                    "🚨 Critical Approval Needed: Update Staff Role/Permissions",
                    "Admin {$currentUser->name} requested to update role/permissions for '{$user->name}'. Please review and approve.",
                    route('users.index')
                );
            }

            return redirect()->route('users.index')->with('warning', "⚠️ Approval Request Submitted! Modifying Admin or Director permissions requires Company Director / Main Owner verification.");
        }

        // Direct Execution
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'branch' => $validated['branch'],
            'department' => $validated['department'],
            'designation' => $validated['designation'],
            'reporting_manager_id' => $validated['reporting_manager_id'] ?? null,
            'role_id' => $validated['role_id'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', "Staff member {$user->name} updated successfully!");
    }

    public function destroy(User $user, NotificationService $notificationService)
    {
        Gate::authorize('manage-users');

        $currentUser = Auth::user();

        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own logged-in account.');
        }

        // CRITICAL APPROVAL FLOW: If non-Director tries to delete a staff account, send approval request
        if (!$currentUser->isDirectorOrFounder()) {
            SaasApprovalRequest::create([
                'company_id' => $currentUser->company_id,
                'requested_by_user_id' => $currentUser->id,
                'action_type' => 'delete_user',
                'target_type' => User::class,
                'target_id' => $user->id,
                'target_name' => $user->name . " (" . $user->email . ")",
                'payload' => ['user_id' => $user->id],
                'reason' => "Admin {$currentUser->name} requested termination of staff account '{$user->name}'.",
                'status' => 'pending',
            ]);

            $directors = User::where('company_id', $currentUser->company_id)
                ->whereHas('role', fn($q) => $q->whereIn('slug', ['director', 'founder']))
                ->get();

            foreach ($directors as $director) {
                $notificationService->notify(
                    $director,
                    'critical_approval_request',
                    "🚨 Critical Approval Needed: Delete Staff Account",
                    "Admin {$currentUser->name} requested to DELETE staff account '{$user->name}'. Please review and approve.",
                    route('users.index')
                );
            }

            return redirect()->route('users.index')->with('warning', "⚠️ Deletion Request Submitted! Account termination requires Main Owner / Director approval. Request sent to Director.");
        }

        // Direct Deletion by Director/Founder
        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Staff member {$name} deleted successfully!");
    }

    /**
     * Dedicated Company Critical Approvals Queue for Director / Main Owner
     */
    public function companyApprovals(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isDirectorOrFounder()) {
            return back()->with('error', 'Unauthorized. Only Company Directors / Main Owners can view critical approvals.');
        }

        $pendingApprovals = SaasApprovalRequest::where('company_id', $currentUser->company_id)
            ->where('status', 'pending')
            ->with(['requestedBy'])
            ->latest()
            ->get();

        $processedApprovals = SaasApprovalRequest::where('company_id', $currentUser->company_id)
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['requestedBy', 'reviewedBy'])
            ->latest()
            ->take(20)
            ->get();

        return view('users.approvals', compact('pendingApprovals', 'processedApprovals'));
    }

    /**
     * Director / Founder Approves a Critical User Management Request
     */
    public function approveRequest(Request $request, SaasApprovalRequest $approvalRequest, NotificationService $notificationService)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isDirectorOrFounder() && !$currentUser->isSaaSFounder()) {
            return back()->with('error', 'Unauthorized. Only Main Company Owners / Directors can approve critical permission requests.');
        }

        if ($approvalRequest->status !== 'pending') {
            return back()->with('error', 'This approval request has already been processed.');
        }

        $reviewerNotes = $request->input('reviewer_notes', 'Approved by Company Director / Main Owner');
        $payload = $approvalRequest->payload ?? [];

        try {
            switch ($approvalRequest->action_type) {
                case 'delete_user':
                    $userId = $payload['user_id'] ?? $approvalRequest->target_id;
                    $targetUser = User::find($userId);
                    if ($targetUser) {
                        $targetUser->delete();
                    }
                    break;

                case 'delete_project':
                    $projectId = $payload['project_id'] ?? $approvalRequest->target_id;
                    $targetProject = \App\Models\Project::find($projectId);
                    if ($targetProject) {
                        $targetProject->delete();
                    }
                    break;

                case 'delete_broker':
                    $brokerId = $payload['broker_id'] ?? $approvalRequest->target_id;
                    $targetBroker = \App\Models\Broker::find($brokerId);
                    if ($targetBroker) {
                        $targetBroker->delete();
                    }
                    break;

                case 'delete_booking':
                    $bookingId = $payload['booking_id'] ?? $approvalRequest->target_id;
                    $targetBooking = \App\Models\Booking::find($bookingId);
                    if ($targetBooking) {
                        $targetBooking->delete();
                    }
                    break;

                case 'create_admin_user':
                case 'create_user':
                    User::create([
                        'company_id' => $currentUser->company_id,
                        'role_id' => $payload['role_id'],
                        'name' => $payload['name'],
                        'email' => $payload['email'],
                        'phone' => $payload['phone'],
                        'branch' => $payload['branch'] ?? 'Head Office',
                        'department' => $payload['department'] ?? 'Sales',
                        'designation' => $payload['designation'] ?? 'Executive',
                        'password' => Hash::make($payload['password']),
                    ]);
                    break;

                case 'update_user_role':
                case 'update_user':
                    $userId = $payload['user_id'] ?? $approvalRequest->target_id;
                    $targetUser = User::find($userId);
                    if ($targetUser) {
                        $updateData = [
                            'name' => $payload['name'],
                            'email' => $payload['email'],
                            'phone' => $payload['phone'],
                            'branch' => $payload['branch'] ?? $targetUser->branch,
                            'department' => $payload['department'] ?? $targetUser->department,
                            'designation' => $payload['designation'] ?? $targetUser->designation,
                            'role_id' => $payload['role_id'],
                        ];
                        if (!empty($payload['password'])) {
                            $updateData['password'] = Hash::make($payload['password']);
                        }
                        $targetUser->update($updateData);
                    }
                    break;

                default:
                    throw new \InvalidArgumentException("Unknown action type: {$approvalRequest->action_type}");
            }

            $approvalRequest->update([
                'status' => 'approved',
                'reviewed_by_user_id' => $currentUser->id,
                'reviewer_notes' => $reviewerNotes,
                'executed_at' => now(),
            ]);

            if ($approvalRequest->requestedBy) {
                $notificationService->notify(
                    $approvalRequest->requestedBy,
                    'user_approval_approved',
                    "Request Approved: {$approvalRequest->action_label}",
                    "Your request to {$approvalRequest->action_label} ({$approvalRequest->target_name}) was APPROVED & EXECUTED by Director {$currentUser->name}.",
                    route('users.index')
                );
            }

            return redirect()->route('users.index')->with('success', "Approval Request #{$approvalRequest->id} ('{$approvalRequest->action_label}') APPROVED & EXECUTED successfully!");
        } catch (\Throwable $e) {
            Log::error("[USER APPROVAL EXECUTION FAILED] " . $e->getMessage());
            return back()->with('error', "Failed to execute request action: " . $e->getMessage());
        }
    }

    /**
     * Director / Founder Rejects a Critical User Management Request
     */
    public function rejectRequest(Request $request, SaasApprovalRequest $approvalRequest, NotificationService $notificationService)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isDirectorOrFounder() && !$currentUser->isSaaSFounder()) {
            return back()->with('error', 'Unauthorized. Only Main Company Owners / Directors can reject critical permission requests.');
        }

        if ($approvalRequest->status !== 'pending') {
            return back()->with('error', 'This approval request has already been processed.');
        }

        $reviewerNotes = $request->input('reviewer_notes', 'Rejected by Company Director / Main Owner');

        $approvalRequest->update([
            'status' => 'rejected',
            'reviewed_by_user_id' => $currentUser->id,
            'reviewer_notes' => $reviewerNotes,
        ]);

        if ($approvalRequest->requestedBy) {
            $notificationService->notify(
                $approvalRequest->requestedBy,
                'user_approval_rejected',
                "Request Rejected: {$approvalRequest->action_label}",
                "Your request to {$approvalRequest->action_label} ({$approvalRequest->target_name}) was REJECTED by Director {$currentUser->name}. Notes: {$reviewerNotes}",
                route('users.index')
            );
        }

        return redirect()->route('users.index')->with('success', "Approval Request #{$approvalRequest->id} REJECTED.");
    }
}
