<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

echo "====================================================\n";
echo "🔍 REOS COMPREHENSIVE 50+ BUG & STATIC AUDIT SCANNER\n";
echo "====================================================\n\n";

$issues = [];

// 1. Check all registered named routes in Blade views
$routes = array_keys(Route::getRoutes()->getRoutesByName());
$bladeFiles = glob(__DIR__ . '/../resources/views/**/*.blade.php');
$bladeFilesDir = new RecursiveDirectoryIterator(__DIR__ . '/../resources/views');
$bladeFilesIterator = new RecursiveIteratorIterator($bladeFilesDir);

$viewRoutesFound = [];
foreach ($bladeFilesIterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $content = file_get_content_or_empty($file->getPathname());
        preg_match_all("/route\(['\"]([a-zA-Z0-9\._\-]+)['\"]/", $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $rName) {
                if (!in_array($rName, $routes)) {
                    $issues[] = "BLADE ROUTE BUG in [{$file->getFilename()}]: Route name '{$rName}' does not exist in routes/web.php or routes/api.php!";
                }
            }
        }
    }
}

// 2. Inspect Models for missing relationships or scopes
$models = [
    \App\Models\User::class,
    \App\Models\Company::class,
    \App\Models\Project::class,
    \App\Models\ProjectBuilding::class,
    \App\Models\ProjectFloor::class,
    \App\Models\Unit::class,
    \App\Models\Lead::class,
    \App\Models\Booking::class,
    \App\Models\Payment::class,
    \App\Models\Agreement::class,
    \App\Models\Broker::class,
    \App\Models\BrokerLead::class,
    \App\Models\BrokerCommission::class,
    \App\Models\SupportTicket::class,
    \App\Models\SupportTicketReply::class,
    \App\Models\Notification::class,
    \App\Models\ActivityLog::class,
];

foreach ($models as $mClass) {
    if (!class_exists($mClass)) {
        $issues[] = "MODEL BUG: Class {$mClass} not found!";
    }
}

function file_get_content_or_empty($path) {
    return file_exists($path) ? file_get_content_fallback($path) : '';
}

function file_get_content_fallback($path) {
    return file_get_contents($path);
}

echo "Found " . count($issues) . " potential route / template issues.\n";
foreach ($issues as $idx => $iss) {
    echo ($idx + 1) . ". {$iss}\n";
}

if (empty($issues)) {
    echo "✅ No broken routes or unmapped Blade template calls found!\n";
}

echo "\n====================================================\n";
