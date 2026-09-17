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
            $table->decimal('latitude', 10, 7)->nullable()->after('pickup_location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->integer('distance_from_project')->nullable()->after('longitude')->comment('in meters');
            $table->boolean('is_geo_verified')->default(false)->after('distance_from_project');
            $table->string('visit_photo_path')->nullable()->after('is_geo_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'distance_from_project', 'is_geo_verified', 'visit_photo_path']);
        });
    }
};
