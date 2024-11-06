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
        Schema::table('slc_registrations', function (Blueprint $table) {
            $table->index('student_id');
            $table->bigInteger('student_course_relation_id')->unsigned()->nullable()->change();
            $table->index('student_course_relation_id');
            $table->index('course_creation_instance_id');
            $table->index('academic_year_id');

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_course_relation_id', 'slc_reg_screl_id_fnk')->references('id')->on('student_course_relations')->onDelete('cascade')->onUpdate('cascade');
            //$table->foreign('course_creation_instance_id', 'slc_reg_cci_id_fnk')->references('id')->on('course_creation_instances')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('slc_registration_status_id', 'slc_reg_slc_sts_id_fnk')->references('id')->on('slc_registration_statuses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('academic_year_id', 'slc_reg_ay_id_fnk')->references('id')->on('academic_years')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slc_registrations', function (Blueprint $table) {
            //
        });
    }
};
