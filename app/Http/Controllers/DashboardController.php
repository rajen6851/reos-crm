<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\SubscriptionPlan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1a. SaaS Platform SuperAdmin / Founder Dashboard (Full Control)
        if ($user->isSaaSFounder()) {
            $totalCompanies = Company::count();
            $activeSubscriptions = Company::where('status', 'active')->count();
            $totalPlatformRevenue = 4999.00 + 14999.00;
            $subscriptionPlans = SubscriptionPlan::all();
            $companies = Company::with([
                'subscriptionPlan',
                'projects' => function ($q) {
                    $q->withoutGlobalScopes()->with(['buildings.floors', 'units' => function ($uq) {
                        $uq->withoutGlobalScopes();
                    }]);
                },
                'users' => function ($uq) {
                    $uq->withoutGlobalScopes()->with('role');
                }
            ])->latest()->get();

            $pendingApprovalsCount = \App\Models\SaasApprovalRequest::where('status', 'pending')->count();
            $pendingApprovalRequests = \App\Models\SaasApprovalRequest::where('status', 'pending')
                ->with(['requestedBy' => function($q) { $q->withoutGlobalScopes(); }])
                ->latest()
                ->get();
            $saasSubAdminsCount = User::where('is_saas_sub_admin', true)->count();

            // Additional Platform-wide Analytics
            $totalPlatformLeads = \App\Models\Lead::withoutGlobalScopes()->count();
            $totalPlatformUnits = \App\Models\Unit::withoutGlobalScopes()->count();
            $totalPlatformBookings = \App\Models\Booking::withoutGlobalScopes()->count();
            $totalGrossBookingValue = \App\Models\Booking::withoutGlobalScopes()->sum('booking_amount');
            $recentPlatformActivities = \App\Models\AuditLog::withoutGlobalScopes()
                ->with(['user' => function($q){ $q->withoutGlobalScopes(); }])
                ->latest()
                ->take(8)
                ->get();

            return view('dashboard.founder', compact(
                'user', 'totalCompanies', 'activeSubscriptions', 'totalPlatformRevenue',
                'subscriptionPlans', 'companies', 'pendingApprovalsCount', 'pendingApprovalRequests',
                'saasSubAdminsCount', 'totalPlatformLeads', 'totalPlatformUnits',
                'totalPlatformBookings', 'totalGrossBookingValue', 'recentPlatformActivities'
            ));
        }

        // 1b. SaaS Sub-Admin Dashboard (Delegated Monitoring — permission-scoped)
        if ($user->isSaaSSubAdmin()) {
            $myPermissions = $user->saas_permissions ?? [];

            // Companies this sub-admin can view (all for now, scoped by permission)
            $companies = Company::with(['subscriptionPlan', 'users' => function ($uq) {
                $uq->withoutGlobalScopes()->with('role');
            }])->latest()->get();

            $totalCompanies = $companies->count();
            $activeCompanies = $companies->where('status', 'active')->count();
            $subscriptionPlans = SubscriptionPlan::all();

            // Pending approval requests submitted by this sub-admin
            $myPendingRequests = \App\Models\SaasApprovalRequest::where('requested_by_user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->get();

            // All pending approvals (for review badge)
            $pendingApprovalsCount = \App\Models\SaasApprovalRequest::where('status', 'pending')->count();
            $pendingApprovalRequests = \App\Models\SaasApprovalRequest::where('status', 'pending')
                ->with(['requestedBy' => function($q) { $q->withoutGlobalScopes(); }])
                ->latest()
                ->take(5)
                ->get();

            $totalPlatformLeads = \App\Models\Lead::withoutGlobalScopes()->count();
            $totalPlatformBookings = \App\Models\Booking::withoutGlobalScopes()->count();

            $recentPlatformActivities = \App\Models\AuditLog::withoutGlobalScopes()
                ->with(['user' => function($q){ $q->withoutGlobalScopes(); }])
                ->latest()
                ->take(6)
                ->get();

            return view('dashboard.sub_admin', compact(
                'user', 'myPermissions', 'companies', 'totalCompanies', 'activeCompanies',
                'subscriptionPlans', 'myPendingRequests', 'pendingApprovalsCount',
                'pendingApprovalRequests', 'totalPlatformLeads', 'totalPlatformBookings',
                'recentPlatformActivities'
            ));
        }

        // 2. Broker / Channel Partner Dashboard
        if ($user->isBroker()) {
            return (new \App\Http\Controllers\BrokerController())->index();
        }

        // 3. Company Admin / Director Dashboard
        if ($user->isCompanyAdmin()) {
            $company = $user->company;
            $totalUsers = User::where('company_id', $user->company_id)
                ->whereHas('role', function ($q) {
                    $q->where('slug', '!=', 'broker');
                })->count();
            $totalProjects = Project::count();
            $totalUnits = Unit::count();
            $availableUnits = Unit::where('status', 'available')->count();
            $bookedUnits = Unit::where('status', 'booked')->count();
            $totalLeads = Lead::count();
            $teamUsers = User::where('company_id', $user->company_id)
                ->whereHas('role', function ($q) {
                    $q->where('slug', '!=', 'broker');
                })->with('role')->latest()->take(6)->get();

            $subscriptionPlans = SubscriptionPlan::where('is_active', true)->get();
            $subscriptionSummary = $company ? $company->usageSummary() : null;

            $pendingCompanyApprovals = \App\Models\SaasApprovalRequest::where('company_id', $user->company_id)
                ->where('status', 'pending')
                ->with(['requestedBy'])
                ->latest()
                ->get();

            return view('dashboard.admin', compact(
                'user', 'company', 'totalUsers', 'totalProjects', 'totalUnits', 'availableUnits',
                'bookedUnits', 'totalLeads', 'teamUsers', 'subscriptionPlans', 'subscriptionSummary', 'pendingCompanyApprovals'
            ));
        }

        // 4. Sales Executive Dashboard
        if ($user->isSales()) {
            $myLeads = Lead::where('assigned_to_user_id', $user->id)
                ->with(['project', 'calls.user'])
                ->latest()
                ->get();
            $myLeadsCount = $myLeads->count();
            $mySiteVisitsCount = $myLeads->where('status', 'site_visit')->count();
            $myConvertedCount = $myLeads->where('status', 'converted')->count();

            return view('dashboard.sales', compact('user', 'myLeads', 'myLeadsCount', 'mySiteVisitsCount', 'myConvertedCount'));
        }

        // 5. Operations & Sales Manager Dashboard
        $company = $user->company;
        $totalLeads = Lead::count();
        $newLeadsCount = Lead::where('status', 'new')->count();
        $siteVisitsCount = Lead::where('status', 'site_visit')->count();
        $negotiationCount = Lead::where('status', 'negotiation')->count();
        $convertedCount = Lead::where('status', 'converted')->count();

        $totalProjects = Project::count();
        $totalUnits = Unit::count();
        $availableUnits = Unit::where('status', 'available')->count();
        $holdUnits = Unit::where('status', 'hold')->count();
        $bookedUnits = Unit::where('status', 'booked')->count();

        $recentLeads = Lead::with(['assignedTo', 'project', 'calls.user'])->latest()->take(8)->get();
        $pendingBookings = Booking::with(['lead', 'unit', 'project'])->where('approval_status', 'pending')->get();
        $units = Unit::with('project')->get();

        $salesExecutives = User::where('company_id', $user->company_id)
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['sales_executive', 'executive']);
            })
            ->with(['role'])
            ->withCount([
                'assignedLeads as total_assigned_leads',
                'assignedLeads as converted_leads_count' => function ($q) {
                    $q->whereIn('status', ['converted', 'booked']);
                }
            ])
            ->get();

        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->get();
        $subscriptionSummary = $company ? $company->usageSummary() : null;

        return view('dashboard.manager', compact(
            'user', 'company', 'totalLeads', 'newLeadsCount', 'siteVisitsCount', 'negotiationCount',
            'convertedCount', 'totalProjects', 'totalUnits', 'availableUnits', 'holdUnits',
            'bookedUnits', 'recentLeads', 'pendingBookings', 'units', 'salesExecutives',
            'subscriptionPlans', 'subscriptionSummary'
        ));
    }

    public function selectSubscriptionPlan(Request $request, \App\Services\SubscriptionService $subService)
    {
        $user = Auth::user();

        if (!$user->isCompanyAdmin() && !$user->isSaaSFounder()) {
            return back()->with('error', 'Only Company Admin can upgrade or select SaaS subscription plans.');
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $company = $user->company;

        if (!$company) {
            return back()->with('error', 'No company associated with this account.');
        }

        $subService->subscribe($company, $plan, 'dashboard_web', 'TXN-WEB-' . strtoupper(\Illuminate\Support\Str::random(8)));

        return back()->with('success', "Subscription plan updated to {$plan->name} successfully!");
    }

    public function seedSampleData(Request $request)
    {
        Gate::authorize('manage-users');

        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return back()->with('error', 'No company associated with this account.');
        }

        // Create Sample Project
        $project = Project::create([
            'company_id' => $company->id,
            'name' => $company->name . ' Royal Enclave',
            'code' => $company->code . '-RE01',
            'city' => 'Hyderabad',
            'rera_number' => 'P0240000' . rand(1000, 9999),
            'project_type' => 'residential',
            'banner_image' => '/uploads/projects/default_project.jpg',
            'amenities' => ['Clubhouse', 'Swimming Pool', 'Gym'],
            'status' => 'active',
        ]);

        return back()->with('success', "Sample project {$project->name} seeded successfully!");
    }

    public function updateCompanySubscriptionByFounder(Request $request, Company $company)
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin() || !$user->hasSaaSPermission('manage_subscriptions')) {
            return back()->with('error', 'Unauthorized. SaaS subscription management permission required.');
        }

        $validated = $request->validate([
            'status' => 'required|in:active,suspended,pending_subscription,expired',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'reason' => 'nullable|string',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);

        // If Sub-Admin, create approval request instead of direct update
        if ($user->isSaaSSubAdmin()) {
            \App\Models\SaasApprovalRequest::create([
                'requested_by_user_id' => $user->id,
                'action_type' => 'update_company_status',
                'target_type' => Company::class,
                'target_id' => $company->id,
                'target_name' => $company->name,
                'payload' => [
                    'company_id' => $company->id,
                    'status' => $validated['status'],
                    'subscription_plan_id' => $plan->id,
                ],
                'reason' => $validated['reason'] ?? "Request to update subscription status to '{$validated['status']}' on plan '{$plan->name}'",
                'status' => 'pending',
            ]);

            return back()->with('success', "Subscription update request for company '{$company->name}' submitted to SaaS Founder for approval!");
        }

        $company->update([
            'status' => $validated['status'],
            'subscription_plan_id' => $plan->id,
            'subscription_expires_at' => now()->addDays(30),
        ]);

        return back()->with('success', "Company '{$company->name}' updated to status '{$company->status}' on '{$plan->name}'!");
    }

    public function updateCompanyByFounder(Request $request, Company $company)
    {
        $user = Auth::user();
        if (!$user->isSaaSFounder()) {
            return back()->with('error', 'Unauthorized.');
        }

        $oldEmail = $company->email;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
            'status' => 'nullable|in:active,suspended,pending_subscription,expired',
            'password' => 'nullable|string|min:6',
        ]);

        $company->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $company->phone,
            'subscription_plan_id' => $validated['subscription_plan_id'] ?? $company->subscription_plan_id,
            'status' => $validated['status'] ?? $company->status,
        ]);

        $builderUser = User::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where(function ($q) use ($company, $oldEmail) {
                $q->where('email', $company->email)
                  ->orWhere('email', $oldEmail)
                  ->orWhereHas('role', function ($rq) {
                      $rq->whereIn('slug', ['founder', 'director', 'admin']);
                  });
            })
            ->first();

        if ($builderUser) {
            $userUpdate = [];
            if ($builderUser->email !== $company->email) {
                $userUpdate['email'] = $company->email;
            }
            if (!empty($validated['password'])) {
                $userUpdate['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
            }
            if (!empty($userUpdate)) {
                $builderUser->update($userUpdate);
            }
        }

        return back()->with('success', "Company '{$company->name}' details updated successfully!");
    }

    public function destroyCompanyByFounder(Request $request, Company $company)
    {
        $user = Auth::user();
        if (!$user->isSaaSAdmin() || !$user->hasSaaSPermission('delete_companies')) {
            return back()->with('error', 'Unauthorized to delete builder companies.');
        }

        // If Sub-Admin, request approval from SaaS Founder
        if ($user->isSaaSSubAdmin()) {
            \App\Models\SaasApprovalRequest::create([
                'requested_by_user_id' => $user->id,
                'action_type' => 'delete_company',
                'target_type' => Company::class,
                'target_id' => $company->id,
                'target_name' => $company->name . " (Code: {$company->code})",
                'payload' => ['company_id' => $company->id],
                'reason' => $request->input('reason', 'Sub-admin requested company deletion.'),
                'status' => 'pending',
            ]);

            return back()->with('success', "Approval request to delete company '{$company->name}' submitted to SaaS Founder!");
        }

        $name = $company->name;
        $company->delete();

        return back()->with('success', "Company '{$name}' deleted successfully!");
    }

    public function showCompanyByFounder($id)
    {
        $user = Auth::user();
        if (!$user->isSaaSAdmin() || (!$user->hasSaaSPermission('view_companies') && !$user->hasSaaSPermission('onboard_companies'))) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized. Company viewing permission required.');
        }

        $company = Company::withoutGlobalScopes()
            ->with([
                'subscriptionPlan',
                'users' => function ($q) {
                    $q->withoutGlobalScopes()->with('role');
                },
                'projects' => function ($q) {
                    $q->withoutGlobalScopes()->with(['buildings', 'units']);
                }
            ])
            ->findOrFail($id);

        $usageSummary = $company->usageSummary();

        $totalLeads = \App\Models\Lead::withoutGlobalScopes()->where('company_id', $company->id)->count();
        $convertedLeads = \App\Models\Lead::withoutGlobalScopes()->where('company_id', $company->id)->where('status', 'converted')->count();
        $siteVisitsCount = \App\Models\SiteVisit::withoutGlobalScopes()->where('company_id', $company->id)->count();
        
        $totalBookings = \App\Models\Booking::withoutGlobalScopes()->where('company_id', $company->id)->count();
        $totalRevenue = \App\Models\Booking::withoutGlobalScopes()->where('company_id', $company->id)->sum('booking_amount');

        $companyLeads = \App\Models\Lead::withoutGlobalScopes()->where('company_id', $company->id)->latest()->take(10)->get();
        $companyBookings = \App\Models\Booking::withoutGlobalScopes()->where('company_id', $company->id)->with('unit')->latest()->take(10)->get();
        $companyBrokers = \App\Models\Broker::withoutGlobalScopes()->where('company_id', $company->id)->latest()->get();

        $activities = \App\Models\AuditLog::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->with(['user' => function($uq){ $uq->withoutGlobalScopes(); }])
            ->latest()
            ->take(15)
            ->get();

        $subscriptionPlans = SubscriptionPlan::all();

        return view('admin.companies.show', compact(
            'user', 'company', 'usageSummary', 'totalLeads', 'convertedLeads', 'siteVisitsCount', 
            'totalBookings', 'totalRevenue', 'companyLeads', 'companyBookings', 'companyBrokers', 
            'activities', 'subscriptionPlans'
        ));
    }

    public function companiesListByFounder(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin() || (!$user->hasSaaSPermission('view_companies') && !$user->hasSaaSPermission('onboard_companies'))) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized. Company viewing permission required.');
        }

        $totalCompanies = Company::count();
        $activeSubscriptions = Company::where('status', 'active')->count();
        $subscriptionPlans = SubscriptionPlan::all();

        $query = Company::with(['subscriptionPlan', 'users' => function ($uq) {
            $uq->withoutGlobalScopes()->with('role');
        }, 'projects' => function ($pq) {
            $pq->withoutGlobalScopes();
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $companies = $query->latest()->get();

        return view('admin.companies.index', compact('user', 'companies', 'totalCompanies', 'activeSubscriptions', 'subscriptionPlans'));
    }

    public function createCompanyFormByFounder()
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin() || !$user->hasSaaSPermission('onboard_companies')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized. Company onboarding permission required.');
        }

        $subscriptionPlans = SubscriptionPlan::all();
        $companies = Company::with(['subscriptionPlan', 'users' => function ($uq) {
            $uq->withoutGlobalScopes()->with('role');
        }])->latest()->get();

        return view('admin.companies.create', compact('user', 'subscriptionPlans', 'companies'));
    }

    public function storeCompanyByFounder(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin() || !$user->hasSaaSPermission('onboard_companies')) {
            return back()->with('error', 'Unauthorized. Company onboarding permission required.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:companies,code',
            'email' => 'required|email|unique:companies,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'owner_name' => 'required|string|max:255',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'password' => 'required|string|min:6',
        ]);

        $company = Company::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . rand(100, 999),
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => 'active',
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'subscription_expires_at' => now()->addDays(30),
            'settings' => ['onboarding_source' => 'founder_created', 'created_by_user_id' => Auth::id()],
        ]);

        // Initialize tenant company roles
        $roleSlugs = [
            'founder' => 'Founder / Director',
            'director' => 'Director',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'sales_executive' => 'Sales Executive',
            'field_team' => 'Field Executive',
            'support_team' => 'Support Desk',
            'broker' => 'Channel Partner / Broker',
        ];

        $founderRole = null;
        $adminRole = null;
        foreach ($roleSlugs as $slug => $roleName) {
            $role = \App\Models\Role::create([
                'company_id' => $company->id,
                'name' => $roleName,
                'slug' => $slug,
                'description' => "{$roleName} role for {$company->name}",
            ]);

            if ($slug === 'founder') {
                $founderRole = $role;
            }
            if ($slug === 'admin') {
                $adminRole = $role;
            }
        }

        $adminUser = User::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => $validated['owner_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role_id' => $founderRole?->id ?? $adminRole?->id,
        ]);

        \App\Services\AuditLogService::log(
            'company_created',
            "SaaS Founder created new builder tenant company {$company->name} (Code: {$company->code}) with Founder/Owner account {$adminUser->email}.",
            $company,
            null,
            ['company_code' => $company->code, 'plan_id' => $company->subscription_plan_id]
        );

        return redirect()->route('admin.companies.index')->with('success', "Tenant Builder Company '{$company->name}' onboarded successfully! Founder account '{$adminUser->email}' initialized.");
    }

    public function saasSubscriptions(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin() || !$user->hasSaaSPermission('manage_subscriptions')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized. SaaS subscription management permission required.');
        }

        $totalCompanies = Company::count();
        $activeSubscriptions = Company::where('status', 'active')->count();
        $totalPlatformRevenue = 4999.00 + 14999.00;
        $subscriptionPlans = SubscriptionPlan::all();
        $companies = Company::with('subscriptionPlan')->latest()->get();

        return view('admin.saas_subscriptions', compact(
            'user', 'totalCompanies', 'activeSubscriptions', 'totalPlatformRevenue', 'subscriptionPlans', 'companies'
        ));
    }

    public function storeSubscriptionPlan(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin() || !$user->hasSaaSPermission('manage_subscriptions')) {
            return back()->with('error', 'Unauthorized. SaaS subscription management permission required.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_users' => 'nullable|integer|min:1',
            'max_projects' => 'nullable|integer|min:1',
            'max_leads_per_month' => 'nullable|integer|min:1',
            'features' => 'nullable|string',
        ]);

        $featuresArr = array_values(array_filter(array_map('trim', explode(',', $request->input('features', '')))));

        $plan = SubscriptionPlan::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'price' => $validated['price'],
            'billing_cycle' => 'monthly',
            'max_users' => $validated['max_users'] ?? null,
            'max_projects' => $validated['max_projects'] ?? null,
            'max_leads_per_month' => $validated['max_leads_per_month'] ?? null,
            'features' => $featuresArr,
            'is_active' => true,
        ]);

        return back()->with('success', "New SaaS Subscription Plan '{$plan->name}' created successfully!");
    }

    public function destroySubscriptionPlan(Request $request, SubscriptionPlan $plan)
    {
        $user = Auth::user();

        if (!$user->isSaaSAdmin() || !$user->hasSaaSPermission('delete_plans')) {
            return back()->with('error', 'Unauthorized to delete subscription plans.');
        }

        // If Sub-Admin, request approval from SaaS Founder
        if ($user->isSaaSSubAdmin()) {
            \App\Models\SaasApprovalRequest::create([
                'requested_by_user_id' => $user->id,
                'action_type' => 'destroy_plan',
                'target_type' => SubscriptionPlan::class,
                'target_id' => $plan->id,
                'target_name' => $plan->name,
                'payload' => ['plan_id' => $plan->id],
                'reason' => $request->input('reason', 'Sub-admin requested SaaS plan deletion.'),
                'status' => 'pending',
            ]);

            return back()->with('success', "Approval request to delete plan '{$plan->name}' submitted to SaaS Founder!");
        }

        $name = $plan->name;
        $plan->delete();

        return back()->with('success', "SaaS Subscription Plan '{$name}' deleted successfully!");
    }
}
