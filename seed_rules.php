<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\DistributionRule;
use App\Models\DistributionRuleMember;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;

// Get the director user
$director = User::where('email', 'director@apexrealty.com')->first();
if (!$director) {
    echo "Director not found.\n";
    exit;
}

$companyId = $director->company_id;
if (!$companyId) {
    echo "Company not found for director.\n";
    exit;
}

// Find existing users or create dummy ones
$salesMembers = User::where('company_id', $companyId)
    ->where('id', '!=', $director->id)
    ->where('is_active', true)
    ->take(3)
    ->get();

if ($salesMembers->count() < 2) {
    echo "Creating dummy sales executives...\n";
    $names = ['Dheeraj', 'Suresh', 'Praveen'];
    foreach ($names as $name) {
        // Create user if not exists
        $user = User::firstOrCreate(
            ['email' => strtolower($name) . '@apexrealty.com'],
            [
                'name' => $name . ' Sales',
                'password' => Hash::make('password123'),
                'company_id' => $companyId,
                'role_id' => 3, // Assuming 3 is sales
                'is_active' => true,
                'phone' => '999999999' . rand(1, 9)
            ]
        );
    }
    
    // Re-fetch
    $salesMembers = User::where('company_id', $companyId)
        ->where('id', '!=', $director->id)
        ->where('is_active', true)
        ->take(3)
        ->get();
}

// Get a project for testing
$project = Project::where('company_id', $companyId)->first();

// --- Rule 1: Percentage Distribution (Global) ---
$rule1 = DistributionRule::firstOrCreate(
    ['company_id' => $companyId, 'name' => 'Default Percentage Allocation'],
    [
        'priority' => 10,
        'distribution_method' => 'percentage',
        'fallback_behavior' => 'redistribute',
        'is_active' => true,
        'version' => 1,
    ]
);

if ($rule1->wasRecentlyCreated) {
    $percentages = [50, 30, 20];
    foreach ($salesMembers as $index => $member) {
        $percent = $percentages[$index] ?? 0;
        if ($percent == 0) continue;
        
        DistributionRuleMember::create([
            'distribution_rule_id' => $rule1->id,
            'user_id' => $member->id,
            'allocation_value' => $percent,
        ]);
    }
    echo "Created Rule 1: Percentage Allocation\n";
}

// --- Rule 2: Round Robin (Project Specific) ---
if ($project) {
    $rule2 = DistributionRule::firstOrCreate(
        ['company_id' => $companyId, 'name' => 'Round Robin for ' . $project->name],
        [
            'project_id' => $project->id,
            'priority' => 2,
            'distribution_method' => 'round_robin',
            'fallback_behavior' => 'skip',
            'is_active' => true,
            'version' => 1,
        ]
    );

    if ($rule2->wasRecentlyCreated) {
        foreach ($salesMembers as $member) {
            DistributionRuleMember::create([
                'distribution_rule_id' => $rule2->id,
                'user_id' => $member->id,
            ]);
        }
        echo "Created Rule 2: Round Robin for {$project->name}\n";
    }
}

// --- Rule 3: Performance Based ---
$rule3 = DistributionRule::firstOrCreate(
    ['company_id' => $companyId, 'name' => 'High Intent Performance Distribution'],
    [
        'priority' => 1,
        'distribution_method' => 'performance',
        'fallback_behavior' => 'redistribute',
        'is_active' => false,
        'version' => 1,
    ]
);

if ($rule3->wasRecentlyCreated) {
    foreach ($salesMembers as $member) {
        DistributionRuleMember::create([
            'distribution_rule_id' => $rule3->id,
            'user_id' => $member->id,
        ]);
    }
    echo "Created Rule 3: Performance Based (Inactive)\n";
}

echo "Successfully seeded distribution rules for Director!\n";
