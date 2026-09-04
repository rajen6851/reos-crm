<?php

namespace Tests\Feature;

use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_sees_only_assigned_leads_and_sanitized_status(): void
    {
        $company = Company::create(['name' => 'Broker Test Co', 'code' => 'BTC', 'slug' => 'btc', 'status' => 'active']);

        $brokerRole = Role::create([
            'company_id' => $company->id,
            'name' => 'Broker',
            'slug' => 'broker',
        ]);

        $brokerUser = User::create([
            'company_id' => $company->id,
            'role_id' => $brokerRole->id,
            'name' => 'Broker User',
            'email' => 'broker@btc.com',
            'phone' => '9888899999',
            'password' => bcrypt('password'),
        ]);

        $broker = Broker::create([
            'company_id' => $company->id,
            'user_id' => $brokerUser->id,
            'agency_name' => 'Prime Realty',
            'broker_code' => 'BRK-001',
            'phone' => '9888899999',
            'email' => 'broker@btc.com',
            'status' => 'active',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Grand Residency',
            'code' => 'GR-01',
            'status' => 'active',
        ]);

        $lead = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-BRK',
            'first_name' => 'Client',
            'last_name' => 'One',
            'phone' => '9777777777',
            'status' => 'negotiation',
            'notes' => 'INTERNAL SENSITIVE MANAGER REMARKS - DO NOT EXPOSE TO BROKER',
        ]);

        $brokerLead = BrokerLead::create([
            'company_id' => $company->id,
            'broker_id' => $broker->id,
            'lead_id' => $lead->id,
            'project_id' => $project->id,
            'submitted_at' => now(),
            'broker_visible_status' => 'Under Negotiation',
        ]);

        $this->actingAs($brokerUser);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Under Negotiation');
        $response->assertDontSee('INTERNAL SENSITIVE MANAGER REMARKS');
    }
}
