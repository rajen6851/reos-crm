<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\AiIntelligenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $project;
    protected Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'AI Test Realty',
            'code' => 'AIRT',
            'slug' => 'ai-test-realty',
            'status' => 'active',
        ]);

        $role = Role::create([
            'company_id' => $this->company->id,
            'name' => 'Sales Executive',
            'slug' => 'sales_executive',
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'role_id' => $role->id,
            'name' => 'Sales Agent',
            'email' => 'agent@aitest.com',
            'password' => bcrypt('password'),
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'AI Smart Heights',
            'code' => 'AISH',
            'city' => 'Bangalore',
            'status' => 'active',
        ]);

        $this->lead = Lead::create([
            'company_id' => $this->company->id,
            'lead_code' => 'LD-AI101',
            'first_name' => 'Rahul',
            'last_name' => 'Sharma',
            'phone' => '9876543210',
            'email' => 'rahul@example.com',
            'interested_project_id' => $this->project->id,
            'interested_unit_type' => '2BHK',
            'budget_min' => 4000000,
            'budget_max' => 6000000,
            'status' => 'interested',
        ]);

        $buildingId = \Illuminate\Support\Facades\DB::table('project_buildings')->insertGetId([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'name' => 'Tower A',
            'code' => 'TWR-A',
            'total_floors' => 10,
            'total_units' => 40,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $floorId = \Illuminate\Support\Facades\DB::table('project_floors')->insertGetId([
            'company_id' => $this->company->id,
            'building_id' => $buildingId,
            'floor_number' => 4,
            'name' => '4th Floor',
            'total_units' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Unit::create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'building_id' => $buildingId,
            'floor_id' => $floorId,
            'unit_number' => 'A-402',
            'unit_type' => '2BHK Luxury',
            'base_price' => 5000000,
            'final_price' => 5200000,
            'status' => 'available',
        ]);
    }

    public function test_ai_lead_scoring_service_calculates_hot_score()
    {
        $aiService = app(AiIntelligenceService::class);
        $scoreData = $aiService->calculateLeadScore($this->lead);

        $this->assertIsArray($scoreData);
        $this->assertGreaterThanOrEqual(50, $scoreData['score']);
        $this->assertNotEmpty($scoreData['key_drivers']);
        $this->assertNotEmpty($scoreData['recommended_action']);
    }

    public function test_ai_call_summary_and_sentiment_analysis()
    {
        $aiService = app(AiIntelligenceService::class);
        $result = $aiService->generateCallSummaryAndSentiment('Customer loved the 2BHK flat layout and wants to book tomorrow.');

        $this->assertStringContainsString('Positive', $result['sentiment']);
        $this->assertEquals('emerald', $result['sentiment_color']);
        $this->assertNotEmpty($result['suggested_followup']);
    }

    public function test_smart_property_recommendations()
    {
        $aiService = app(AiIntelligenceService::class);
        $recommendations = $aiService->getSmartPropertyRecommendations($this->lead);

        $this->assertNotEmpty($recommendations);
        $this->assertEquals('A-402', $recommendations->first()['unit_number']);
        $this->assertGreaterThanOrEqual(60, $recommendations->first()['match_score_percentage']);
    }

    public function test_ai_sanctum_api_endpoints()
    {
        $token = $this->user->createToken('test_token')->plainTextToken;

        // Test Lead Score API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/ai/lead-score/{$this->lead->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['ai_score' => ['score', 'label', 'color', 'key_drivers']]);

        // Test Smart Recommendations API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/ai/recommendations/{$this->lead->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Test Call Summarize API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/ai/summarize-call', [
                'notes' => 'Customer is checking budget with home loan agent.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Test Predictive Analytics API
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/ai/predictive-analytics');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }
}
