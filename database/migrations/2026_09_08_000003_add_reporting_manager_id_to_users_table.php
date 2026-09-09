<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'reporting_manager_id')) {
                    $table->foreignId('reporting_manager_id')
                        ->nullable()
                        ->after('company_id')
                        ->constrained('users')
                        ->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'reporting_manager_id')) {
                    $table->dropForeign(['reporting_manager_id']);
                    $table->dropColumn('reporting_manager_id');
                }
            });
        }
    }
};
