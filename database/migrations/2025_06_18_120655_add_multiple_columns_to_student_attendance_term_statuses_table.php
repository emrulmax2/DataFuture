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
        Schema::table('student_attendance_term_statuses', function (Blueprint $table) {
            $table->date('status_end_date')->nullable()->after('status_change_date');
            $table->unsignedBigInteger('reason_for_engagement_ending_id')->nullable()->after('status_end_date');
            $table->string('qual_award_type', 20)->nullable()->after('reason_for_engagement_ending_id');
            $table->unsignedBigInteger('qual_award_result_id')->nullable()->after('qual_award_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendance_term_statuses', function (Blueprint $table) {
            //
        });
    }
};
