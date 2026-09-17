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
        Schema::table('distribution_rule_members', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_member_id')->nullable()->after('user_id')->comment('Points to the manager member within the same rule if this is an executive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distribution_rule_members', function (Blueprint $table) {
            $table->dropColumn('parent_member_id');
        });
    }
};
