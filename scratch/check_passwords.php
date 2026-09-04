<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$emails = [
    'founder@reos.com',
    'rajendrarajput72339@gmail.com',
    'gruopkaizen@gmail.com',
    'cakesdekho@gmail.com',
    'rajendrarajput39756@gmail.com'
];

foreach ($emails as $email) {
    $u = User::withoutGlobalScopes()->where('email', $email)->first();
    if ($u) {
        $checkPassword = Hash::check('password', $u->password);
        $checkPassword123 = Hash::check('password123', $u->password);
        echo "User: {$email} | Matches 'password': " . ($checkPassword ? 'YES' : 'NO') . " | Matches 'password123': " . ($checkPassword123 ? 'YES' : 'NO') . "\n";
    } else {
        echo "User: {$email} NOT FOUND!\n";
    }
}
