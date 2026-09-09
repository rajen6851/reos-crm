<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Services\LeadDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoTierLeadDistributionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Founder', 'slug' => 'founder']);
        Role::create(['name' => 'Director', 'slug' => 'director']);
        Role::create(['name' => 'Admin', 'slug' => 'admin']);
        Role::create(['name' => 'Manager', 'slug' => 'manager']);
        Role::create(['name' => 'Sales Executive', 'slug' => 'sales_executive']);
    }

    public function test_leads_are_auto_distributed_equally_between_two_managers()
    {
        $company = Company::create(['name' => 'Apex Infra', 'slug' => 'apex-infra', 'code' => 'APEX01']);
        $managerRole = Role::where('slug', 'manager')->first();
        $salesRole = Role::where('slug', 'sales_executive')->first();

        // Create 2 Managers in Company
        $manager1 = User::create([
            'company_id' => $company->id,
            'role_id' => $managerRole->id,
            'name' => 'Manager One',
            'email' => 'm1@apex.com',
            'phone' => '9000000001',
            'password' => Hash::make('password'),
        ]);

        $manager2 = User::create([
            'company_id' => $company->id,
            'role_id' => $managerRole->id,
            'name' => 'Manager Two',
            'email' => 'm2@apex.com',
            'phone' => '9000000002',
            'password' => Hash::make('password'),
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Grand Residency',
            'code' => 'GR-01',
            'location_address' => 'City',
            'city' => 'City',
            'state' => 'State',
            'pincode' => '400001',
            'project_type' => 'residential',
            'status' => 'active',
        ]);

        $service = new LeadDistributionService();

        // Create 10 leads and auto-distribute
        for ($i = 1; $i <= 10; $i++) {
            $lead = Lead::create([
                'company_id' => $company->id,
                'lead_code' => "LD-TEST-{$i}",
                'first_name' => "Customer",
                'last_name' => "#{$i}",
                'phone' => "980000000{$i}",
                'interested_project_id' => $project->id,
                'status' => 'new',
            ]);

            $service->distributeNewLead($lead);
        }

        // Verify Manager 1 got 5 leads and Manager 2 got 5 leads!
        $m1Count = Lead::where('assigned_to_manager_id', $manager1->id)->count();
        $m2Count = Lead::where('assigned_to_manager_id', $manager2->id)->count();

        $this->assertEquals(5, $m1Count);
        $this->assertEquals(5, $m2Count);
    }

    public function test_manager_only_sees_leads_assigned_to_their_manager_pool()
    {
        $company = Company::create(['name' => 'Apex Infra', 'slug' => 'apex-infra-2', 'code' => 'APEX02']);
        $managerRole = Role::where('slug', 'manager')->first();

        $manager1 = User::create([
            'company_id' => $company->id,
            'role_id' => $managerRole->id,
            'name' => 'Manager Alpha',
            'email' => 'malpha@apex.com',
            'phone' => '9000000011',
            'password' => Hash::make('password'),
        ]);

        $manager2 = User::create([
            'company_id' => $company->id,
            'role_id' => $managerRole->id,
            'name' => 'Manager Beta',
            'email' => 'mbeta@apex.com',
            'phone' => '9000000012',
            'password' => Hash::make('password'),
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Royal Heights',
            'code' => 'RH-01',
            'location_address' => 'City',
            'city' => 'City',
            'state' => 'State',
            'pincode' => '400001',
            'project_type' => 'residential',
            'status' => 'active',
        ]);

        // Create 2 leads for Manager Alpha and 1 for Manager Beta
        Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-A1',
            'first_name' => 'Alpha',
            'last_name' => 'One',
            'phone' => '9900000001',
            'interested_project_id' => $project->id,
            'assigned_to_manager_id' => $manager1->id,
            'status' => 'new',
        ]);

        Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-A2',
            'first_name' => 'Alpha',
            'last_name' => 'Two',
            'phone' => '9900000002',
            'interested_project_id' => $project->id,
            'assigned_to_manager_id' => $manager1->id,
            'status' => 'new',
        ]);

        Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-B1',
            'first_name' => 'Beta',
            'last_name' => 'One',
            'phone' => '9900000003',
            'interested_project_id' => $project->id,
            'assigned_to_manager_id' => $manager2->id,
            'status' => 'new',
        ]);

        // Act as Manager Alpha
        $response = $this->actingAs($manager1)->get('/leads');

        $response->assertStatus(200);
        $response->assertSee('LD-A1');
        $response->assertSee('LD-A2');
        $response->assertDontSee('LD-B1');
    }
}
