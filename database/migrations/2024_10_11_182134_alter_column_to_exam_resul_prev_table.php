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
        Schema::table('exam_result_prev', function (Blueprint $table) {

            $table->unsignedBigInteger('student_id')->nullable()->change();
            $table->unsignedBigInteger('course_id')->nullable()->change();
            $table->unsignedBigInteger('semester_id')->nullable()->change();
            $table->unsignedBigInteger('course_module_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_result_prev', function (Blueprint $table) {
            //
        });
    }
};
