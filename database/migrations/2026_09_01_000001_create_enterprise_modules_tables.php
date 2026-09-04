<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lead Escalations Table
        if (!Schema::hasTable('lead_escalations')) {
            Schema::create('lead_escalations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
                $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('escalated_to_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reason')->default('Untouched for over 48 hours');
                $table->string('status')->default('pending'); // pending, resolved, reassigned
                $table->timestamps();
            });
        }

        // 2. Enhance Payment Schedules Table
        if (Schema::hasTable('payment_schedules')) {
            Schema::table('payment_schedules', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_schedules', 'percentage')) {
                    $table->decimal('percentage', 5, 2)->default(0.00)->after('milestone_name');
                }
                if (!Schema::hasColumn('payment_schedules', 'demand_letter_sent_at')) {
                    $table->timestamp('demand_letter_sent_at')->nullable()->after('status');
                }
            });
        }

        // 3. Customer Co-Applicants / Joint Buyers Table
        if (!Schema::hasTable('co_applicants')) {
            Schema::create('co_applicants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->string('full_name');
                $table->string('relationship')->default('Co-owner'); // Spouse, Parent, Partner, Sibling, etc.
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('pan_number')->nullable();
                $table->string('aadhar_number')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        // 4. KYC & Expiry Tracking Documents Table
        if (!Schema::hasTable('kyc_documents')) {
            Schema::create('kyc_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->nullableMorphs('documentable'); // Customer, Broker, User, Project
                $table->string('document_type'); // Aadhar Card, PAN Card, RERA License, Partnership Agreement, GST Certificate
                $table->string('document_number')->nullable();
                $table->string('file_path');
                $table->date('expiry_date')->nullable();
                $table->string('status')->default('verified'); // pending, verified, expired, rejected
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Unit Price Revision History Table
        if (!Schema::hasTable('unit_price_histories')) {
            Schema::create('unit_price_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('old_base_price', 12, 2)->default(0.00);
                $table->decimal('new_base_price', 12, 2)->default(0.00);
                $table->decimal('old_total_price', 12, 2)->default(0.00);
                $table->decimal('new_total_price', 12, 2)->default(0.00);
                $table->string('change_reason')->nullable();
                $table->timestamps();
            });
        }

        // 6. Security Login Audit Logs Table
        if (!Schema::hasTable('login_audit_logs')) {
            Schema::create('login_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('email');
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->string('login_status')->default('success'); // success, failed
                $table->string('failure_reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_audit_logs');
        Schema::dropIfExists('unit_price_histories');
        Schema::dropIfExists('kyc_documents');
        Schema::dropIfExists('co_applicants');
        Schema::dropIfExists('lead_escalations');
    }
};
