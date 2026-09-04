<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('REOS Gmail App Password Test Email Success! Your REOS system is now connected to Gmail SMTP.', function ($m) {
        $m->to('hostelpgdekho@gmail.com')->subject('🎉 REOS Gmail App Password SMTP Live Test');
    });
    echo "SUCCESS: Test email delivered via Gmail SMTP to hostelpgdekho@gmail.com\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
