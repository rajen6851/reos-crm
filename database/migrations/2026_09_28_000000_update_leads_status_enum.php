<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'assigned', 'contacted', 'follow_up', 'site_visit', 'site_visit_completed', 'interested', 'negotiation', 'booking_initiated', 'booked', 'converted', 'lost') DEFAULT 'new'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'contacted', 'follow_up', 'site_visit', 'interested', 'negotiation', 'converted', 'lost') DEFAULT 'new'");
    }
};
