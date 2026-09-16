<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Broker;
use App\Models\Lead;
use App\Models\BrokerLead;
use Illuminate\Support\Str;

echo "Ingesting 10 Dummy Broker Leads...\n";

// Get the broker user
$user = User::where('email', 'independent_broker@test.com')->first();
if (!$user) {
    die("Broker user not found!\n");
}

$broker = Broker::withoutGlobalScopes()->where('user_id', $user->id)->first();
if (!$broker) {
    die("Broker profile not found!\n");
}

$companyId = $user->company_id;
$engine = app(\App\Services\LeadDistribution\LeadDistributionEngine::class);

for ($i = 1; $i <= 10; $i++) {
    $phone = '77000' . rand(10000, 99999);
    
    // Create main Lead record
    $lead = Lead::create([
        'company_id' => $companyId,
        'lead_code' => 'LD-BRK-' . strtoupper(Str::random(6)),
        'first_name' => "Broker Lead $i",
        'last_name' => "Test",
        'email' => "broker_lead_$i@example.com",
        'phone' => $phone,
        'broker_id' => $broker->id,
        'interested_project_id' => 1,
        'status' => 'new',
        'is_duplicate' => false,
        'notes' => "Dummy Broker Lead $i",
    ]);

    // Distribute lead using the new flexible Distribution Engine
    $lead = $engine->assignLead($lead);

    // Create BrokerLead authoritative visibility record
    $brokerLead = BrokerLead::create([
        'company_id' => $companyId,
        'broker_id' => $broker->id,
        'lead_id' => $lead->id,
        'project_id' => 1,
        'submitted_at' => now(),
        'broker_visible_status' => 'Submitted',
        'broker_visible_message' => 'Lead successfully submitted and waiting for manager review.',
    ]);

    $assignedUser = $lead->assignedTo ? $lead->assignedTo->name : 'Unassigned';
    echo "✅ Lead $i created! Assigned To: $assignedUser\n";
}

echo "\nDone!\n";
