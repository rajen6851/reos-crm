<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Tracks last call/followup timestamp for no-response detection
            $table->timestamp('last_activity_at')->nullable()->after('notes');
            // How many times this lead has been auto/manually transferred
            $table->unsignedTinyInteger('transfer_count')->default(0)->after('last_activity_at');
            // When this lead became eligible for auto-transfer
            $table->timestamp('transfer_eligible_at')->nullable()->after('transfer_count');
        });

        Schema::table('lead_assignments', function (Blueprint $table) {
            // Optional manager note on why transfer was done
            $table->text('transfer_note')->nullable()->after('assignment_reason');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'transfer_count', 'transfer_eligible_at']);
        });

        Schema::table('lead_assignments', function (Blueprint $table) {
            $table->dropColumn(['transfer_note']);
        });
    }
};

