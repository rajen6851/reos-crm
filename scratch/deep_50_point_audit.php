<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use App\Models\Unit;
use App\Models\Lead;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\BrokerCommission;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Route;

echo "====================================================\n";
echo "🔬 REOS DEEP 50-POINT INTEGRITY & BUG SCANNER\n";
echo "====================================================\n\n";

$passed = 0;
$failed = 0;
$failures = [];

function check($description, $condition) {
    global $passed, $failed, $failures;
    if ($condition) {
        $passed++;
        echo "✅ [PASS] {$description}\n";
    } else {
        $failed++;
        $failures[] = $description;
        echo "❌ [FAIL] {$description}\n";
    }
}

// 1-10: Database Models & Relationships
check("User model belongs to Company relationship", method_exists(User::class, 'company'));
check("Lead model belongs to Company relationship", method_exists(Lead::class, 'company'));
check("Lead model belongs to Project relationship", method_exists(Lead::class, 'project'));
check("Lead model has getNameAttribute accessor", method_exists(Lead::class, 'getNameAttribute'));
check("Unit model belongs to Building relationship", method_exists(Unit::class, 'building'));
check("Unit model belongs to Floor relationship", method_exists(Unit::class, 'floor'));
check("Booking model belongs to Lead relationship", method_exists(Booking::class, 'lead'));
check("Booking model belongs to Unit relationship", method_exists(Booking::class, 'unit'));
check("Broker model has brokerLeads relationship", method_exists(Broker::class, 'brokerLeads'));
check("BrokerLead model belongs to Lead relationship", method_exists(BrokerLead::class, 'lead'));

// 11-20: Controllers & Methods Existence
check("DashboardController@index exists", method_exists(\App\Http\Controllers\DashboardController::class, 'index'));
check("LeadController@index exists", method_exists(\App\Http\Controllers\LeadController::class, 'index'));
check("LeadController@show exists", method_exists(\App\Http\Controllers\LeadController::class, 'show'));
check("ProjectController@index exists", method_exists(\App\Http\Controllers\ProjectController::class, 'index'));
check("ProjectController@show exists", method_exists(\App\Http\Controllers\ProjectController::class, 'show'));
check("ProjectController@updateUnit exists", method_exists(\App\Http\Controllers\ProjectController::class, 'updateUnit'));
check("BookingController@index exists", method_exists(\App\Http\Controllers\BookingController::class, 'index'));
check("BookingController@show exists", method_exists(\App\Http\Controllers\BookingController::class, 'show'));
check("BrokerController@brokersDirectory exists", method_exists(\App\Http\Controllers\BrokerController::class, 'brokersDirectory'));
check("BrokerController@show exists", method_exists(\App\Http\Controllers\BrokerController::class, 'show'));

// 21-30: Named Web Routes
$webRoutes = Route::getRoutes();
check("Route 'dashboard' exists", $webRoutes->hasNamedRoute('dashboard'));
check("Route 'leads.index' exists", $webRoutes->hasNamedRoute('leads.index'));
check("Route 'leads.show' exists", $webRoutes->hasNamedRoute('leads.show'));
check("Route 'projects.index' exists", $webRoutes->hasNamedRoute('projects.index'));
check("Route 'projects.show' exists", $webRoutes->hasNamedRoute('projects.show'));
check("Route 'units.update' exists", $webRoutes->hasNamedRoute('units.update'));
check("Route 'bookings.index' exists", $webRoutes->hasNamedRoute('bookings.index'));
check("Route 'bookings.show' exists", $webRoutes->hasNamedRoute('bookings.show'));
check("Route 'brokers.index' exists", $webRoutes->hasNamedRoute('brokers.index'));
check("Route 'brokers.show' exists", $webRoutes->hasNamedRoute('brokers.show'));

// 31-40: Additional Named Routes & Views
check("Route 'customers.index' exists", $webRoutes->hasNamedRoute('customers.index'));
check("Route 'follow-ups.index' exists", $webRoutes->hasNamedRoute('follow-ups.index'));
check("Route 'site-visits.index' exists", $webRoutes->hasNamedRoute('site-visits.index'));
check("Route 'reports.index' exists", $webRoutes->hasNamedRoute('reports.index'));
check("Route 'notifications.index' exists", $webRoutes->hasNamedRoute('notifications.index'));
check("Route 'activity-logs.index' exists", $webRoutes->hasNamedRoute('activity-logs.index'));
check("Route 'support-tickets.index' exists", $webRoutes->hasNamedRoute('support-tickets.index'));
check("Route 'support-tickets.show' exists", $webRoutes->hasNamedRoute('support-tickets.show'));
check("Route 'company-settings.index' exists", $webRoutes->hasNamedRoute('company-settings.index'));
check("Route 'profile.edit' exists", $webRoutes->hasNamedRoute('profile.edit'));

// 41-50: Blade Views Files Existence
$views = [
    'dashboard',
    'leads.index',
    'leads.show',
    'projects.index',
    'projects.show',
    'bookings.index',
    'bookings.show',
    'brokers.index',
    'brokers.show',
    'customers.index',
];

foreach ($views as $idx => $vName) {
    check("Blade view '{$vName}' exists", view()->exists($vName));
}

echo "\n====================================================\n";
echo "SUMMARY RESULTS: Passed {$passed} / 50 | Failed: {$failed}\n";
echo "====================================================\n";

if ($failed > 0) {
    echo "Failures list:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
}
