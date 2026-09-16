<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('feedback_notes');
            $table->string('google_sync_status')->default('pending')->after('google_event_id');
            $table->timestamp('google_synced_at')->nullable()->after('google_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn(['google_event_id', 'google_sync_status', 'google_synced_at']);
        });
    }
};
