<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('support_tickets') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE support_tickets MODIFY category VARCHAR(100) NOT NULL DEFAULT 'General'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('support_tickets') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE support_tickets MODIFY category VARCHAR(100) NOT NULL DEFAULT 'General'");
        }
    }
};
