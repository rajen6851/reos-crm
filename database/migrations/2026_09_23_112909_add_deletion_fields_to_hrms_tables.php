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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('deletion_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deletion_reason')->nullable();
        });

        Schema::table('salary_slips', function (Blueprint $table) {
            $table->foreignId('deletion_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deletion_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['deletion_requested_by']);
            $table->dropColumn(['deletion_requested_by', 'deletion_reason']);
        });

        Schema::table('salary_slips', function (Blueprint $table) {
            $table->dropForeign(['deletion_requested_by']);
            $table->dropColumn(['deletion_requested_by', 'deletion_reason']);
        });
    }
};
