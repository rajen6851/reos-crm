<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable CSRF verification in tests
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }
    protected function seedPermissions()
    {
        $permissions = [
            'manage-users',
            'manage-projects',
            'manage-units',
            'manage-leads',
            'assign-leads',
            'delete-leads',
            'export-leads',
            'approve-bookings',
            'manage-commissions',
            'broker-access',
            'approve-agreement-skips',
            'process-payouts',
            'company-settings',
            'view-reports',
        ];

        foreach ($permissions as $slug) {
            \App\Models\Permission::firstOrCreate(['slug' => $slug], [
                'name' => ucwords(str_replace('-', ' ', $slug)),
                'module' => 'core',
                'description' => 'Test permission'
            ]);
        }
    }

    protected function attachPermissionsToRole(\App\Models\Role $role, array $permissions)
    {
        $this->seedPermissions();
        $permIds = \App\Models\Permission::whereIn('slug', $permissions)->pluck('id');
        $role->permissions()->syncWithoutDetaching($permIds);
    }
}
