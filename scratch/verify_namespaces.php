<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\KycDocumentController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\CoApplicantController;

echo "KycDocumentController class loaded: " . (class_exists(KycDocumentController::class) ? "YES" : "NO") . "\n";
echo "LeadImportController class loaded: " . (class_exists(LeadImportController::class) ? "YES" : "NO") . "\n";
echo "CoApplicantController class loaded: " . (class_exists(CoApplicantController::class) ? "YES" : "NO") . "\n";
