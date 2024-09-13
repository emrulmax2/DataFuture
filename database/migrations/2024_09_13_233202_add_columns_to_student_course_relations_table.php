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
        Schema::table('student_course_relations', function (Blueprint $table) {
            $table->date('course_start_date')->after('student_id')->nullable();
            $table->date('course_end_date')->after('course_start_date')->nullable();
            $table->enum('type', ['UK', 'BOTH', 'OVERSEAS'])->after('course_end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_course_relations', function (Blueprint $table) {

        });
    }
};
