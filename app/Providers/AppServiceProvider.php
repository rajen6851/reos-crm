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
        Gate::define('manage-users', fn (User $user) => $user->hasPermission('manage-users') || $user->isCompanyAdmin() || $user->isManager());
        Gate::define('manage-projects', fn (User $user) => $user->hasPermission('manage-projects') || $user->isCompanyAdmin() || $user->isManager());
        Gate::define('manage-inventory', fn (User $user) => $user->hasPermission('manage-projects') || $user->isCompanyAdmin() || $user->isManager());
        Gate::define('manage-leads', fn (User $user) => $user->hasPermission('manage-leads') || !$user->isBroker());
        Gate::define('assign-leads', fn (User $user) => $user->hasPermission('assign-leads') || $user->isCompanyAdmin() || $user->isManager());
        Gate::define('approve-bookings', fn (User $user) => $user->hasPermission('approve-bookings') || $user->isCompanyAdmin() || $user->isManager());
        Gate::define('approve-agreement-skips', fn (User $user) => $user->hasPermission('approve-agreement-skips') || $user->isCompanyAdmin() || $user->isManager());
        Gate::define('manage-commissions', fn (User $user) => $user->hasPermission('manage-commissions') || $user->isCompanyAdmin() || $user->isManager());
        Gate::define('process-payouts', fn (User $user) => $user->hasPermission('process-payouts') || $user->isCompanyAdmin() || $user->isManager());
        Gate::define('broker-access', fn (User $user) => $user->isBroker());

        // HRMS, Reports & Settings Security Gates
        Gate::define('manage-hrms', fn (User $user) => $user->isCompanyAdmin() || $user->isManager() || $user->isSaaSFounder());
        Gate::define('manage-company-settings', fn (User $user) => $user->isCompanyAdmin() || $user->isSaaSFounder());
        Gate::define('view-executive-reports', fn (User $user) => $user->isCompanyAdmin() || $user->isManager() || $user->isSaaSFounder());

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
