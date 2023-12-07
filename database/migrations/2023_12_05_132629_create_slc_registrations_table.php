<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slc_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id');
            $table->unsignedBigInteger('course_creation_instance_id');
            $table->string('ssn', 191)->nullable();
            $table->date('confirmation_date');
            $table->unsignedBigInteger('academic_year_id');
            $table->integer('registration_year');
            $table->unsignedBigInteger('slc_registration_status_id');
            $table->text('note');

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_course_relation_id', 'slc_reg_screl_id_fnk')->references('id')->on('student_course_relations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_creation_instance_id', 'slc_reg_cci_id_fnk')->references('id')->on('course_creation_instances')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('slc_registration_status_id', 'slc_reg_slc_sts_id_fnk')->references('id')->on('slc_registration_statuses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('academic_year_id', 'slc_reg_ay_id_fnk')->references('id')->on('academic_years')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_registrations');
    }
};
