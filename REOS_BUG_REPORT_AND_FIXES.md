# REOS – Comprehensive Bug Identification & Resolution Report

---

### Executive Summary

This document presents a comprehensive audit, bug report, and resolution matrix for the **REOS (Real Estate Operating System)** platform. It details 8 critical edge cases and potential failure modes identified across multi-tenancy scoping, RBAC permission gates, database integrity, broker commission generation, phone number duplicate detection, status mapping, and UI permission hiding, alongside the verified code resolutions implemented.

---

## Identified Bugs & Resolution Matrix

### Bug 1: Multi-Tenant Sanctum API Scoping Failure in `TenantScope`
- **Component**: [TenantScope.php](file:///c:/xampp/htdocs/reos/app/Models/Scopes/TenantScope.php)
- **Severity**: HIGH (Security & Data Isolation)
- **Symptom / Vulnerability**: `TenantScope` checked `Auth::user()` using the default `web` session guard. For Sanctum API requests (`auth:sanctum`), `Auth::user()` returned `null`, which bypassed the global `company_id` Eloquent filter on API queries.
- **Root Cause**: Sanctum API requests resolve authentication via `request()->user()` or `Auth::guard('sanctum')->user()`, which was missing from the check.
- **Resolution**:
  ```php
  $user = Auth::user() ?? request()->user() ?? Auth::guard('sanctum')->user();
  if ($user && !$user->is_super_admin && $user->company_id) {
      $builder->where($model->getTable() . '.company_id', $user->company_id);
  }
  ```

---

### Bug 2: Global System Roles Filtered Out by Tenant Scope on `Role` Model
- **Component**: [Role.php](file:///c:/xampp/htdocs/reos/app/Models/Role.php) & [UserController.php](file:///c:/xampp/htdocs/reos/app/Http/Controllers/UserController.php)
- **Severity**: HIGH (RBAC & Permission System)
- **Symptom**: System roles (Sales Executive, Manager, Broker, Accounts Admin) have `company_id = NULL`. Applying `BelongsToTenant` scope to `Role` caused Eloquent to append `WHERE roles.company_id = user.company_id`, which prevented users from retrieving system roles and caused 403 Forbidden errors on authorized action checks.
- **Root Cause**: `Role` model mistakenly used `BelongsToTenant` trait.
- **Resolution**: Removed `BelongsToTenant` from `Role.php` and updated `UserController.php` to query `Role::whereNull('company_id')->orWhere('company_id', $user->company_id)->get()`.

---

### Bug 3: Missing Granular Permission Gates & Controller Authorization Guards
- **Component**: [AppServiceProvider.php](file:///c:/xampp/htdocs/reos/app/Providers/AppServiceProvider.php), [User.php](file:///c:/xampp/htdocs/reos/app/Models/User.php), [BookingController.php](file:///c:/xampp/htdocs/reos/app/Http/Controllers/BookingController.php), [UserController.php](file:///c:/xampp/htdocs/reos/app/Http/Controllers/UserController.php)
- **Severity**: HIGH (Authorization & Security)
- **Symptom**: Controllers previously relied solely on basic user role helpers without enforcing Laravel `Gate::authorize()` or `$user->hasPermission()` checks for privileged operations (managing users, approving bookings, skipping sale agreements, recording payments).
- **Root Cause**: Absence of dynamic Gate definitions in `AppServiceProvider`.
- **Resolution**: Added `hasPermission()` method in `User.php` with fallback role permission mapping and registered dynamic gates in `AppServiceProvider.php`:
  - `Gate::define('manage-users')`
  - `Gate::define('manage-projects')`
  - `Gate::define('manage-leads')`
  - `Gate::define('assign-leads')`
  - `Gate::define('approve-bookings')`
  - `Gate::define('approve-agreement-skips')`
  - `Gate::define('manage-commissions')`
  - `Gate::define('process-payouts')`
  Enforced `Gate::authorize()` across `UserController`, `BookingController`, and `ProjectController`.

---

### Bug 4: Cross-Executive Customer Lead Privacy Leak
- **Component**: [LeadController.php](file:///c:/xampp/htdocs/reos/app/Http/Controllers/LeadController.php) & [SalesExecutiveLeadPrivacyTest.php](file:///c:/xampp/htdocs/reos/tests/Feature/SalesExecutiveLeadPrivacyTest.php)
- **Severity**: HIGH (Data Privacy & Lead Isolation)
- **Symptom**: Sales Executive A logged into `/leads` saw customer leads assigned to Sales Executive B.
- **Root Cause**: Missing role-based scope check in `LeadController::index()`.
- **Resolution**: Implemented `$query->where('assigned_to_user_id', $user->id)` if `$user->isSales()`, ensuring Sales Executives strictly see only their assigned customer leads.

---

### Bug 5: Unfiltered Status Dropdown Options for Sales Executives
- **Component**: [leads/index.blade.php](file:///c:/xampp/htdocs/reos/resources/views/leads/index.blade.php) & [BrokerLeadStatusService.php](file:///c:/xampp/htdocs/reos/app/Services/BrokerLeadStatusService.php)
- **Severity**: MEDIUM (Workflow Control)
- **Symptom**: Sales Executives saw options like `CONVERTED` (Booked) or invalid backward transition options in the dropdown.
- **Root Cause**: Static dropdown rendering without state machine stage restrictions.
- **Resolution**: Implemented dynamic `$allowedForUser` filtering based on state machine rules (`match($lead->status)`). Excluded `CONVERTED` from manual dropdowns, requiring formal booking approval.

---

### Bug 6: UI Element Leaks for Unauthorized Roles in Sidebar & Action Buttons
- **Component**: [reos.blade.php](file:///c:/xampp/htdocs/reos/resources/views/layouts/reos.blade.php), [projects/index.blade.php](file:///c:/xampp/htdocs/reos/resources/views/projects/index.blade.php), [bookings/index.blade.php](file:///c:/xampp/htdocs/reos/resources/views/bookings/index.blade.php)
- **Severity**: MEDIUM (UI/UX Security Alignment)
- **Symptom**: Buttons like `Team & Users`, `+ Create Project`, `Approve Booking`, `Approve Skip`, and `+ Pay Razorpay` were visible to unauthorized roles.
- **Root Cause**: Absence of `@can(...)` directive wrappers.
- **Resolution**: Wrapped all privileged sidebar links and action buttons with `@can('manage-users')`, `@can('manage-projects')`, `@can('approve-bookings')`, `@can('approve-agreement-skips')`, and `@can('manage-commissions')`.

---

### Bug 7: Broker Channel Partner URL Direct Access Vulnerability
- **Component**: [LeadController.php](file:///c:/xampp/htdocs/reos/app/Http/Controllers/LeadController.php), [ProjectController.php](file:///c:/xampp/htdocs/reos/app/Http/Controllers/ProjectController.php), [BookingController.php](file:///c:/xampp/htdocs/reos/app/Http/Controllers/BookingController.php)
- **Severity**: HIGH (Tenant & Role Isolation)
- **Symptom**: A logged-in broker user typing `/leads` or `/bookings` directly in the browser address bar could access internal CRM views.
- **Root Cause**: Missing role redirect guard in controller index methods.
- **Resolution**: Added `if (Auth::user()->isBroker()) return redirect()->route('dashboard');` across all internal CRM controllers.

---

### Bug 8: Broker Commission Not Dispatched on Manual Status Change to "Booked"
- **Component**: [BrokerCommissionService.php](file:///c:/xampp/htdocs/reos/app/Services/BrokerCommissionService.php) & [BrokerController.php](file:///c:/xampp/htdocs/reos/app/Http/Controllers/BrokerController.php)
- **Severity**: HIGH (Business Workflow)
- **Symptom**: When a sales manager marked a lead status as `converted` / `Booked`, the Broker Portal updated the milestone badge to `Booked`, but `Total Commissions Earned` remained `₹0.00` because no `Booking` object had been formally created/linked.
- **Root Cause**: Commission calculation was previously tied strictly to manual `BookingController::approve()` execution, without a fallback auto-heal engine for booked broker leads.
- **Resolution**: Implemented `ensureCommissionForBrokerLead()` in `BrokerCommissionService`. If a lead is marked `Booked`, the system auto-heals and generates the booking, cost sheet, and approved broker commission automatically.

---

## Verification Results

After applying all resolutions, the complete test suite was executed:

```text
   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Feature\Api\AuthApiTest
   PASS  Tests\Feature\Api\LeadApiTest
   PASS  Tests\Feature\Auth\AuthenticationTest
   PASS  Tests\Feature\Auth\EmailVerificationTest
   PASS  Tests\Feature\Auth\PasswordConfirmationTest
   PASS  Tests\Feature\Auth\PasswordResetTest
   PASS  Tests\Feature\Auth\PasswordUpdateTest
   PASS  Tests\Feature\Auth\RegistrationTest
   PASS  Tests\Feature\BookingConcurrencyTest
   PASS  Tests\Feature\BrokerPrivacyTest
   PASS  Tests\Feature\Broker\BrokerCommissionAndPayoutTest
   PASS  Tests\Feature\Broker\BrokerLeadIsolationTest
   PASS  Tests\Feature\Broker\BrokerLeadPrivacyTest
   PASS  Tests\Feature\Broker\BrokerLeadSubmissionTest
   PASS  Tests\Feature\DuplicateLeadTest
   PASS  Tests\Feature\ExampleTest
   PASS  Tests\Feature\PermissionAuthorizationTest
   PASS  Tests\Feature\ProfileTest
   PASS  Tests\Feature\SalesExecutiveLeadPrivacyTest
   PASS  Tests\Feature\TenantIsolationTest

  Tests:    39 passed (126 assertions)
  Duration: 3.94s
```

All 8 bugs and permission authorization gates have been fully resolved and verified with 100% test pass rate.
