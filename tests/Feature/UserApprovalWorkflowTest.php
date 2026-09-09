<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\SaasApprovalRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::create(['name' => 'Founder', 'slug' => 'founder']);
        Role::create(['name' => 'Director', 'slug' => 'director']);
        Role::create(['name' => 'Admin', 'slug' => 'admin']);
        Role::create(['name' => 'Sales Executive', 'slug' => 'sales_executive']);
    }

    public function test_admin_creating_admin_user_creates_approval_request()
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company', 'code' => 'TC01']);
        $adminRole = Role::where('slug', 'admin')->first();

        $adminUser = User::create([
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'phone' => '9800000001',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($adminUser)->post(route('users.store'), [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'phone' => '9800000002',
            'role_id' => $adminRole->id,
            'password' => 'password123',
            'branch' => 'Head Office',
            'department' => 'Management',
            'designation' => 'Admin',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('saas_approval_requests', [
            'company_id' => $company->id,
            'requested_by_user_id' => $adminUser->id,
            'action_type' => 'create_admin_user',
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'newadmin@test.com',
        ]);
    }

    public function test_director_creating_admin_user_executes_immediately()
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company', 'code' => 'TC01']);
        $directorRole = Role::where('slug', 'director')->first();
        $adminRole = Role::where('slug', 'admin')->first();

        $directorUser = User::create([
            'company_id' => $company->id,
            'role_id' => $directorRole->id,
            'name' => 'Director User',
            'email' => 'director@test.com',
            'phone' => '9800000001',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($directorUser)->post(route('users.store'), [
            'name' => 'Direct Admin',
            'email' => 'directadmin@test.com',
            'phone' => '9800000003',
            'role_id' => $adminRole->id,
            'password' => 'password123',
            'branch' => 'Head Office',
            'department' => 'Management',
            'designation' => 'Admin',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'directadmin@test.com',
        ]);
    }

    public function test_director_can_approve_pending_user_request()
    {
        $company = Company::create(['name' => 'Test Company 3', 'slug' => 'test-company-3', 'code' => 'TC03']);
        $directorRole = Role::where('slug', 'director')->first();
        $adminRole = Role::where('slug', 'admin')->first();

        $directorUser = User::create([
            'company_id' => $company->id,
            'role_id' => $directorRole->id,
            'name' => 'Director User',
            'email' => 'director@test.com',
            'phone' => '9800000001',
            'password' => Hash::make('password'),
        ]);

        $adminUser = User::create([
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'phone' => '9800000002',
            'password' => Hash::make('password'),
        ]);

        $approval = SaasApprovalRequest::create([
            'company_id' => $company->id,
            'requested_by_user_id' => $adminUser->id,
            'action_type' => 'create_admin_user',
            'target_name' => 'Approved Admin (approved@test.com)',
            'payload' => [
                'name' => 'Approved Admin',
                'email' => 'approved@test.com',
                'phone' => '9800000009',
                'role_id' => $adminRole->id,
                'password' => 'password123',
                'branch' => 'Head Office',
                'department' => 'Management',
                'designation' => 'Admin',
            ],
            'reason' => 'Need second admin',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($directorUser)->post(route('users.approvals.approve', $approval->id));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'approved@test.com',
        ]);

        $this->assertDatabaseHas('saas_approval_requests', [
            'id' => $approval->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_deleting_project_creates_approval_request()
    {
        $company = Company::create(['name' => 'Test Company 4', 'slug' => 'test-company-4', 'code' => 'TC04']);
        $adminRole = Role::where('slug', 'admin')->first();

        $adminUser = User::create([
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin4@test.com',
            'phone' => '9800000041',
            'password' => Hash::make('password'),
        ]);

        $project = \App\Models\Project::create([
            'company_id' => $company->id,
            'name' => 'Project Alpha',
            'code' => 'ALPHA-01',
            'location_address' => 'Test Address',
            'city' => 'City',
            'state' => 'State',
            'pincode' => '400001',
            'project_type' => 'residential',
            'status' => 'active',
        ]);

        $response = $this->actingAs($adminUser)->delete(route('projects.destroy', $project->id));

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('saas_approval_requests', [
            'company_id' => $company->id,
            'requested_by_user_id' => $adminUser->id,
            'action_type' => 'delete_project',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
        ]);
    }
}
