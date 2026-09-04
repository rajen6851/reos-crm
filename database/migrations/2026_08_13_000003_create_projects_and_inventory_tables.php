<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('location_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('rera_number')->nullable();
            $table->json('amenities')->nullable();
            $table->enum('project_type', ['residential', 'commercial', 'mixed', 'land'])->default('residential');
            $table->enum('status', ['planning', 'active', 'completed'])->default('active');
            $table->string('banner_image')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('project_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->integer('total_floors')->default(1);
            $table->integer('total_units')->default(0);
            $table->timestamps();
        });

        Schema::create('project_floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained('project_buildings')->cascadeOnDelete();
            $table->integer('floor_number');
            $table->string('name');
            $table->integer('total_units')->default(0);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained('project_buildings')->cascadeOnDelete();
            $table->foreignId('floor_id')->constrained('project_floors')->cascadeOnDelete();
            $table->string('unit_number');
            $table->string('unit_type')->default('2BHK'); // 1BHK, 2BHK, 3BHK, Villa, Plot
            $table->decimal('carpet_area', 10, 2)->default(0);
            $table->decimal('builtup_area', 10, 2)->default(0);
            $table->decimal('super_builtup_area', 10, 2)->default(0);
            $table->string('facing')->nullable(); // East, West, North, South
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('final_price', 12, 2)->default(0);
            $table->enum('status', ['available', 'hold', 'booking_pending', 'booked', 'agreement_pending', 'sold', 'cancelled'])->default('available');
            $table->timestamp('holding_expires_at')->nullable();
            $table->foreignId('hold_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['building_id', 'unit_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
        Schema::dropIfExists('project_floors');
        Schema::dropIfExists('project_buildings');
        Schema::dropIfExists('projects');
    }
};
