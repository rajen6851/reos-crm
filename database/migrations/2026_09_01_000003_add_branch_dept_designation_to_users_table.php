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
                if (!Schema::hasColumn('users', 'branch')) {
                    $table->string('branch')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('users', 'department')) {
                    $table->string('department')->nullable()->after('branch');
                }
                if (!Schema::hasColumn('users', 'designation')) {
                    $table->string('designation')->nullable()->after('department');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['branch', 'department', 'designation']);
            });
        }
    }
};
