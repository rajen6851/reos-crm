<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lead Assignments Table (preserves full history)
        if (!Schema::hasTable('lead_assignments')) {
            Schema::create('lead_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
                $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_to_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('assignment_type')->default('initial'); // initial, reassignment
                $table->foreignId('previous_assignee_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('assignment_reason')->nullable();
                $table->timestamp('assigned_at');
                $table->timestamps();
            });
        }

        // 2. Enhance Broker Leads Table
        Schema::table('broker_leads', function (Blueprint $table) {
            if (!Schema::hasColumn('broker_leads', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->after('project_id')->constrained('units')->nullOnDelete();
            }
            if (!Schema::hasColumn('broker_leads', 'broker_visible_message')) {
                $table->text('broker_visible_message')->nullable()->after('broker_visible_status');
            }
            if (!Schema::hasColumn('broker_leads', 'property_type')) {
                $table->string('property_type')->nullable()->after('broker_visible_message');
            }
            if (!Schema::hasColumn('broker_leads', 'unit_type')) {
                $table->string('unit_type')->nullable()->after('property_type');
            }
            if (!Schema::hasColumn('broker_leads', 'budget_min')) {
                $table->decimal('budget_min', 12, 2)->nullable()->after('unit_type');
            }
            if (!Schema::hasColumn('broker_leads', 'budget_max')) {
                $table->decimal('budget_max', 12, 2)->nullable()->after('budget_min');
            }
            if (!Schema::hasColumn('broker_leads', 'preferred_location')) {
                $table->string('preferred_location')->nullable()->after('budget_max');
            }
            if (!Schema::hasColumn('broker_leads', 'requirement_notes')) {
                $table->text('requirement_notes')->nullable()->after('preferred_location');
            }
            if (!Schema::hasColumn('broker_leads', 'city')) {
                $table->string('city')->nullable()->after('requirement_notes');
            }
            if (!Schema::hasColumn('broker_leads', 'customer_type')) {
                $table->string('customer_type')->nullable()->after('city');
            }
        });

        // 3. Broker Payout Commissions Pivot Table
        if (!Schema::hasTable('broker_payout_commissions')) {
            Schema::create('broker_payout_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('broker_payout_id')->constrained('broker_payouts')->cascadeOnDelete();
                $table->foreignId('broker_commission_id')->constrained('broker_commissions')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 4. Performance Indexes
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['company_id', 'phone']);
            $table->index(['company_id', 'email']);
            $table->index(['company_id', 'broker_id']);
            $table->index(['company_id', 'assigned_to_user_id']);
            $table->index(['company_id', 'status']);
        });

        Schema::table('broker_leads', function (Blueprint $table) {
            $table->index(['company_id', 'broker_id']);
            $table->index(['company_id', 'lead_id']);
            $table->index(['company_id', 'project_id']);
            $table->index(['company_id', 'broker_visible_status']);
        });

        Schema::table('broker_commissions', function (Blueprint $table) {
            $table->index(['company_id', 'broker_id']);
            $table->index(['company_id', 'booking_id']);
            $table->index(['company_id', 'status']);
        });

        Schema::table('broker_payouts', function (Blueprint $table) {
            $table->index(['company_id', 'broker_id']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_payout_commissions');
        Schema::dropIfExists('lead_assignments');
    }
};
