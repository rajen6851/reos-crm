<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\DistributionRule;
use App\Models\DistributionRuleMember;
use App\Models\Lead;

// 1. Get a proper Sales Manager
$salesManager = User::whereHas('role', function($q) {
    $q->where('slug', 'manager');
})->first();

echo "Selected Sales Manager: " . $salesManager->name . "\n";

// 2. Get proper Sales Executives
$salesExecs = User::whereHas('role', function($q) {
    $q->where('slug', 'sales_executive');
})->take(3)->get();

// 3. Make them report to the Sales Manager
foreach ($salesExecs as $exec) {
    $exec->reporting_manager_id = $salesManager->id;
    $exec->save();
    echo $exec->name . " now reports to " . $salesManager->name . "\n";
}

// 4. Update the Default Percentage Allocation rule to use these 3 Sales Executives
$rule = DistributionRule::where('name', 'Default Percentage Allocation')->first();
if ($rule) {
    // Clear old members
    DistributionRuleMember::where('distribution_rule_id', $rule->id)->delete();
    
    // Add new ones
    foreach ($salesExecs as $index => $exec) {
        DistributionRuleMember::create([
            'distribution_rule_id' => $rule->id,
            'user_id' => $exec->id,
            'allocation_percentage' => [50, 30, 20][$index],
            'daily_limit' => 50,
            'is_active' => true,
        ]);
        echo "Added " . $exec->name . " to rule with " . [50, 30, 20][$index] . "%\n";
    }
}

// 5. Let's fix the existing 32 leads so they are assigned to these real Sales Execs and Manager
$leads = Lead::all();
$i = 0;
foreach ($leads as $lead) {
    $exec = $salesExecs[$i % 3];
    $lead->assigned_to_user_id = $exec->id;
    $lead->assigned_to_manager_id = $salesManager->id;
    $lead->save();
    
    // Also update distribution log
    if ($log = $lead->latestDistributionLog) {
        $log->assigned_user_id = $exec->id;
        $log->save();
    }
    $i++;
}

echo "Successfully updated all leads to have proper Sales Execs and Sales Manager!\n";
