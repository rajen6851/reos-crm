<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectFloor;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_service_locks_unit_and_prevents_double_booking(): void
    {
        $company = Company::create(['name' => 'Apex Infra', 'code' => 'AI', 'slug' => 'apex', 'status' => 'active']);
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Sales Agent',
            'email' => 'sales@apex.com',
            'phone' => '9888877771',
            'password' => bcrypt('password'),
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Apex Heights',
            'code' => 'AH-01',
            'project_type' => 'residential',
            'status' => 'active',
        ]);

        $building = ProjectBuilding::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'name' => 'Tower 1',
            'code' => 'T1',
            'total_floors' => 10,
            'total_units' => 20,
        ]);

        $floor = ProjectFloor::create([
            'company_id' => $company->id,
            'building_id' => $building->id,
            'floor_number' => 1,
            'name' => 'Floor 1',
            'total_units' => 2,
        ]);

        $unit = Unit::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'unit_number' => '101',
            'unit_type' => '3BHK',
            'carpet_area' => 1200,
            'builtup_area' => 1400,
            'super_builtup_area' => 1600,
            'base_price' => 5000000,
            'final_price' => 5500000,
            'status' => 'available',
        ]);

        $lead = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-CONC',
            'first_name' => 'Bob',
            'last_name' => 'Builder',
            'phone' => '9444444444',
            'status' => 'site_visit',
        ]);

        $bookingService = new BookingService();

        // First booking attempt - Should succeed
        $booking = $bookingService->createBooking([
            'unit_id' => $unit->id,
            'lead_id' => $lead->id,
            'customer_name' => 'Bob Builder',
            'customer_phone' => '9444444444',
            'booking_amount' => 500000,
        ], $user);

        $this->assertNotNull($booking);
        $this->assertEquals('booking_pending', $unit->fresh()->status);

        // Second booking attempt on same unit - Should fail with Exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not available for booking');

        $bookingService->createBooking([
            'unit_id' => $unit->id,
            'lead_id' => $lead->id,
            'customer_name' => 'Another Buyer',
            'customer_phone' => '9555555555',
            'booking_amount' => 500000,
        ], $user);
    }
}
