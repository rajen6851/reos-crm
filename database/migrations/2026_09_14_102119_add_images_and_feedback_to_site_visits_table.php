<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            // JSON array of uploaded image paths (up to 10 photos per visit)
            $table->json('visit_images')->nullable()->after('feedback_notes');
            // Customer interest rating given during visit (1–5 stars)
            $table->unsignedTinyInteger('customer_rating')->nullable()->after('visit_images');
            // Who submitted the feedback (user_id)
            $table->foreignId('visit_feedback_by')->nullable()->constrained('users')->nullOnDelete()->after('customer_rating');
            // When feedback was submitted
            $table->timestamp('feedback_submitted_at')->nullable()->after('visit_feedback_by');
        });
    }

    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropForeign(['visit_feedback_by']);
            $table->dropColumn(['visit_images', 'customer_rating', 'visit_feedback_by', 'feedback_submitted_at']);
        });
    }
};
