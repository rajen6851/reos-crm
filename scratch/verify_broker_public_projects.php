<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

$brokerUser = User::withoutGlobalScopes()->where('email', 'rajendrarajput39756@gmail.com')->first();
if ($brokerUser) {
    Auth::login($brokerUser);
    echo "Logged in as Broker: {$brokerUser->name}\n";

    $projects = Project::withoutGlobalScopes()
        ->where('status', 'active')
        ->where(function ($vq) {
            $vq->where('visibility', 'public')->orWhereNull('visibility');
        })
        ->with('company')
        ->get();

    echo "Public Projects count for Broker: " . $projects->count() . "\n";
    foreach ($projects as $p) {
        echo " - Project ID: {$p->id} | Name: {$p->name} | Company: " . ($p->company->name ?? 'N/A') . " | Visibility: {$p->visibility}\n";
    }
} else {
    echo "Broker user not found\n";
}
