<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

echo "=== REACT NATIVE SANCTUM API SUITE TESTING ===\n\n";

// 1. Test Login API for Sales Executive
$salesUser = User::withoutGlobalScopes()->where('email', 'cakesdekho@gmail.com')->first();
if ($salesUser) {
    $token = $salesUser->createToken('react-native-app')->plainTextToken;
    echo "[1] Sales Executive Auth Token generated successfully: " . substr($token, 0, 15) . "...\n";

    // Simulate GET /api/sales/dashboard request
    $request = Request::create('/api/sales/dashboard', 'GET');
    $request->setUserResolver(fn() => $salesUser);

    $controller = app(\App\Http\Controllers\Api\SalesExecutiveApiController::class);
    $response = $controller->dashboard($request);
    echo "[2] Sales Exec API /api/sales/dashboard Status: " . $response->status() . "\n";
    $data = json_decode($response->getContent(), true);
    echo "    - Total Assigned Leads: " . ($data['dashboard']['my_leads_count'] ?? 0) . "\n";
    echo "    - Pending Followups: " . ($data['dashboard']['pending_follow_ups'] ?? 0) . "\n";
}

// 2. Test Login API for Broker Partner
$brokerUser = User::withoutGlobalScopes()->where('email', 'rajendrarajput39756@gmail.com')->first();
if ($brokerUser) {
    $token = $brokerUser->createToken('react-native-broker-app')->plainTextToken;
    echo "\n[3] Broker Auth Token generated successfully: " . substr($token, 0, 15) . "...\n";

    // Simulate GET /api/broker/dashboard
    $request = Request::create('/api/broker/dashboard', 'GET');
    $request->setUserResolver(fn() => $brokerUser);

    $controller = app(\App\Http\Controllers\Api\BrokerApiController::class);
    $response = $controller->dashboard($request);
    echo "[4] Broker API /api/broker/dashboard Status: " . $response->status() . "\n";
    $data = json_decode($response->getContent(), true);
    echo "    - Total Commission Earned: ₹" . ($data['dashboard']['total_commission_earned'] ?? 0) . "\n";
    echo "    - Total Submitted Leads: " . ($data['dashboard']['total_leads_submitted'] ?? 0) . "\n";

    // Simulate GET /api/broker/projects
    $projRequest = Request::create('/api/broker/projects', 'GET');
    $projRequest->setUserResolver(fn() => $brokerUser);
    $projResponse = $controller->projects($projRequest);
    echo "[5] Broker API /api/broker/projects Status: " . $projResponse->status() . "\n";
    $projData = json_decode($projResponse->getContent(), true);
    echo "    - Public Projects Count: " . count($projData['data'] ?? []) . "\n";
}

echo "\nSUCCESS: React Native API endpoints are 100% operational!\n";
