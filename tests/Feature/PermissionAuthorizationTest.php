<?php

namespace Tests\Feature;

use App\Models\Booking;
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
use Tests\TestCase;

class PermissionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_executive_cannot_approve_booking_or_manage_users()
    {
        $company = Company::create(['name' => 'Perm Test Company', 'code' => 'PTC', 'slug' => 'perm-test']);

        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $salesRole = Role::create(['name' => 'Sales Executive', 'slug' => 'sales_executive']);

        $adminUser = User::factory()->create(['company_id' => $company->id, 'role_id' => $adminRole->id]);
        $salesUser = User::factory()->create(['company_id' => $company->id, 'role_id' => $salesRole->id]);

        $project = Project::create(['company_id' => $company->id, 'name' => 'Project P', 'code' => 'PP']);
        $building = ProjectBuilding::create(['company_id' => $company->id, 'project_id' => $project->id, 'name' => 'B1', 'code' => 'B1']);
        $floor = ProjectFloor::create(['company_id' => $company->id, 'building_id' => $building->id, 'floor_number' => 1, 'name' => 'F1']);
        $unit = Unit::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'unit_number' => '101',
            'unit_type' => '2BHK',
            'carpet_area' => 1000,
            'builtup_area' => 1200,
            'super_builtup_area' => 1400,
            'facing' => 'North',
            'base_price' => 5000000,
            'final_price' => 5000000,
            'status' => 'available',
        ]);
        $lead = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-P',
            'first_name' => 'Client',
            'phone' => '9990001112',
            'interested_project_id' => $project->id,
        ]);
        $costSheet = CostSheet::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'base_cost' => 5000000,
            'total_cost' => 5000000,
            'created_by_user_id' => $adminUser->id,
        ]);
        $booking = Booking::create([
            'company_id' => $company->id,
            'booking_code' => 'BK-P',
            'lead_id' => $lead->id,
            'customer_name' => 'Client',
            'customer_phone' => '9990001112',
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'sales_user_id' => $salesUser->id,
            'cost_sheet_id' => $costSheet->id,
            'booking_amount' => 500000,
            'total_unit_cost' => 5000000,
            'booking_date' => now(),
            'status' => 'pending_approval',
            'approval_status' => 'pending',
        ]);

        // 1. Sales Executive tries to manage users -> Expect 403
        $userResponse = $this->actingAs($salesUser)->get('/users');
        $userResponse->assertStatus(403);

        // 2. Sales Executive tries to approve booking -> Expect 403
        $bookingResponse = $this->actingAs($salesUser)->post("/bookings/{$booking->id}/approve");
        $bookingResponse->assertStatus(403);

        // 3. Admin user approves booking -> Expect 302 Redirect with success
        $adminBookingResponse = $this->actingAs($adminUser)->post("/bookings/{$booking->id}/approve");
        $adminBookingResponse->assertStatus(302);
        $this->assertEquals('approved', $booking->fresh()->approval_status);
    }
}
