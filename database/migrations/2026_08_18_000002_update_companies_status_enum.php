<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify MySQL enum column to allow pending_subscription
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE companies MODIFY COLUMN status ENUM('active', 'suspended', 'trial', 'pending_subscription', 'expired') NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE companies MODIFY COLUMN status ENUM('active', 'suspended', 'trial') NOT NULL DEFAULT 'trial'");
        }
    }
};
