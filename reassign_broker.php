<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = app(App\Services\LeadDistribution\LeadDistributionEngine::class); 
$lead = App\Models\Lead::find(36); 
if ($lead) {
    $lead->assigned_to_user_id = null; 
    $lead->assigned_to_manager_id = null; 
    $lead->save(); 
    App\Models\LeadAssignment::where('lead_id', 36)->delete(); 
    $engine->assignLead($lead); 
    echo "Fixed lead 36\n"; 
}
