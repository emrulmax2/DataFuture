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

            $table->renameColumn('student_data_id', 'student_id');
            $table->renameColumn('coursemodule_id', 'course_module_id');
            $table->renameColumn('createddate', 'created_at');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_result_prev', function (Blueprint $table) {

            $table->renameColumn('student_id', 'student_data_id');
            $table->renameColumn('course_module_id', 'coursemodule_id');
            $table->renameColumn('created_at', 'createddate');

        });
    }
};
