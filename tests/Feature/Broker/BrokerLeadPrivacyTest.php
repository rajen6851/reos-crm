<?php

namespace Tests\Feature\Broker;

use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerLeadPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_broker_cannot_see_internal_notes_and_remarks()
    {
        $company = Company::create(['name' => 'Privacy Estate', 'code' => 'PE-01', 'slug' => 'priv-est']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $broker = Broker::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'agency_name' => 'Private Partner',
            'broker_code' => 'PRV-100',
            'phone' => '1234567890',
        ]);
        $project = Project::create(['company_id' => $company->id, 'name' => 'Grand Residency', 'code' => 'GR-01']);

        $lead = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-PRIV',
            'first_name' => 'Vikram',
            'last_name' => 'Singh',
            'phone' => '9123456789',
            'broker_id' => $broker->id,
            'interested_project_id' => $project->id,
            'notes' => 'INTERNAL SENSITIVE NOTE: Customer requested 10% cash discount on margin.',
        ]);

        $brokerLead = BrokerLead::create([
            'company_id' => $company->id,
            'broker_id' => $broker->id,
            'lead_id' => $lead->id,
            'project_id' => $project->id,
            'submitted_at' => now(),
            'broker_visible_status' => 'Under Review',
        ]);

        // Add internal activity with internal notes
        LeadActivity::create([
            'company_id' => $company->id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'internal_manager_review',
            'description' => 'INTERNAL MANAGER REMARK: Customer margin tight, do not lower floor rate below 50L.',
        ]);

        // 1. Fetch Lead Details
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/broker/leads/{$brokerLead->id}");

        $response->assertStatus(200);
        $json = $response->json();

        // Ensure internal notes are NOT exposed in API resource
        $this->assertArrayNotHasKey('notes', $json['data']);
        $this->assertStringNotContainsString('cash discount', json_encode($json));

        // 2. Fetch Lead Timeline
        $timelineResponse = $this->actingAs($user, 'sanctum')
            ->getJson("/api/broker/leads/{$brokerLead->id}/timeline");

        $timelineResponse->assertStatus(200);
        $timelineJson = $timelineResponse->json();

        // Ensure internal manager remarks are filtered out from timeline
        $this->assertStringNotContainsString('floor rate below 50L', json_encode($timelineJson));
    }
}
