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
            $table->string('broker_name')->nullable()->after('pickup_location');
            $table->string('broker_phone')->nullable()->after('broker_name');
            $table->string('broker_company')->nullable()->after('broker_phone');
            $table->text('visit_description')->nullable()->after('broker_company');
            $table->string('remark_1')->nullable()->after('visit_description');
            $table->string('remark_2')->nullable()->after('remark_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn(['broker_name', 'broker_phone', 'broker_company', 'visit_description', 'remark_1', 'remark_2']);
        });
    }
};
