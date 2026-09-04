<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectFloor;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FullSystemApiVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $salesUser;
    protected User $brokerUser;
    protected User $managerUser;
    protected Broker $broker;
    protected Project $project;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Verify Realty Ltd',
            'code' => 'VRL',
            'slug' => 'verify-realty',
        ]);

        $salesRole = Role::create(['company_id' => $this->company->id, 'name' => 'Sales Executive', 'slug' => 'sales_executive']);
        $brokerRole = Role::create(['company_id' => $this->company->id, 'name' => 'Broker', 'slug' => 'broker']);
        $managerRole = Role::create(['company_id' => $this->company->id, 'name' => 'Manager', 'slug' => 'manager']);

        $password = Hash::make('secret123');

        $this->salesUser = User::create([
            'company_id' => $this->company->id,
            'role_id' => $salesRole->id,
            'name' => 'Sales Exec One',
            'email' => 'sales1@verify.com',
            'phone' => '9000000001',
            'password' => $password,
        ]);

        $this->brokerUser = User::create([
            'company_id' => $this->company->id,
            'role_id' => $brokerRole->id,
            'name' => 'Broker Agency',
            'email' => 'broker1@verify.com',
            'phone' => '9000000002',
            'password' => $password,
        ]);

        $this->managerUser = User::create([
            'company_id' => $this->company->id,
            'role_id' => $managerRole->id,
            'name' => 'Sales Manager',
            'email' => 'manager1@verify.com',
            'phone' => '9000000003',
            'password' => $password,
        ]);

        $this->broker = Broker::create([
            'company_id' => $this->company->id,
            'user_id' => $this->brokerUser->id,
            'agency_name' => 'Broker Agency Inc',
            'broker_code' => 'BRK-100',
            'phone' => '9000000002',
            'email' => 'broker1@verify.com',
            'commission_rate' => 2.00,
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Verification Residency',
            'code' => 'VR-01',
            'status' => 'active',
        ]);

        $building = ProjectBuilding::create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'name' => 'Tower A',
            'code' => 'TA',
        ]);

        $floor = ProjectFloor::create([
            'company_id' => $this->company->id,
            'building_id' => $building->id,
            'floor_number' => 1,
            'name' => 'Floor 1',
        ]);

        $this->unit = Unit::create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'unit_number' => '101',
            'unit_type' => '3BHK',
            'base_price' => 5000000,
            'final_price' => 5500000,
            'status' => 'available',
        ]);
    }

    public function test_full_sales_executive_and_broker_api_flow()
    {
        // 1. Login Sales Executive
        $loginRes = $this->postJson('/api/auth/login', [
            'email' => 'sales1@verify.com',
            'password' => 'secret123',
        ]);
        $loginRes->assertStatus(200)->assertJsonPath('status', 'success');
        $token = $loginRes->json('token');

        // 2. Fetch Sales Executive Dashboard
        $dashRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/sales/dashboard');
        $dashRes->assertStatus(200)->assertJsonPath('status', 'success');

        // 3. Sales Executive creates a lead
        $createLeadRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/sales/leads', [
                'first_name' => 'Rohan',
                'last_name' => 'Mehta',
                'phone' => '9888877777',
                'email' => 'rohan@gmail.com',
                'interested_project_id' => $this->project->id,
                'broker_id' => $this->broker->id,
            ]);
        $createLeadRes->assertStatus(201)->assertJsonPath('status', 'success');
        $leadId = $createLeadRes->json('data.id');

        // Create BrokerLead link for tracking
        BrokerLead::create([
            'company_id' => $this->company->id,
            'broker_id' => $this->broker->id,
            'lead_id' => $leadId,
            'project_id' => $this->project->id,
            'submitted_at' => now(),
            'broker_visible_status' => 'Submitted',
        ]);

        // 4. Update Lead Status (contacted -> site_visit)
        $statusRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/sales/leads/{$leadId}/status", [
                'status' => 'contacted',
                'notes' => 'Customer is interested in 3BHK',
            ]);
        $statusRes->assertStatus(200);

        // 5. Schedule Site Visit
        $visitRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/sales/site-visits', [
                'lead_id' => $leadId,
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'project_id' => $this->project->id,
                'notes' => 'Pickup from Metro station',
            ]);
        $visitRes->assertStatus(201);
        $visitId = $visitRes->json('data.id');

        // 6. Update Site Visit Status to Visited
        $visitStatusRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/sales/site-visits/{$visitId}/status", [
                'status' => 'visited',
                'outcome' => 'interested',
            ]);
        $visitStatusRes->assertStatus(200);

        // 7. Check Available Units in Project
        $unitsRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/sales/projects/{$this->project->id}/units");
        $unitsRes->assertStatus(200)->assertJsonPath('units.0.unit_number', '101');

        // 8. Create Booking for Unit 101
        $bookingRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/sales/bookings', [
                'unit_id' => $this->unit->id,
                'lead_id' => $leadId,
                'customer_name' => 'Rohan Mehta',
                'customer_phone' => '9888877777',
                'booking_amount' => 500000,
                'broker_id' => $this->broker->id,
            ]);
        $bookingRes->assertStatus(201)->assertJsonPath('status', 'success');
        $bookingId = $bookingRes->json('data.id');

        // 9. Login Broker
        $brokerLoginRes = $this->postJson('/api/auth/login', [
            'email' => 'broker1@verify.com',
            'password' => 'secret123',
        ]);
        $brokerLoginRes->assertStatus(200);

        // 10. Fetch Broker Dashboard
        $brokerDashRes = $this->actingAs($this->brokerUser, 'sanctum')
            ->getJson('/api/broker/dashboard');
        $brokerDashRes->assertStatus(200)->assertJsonPath('status', 'success');

        // 11. Fetch Broker Leads List
        $brokerLeadsRes = $this->actingAs($this->brokerUser, 'sanctum')
            ->getJson('/api/broker/leads');
        $brokerLeadsRes->assertStatus(200)->assertJsonPath('status', 'success');

        // 12. Fetch Broker Lead Timeline
        $timelineRes = $this->actingAs($this->brokerUser, 'sanctum')
            ->getJson("/api/broker/leads/{$leadId}/timeline");
        $timelineRes->assertStatus(200)->assertJsonPath('status', 'success');

        // 13. Manager approves booking and commission is generated
        $booking = Booking::findOrFail($bookingId);
        $approveRes = $this->actingAs($this->managerUser, 'sanctum')
            ->postJson("/api/bookings/{$bookingId}/approve");
        $approveRes->assertStatus(200)->assertJsonPath('status', 'success');

        // 14. Verify Broker Commission is generated
        $commRes = $this->actingAs($this->brokerUser, 'sanctum')
            ->getJson('/api/broker/commissions');
        $commRes->assertStatus(200)->assertJsonPath('status', 'success');

        // 15. Update FCM Token
        $fcmRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/fcm-token', [
                'fcm_token' => 'fcm_token_verified_123',
                'device_type' => 'android',
            ]);
        $fcmRes->assertStatus(200)->assertJsonPath('status', 'success');
    }
}
