<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_user_can_only_see_their_own_company_leads(): void
    {
        $companyA = Company::create(['name' => 'Company A', 'code' => 'COA', 'slug' => 'coa', 'status' => 'active']);
        $companyB = Company::create(['name' => 'Company B', 'code' => 'COB', 'slug' => 'cob', 'status' => 'active']);

        $userA = User::create([
            'company_id' => $companyA->id,
            'name' => 'User A',
            'email' => 'usera@coa.com',
            'phone' => '9000000001',
            'password' => bcrypt('password'),
        ]);

        $userB = User::create([
            'company_id' => $companyB->id,
            'name' => 'User B',
            'email' => 'userb@cob.com',
            'phone' => '9000000002',
            'password' => bcrypt('password'),
        ]);

        $leadA = Lead::create([
            'company_id' => $companyA->id,
            'lead_code' => 'LD-A01',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '9111111111',
            'status' => 'new',
        ]);

        $leadB = Lead::create([
            'company_id' => $companyB->id,
            'lead_code' => 'LD-B01',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '9222222222',
            'status' => 'new',
        ]);

        // Act as User A
        $this->actingAs($userA);
        $leadsForA = Lead::all();

        $this->assertCount(1, $leadsForA);
        $this->assertEquals($leadA->id, $leadsForA->first()->id);

        // Act as User B
        $this->actingAs($userB);
        $leadsForB = Lead::all();

        $this->assertCount(1, $leadsForB);
        $this->assertEquals($leadB->id, $leadsForB->first()->id);
    }

    public function test_creating_lead_automatically_attaches_authenticated_user_company_id(): void
    {
        $company = Company::create(['name' => 'Test Infra', 'code' => 'INF', 'slug' => 'inf', 'status' => 'active']);
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Test Manager',
            'email' => 'manager@inf.com',
            'phone' => '9000000003',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $lead = Lead::create([
            'lead_code' => 'LD-AUTO',
            'first_name' => 'Alice',
            'last_name' => 'Wonder',
            'phone' => '9333333333',
            'status' => 'new',
        ]);

        $this->assertEquals($company->id, $lead->company_id);
    }
}
