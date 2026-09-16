<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Broker;
use Illuminate\Support\Facades\Hash;

$company = Company::find(1); // Apex Realty
$brokerRole = Role::where('slug', 'broker')->first();

if (!$brokerRole) {
    echo "Broker role not found.\n";
    exit;
}

$email = 'independent_broker@test.com';
$password = 'password123';

$user = User::firstOrCreate(
    ['email' => $email],
    [
        'name' => 'Independent Broker Test',
        'password' => Hash::make($password),
        'company_id' => $company->id,
        'role_id' => $brokerRole->id,
        'is_active' => true,
        'phone' => '9988776655'
    ]
);

$rm = User::whereHas('role', function($q){ $q->where('slug', 'sales_executive'); })->first();

$broker = Broker::firstOrCreate(
    ['user_id' => $user->id],
    [
        'company_id' => $company->id,
        'broker_code' => 'BRK-' . rand(1000, 9999),
        'agency_name' => 'Independent Testing Agency',
        'phone' => '9988776655',
        'relationship_manager_id' => $rm ? $rm->id : null,
        'commission_rate' => 2.50,
        'status' => 'active'
    ]
);

echo "Broker created successfully!\n";
echo "Email: " . $email . "\n";
echo "Password: " . $password . "\n";
echo "RM Assigned: " . ($rm ? $rm->name : 'None') . "\n";
