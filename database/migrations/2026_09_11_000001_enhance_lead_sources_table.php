<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_sources', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_sources', 'type')) {
                $table->string('type')->default('website')->after('slug');
            }
            if (!Schema::hasColumn('lead_sources', 'webhook_token')) {
                $table->string('webhook_token')->nullable()->unique()->after('type');
            }
            if (!Schema::hasColumn('lead_sources', 'status')) {
                $table->string('status')->default('connected')->after('webhook_token');
            }
            if (!Schema::hasColumn('lead_sources', 'credentials')) {
                $table->json('credentials')->nullable()->after('status');
            }
            if (!Schema::hasColumn('lead_sources', 'settings')) {
                $table->json('settings')->nullable()->after('credentials');
            }
            if (!Schema::hasColumn('lead_sources', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('settings');
            }
            if (!Schema::hasColumn('lead_sources', 'error_log')) {
                $table->text('error_log')->nullable()->after('last_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_sources', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'webhook_token',
                'status',
                'credentials',
                'settings',
                'last_synced_at',
                'error_log',
            ]);
        });
    }
};
