<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::withoutGlobalScopes()->where('email', 'director@apexrealty.com')->first();
if ($user) {
    Auth::login($user);
    echo "Logged in as: " . Auth::user()->name . "\n";
    echo "Role Name: " . Auth::user()->role->name . " (Slug: " . Auth::user()->role->slug . ")\n";
    echo "Is Company Admin/Director?: " . (Auth::user()->isCompanyAdmin() ? "YES" : "NO") . "\n";
    echo "Is Director?: " . (Auth::user()->isDirector() ? "YES" : "NO") . "\n";
    echo "Has 'manage-users' permission?: " . (Auth::user()->hasPermission('manage-users') ? "YES" : "NO") . "\n";
    echo "Has 'manage-projects' permission?: " . (Auth::user()->hasPermission('manage-projects') ? "YES" : "NO") . "\n";
    echo "Has 'approve-bookings' permission?: " . (Auth::user()->hasPermission('approve-bookings') ? "YES" : "NO") . "\n";
    echo "Has 'approve-agreement-skips' permission?: " . (Auth::user()->hasPermission('approve-agreement-skips') ? "YES" : "NO") . "\n";
    echo "Has 'process-payouts' permission?: " . (Auth::user()->hasPermission('process-payouts') ? "YES" : "NO") . "\n";
} else {
    echo "Director user not found!\n";
}
