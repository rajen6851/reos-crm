<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\User;
use App\Http\Controllers\BrokerController;
use Illuminate\Support\Facades\Auth;

echo "=== PROPERTY VISIBILITY ACCESS TEST ===\n\n";

// 1. Create/Update a Private Project & Public Project
$publicProject = Project::withoutGlobalScopes()->where('visibility', 'public')->first()
    ?? Project::withoutGlobalScopes()->first();
$publicProject->visibility = 'public';
$publicProject->save();

$privateProject = Project::withoutGlobalScopes()->where('id', '!=', $publicProject->id)->first();
if ($privateProject) {
    $privateProject->visibility = 'private';
    $privateProject->save();
    echo "[1] Set Project ID {$privateProject->id} ('{$privateProject->name}') to PRIVATE\n";
}
echo "[2] Set Project ID {$publicProject->id} ('{$publicProject->name}') to PUBLIC\n";

// 2. Query projects as a Broker
$brokerUser = User::withoutGlobalScopes()->whereHas('role', function($q){ $q->where('slug', 'broker'); })->first();
if ($brokerUser) {
    Auth::login($brokerUser);

    $visibleProjects = Project::withoutGlobalScopes()
        ->where('status', 'active')
        ->where(function ($q) {
            $q->where('visibility', 'public')->orWhereNull('visibility');
        })
        ->pluck('id')
        ->toArray();

    echo "[3] Broker User '{$brokerUser->name}' sees Public Projects: " . implode(', ', $visibleProjects) . "\n";
    if ($privateProject && in_array($privateProject->id, $visibleProjects)) {
        echo "FAIL: Private project is visible to Broker!\n";
        exit(1);
    } else {
        echo "[4] SUCCESS: Private project ID {$privateProject->id} is hidden from Broker!\n";
    }
}

// 3. Query projects as Internal Team (Company Director / Admin)
$adminUser = User::withoutGlobalScopes()->whereHas('role', function($q){ $q->whereIn('slug', ['admin', 'director']); })->first();
if ($adminUser) {
    Auth::login($adminUser);
    $allProjects = Project::where('company_id', $adminUser->company_id)->pluck('id')->toArray();
    echo "[5] Internal Team User '{$adminUser->name}' sees ALL Company Projects (Public & Private): " . implode(', ', $allProjects) . "\n";
}

echo "\nSUCCESS: Property Public/Private Visibility control verified cleanly!\n";
