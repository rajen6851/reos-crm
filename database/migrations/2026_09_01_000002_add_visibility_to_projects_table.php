<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (!Schema::hasColumn('projects', 'visibility')) {
                    $table->enum('visibility', ['public', 'private'])->default('public')->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'visibility')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('visibility');
            });
        }
    }
};
