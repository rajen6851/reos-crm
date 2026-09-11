<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Services\LeadSources\LeadSourceManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeadSourceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $manager;
    protected User $executive;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Founder', 'slug' => 'founder']);
        Role::create(['name' => 'Manager', 'slug' => 'manager']);
        Role::create(['name' => 'Sales Executive', 'slug' => 'sales_executive']);

        $this->company = Company::create([
            'name' => 'Horizon Developers',
            'slug' => 'horizon-devs',
            'code' => 'HORIZON',
        ]);

        $managerRole = Role::where('slug', 'manager')->first();
        $this->manager = User::create([
            'company_id' => $this->company->id,
            'role_id' => $managerRole->id,
            'name' => 'Lead Manager',
            'email' => 'manager@horizon.com',
            'phone' => '9800000001',
            'password' => Hash::make('password'),
        ]);

        $salesRole = Role::where('slug', 'sales_executive')->first();
        $this->executive = User::create([
            'company_id' => $this->company->id,
            'role_id' => $salesRole->id,
            'reporting_manager_id' => $this->manager->id,
            'name' => 'Executive One',
            'email' => 'exec1@horizon.com',
            'phone' => '9800000002',
            'password' => Hash::make('password'),
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Horizon Heights',
            'code' => 'HH-01',
            'location_address' => 'City',
            'city' => 'City',
            'state' => 'State',
            'pincode' => '400001',
            'project_type' => 'residential',
            'status' => 'active',
        ]);
    }

    public function test_lead_sources_index_view_renders_successfully()
    {
        $response = $this->actingAs($this->manager)->get('/lead-sources');
        $response->assertStatus(200);
        $response->assertSee('Lead Sources Integration Engine');
    }

    public function test_meta_webhook_verification_handshake()

    {
        $source = LeadSource::create([
            'company_id' => $this->company->id,
            'name' => 'Facebook Ads',
            'slug' => 'facebook-ads',
            'type' => 'meta',
            'webhook_token' => 'test-meta-token-123',
            'status' => 'connected',
            'credentials' => ['verify_token' => 'test-meta-token-123'],
        ]);

        $response = $this->get("/api/webhooks/lead-sources/meta/{$source->webhook_token}?hub_mode=subscribe&hub_verify_token=test-meta-token-123&hub_challenge=CHALLENGE_STRING_123");

        $response->assertStatus(200);
        $response->assertSee('CHALLENGE_STRING_123');
    }

    public function test_meta_lead_ingestion_and_auto_distribution()
    {
        $source = LeadSource::create([
            'company_id' => $this->company->id,
            'name' => 'Meta Lead Ads',
            'slug' => 'meta-lead-ads',
            'type' => 'meta',
            'webhook_token' => 'meta-token-999',
            'status' => 'connected',
            'settings' => ['default_project_id' => $this->project->id],
        ]);

        $payload = [
            'leadgen_id' => '100099123',
            'campaign_name' => 'Luxury Apartments Campaign',
            'field_data' => [
                ['name' => 'full_name', 'values' => ['Rahul Verma']],
                ['name' => 'phone_number', 'values' => ['9898989898']],
                ['name' => 'email', 'values' => ['rahul@gmail.com']],
            ]
        ];

        $response = $this->postJson("/api/webhooks/lead-sources/meta/{$source->webhook_token}", $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert Lead was created in DB
        $lead = Lead::where('phone', '9898989898')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('Rahul', $lead->first_name);
        $this->assertEquals('Verma', $lead->last_name);
        $this->assertEquals($this->company->id, $lead->company_id);
        $this->assertEquals($source->id, $lead->source_id);

        // Assert Two-Tier Round-Robin Auto Assignment
        $this->assertEquals($this->manager->id, $lead->assigned_to_manager_id);
        $this->assertEquals($this->executive->id, $lead->assigned_to_user_id);
    }

    public function test_google_lead_ingestion()
    {
        $source = LeadSource::create([
            'company_id' => $this->company->id,
            'name' => 'Google Search Ads',
            'slug' => 'google-search-ads',
            'type' => 'google',
            'webhook_token' => 'google-token-888',
            'status' => 'connected',
            'settings' => ['default_project_id' => $this->project->id],
        ]);

        $payload = [
            'lead_id' => 'GGL-998877',
            'campaign_id' => '1234567',
            'user_column_data' => [
                ['column_id' => 'FIRST_NAME', 'string_value' => 'Priya'],
                ['column_id' => 'LAST_NAME', 'string_value' => 'Sharma'],
                ['column_id' => 'PHONE_NUMBER', 'string_value' => '9797979797'],
                ['column_id' => 'EMAIL', 'string_value' => 'priya@yahoo.com'],
            ]
        ];

        $response = $this->postJson("/api/webhooks/lead-sources/google/{$source->webhook_token}", $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $lead = Lead::where('phone', '9797979797')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('Priya', $lead->first_name);
        $this->assertEquals('Sharma', $lead->last_name);
        $this->assertEquals($this->manager->id, $lead->assigned_to_manager_id);
        $this->assertEquals($this->executive->id, $lead->assigned_to_user_id);
    }

    public function test_99acres_portal_lead_ingestion()
    {
        $source = LeadSource::create([
            'company_id' => $this->company->id,
            'name' => '99acres Portal',
            'slug' => '99acres-portal',
            'type' => '99acres',
            'webhook_token' => 'acres-token-777',
            'status' => 'connected',
            'settings' => ['default_project_id' => $this->project->id],
        ]);

        $payload = [
            'query_id' => '99AC-12345',
            'buyer_name' => 'Amit Patel',
            'phone' => '9696969696',
            'email' => 'amit@patel.com',
            'project' => 'Horizon Heights',
        ];

        $response = $this->postJson("/api/webhooks/lead-sources/99acres/{$source->webhook_token}", $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $lead = Lead::where('phone', '9696969696')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('Amit', $lead->first_name);
        $this->assertEquals('Patel', $lead->last_name);
        $this->assertEquals($this->project->id, $lead->interested_project_id);
    }

    public function test_duplicate_lead_detection_on_ingestion()
    {
        // First create an existing lead
        $existingLead = Lead::create([
            'company_id' => $this->company->id,
            'lead_code' => 'LD-EXISTING-01',
            'first_name' => 'Original',
            'last_name' => 'Customer',
            'phone' => '9595959595',
            'status' => 'new',
        ]);

        $source = LeadSource::create([
            'company_id' => $this->company->id,
            'name' => 'Website Form',
            'slug' => 'website-form',
            'type' => 'website',
            'webhook_token' => 'web-token-555',
            'status' => 'connected',
        ]);

        $payload = [
            'name' => 'Duplicate Inquirer',
            'phone' => '9595959595',
            'email' => 'dup@test.com',
        ];

        $response = $this->postJson("/api/webhooks/lead-sources/website/{$source->webhook_token}", $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_duplicate' => true]);

        $newLead = Lead::where('email', 'dup@test.com')->first();
        $this->assertNotNull($newLead);
        $this->assertTrue($newLead->is_duplicate);
        $this->assertEquals($existingLead->id, $newLead->duplicate_of_lead_id);
    }
}
