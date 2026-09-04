<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\Company;

$roles = Role::withoutGlobalScopes()->get();
foreach ($roles as $r) {
    echo "ID: {$r->id} | CompanyID: {$r->company_id} | Name: {$r->name} | Slug: {$r->slug}\n";
}
