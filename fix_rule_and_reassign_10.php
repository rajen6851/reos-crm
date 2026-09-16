<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fix the allocation_value for Rule 1
$members = App\Models\DistributionRuleMember::where('distribution_rule_id', 1)->orderBy('id', 'asc')->get(); 
$values = [50, 30, 20]; 
foreach($members as $index => $member) { 
    $member->allocation_value = $values[$index]; 
    $member->save(); 
}
echo "Fixed allocation_values.\n";

// Re-assign Leads 37 to 46
$engine = app(App\Services\LeadDistribution\LeadDistributionEngine::class); 
for($id = 37; $id <= 46; $id++) {
    $lead = App\Models\Lead::find($id); 
    if ($lead) {
        $lead->assigned_to_user_id = null; 
        $lead->assigned_to_manager_id = null; 
        $lead->save(); 
        App\Models\LeadAssignment::where('lead_id', $id)->delete(); 
        $engine->assignLead($lead); 
        echo "Fixed lead $id\n"; 
    }
}
