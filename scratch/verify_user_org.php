<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::withoutGlobalScopes()->first();
if ($user) {
    $user->branch = 'Cyber City Branch';
    $user->department = 'Executive Management';
    $user->designation = 'Managing Director';
    $user->save();

    echo "SUCCESS: Updated User {$user->name} with Branch: {$user->branch}, Dept: {$user->department}, Designation: {$user->designation}\n";
}
