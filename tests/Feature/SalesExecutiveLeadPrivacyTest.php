<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesExecutiveLeadPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_executive_sees_only_assigned_leads()
    {
        $company = Company::create(['name' => 'Privacy Test Co', 'code' => 'PTC', 'slug' => 'privacy-test']);
        $salesRole = Role::create(['name' => 'Sales Executive', 'slug' => 'sales_executive']);
        $managerRole = Role::create(['name' => 'Manager', 'slug' => 'manager']);

        $execA = User::factory()->create(['company_id' => $company->id, 'role_id' => $salesRole->id, 'name' => 'Exec A']);
        $execB = User::factory()->create(['company_id' => $company->id, 'role_id' => $salesRole->id, 'name' => 'Exec B']);
        $manager = User::factory()->create(['company_id' => $company->id, 'role_id' => $managerRole->id, 'name' => 'Manager M']);

        $project = Project::create(['company_id' => $company->id, 'name' => 'Apex Residency', 'code' => 'AR']);

        $leadA = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-A',
            'first_name' => 'Customer A',
            'phone' => '9000000001',
            'assigned_to_user_id' => $execA->id,
            'interested_project_id' => $project->id,
        ]);

        $leadB = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-B',
            'first_name' => 'Customer B',
            'phone' => '9000000002',
            'assigned_to_user_id' => $execB->id,
            'interested_project_id' => $project->id,
        ]);

        // 1. Exec A logs in and visits CRM Leads -> Sees Lead A, DOES NOT see Lead B
        $responseA = $this->actingAs($execA)->get('/leads');
        $responseA->assertOk();
        $responseA->assertSee('Customer A');
        $responseA->assertDontSee('Customer B');

        // 2. Exec B logs in and visits CRM Leads -> Sees Lead B, DOES NOT see Lead A
        $responseB = $this->actingAs($execB)->get('/leads');
        $responseB->assertOk();
        $responseB->assertSee('Customer B');
        $responseB->assertDontSee('Customer A');

        // 3. Manager logs in -> Sees BOTH Lead A and Lead B
        $responseManager = $this->actingAs($manager)->get('/leads');
        $responseManager->assertOk();
        $responseManager->assertSee('Customer A');
        $responseManager->assertSee('Customer B');
    }
}
