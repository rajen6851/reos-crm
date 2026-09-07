<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_saas_sub_admin')) {
                $table->boolean('is_saas_sub_admin')->default(false)->after('is_super_admin');
            }
            if (!Schema::hasColumn('users', 'saas_permissions')) {
                $table->json('saas_permissions')->nullable()->after('is_saas_sub_admin');
            }
        });

        Schema::create('saas_approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type'); // e.g. delete_company, destroy_plan, update_company_status, delete_subadmin
            $table->string('target_type')->nullable(); // e.g. App\Models\Company, App\Models\SubscriptionPlan
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_name')->nullable();
            $table->json('payload')->nullable(); // Action data to execute on approval
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_approval_requests');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'saas_permissions')) {
                $table->dropColumn('saas_permissions');
            }
            if (Schema::hasColumn('users', 'is_saas_sub_admin')) {
                $table->dropColumn('is_saas_sub_admin');
            }
        });
    }
};
