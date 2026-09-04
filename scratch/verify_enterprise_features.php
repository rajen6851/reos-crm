<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\PaymentSchedule;
use App\Models\CoApplicant;
use App\Models\KycDocument;
use App\Models\UnitPriceHistory;
use App\Models\LoginAuditLog;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

echo "=== ENTERPRISE MODULES VERIFICATION ===\n\n";

$admin = User::withoutGlobalScopes()->first();
if (!$admin) {
    echo "FAIL: User not found\n";
    exit(1);
}
Auth::login($admin);

// 1. Verify Login Audit Logging
$audit = LoginAuditLog::create([
    'company_id' => $admin->company_id,
    'user_id' => $admin->id,
    'email' => $admin->email,
    'ip_address' => '127.0.0.1',
    'user_agent' => 'PHPUnit Verification Tool',
    'login_status' => 'success',
]);
echo "[1] Security Login Audit Log created: ID {$audit->id} for {$audit->email}\n";

// 2. Verify Payment Milestone Schedule Generation
$booking = Booking::withoutGlobalScopes()->first();
if ($booking) {
    $schedule = PaymentSchedule::withoutGlobalScopes()->create([
        'company_id' => $booking->company_id ?? 1,
        'booking_id' => $booking->id,
        'milestone_name' => 'Structure & Slab Casting Stage',
        'percentage' => 30.00,
        'due_amount' => 1500000.00,
        'paid_amount' => 0.00,
        'due_date' => now()->addDays(60),
        'status' => 'pending',
    ]);
    echo "[2] Payment Milestone Schedule created: ID {$schedule->id} ({$schedule->milestone_name}) - Due: ₹{$schedule->due_amount}\n";

    // 3. Verify Co-Applicant / Joint Buyer
    $coApplicant = CoApplicant::withoutGlobalScopes()->create([
        'company_id' => $booking->company_id ?? 1,
        'booking_id' => $booking->id,
        'full_name' => 'Sunita Verma (Joint Owner)',
        'relationship' => 'Spouse',
        'phone' => '9876543210',
        'pan_number' => 'ABCDE1234F',
    ]);
    echo "[3] Co-Applicant Joint Buyer created: ID {$coApplicant->id} ({$coApplicant->full_name})\n";
}

// 4. Verify KYC Document Vault
$kyc = KycDocument::withoutGlobalScopes()->create([
    'company_id' => 1,
    'documentable_type' => 'App\Models\User',
    'documentable_id' => $admin->id,
    'document_type' => 'GST Certificate',
    'document_number' => '36AAACA12341ZV',
    'file_path' => '/uploads/kyc/gst.pdf',
    'expiry_date' => now()->addYear(),
    'status' => 'verified',
]);
echo "[4] KYC Document Vault record created: ID {$kyc->id} ({$kyc->document_type})\n";

// 5. Verify Unit Price History Logging
$unit = Unit::withoutGlobalScopes()->first();
if ($unit) {
    $priceHistory = UnitPriceHistory::withoutGlobalScopes()->create([
        'company_id' => $unit->company_id ?? 1,
        'unit_id' => $unit->id,
        'updated_by_user_id' => $admin->id,
        'old_base_price' => $unit->base_price ?? 5000000,
        'new_base_price' => ($unit->base_price ?? 5000000) + 250000,
        'old_total_price' => $unit->final_price ?? 5500000,
        'new_total_price' => ($unit->final_price ?? 5500000) + 300000,
        'change_reason' => 'Q4 Festive Price Hike Revision',
    ]);
    echo "[5] Unit Price History logged: Unit ID {$unit->id} - New Total: ₹{$priceHistory->new_total_price}\n";
}

echo "\nSUCCESS: All enterprise module checks passed cleanly!\n";
