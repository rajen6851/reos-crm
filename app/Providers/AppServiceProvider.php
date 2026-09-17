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

        // Register Granular Security Permission Gates linked to the DB Permissions Matrix
        Gate::define('manage-users', fn (User $user) => $user->hasPermission('manage-users'));
        Gate::define('manage-projects', fn (User $user) => $user->hasPermission('manage-projects'));
        Gate::define('manage-inventory', fn (User $user) => $user->hasPermission('manage-units'));
        Gate::define('manage-leads', fn (User $user) => $user->hasPermission('manage-leads'));
        Gate::define('assign-leads', fn (User $user) => $user->hasPermission('assign-leads'));
        Gate::define('delete-leads', fn (User $user) => $user->hasPermission('delete-leads'));
        Gate::define('export-leads', fn (User $user) => $user->hasPermission('export-leads'));
        Gate::define('approve-bookings', fn (User $user) => $user->hasPermission('approve-bookings'));
        Gate::define('approve-agreements', fn (User $user) => $user->hasPermission('approve-bookings'));
        Gate::define('manage-commissions', fn (User $user) => $user->hasPermission('manage-commissions'));
        Gate::define('broker-access', fn (User $user) => $user->hasPermission('broker-access'));

        // Director & Founder Approval Dual-Auth High-Risk Gates
        Gate::define('approve-agreement-skips', fn (User $user) => $user->hasPermission('approve-agreement-skips'));
        Gate::define('process-payouts', fn (User $user) => $user->hasPermission('process-payouts'));
        Gate::define('manage-company-settings', fn (User $user) => $user->hasPermission('company-settings'));
        Gate::define('director-approval', fn (User $user) => $user->isDirectorOrFounder());

        // HRMS, Financials, Reports & Settings Security Gates
        Gate::define('manage-hrms', fn (User $user) => $user->hasPermission('manage-users'));
        Gate::define('view-executive-reports', fn (User $user) => $user->hasPermission('view-reports'));
        Gate::define('view-financials', fn (User $user) => $user->hasPermission('process-payouts'));
        Gate::define('view-activity-logs', fn (User $user) => $user->hasPermission('view-reports'));

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
