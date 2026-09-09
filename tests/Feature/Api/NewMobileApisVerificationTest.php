<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Broker;
use App\Models\BrokerCommission;
use App\Models\Company;
use App\Models\CostSheet;
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

class NewMobileApisVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::create([
            'name' => 'Mobile Test Realty',
            'code' => 'MTR',
            'slug' => 'mobile-test-realty',
        ]);
    }

    public function test_sales_executive_can_check_duplicate_lead()
    {
        $salesRole = Role::create(['company_id' => $this->company->id, 'name' => 'Sales Executive', 'slug' => 'sales_executive']);
        $user = User::create([
            'company_id' => $this->company->id,
            'role_id' => $salesRole->id,
            'name' => 'Sales Exec Test',
            'email' => 'salescheck@test.com',
            'phone' => '9000000111',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        
        Lead::create([
            'company_id' => $this->company->id,
            'lead_code' => 'LD-EXISTING',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '9876543210',
            'email' => 'john@example.com',
            'status' => 'new',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/sales/leads/check-duplicate', [
            'phone' => '9876543210',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'is_duplicate' => true,
            ]);
    }

    public function test_sales_executive_can_log_call()
    {
        $salesRole = Role::create(['company_id' => $this->company->id, 'name' => 'Sales Executive', 'slug' => 'sales_executive']);
        $user = User::create([
            'company_id' => $this->company->id,
            'role_id' => $salesRole->id,
            'name' => 'Sales Exec Test 2',
            'email' => 'salescall@test.com',
            'phone' => '9000000112',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $lead = Lead::create([
            'company_id' => $this->company->id,
            'lead_code' => 'LD-CALL',
            'first_name' => 'Alice',
            'phone' => '9123456789',
            'status' => 'new',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/sales/leads/{$lead->id}/calls", [
            'call_status' => 'connected',
            'duration_seconds' => 120,
            'notes' => 'Customer interested in 3BHK flat.',
            'next_followup_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Call logged successfully.',
            ]);
    }

    public function test_sales_executive_can_record_payment_and_request_agreement_skip()
    {
        $salesRole = Role::create(['company_id' => $this->company->id, 'name' => 'Sales Executive', 'slug' => 'sales_executive']);
        $user = User::create([
            'company_id' => $this->company->id,
            'role_id' => $salesRole->id,
            'name' => 'Sales Exec Test 3',
            'email' => 'salespayment@test.com',
            'phone' => '9000000113',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $project = Project::create(['company_id' => $this->company->id, 'name' => 'Ocean Heights', 'code' => 'OH']);
        $building = ProjectBuilding::create(['company_id' => $this->company->id, 'project_id' => $project->id, 'name' => 'Tower A', 'code' => 'TA']);
        $floor = ProjectFloor::create(['company_id' => $this->company->id, 'project_id' => $project->id, 'building_id' => $building->id, 'floor_number' => 1, 'name' => 'First Floor']);
        $unit = Unit::create(['company_id' => $this->company->id, 'project_id' => $project->id, 'building_id' => $building->id, 'floor_id' => $floor->id, 'unit_number' => '101', 'status' => 'available']);
        $lead = Lead::create(['company_id' => $this->company->id, 'lead_code' => 'LD-B', 'first_name' => 'Bob', 'phone' => '9988776655']);
        $costSheet = CostSheet::create(['company_id' => $this->company->id, 'project_id' => $project->id, 'unit_id' => $unit->id, 'base_cost' => 5000000, 'total_cost' => 5000000, 'created_by_user_id' => $user->id]);

        $booking = Booking::create([
            'company_id' => $this->company->id,
            'cost_sheet_id' => $costSheet->id,
            'booking_code' => 'BK-101',
            'booking_date' => now()->toDateString(),
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'lead_id' => $lead->id,
            'customer_name' => 'Bob',
            'customer_phone' => '9988776655',
            'total_unit_cost' => 5000000,
            'booking_amount' => 100000,
            'sales_user_id' => $user->id,
            'status' => 'confirmed',
        ]);

        // Record payment
        $paymentRes = $this->actingAs($user, 'sanctum')->postJson("/api/sales/bookings/{$booking->id}/payments", [
            'amount' => 50000,
            'payment_method' => 'upi',
            'reference_number' => 'UPI-9988776655',
            'notes' => 'Token payment received via PhonePe',
        ]);
        $paymentRes->assertStatus(201)->assertJson(['status' => 'success']);

        // Agreement Skip Request
        $skipRes = $this->actingAs($user, 'sanctum')->postJson("/api/sales/bookings/{$booking->id}/skip-agreement-request", [
            'reason' => 'Customer is traveling abroad, requested agreement bypass.',
        ]);
        $skipRes->assertStatus(201)->assertJson(['status' => 'success']);
    }

    public function test_broker_profile_bank_details_and_payout_claim()
    {
        $brokerRole = Role::create(['company_id' => $this->company->id, 'name' => 'Broker', 'slug' => 'broker']);
        $salesRole = Role::create(['company_id' => $this->company->id, 'name' => 'Sales Executive', 'slug' => 'sales_executive']);
        
        $salesUser = User::create([
            'company_id' => $this->company->id,
            'role_id' => $salesRole->id,
            'name' => 'Sales Exec Dummy',
            'email' => 'salesdummy@test.com',
            'phone' => '9000000999',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $user = User::create([
            'company_id' => $this->company->id,
            'role_id' => $brokerRole->id,
            'name' => 'Prime Broker',
            'email' => 'broker@test.com',
            'phone' => '9876500000',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);

        $broker = Broker::create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
            'agency_name' => 'Prime Realty Firm',
            'broker_code' => 'BRK-001',
            'name' => 'Prime Associates',
            'email' => 'broker@test.com',
            'phone' => '9876500000',
            'commission_rate' => 2.50,
            'status' => 'active',
        ]);

        // Fetch Broker Profile
        $profileRes = $this->actingAs($user, 'sanctum')->getJson('/api/broker/profile');
        $profileRes->assertStatus(200)->assertJson(['status' => 'success']);

        // Update Bank Details
        $bankRes = $this->actingAs($user, 'sanctum')->postJson('/api/broker/bank-details', [
            'bank_name' => 'HDFC Bank',
            'account_number' => '5010023456789',
            'ifsc_code' => 'HDFC0000123',
            'pan_number' => 'ABCDE1234F',
            'rera_number' => 'RERA-DELHI-99',
        ]);
        $bankRes->assertStatus(200)->assertJson(['status' => 'success']);

        // Add dummy approved commission
        $project = Project::create(['company_id' => $this->company->id, 'name' => 'Green Valley', 'code' => 'GV']);
        $building = ProjectBuilding::create(['company_id' => $this->company->id, 'project_id' => $project->id, 'name' => 'Block B', 'code' => 'BB']);
        $floor = ProjectFloor::create(['company_id' => $this->company->id, 'project_id' => $project->id, 'building_id' => $building->id, 'floor_number' => 2, 'name' => 'Second Floor']);
        $unit = Unit::create(['company_id' => $this->company->id, 'project_id' => $project->id, 'building_id' => $building->id, 'floor_id' => $floor->id, 'unit_number' => '202', 'status' => 'available']);
        $lead = Lead::create(['company_id' => $this->company->id, 'lead_code' => 'LD-BRK', 'first_name' => 'Clara', 'phone' => '9988001122']);
        $costSheet = CostSheet::create(['company_id' => $this->company->id, 'project_id' => $project->id, 'unit_id' => $unit->id, 'base_cost' => 10000000, 'total_cost' => 10000000, 'created_by_user_id' => $salesUser->id]);

        $booking = Booking::create([
            'company_id' => $this->company->id,
            'cost_sheet_id' => $costSheet->id,
            'booking_code' => 'BK-BRK',
            'booking_date' => now()->toDateString(),
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'lead_id' => $lead->id,
            'sales_user_id' => $salesUser->id,
            'customer_name' => 'Clara',
            'customer_phone' => '9988001122',
            'total_unit_cost' => 10000000,
            'booking_amount' => 200000,
            'status' => 'confirmed',
        ]);

        BrokerCommission::create([
            'company_id' => $this->company->id,
            'broker_id' => $broker->id,
            'booking_id' => $booking->id,
            'lead_id' => $lead->id,
            'commission_type' => 'percentage',
            'rate_value' => 2.50,
            'total_commission_amount' => 250000,
            'status' => 'approved',
        ]);

        // Request Payout
        $payoutRes = $this->actingAs($user, 'sanctum')->postJson('/api/broker/payout-request', [
            'amount' => 150000,
            'notes' => 'Urgent payout claim request',
        ]);
        $payoutRes->assertStatus(201)->assertJson(['status' => 'success']);
    }
}
