<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Company;
use App\Models\CostSheet;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
    }

    public function test_user_can_download_booking_pdf_receipt()
    {
        $company = Company::first();
        $user = User::factory()->create(['company_id' => $company->id]);
        $project = Project::first();
        $unit = Unit::first();

        $lead = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-TEST-1001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '9876543210',
            'status' => 'new',
        ]);

        $costSheet = CostSheet::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'base_cost' => 5000000,
            'total_cost' => 5000000,
            'created_by_user_id' => $user->id,
        ]);

        $booking = Booking::create([
            'company_id' => $company->id,
            'lead_id' => $lead->id,
            'project_id' => $project->id,
            'unit_id' => $unit->id,
            'sales_user_id' => $user->id,
            'cost_sheet_id' => $costSheet->id,
            'booking_code' => 'BK-TEST-1001',
            'customer_name' => 'John Doe',
            'customer_phone' => '9876543210',
            'customer_email' => 'john@example.com',
            'booking_amount' => 50000,
            'total_unit_cost' => 5000000,
            'status' => 'confirmed',
            'booking_date' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('bookings.download-receipt', $booking->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
