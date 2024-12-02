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
        Schema::table('result_submissions', function (Blueprint $table) {
            $table->dropForeign(['course_creation_id']);
            $table->dropColumn('course_creation_id');
            $table->foreignId('student_course_relation_id')->after('student_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_submissions', function (Blueprint $table) {
            $table->dropForeign(['student_course_relation_id']);
            $table->dropColumn('student_course_relation_id');
            $table->foreignId('course_creation_id')->after('student_id')->constrained()->cascadeOnDelete();
        });
    }
};
