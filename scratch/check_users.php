<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::withoutGlobalScopes()->with('role', 'company')->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Email: {$u->email} | Name: {$u->name} | Role: " . ($u->role ? "{$u->role->name} ({$u->role->slug})" : "No Role") . " | SuperAdmin: " . ($u->is_super_admin ? "Yes" : "No") . " | Company: " . ($u->company ? $u->company->name : "None") . "\n";
}
