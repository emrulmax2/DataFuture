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
        Schema::table('slc_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('course_relation_id')->nullable()->change();
            $table->unsignedBigInteger('student_course_relation_id')->nullable()->change();
            $table->unsignedBigInteger('course_creation_instance_id')->nullable()->change();
            $table->integer('attendance_year')->nullable()->change();
            $table->unsignedBigInteger('term_declaration_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slc_attendances', function (Blueprint $table) {
            //
        });
    }
};
