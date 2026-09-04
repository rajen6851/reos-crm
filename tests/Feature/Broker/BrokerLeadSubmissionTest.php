<?php

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerLeadSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_can_submit_lead_for_specific_project()
    {
        $company = Company::create(['name' => 'Test Real Estate', 'code' => 'TRE-01', 'slug' => 'test-re']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $broker = Broker::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'agency_name' => 'Apex Realty',
            'broker_code' => 'APX-001',
            'phone' => '9876543210',
            'commission_rate' => 2.50,
        ]);
        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Skyline Heights',
            'code' => 'SKH-01',
        ]);

        $payload = [
            'first_name' => 'Rahul',
            'last_name' => 'Sharma',
            'phone' => '9988776655',
            'email' => 'rahul@example.com',
            'project_id' => $project->id,
            'property_type' => '3 BHK',
            'unit_type' => '3 BHK Apartment',
            'budget_min' => 5000000,
            'budget_max' => 6500000,
            'requirement_notes' => 'Looking for high floor unit with parking.',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/broker/leads', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.first_name', 'Rahul')
            ->assertJsonPath('data.broker_visible_status', 'Submitted');

        $this->assertDatabaseHas('leads', [
            'company_id' => $company->id,
            'phone' => '9988776655',
            'broker_id' => $broker->id,
        ]);

        $this->assertDatabaseHas('broker_leads', [
            'company_id' => $company->id,
            'broker_id' => $broker->id,
            'project_id' => $project->id,
            'property_type' => '3 BHK',
            'broker_visible_status' => 'Submitted',
        ]);
    }
}
