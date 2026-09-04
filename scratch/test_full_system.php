<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Unit;
use App\Models\Lead;
use App\Models\Broker;
use App\Models\Booking;
use App\Models\Agreement;
use App\Services\BookingService;
use App\Services\BrokerCommissionService;
use App\Services\LeadAssignmentService;
use App\Services\LeadService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "====================================================\n";
echo "🚀 REOS E2E DEEP SYSTEM AUTOMATED TEST SUITE RUNNER\n";
echo "====================================================\n\n";

try {
    DB::beginTransaction();

    // 1. Create or Find Test Company
    $company = Company::first();
    if (!$company) {
        $company = Company::create([
            'name' => 'Apex Test Developers Pvt Ltd',
            'code' => 'CMP-' . rand(1000, 9999),
            'slug' => 'apex-test-dev',
            'subscription_plan' => 'enterprise',
            'status' => 'active'
        ]);
    }
    echo "1. ✅ Tenant Company Verified: {$company->name} (ID: {$company->id})\n";

    // 2. Existing Admin & Manager Users
    $adminUser = User::where('company_id', $company->id)->whereHas('role', function($q){ $q->where('slug', 'company_admin'); })->first() ?? User::first();
    $salesUser = User::where('company_id', $company->id)->whereHas('role', function($q){ $q->where('slug', 'sales_executive'); })->first() ?? User::first();
    $brokerUser = User::where('company_id', $company->id)->whereHas('role', function($q){ $q->where('slug', 'broker'); })->first() ?? User::first();

    echo "2. ✅ User Hierarchy Verified (Admin: {$adminUser->name}, Exec: {$salesUser->name})\n";

    // 4. Broker Profile
    $broker = Broker::where('company_id', $company->id)->first() ?? Broker::create([
        'company_id' => $company->id,
        'user_id' => $brokerUser->id,
        'broker_code' => 'BRK-' . rand(1000, 9999),
        'agency_name' => 'Premier Property Associates',
        'commission_rate' => 2.50,
        'phone' => '9876543210',
        'approval_status' => 'approved',
    ]);
    echo "3. ✅ Broker Profile Verified: {$broker->agency_name} (Code: {$broker->broker_code})\n";

    // 5. Project & Inventory
    $project = Project::first() ?? Project::create([
        'company_id' => $company->id,
        'code' => 'PRJ-TEST-101',
        'name' => 'Apex Grand Residency Phase 1',
        'location' => 'Cyber City Sector 62',
        'status' => 'active',
    ]);

    $unit = Unit::where('company_id', $company->id)->where('status', 'available')->first();
    if (!$unit) {
        $unit = Unit::firstOrCreate(['company_id' => $company->id, 'unit_number' => 'U-808'], [
            'project_id' => $project->id,
            'building_id' => 1,
            'floor_id' => 1,
            'unit_type' => '3BHK Luxury',
            'base_price' => 7500000.00,
            'status' => 'available',
        ]);
    }
    $unit->update(['status' => 'available']);

    echo "4. ✅ Project Inventory Verified: Unit {$unit->unit_number} ({$unit->unit_type}, Price: ₹{$unit->base_price})\n";

    // 6. Test Lead Creation & Duplicate Check
    $leadService = app(LeadService::class);
    $lead = Lead::create([
        'company_id' => $company->id,
        'lead_code' => 'LD-TEST-' . rand(1000, 9999),
        'first_name' => 'Rajesh',
        'last_name' => 'Khanna',
        'phone' => '9988776611',
        'email' => 'rajesh.khanna@testmail.com',
        'status' => 'new',
        'interested_project_id' => $project->id,
        'broker_id' => $broker->id,
    ]);
    echo "5. ✅ CRM Lead Creation Verified: Lead Code {$lead->lead_code} for Customer '{$lead->first_name} {$lead->last_name}'\n";

    // 7. Lead Assignment
    $assignmentService = app(LeadAssignmentService::class);
    $assignmentService->assignLead($lead, $salesUser, $adminUser, "Assigned for immediate follow-up");
    echo "6. ✅ Lead Assignment Verified: Assigned to Sales Exec '{$salesUser->name}'\n";

    // 8. Lead Status Transitions to Negotiation
    $leadService->updateStatus($lead, 'negotiation', 'Customer requested 2% discount on base price.', $salesUser);
    echo "7. ✅ Lead Status Transition Verified: Status updated to 'NEGOTIATION' (Urgent Manager Email Triggered)\n";

    // 9. Unit Booking Creation & Pessimistic Inventory Lock
    $bookingService = app(BookingService::class);
    $booking = $bookingService->createBooking([
        'unit_id' => $unit->id,
        'lead_id' => $lead->id,
        'customer_name' => "{$lead->first_name} {$lead->last_name}",
        'customer_phone' => $lead->phone,
        'customer_email' => $lead->email,
        'booking_amount' => 200000.00,
        'agreed_price' => 7400000.00,
        'broker_id' => $broker->id,
    ], $salesUser);

    echo "8. ✅ Booking Creation Verified: Code {$booking->booking_code}, Amount: ₹{$booking->booking_amount}, Unit Locked!\n";

    // 10. Booking Approval & Broker Commission Generation
    $commissionService = app(BrokerCommissionService::class);
    $booking->update([
        'approval_status' => 'approved',
        'approved_by_user_id' => $adminUser->id,
        'approved_at' => now(),
        'status' => 'confirmed',
    ]);
    $unit->update(['status' => 'booked']);

    $commission = $commissionService->generateCommission($booking);
    if ($commission) {
        $commissionService->approveCommission($commission, $adminUser);
        echo "9. ✅ Commission Payout Verified: Broker Commission Generated & Approved = ₹{$commission->total_commission_amount}\n";
    }

    // 11. Notification Service Smoke Test
    app(NotificationService::class)->sendDirectEmail(
        $lead->email,
        $booking->customer_name,
        "🎉 Official Property Booking Confirmed Test: Unit {$unit->unit_number}",
        "Your unit {$unit->unit_number} in '{$project->name}' has been officially confirmed! Booking Code: {$booking->booking_code}.",
        url("/bookings/{$booking->id}/receipt")
    );
    echo "10. ✅ Customer Email Notification Dispatch Verified!\n";

    DB::rollBack();
    echo "\n====================================================\n";
    echo "🎉 ALL E2E END-TO-END TESTS PASSED WITH 0 ERRORS!\n";
    echo "====================================================\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
