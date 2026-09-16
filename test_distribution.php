<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\Project;
use App\Models\LeadSource;
use App\Services\LeadSources\LeadSourceManager;
use Illuminate\Support\Str;

use App\Models\User;

$director = User::where('email', 'director@apexrealty.com')->first();
$company = $director ? $director->company : Company::first();


$project = Project::where('company_id', $company->id)->first();
$source = LeadSource::where('company_id', $company->id)->first();

if (!$source) {
    echo "Creating a dummy Lead Source for testing...\n";
    $source = LeadSource::create([
        'company_id' => $company->id,
        'name' => 'Meta Ads',
        'type' => 'meta',
        'status' => 'connected'
    ]);
}

$manager = app(LeadSourceManager::class);

echo "Ingesting 10 Dummy Leads for Apex Realty...\n";
echo "Project: " . ($project ? $project->name : 'None') . "\n";
echo "Source: " . $source->name . "\n\n";

for ($i = 1; $i <= 10; $i++) {
    $phone = '98' . rand(10000000, 99999999);
    
    $payload = [
        'first_name' => 'Dummy',
        'last_name' => 'Lead ' . $i,
        'phone' => $phone,
        'email' => 'dummy' . $i . '@example.com',
        'notes' => 'Test Lead ' . $i,
        'campaign_name' => 'Test Campaign',
        'project_id' => $project ? $project->id : null,
    ];

    echo "Processing Lead $i ($phone)...\n";
    
    // Simulate webhook ingestion which triggers distribution automatically
    $result = $manager->processIncomingLead($source, $payload);
    
    if ($result['success']) {
        $lead = $result['lead'];
        $lead->load(['assignedTo', 'latestDistributionLog.rule']);
        
        $assignedName = $lead->assignedTo ? $lead->assignedTo->name : 'Unassigned';
        $ruleName = $lead->latestDistributionLog && $lead->latestDistributionLog->rule 
                    ? $lead->latestDistributionLog->rule->name 
                    : 'None';
                    
        echo "✅ Lead created! Assigned To: $assignedName (Rule: $ruleName)\n";
    } else {
        echo "❌ Failed to ingest lead.\n";
    }
    
    usleep(500000); // Sleep for 0.5 sec to simulate time gap
}

echo "\nDone!\n";
