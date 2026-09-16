<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'director@apexrealty.com')->first();
$query = App\Models\Lead::where('company_id', $user->company_id);
echo "Total Leads: " . $query->count() . "\n";
