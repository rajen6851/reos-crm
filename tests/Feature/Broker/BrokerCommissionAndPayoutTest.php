<?php

namespace Tests\Feature\Broker;

use App\Models\Booking;
use App\Models\Broker;
use App\Models\BrokerCommission;
use App\Models\Company;
use App\Models\CostSheet;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectFloor;
use App\Models\Unit;
use App\Models\User;
use App\Services\BrokerCommissionService;
use App\Services\BrokerPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerCommissionAndPayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_lifecycle_and_payout_processing()
    {
        $company = Company::create(['name' => 'Commission Corp', 'code' => 'CC-01', 'slug' => 'comm-corp']);
        $brokerUser = User::factory()->create(['company_id' => $company->id]);
        $adminUser = User::factory()->create(['company_id' => $company->id]);

        $broker = Broker::create([
            'company_id' => $company->id,
            'user_id' => $brokerUser->id,
            'agency_name' => 'Gold Brokers',
            'broker_code' => 'GLD-88',
            'phone' => '8888899999',
            'commission_rate' => 3.00, // 3%
        ]);

        $project = Project::create(['company_id' => $company->id, 'name' => 'Palace Towers', 'code' => 'PT-01']);
        $building = ProjectBuilding::create(['company_id' => $company->id, 'project_id' => $project->id, 'name' => 'Tower A', 'code' => 'TA']);
        $floor = ProjectFloor::create(['company_id' => $company->id, 'building_id' => $building->id, 'floor_number' => 1, 'name' => 'Floor 1']);
        $unit = Unit::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'unit_number' => '101',
            'unit_type' => '3 BHK',
            'carpet_area' => 1200,
            'builtup_area' => 1400,
            'super_builtup_area' => 1600,
            'facing' => 'East',
            'base_price' => 6000000,
            'final_price' => 6000000,
            'status' => 'available',
        ]);

        $lead = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-COMM',
            'first_name' => 'Sunil',
            'phone' => '7777766666',
            'broker_id' => $broker->id,
            'interested_project_id' => $project->id,
        ]);

        $costSheet = CostSheet::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'base_cost' => 6000000,
            'total_cost' => 6000000,
            'created_by_user_id' => $adminUser->id,
        ]);

        $booking = Booking::create([
            'company_id' => $company->id,
            'booking_code' => 'BK-TEST-1',
            'lead_id' => $lead->id,
            'customer_name' => 'Sunil',
            'customer_phone' => '7777766666',
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'sales_user_id' => $adminUser->id,
            'broker_id' => $broker->id,
            'cost_sheet_id' => $costSheet->id,
            'booking_amount' => 500000,
            'total_unit_cost' => 6000000, // ₹60,00,000
            'booking_date' => now(),
            'status' => 'confirmed',
            'approval_status' => 'approved',
        ]);

        // 1. Generate Commission (3% of ₹60,00,000 = ₹1,80,000)
        $commissionService = app(BrokerCommissionService::class);
        $commission = $commissionService->generateCommission($booking);

        $this->assertNotNull($commission);
        $this->assertEquals(180000.00, $commission->total_commission_amount);
        $this->assertEquals('pending', $commission->status);

        // 2. Approve Commission
        $commissionService->approveCommission($commission, $adminUser);
        $this->assertEquals('ready_for_payout', $commission->fresh()->status);

        // 3. Process Payout
        $payoutService = app(BrokerPayoutService::class);
        $payout = $payoutService->processPayout($broker->id, [$commission->id], $adminUser, [
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'TXN-99887766',
            'remarks' => 'Monthly commission payout cleared.',
        ]);

        $this->assertNotNull($payout);
        $this->assertEquals(180000.00, $payout->amount_paid);
        $this->assertEquals('paid', $commission->fresh()->status);

        // 4. Broker API checks financial status
        $response = $this->actingAs($brokerUser, 'sanctum')
            ->getJson('/api/broker/commissions');

        $response->assertStatus(200)
            ->assertJsonPath('total_commission', 180000)
            ->assertJsonPath('paid_commission', 180000);
    }
}
