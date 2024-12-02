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
        Schema::table('assessment_plans', function (Blueprint $table) {
            $table->enum('upload_user_type', ['personal_tutor', 'staff'])->default('personal_tutor')->after('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_plans', function (Blueprint $table) {
            $table->dropColumn(['upload_user_type']);
        });
    }
};
