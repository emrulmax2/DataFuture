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
        Schema::table('result_comparisons', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->after('assessment_plan_id');
            $table->foreign('student_id','fk_comparison_student')->references('id')->on('students')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_comparisons', function (Blueprint $table) {
            $table->dropForeign('fk_comparison_student');
            $table->dropColumn('student_id');
        });
    }
};
