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
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'description')) {
                $table->text('description')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('support_tickets', 'ticket_number')) {
                $table->string('ticket_number')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('support_tickets', 'ticket_number')) {
                $table->dropColumn('ticket_number');
            }
        });
    }
};
