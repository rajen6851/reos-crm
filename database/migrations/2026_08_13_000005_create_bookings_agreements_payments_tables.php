<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_cost', 12, 2);
            $table->decimal('plc_cost', 12, 2)->default(0); // Preferential location charges
            $table->decimal('parking_cost', 12, 2)->default(0);
            $table->decimal('statutory_charges', 12, 2)->default(0); // GST, Stamp duty, Reg
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2);
            $table->enum('payment_plan_type', ['construction_linked', 'time_linked', 'lump_sum'])->default('construction_linked');
            $table->timestamp('valid_until')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('booking_code')->unique();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('broker_id')->nullable()->constrained('brokers')->nullOnDelete();
            $table->foreignId('cost_sheet_id')->constrained('cost_sheets')->cascadeOnDelete();
            $table->decimal('booking_amount', 12, 2);
            $table->decimal('total_unit_cost', 12, 2);
            $table->timestamp('booking_date');
            $table->enum('status', ['pending_approval', 'confirmed', 'agreement_pending', 'completed', 'cancelled'])->default('pending_approval');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('level')->default('manager'); // manager, director
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('agreement_number')->nullable();
            $table->string('draft_file_path')->nullable();
            $table->string('signed_file_path')->nullable();
            $table->enum('status', ['pending_draft', 'pending_signature', 'completed', 'skip_requested', 'skipped'])->default('pending_draft');
            $table->foreignId('skip_requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('skip_approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('skip_reason')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('milestone_name');
            $table->timestamp('due_date');
            $table->decimal('due_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'partially_paid', 'paid', 'overdue'])->default('pending');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_schedule_id')->nullable()->constrained('payment_schedules')->nullOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->timestamp('payment_date');
            $table->enum('payment_method', ['razorpay', 'cheque', 'net_banking', 'upi', 'card', 'cash'])->default('razorpay');
            $table->string('transaction_reference')->nullable();
            $table->string('bank_name')->nullable();
            $table->enum('status', ['pending_clearance', 'cleared', 'bounced', 'rejected'])->default('cleared');
            $table->foreignId('recorded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_code')->unique();
            $table->string('pdf_path')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
        });

        Schema::create('broker_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('rate_value', 10, 2);
            $table->decimal('total_commission_amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'ready_for_payout', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('broker_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_id')->constrained()->cascadeOnDelete();
            $table->string('payout_code')->unique();
            $table->decimal('amount_paid', 12, 2);
            $table->timestamp('payout_date');
            $table->string('payment_method')->default('bank_transfer');
            $table->string('transaction_reference')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['processing', 'completed', 'failed'])->default('completed');
            $table->foreignId('processed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number')->unique()->nullable();
            $table->string('ticket_code')->nullable();
            $table->string('subject');
            $table->string('category')->default('General');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module');
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('broker_payouts');
        Schema::dropIfExists('broker_commissions');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_schedules');
        Schema::dropIfExists('agreements');
        Schema::dropIfExists('booking_approvals');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('cost_sheets');
    }
};
