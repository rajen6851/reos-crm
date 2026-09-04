<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$emails = [
    'founder@reos.com',
    'admin@apexrealty.com',
    'manager@apexrealty.com',
    'sales@apexrealty.com',
    'broker@apexrealty.com'
];

foreach ($emails as $email) {
    $u = User::withoutGlobalScopes()->where('email', $email)->first();
    if ($u) {
        $u->password = Hash::make('password');
        $u->save();
        echo "Reset password for {$email} to 'password'\n";
    }
}
