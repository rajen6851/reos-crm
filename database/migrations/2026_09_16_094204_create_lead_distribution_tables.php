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
        Schema::create('distribution_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('lead_source_id')->nullable()->index();
            $table->string('campaign_id')->nullable();
            $table->string('property_type')->nullable();
            $table->integer('priority')->default(5);
            $table->string('distribution_method')->default('round_robin'); // round_robin, percentage, fixed_quantity
            $table->string('fallback_behavior')->default('redistribute'); // redistribute, skip
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        Schema::create('distribution_rule_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribution_rule_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->decimal('allocation_value', 8, 2)->nullable(); // % or count
            $table->timestamps();
        });

        Schema::create('distribution_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribution_rule_id')->unique();
            $table->unsignedBigInteger('last_assigned_user_id')->nullable();
            $table->unsignedBigInteger('total_distributed')->default(0);
            $table->integer('cycle_number')->default(1);
            $table->timestamps();
        });

        Schema::create('distribution_member_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribution_rule_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('actual_assigned_count')->default(0);
            $table->timestamps();
            
            $table->unique(['distribution_rule_id', 'user_id'], 'dist_rule_member_state_unique');
        });

        Schema::create('distribution_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('distribution_rule_id')->nullable()->index();
            $table->unsignedBigInteger('assigned_user_id')->index();
            $table->string('assignment_type')->default('AUTO'); // AUTO, MANUAL, TRANSFER
            $table->decimal('allocation_percentage', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_logs');
        Schema::dropIfExists('distribution_member_states');
        Schema::dropIfExists('distribution_states');
        Schema::dropIfExists('distribution_rule_members');
        Schema::dropIfExists('distribution_rules');
    }
};
