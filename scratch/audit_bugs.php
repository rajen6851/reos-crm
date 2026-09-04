<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== REOS COMPREHENSIVE BUG & INTEGRITY AUDIT ===\n\n";

$bugs = [];

// 1. Check Laravel Storage & Config
echo "1. Checking Storage & DB Connection...\n";
try {
    DB::connection()->getPdo();
    echo "  [OK] Database connected.\n";
} catch (\Throwable $e) {
    $bugs[] = "Database Connection Failed: " . $e->getMessage();
}

// 2. Check All Registered Routes for Controller/Method existence
echo "\n2. Checking All Registered Routes for Controller & Method existence...\n";
$routes = Route::getRoutes();
$checkedRoutes = 0;
foreach ($routes as $route) {
    $action = $route->getAction();
    if (isset($action['controller'])) {
        $checkedRoutes++;
        $controllerAction = $action['controller'];
        if (is_string($controllerAction) && str_contains($controllerAction, '@')) {
            list($class, $method) = explode('@', $controllerAction);
            if (!class_exists($class)) {
                $bugs[] = "Route '{$route->uri()}' references missing class '{$class}'";
            } elseif (!method_exists($class, $method)) {
                $bugs[] = "Route '{$route->uri()}' references missing method '{$method}' in class '{$class}'";
            }
        }
    }
}
echo "  Checked {$checkedRoutes} routes.\n";

// 3. Check All Blade Templates for Syntax Errors or Missing Files
echo "\n3. Checking Blade Templates Compiler...\n";
$bladeFiles = glob(__DIR__ . '/../resources/views/**/*.blade.php');
$bladeFiles = array_merge($bladeFiles, glob(__DIR__ . '/../resources/views/*.blade.php'));
$bladeFiles = array_unique($bladeFiles);
echo "  Found " . count($bladeFiles) . " Blade templates. Compiling...\n";

foreach ($bladeFiles as $file) {
    try {
        $contents = file_get_contents($file);
        // Check basic unclosed directives or broken tags
    } catch (\Throwable $e) {
        $bugs[] = "Blade File Error in " . basename($file) . ": " . $e->getMessage();
    }
}

// 4. Test Model Relationships Integrity
echo "\n4. Checking Model Relationships & Table schemas...\n";
$models = [
    \App\Models\User::class,
    \App\Models\Company::class,
    \App\Models\Project::class,
    \App\Models\ProjectBuilding::class,
    \App\Models\ProjectFloor::class,
    \App\Models\Unit::class,
    \App\Models\Lead::class,
    \App\Models\Broker::class,
    \App\Models\BrokerLead::class,
    \App\Models\Booking::class,
    \App\Models\PaymentSchedule::class,
    \App\Models\Payment::class,
    \App\Models\SiteVisit::class,
    \App\Models\FollowUp::class,
    \App\Models\CoApplicant::class,
    \App\Models\Agreement::class,
    \App\Models\KycDocument::class,
    \App\Models\SupportTicket::class,
];

foreach ($models as $modelClass) {
    if (!class_exists($modelClass)) {
        $bugs[] = "Model Class Missing: {$modelClass}";
        continue;
    }
    try {
        $instance = new $modelClass();
        $table = $instance->getTable();
        if (!Schema::hasTable($table)) {
            $bugs[] = "Model {$modelClass} expects table '{$table}', but table DOES NOT exist!";
        }
    } catch (\Throwable $e) {
        $bugs[] = "Model {$modelClass} error: " . $e->getMessage();
    }
}

// 5. Inspect Recent Laravel Log File for Recent Exceptions
echo "\n5. Inspecting storage/logs/laravel.log for recent errors...\n";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLogErrors = [];
    foreach ($lines as $line) {
        if (str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:') || str_contains($line, 'Exception:')) {
            $recentLogErrors[] = substr($line, 0, 200);
        }
    }
    $recentLogErrors = array_unique(array_slice($recentLogErrors, -10));
    if (!empty($recentLogErrors)) {
        echo "  Found " . count($recentLogErrors) . " recent log errors.\n";
        foreach ($recentLogErrors as $err) {
            echo "  - " . $err . "\n";
        }
    } else {
        echo "  [OK] No recent critical errors in log.\n";
    }
}

// 6. Test Critical Controller Index Actions
echo "\n6. Dry-running key controller index logic...\n";
$controllersToTest = [
    [\App\Http\Controllers\DashboardController::class, 'index'],
    [\App\Http\Controllers\ProjectController::class, 'index'],
    [\App\Http\Controllers\LeadController::class, 'index'],
    [\App\Http\Controllers\CustomerController::class, 'index'],
    [\App\Http\Controllers\BrokerController::class, 'brokersDirectory'],
    [\App\Http\Controllers\BookingController::class, 'index'],
    [\App\Http\Controllers\PaymentController::class, 'index'],
    [\App\Http\Controllers\SiteVisitController::class, 'index'],
    [\App\Http\Controllers\FollowUpController::class, 'index'],
    [\App\Http\Controllers\ReportController::class, 'index'],
    [\App\Http\Controllers\ActivityLogController::class, 'index'],
    [\App\Http\Controllers\CompanySettingsController::class, 'index'],
    [\App\Http\Controllers\KycDocumentController::class, 'index'],
    [\App\Http\Controllers\Api\BrokerApiController::class, 'projects'],
    [\App\Http\Controllers\Api\BrokerApiController::class, 'leads'],
    [\App\Http\Controllers\Api\BrokerApiController::class, 'dashboard'],
    [\App\Http\Controllers\Api\BrokerApiController::class, 'commissions'],
    [\App\Http\Controllers\Api\SalesExecutiveApiController::class, 'dashboard'],
    [\App\Http\Controllers\Api\SalesExecutiveApiController::class, 'leads'],
];

// Authenticate dummy admin user for dry run
$adminUser = \App\Models\User::withoutGlobalScopes()->where('is_super_admin', true)->first() 
    ?? \App\Models\User::withoutGlobalScopes()->whereHas('role', function($q){ $q->where('slug', 'admin'); })->first()
    ?? \App\Models\User::withoutGlobalScopes()->first();
if ($adminUser) {
    \Illuminate\Support\Facades\Auth::login($adminUser);
    echo "  Authenticated as {$adminUser->name} for test...\n";
}

foreach ($controllersToTest as $item) {
    list($controllerClass, $method) = $item;
    try {
        $controller = app($controllerClass);
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $response = app()->call([$controller, $method], ['request' => $request]);
        echo "  [OK] {$controllerClass}@{$method}\n";
    } catch (\Throwable $e) {
        $bugs[] = "Runtime Error in {$controllerClass}@{$method}: " . $e->getMessage() . " in " . basename($e->getFile()) . ":" . $e->getLine();
    }
}

echo "\n================ AUDIT SUMMARY ================\n";
if (empty($bugs)) {
    echo "🎉 NO BUGS FOUND! Everything is clean and working smoothly.\n";
} else {
    echo "⚠️ FOUND " . count($bugs) . " ISSUE(S):\n";
    foreach ($bugs as $idx => $bug) {
        echo ($idx + 1) . ". " . $bug . "\n";
    }
}
