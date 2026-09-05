<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SuperAdmin / SaaS Founder global gate bypass
        Gate::before(function (User $user, string $ability) {
            if ($user->is_super_admin) {
                return true;
            }
        });

        // Register Granular Security Permission Gates
        Gate::define('manage-users', fn (User $user) => $user->isCompanyAdmin() || $user->isManager() || $user->isSaaSFounder());
        Gate::define('manage-projects', fn (User $user) => $user->isCompanyAdmin() || $user->isSaaSFounder());
        Gate::define('manage-inventory', fn (User $user) => $user->isCompanyAdmin() || $user->isManager() || $user->isSaaSFounder());
        Gate::define('manage-leads', fn (User $user) => !$user->isBroker());
        Gate::define('assign-leads', fn (User $user) => $user->isCompanyAdmin() || $user->isManager() || $user->isSaaSFounder());
        Gate::define('approve-bookings', fn (User $user) => $user->isCompanyAdmin() || $user->isManager() || $user->isSaaSFounder());
        Gate::define('approve-agreements', fn (User $user) => $user->isCompanyAdmin() || $user->isManager() || $user->isSaaSFounder());
        Gate::define('manage-commissions', fn (User $user) => $user->isCompanyAdmin() || $user->isSaaSFounder());
        Gate::define('broker-access', fn (User $user) => $user->isBroker());

        // Director & Founder Approval Dual-Auth High-Risk Gates
        Gate::define('approve-agreement-skips', fn (User $user) => $user->isDirectorOrFounder());
        Gate::define('process-payouts', fn (User $user) => $user->isDirectorOrFounder());
        Gate::define('manage-company-settings', fn (User $user) => $user->isDirectorOrFounder());
        Gate::define('director-approval', fn (User $user) => $user->isDirectorOrFounder());

        // HRMS, Financials, Reports & Settings Security Gates
        Gate::define('manage-hrms', fn (User $user) => $user->isCompanyAdmin() || $user->isSaaSFounder());
        Gate::define('view-executive-reports', fn (User $user) => $user->isCompanyAdmin() || $user->isManager() || $user->isSaaSFounder());
        Gate::define('view-financials', fn (User $user) => $user->isCompanyAdmin() || $user->isSaaSFounder());
        Gate::define('view-activity-logs', fn (User $user) => $user->isCompanyAdmin() || $user->isSaaSFounder());

        // Register Morph Map for KYC Documentable Types
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'App\Models\Customer' => \App\Models\Lead::class,
            'Customer' => \App\Models\Lead::class,
            'App\Models\Lead' => \App\Models\Lead::class,
            'Lead' => \App\Models\Lead::class,
            'App\Models\Broker' => \App\Models\Broker::class,
            'Broker' => \App\Models\Broker::class,
            'App\Models\User' => \App\Models\User::class,
            'User' => \App\Models\User::class,
        ]);
    }
}
