<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$manager = App\Models\User::where('role_id', 2)->first(); 
$users = App\Models\User::whereIn('id', [3,4,5])->get(); 
foreach($users as $u) { 
    $u->reporting_manager_id = $manager->id; 
    $u->save(); 
    echo $u->name . ' now reports to ' . $manager->name . "\n"; 
}
