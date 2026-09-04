<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

$company1 = Company::where('slug', 'apex-realty')->first();
if (!$company1) {
    $company1 = Company::first();
}

$roleDirector = Role::withoutGlobalScopes()
    ->where('company_id', $company1->id)
    ->where('slug', 'director')
    ->first();

if (!$roleDirector) {
    $roleDirector = Role::create([
        'company_id' => $company1->id,
        'name' => 'Director',
        'slug' => 'director',
        'description' => 'Company Director role with full system access',
    ]);
}

$directorUser = User::withoutGlobalScopes()->where('email', 'director@apexrealty.com')->first();

if (!$directorUser) {
    $directorUser = User::create([
        'company_id' => $company1->id,
        'role_id' => $roleDirector->id,
        'name' => 'Rajeev Malhotra (Director)',
        'email' => 'director@apexrealty.com',
        'phone' => '9800000005',
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_super_admin' => false,
    ]);
    echo "Director user created: director@apexrealty.com / password\n";
} else {
    $directorUser->password = Hash::make('password');
    $directorUser->role_id = $roleDirector->id;
    $directorUser->save();
    echo "Director user updated: director@apexrealty.com / password\n";
}
