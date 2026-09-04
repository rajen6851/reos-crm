<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- COMPANIES --- \n";
foreach (\App\Models\Company::all() as $c) {
    echo "ID: {$c->id} | Name: {$c->name}\n";
}

echo "\n--- USERS --- \n";
foreach (\App\Models\User::with('role')->get() as $u) {
    echo "ID: {$u->id} | Company: {$u->company_id} | Name: {$u->name} | Email: {$u->email} | Role: " . ($u->role->name ?? 'None') . "\n";
}

$company = \App\Models\Company::where('name', 'like', '%Freelancer%')->first();
if ($company) {
    $role = \App\Models\Role::where('slug', 'broker')->first() 
        ?? \App\Models\Role::create(['company_id' => $company->id, 'name' => 'Channel Partner / Broker', 'slug' => 'broker', 'description' => 'Broker']);
    
    $brokerUser = \App\Models\User::firstOrCreate(
        ['email' => 'sunil.broker@freelancer.com'],
        [
            'company_id' => $company->id,
            'role_id' => $role->id,
            'name' => 'Sunil Realty Services',
            'phone' => '9876500001',
            'password' => \Illuminate\Support\Facades\Hash::make('password123')
        ]
    );

    $broker = \App\Models\Broker::firstOrCreate(
        ['email' => $brokerUser->email],
        [
            'company_id' => $company->id,
            'user_id' => $brokerUser->id,
            'agency_name' => 'Sunil Channel Partner Realty',
            'broker_code' => 'BRK-4001',
            'phone' => $brokerUser->phone,
            'commission_rate' => 2.50,
            'status' => 'active'
        ]
    );
    echo "Seeded Broker: {$broker->agency_name} (Code: {$broker->broker_code}) for {$company->name}\n";
}
