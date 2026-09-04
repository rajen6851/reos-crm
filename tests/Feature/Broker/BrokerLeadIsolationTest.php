<?php

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerLeadIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_a_cannot_see_broker_b_lead_or_other_company_lead()
    {
        // Company 1 with Broker A and Broker B
        $company1 = Company::create(['name' => 'Company One', 'code' => 'C1', 'slug' => 'comp-1']);

        $userA = User::factory()->create(['company_id' => $company1->id]);
        $brokerA = Broker::create([
            'company_id' => $company1->id,
            'user_id' => $userA->id,
            'agency_name' => 'Agency A',
            'broker_code' => 'BRK-A',
            'phone' => '1111111111',
        ]);

        $userB = User::factory()->create(['company_id' => $company1->id]);
        $brokerB = Broker::create([
            'company_id' => $company1->id,
            'user_id' => $userB->id,
            'agency_name' => 'Agency B',
            'broker_code' => 'BRK-B',
            'phone' => '2222222222',
        ]);

        $project1 = Project::create(['company_id' => $company1->id, 'name' => 'Project One', 'code' => 'P1']);

        // Lead submitted by Broker B
        $leadB = Lead::create([
            'company_id' => $company1->id,
            'lead_code' => 'LD-B',
            'first_name' => 'Client of B',
            'phone' => '9999988888',
            'broker_id' => $brokerB->id,
            'interested_project_id' => $project1->id,
        ]);

        $brokerLeadB = BrokerLead::create([
            'company_id' => $company1->id,
            'broker_id' => $brokerB->id,
            'lead_id' => $leadB->id,
            'project_id' => $project1->id,
            'submitted_at' => now(),
            'broker_visible_status' => 'Submitted',
        ]);

        // Company 2
        $company2 = Company::create(['name' => 'Company Two', 'code' => 'C2', 'slug' => 'comp-2']);
        $userC = User::factory()->create(['company_id' => $company2->id]);
        $brokerC = Broker::create([
            'company_id' => $company2->id,
            'user_id' => $userC->id,
            'agency_name' => 'Agency C',
            'broker_code' => 'BRK-C',
            'phone' => '3333333333',
        ]);
        $leadC = Lead::create([
            'company_id' => $company2->id,
            'lead_code' => 'LD-C',
            'first_name' => 'Client of C',
            'phone' => '8888877777',
            'broker_id' => $brokerC->id,
        ]);
        $brokerLeadC = BrokerLead::create([
            'company_id' => $company2->id,
            'broker_id' => $brokerC->id,
            'lead_id' => $leadC->id,
            'submitted_at' => now(),
            'broker_visible_status' => 'Submitted',
        ]);

        // Attempt 1: Broker A tries to access Broker B's lead via URL parameter
        $response1 = $this->actingAs($userA, 'sanctum')
            ->getJson("/api/broker/leads/{$brokerLeadB->id}");
        $response1->assertStatus(403);

        // Attempt 2: Broker A tries to access Company 2's lead via URL parameter
        $response2 = $this->actingAs($userA, 'sanctum')
            ->getJson("/api/broker/leads/{$brokerLeadC->id}");
        $response2->assertStatus(404); // 404 because TenantScope blocks query execution

        // Attempt 3: Broker A lists leads -> receives only own leads
        $response3 = $this->actingAs($userA, 'sanctum')
            ->getJson("/api/broker/leads");
        $response3->assertStatus(200)
            ->assertJsonPath('total_count', 0);
    }
}
