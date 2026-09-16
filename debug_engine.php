<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$engine = app(App\Services\LeadDistribution\LeadDistributionEngine::class); 
$lead = App\Models\Lead::find(46); 
$rule = App\Models\DistributionRule::find(1); 
$eligible = $rule->members()->get(); 
$user = app(\App\Services\LeadDistribution\DistributionMethods\PercentageDistribution::class)->selectUser($rule, $eligible);
echo 'Selected: ' . var_export($user, true) . "\n";
