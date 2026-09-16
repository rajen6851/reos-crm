<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$leads = App\Models\Lead::whereNotNull('assigned_to_user_id')->whereNull('assigned_to_manager_id')->get(); 
foreach($leads as $l) { 
    $u = App\Models\User::find($l->assigned_to_user_id); 
    if($u && $u->reporting_manager_id) { 
        $l->assigned_to_manager_id = $u->reporting_manager_id; 
        $l->save(); 
        echo "Fixed lead {$l->id}\n";
    } 
}
echo "Done!\n";
