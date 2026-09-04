<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Daily Staff Attendances Table
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->time('clock_in')->nullable();
                $table->time('clock_out')->nullable();
                $table->string('work_location')->default('office'); // office, field_visit, wfh
                $table->string('status')->default('present'); // present, late, half_day, absent, on_leave
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'user_id', 'date']);
            });
        }

        // 2. Staff Leave Requests Table
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('leave_type')->default('casual'); // casual, sick, earned, loss_of_pay
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('total_days', 3, 1)->default(1.0);
                $table->text('reason')->nullable();
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Monthly Salary Slips & Payroll Table
        if (!Schema::hasTable('salary_slips')) {
            Schema::create('salary_slips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('month'); // e.g. "2026-08"
                $table->integer('working_days')->default(26);
                $table->integer('present_days')->default(26);
                $table->integer('leave_days')->default(0);
                $table->decimal('basic_salary', 12, 2)->default(0.00);
                $table->decimal('allowances', 12, 2)->default(0.00);
                $table->decimal('commission_earned', 12, 2)->default(0.00);
                $table->decimal('deductions', 12, 2)->default(0.00);
                $table->decimal('net_salary', 12, 2)->default(0.00);
                $table->string('status')->default('generated'); // draft, generated, paid
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendances');
    }
};
