# REOS Dashboard Sidebar Access

This document defines which sidebar items should be visible for each user role and records the current implementation in `resources/views/layouts/reos.blade.php`.

## Business role hierarchy

There are two separate administration hierarchies. They must not be merged:

| Hierarchy | Primary role | Supporting role | Scope |
|---|---|---|---|
| SaaS platform | SaaS Founder | SaaS Sub-Admin | All tenant companies and platform operations |
| Individual company | Company Founder / Director | Company Sub-Admin / Company Admin | One company and its internal operations |

The SaaS Sub-Admin helps the SaaS Founder. The Company Sub-Admin helps the Company Founder/Director. A Company Sub-Admin must not automatically receive SaaS Founder or SaaS Sub-Admin permissions.

## Role names used by the application

The sidebar derives access from these `User` helpers:

- **SaaS Founder**: `isSaaSFounder()` / `is_super_admin = true`
- **SaaS Sub-Admin**: `isSaaSSubAdmin()` / `is_saas_sub_admin = true`
- **Company Founder / Director**: company role slug `founder` or `director` (excluding SaaS Founder)
- **Company Sub-Admin / Company Admin**: the existing `admin` role, exposed through `isCompanySubAdmin()`
- **Director**: `isDirector()`
- **Manager**: `isManager()` (`manager` or `sales_manager`)
- **Sales Executive**: `isSales()` (`sales_executive` or `executive`)
- **Broker**: `isBroker()` (`broker`)

## Recommended sidebar by dashboard

The following is the recommended product structure. `Yes` means the item should be visible; `Limited` means the page must show only records permitted for that role.

| Sidebar item | SaaS Founder | SaaS Sub-Admin | Company Founder / Director | Company Sub-Admin | Manager | Sales Executive | Broker |
|---|---:|---:|---:|---:|---:|---:|---:|
| Dashboard | Yes | Yes | Yes | Yes | Yes | Yes | Yes |
| Leads | Platform | Platform | Yes | Yes | Yes | Own/assigned only | Referral only |
| Contacts | No | No | Yes | Yes | Yes | No | No |
| Properties | Platform | Permission-based | Yes | Yes | Yes | Limited/read-only | Available only |
| Site Visits | No | No | Yes | Yes | Yes | Own/assigned only | Referral only |
| Deals | Platform/monitoring | Permission-based | Yes | Yes | Yes | No or read-only | No |
| Tasks | No | No | Yes | Yes | Yes | Yes | No |
| Follow-ups | No | No | Yes | Yes | Yes | Own/assigned only | No |
| Documents | No | No | Yes | Yes | Yes | Limited through lead | No |
| HRMS | No | No | Yes | Yes | No | No | No |
| Support Desk | Platform support | Permission-based | Yes | Yes | Yes | Yes | Optional |
| Team Chat | Platform/admin chat | Permission-based | Yes | Yes | Yes | Yes | Optional |
| Teams | No | No | Yes | Yes | Own executives only | No | No |
| Brokers | No | No | Yes | Yes | No | No | No |
| Reports | Platform reports | Permission-based | Yes | Yes | No/limited | No/limited | No |
| Payments | Platform monitoring | Permission-based | Yes | Yes | No | No | No |
| Settings | Platform settings | No | Yes | Yes | No | No | No |
| Lead Sources | Platform settings | Permission-based | Yes | Yes | No | No | No |
| Permissions | Full matrix | My permissions only | Company matrix | Company matrix | No | No | No |
| Company Approvals | No | No | Badge when pending | Badge when pending | No | No | No |
| Tenant Companies | Yes | If `view_companies` permission | No | No | No | No | No |
| SaaS Plans | Yes | Permission-based if required | No | No | No | No | No |
| Sub-Admins | Yes | No | No | No | No | No | No |
| Audit Logs | Yes | Permission-based | No | No | No | No | No |
| SaaS Approvals | Yes | Yes | No | No | No | No | No |

## Current implementation

The following is what the code currently attempts to show.

### All roles

- **Dashboard** is always rendered.

### Broker dashboard

When `$isBroker` is true, the sidebar renders the **Channel Partner** section:

- My Referral Leads
- Commission Tracker (only if `brokers.index` exists)
- Available Projects
- Site Visits

The normal internal **Sales & Pipeline**, **Operations**, and **Management** sections are hidden for brokers.

### Company internal users

When the user is not a broker, the **Sales & Pipeline** section is rendered:

- Leads: all internal users
- Contacts: Admin, Director, and Manager only
- Properties: all internal users
- Site Visits: all internal users
- Deals: Admin, Director, Manager, and SaaS Admin

The **Operations** section is rendered for non-brokers, except SaaS-only users who are not company staff:

- Tasks
- Follow-ups
- Documents: Admin, Director, and Manager
- HRMS: Admin and Director, but only when the `hrms.index` route exists
- Support Desk: when the `support-tickets.index` route exists
- Team Chat: when the `chat.index` route exists

The **Management** section is rendered for Admin, Director, Manager, or a user with pending company approvals:

- Teams: Admin, Director, Founder, and Manager (Manager sees only direct-report executives)
- Brokers: Admin, Director, and Founder only
- Reports: Admin, Director, and Founder only
- Payments: Admin, Director, and Founder only
- Settings: Admin and Director
- Lead Sources: Admin and Director
- Permissions: Director only
- Action Approvals: only when pending approvals exist and the user is Admin or Director

### SaaS Founder dashboard

When `$isFounder` is true, the **SaaS Control Tower** section is rendered:

- Tenant Companies
- SaaS Plans
- Sub-Admins
- Permissions
- Audit Logs (only if `admin.audit-logs` exists)
- SaaS Approvals (only when pending approvals exist)

The Founder can also enter the normal company sections if the role/helper conditions allow it.

### SaaS Sub-Admin dashboard

When `$isSubAdmin` is true, the **SaaS Delegate Panel** is rendered. This is the SaaS Founder’s supporting administrator and is separate from any company-level sub-admin:

- Tenant Companies: only with `view_companies` SaaS permission
- SaaS Plans: only with `manage_subscriptions` SaaS permission; sensitive changes remain approval-gated
- My Permissions: currently links to the dashboard
- SaaS Approvals: always rendered, with a pending count badge when applicable
- Tenant detail pages are read-only for SaaS Sub-Admins; company profile editing remains SaaS Founder-only.
- The SaaS Sub-Admin dashboard shows only aggregate platform metrics, permissions, approvals, and platform activity; individual tenant/company data remains outside the dashboard and is available only through the separate Tenant Companies menu.

SaaS platform-only users (SaaS Founder and SaaS Sub-Admin) do not receive the company Sales & Pipeline, Operations, or Management sections in the sidebar. This remains true when they open a tenant detail page, preventing a platform user from being mistaken for a company employee. Other SaaS actions are permission-controlled in the dashboard, but not every sidebar item currently has its own permission check.

SaaS Founder and SaaS Sub-Admin accounts are excluded from the company Teams Directory. They must be managed through SaaS administration, not company staff controls.

### Company Founder / Director and Company Sub-Admin dashboards

The intended company hierarchy is:

- Company Founder/Director: highest authority inside one company
- Company Sub-Admin/Company Admin: delegated assistant for that company
- Both roles are below the SaaS platform hierarchy
- Company Sub-Admin can receive only company-level permissions assigned by the Company Founder/Director

The intended Company Sub-Admin scope is:

- Company data and company users only
- Company leads, properties, site visits, deals, reports, payments, and settings according to company permissions
- No separate SaaS platform controls such as Tenant Companies, SaaS Plans, or Sub-Admins
- Company Approvals only for pending approvals belonging to that company

The Company Sub-Admin now reuses the existing `admin` role and shares the company admin dashboard view. Its sidebar is separated from Company Founder/Director access through `isCompanySubAdmin()`, while the existing `isCompanyAdmin()` helper remains backward-compatible for company-level authorization.

## Current gaps and important notes

1. **Team Chat routes are currently commented out** in `routes/web.php`. Because the sidebar checks `Route::has('chat.index')`, Team Chat will not appear until those routes are enabled.
2. The sidebar controls visibility only. It is not a substitute for backend authorization.
3. The menu labels and access rules are all located in `resources/views/layouts/reos.blade.php`, approximately lines 390–780.
4. The role helper definitions are in `app/Models/User.php`.
5. There are two separate hierarchies: **SaaS Founder → SaaS Sub-Admin** and **Company Founder/Director → Company Sub-Admin**.
6. The current `is_saas_sub_admin` flag represents only SaaS Sub-Admin and must not be used for Company Sub-Admin.
7. The existing `admin` role is used for Company Sub-Admin; `isCompanySubAdmin()` and separate sidebar conditions distinguish it from Company Founder/Director.
8. Manager access is intentionally limited to operational CRM work; broker management, executive reports, payments, booking approvals, agreement approvals, and commission management are not granted by default. Manager Teams access is limited to direct-report Sales Executives.
9. The recommended matrix says “Limited” for data scope; controllers and policies must enforce that scope even if a user manually opens a URL.
10. New leads use a two-level round-robin distribution: first to an active company Manager pool, then to an active Sales Executive reporting to that Manager. Leads created by an Executive retain that Executive and resolve their reporting Manager.
11. Team creation/editing uses `reporting_manager_id` to define team membership. The Teams Directory displays this Manager for every Executive, and the backend accepts only an active Manager from the same company.

## Files to change when applying this matrix

- Sidebar visibility: `resources/views/layouts/reos.blade.php`
- Role helper behavior: `app/Models/User.php`
- Dashboard data and role-specific dashboard views: `app/Http/Controllers/DashboardController.php` and `resources/views/dashboard/`
- Backend page authorization: relevant controllers and policies
- Team Chat route availability: `routes/web.php`
- Team Chat user list and participant validation: `app/Http/Controllers/ChatController.php`
