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
        Schema::create('slc_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id');
            $table->unsignedBigInteger('course_creation_instance_id');
            $table->unsignedBigInteger('slc_attendance_id');
            $table->unsignedBigInteger('slc_agreement_id');
            $table->date('installment_date');
            $table->double('amount', 10, 2);
            $table->enum('term', ['Autumn Term', 'Spring Term', 'Summer Term', 'Winter Term'])->nullable();
            $table->unsignedTinyInteger('session_term');
            $table->unsignedBigInteger('attendance_term')->nullable();

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_course_relation_id', 'slc_inst_screl_id_fnk')->references('id')->on('student_course_relations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_creation_instance_id', 'slc_inst_cci_id_fnk')->references('id')->on('course_creation_instances')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('slc_attendance_id')->references('id')->on('slc_attendances')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('slc_agreement_id')->references('id')->on('slc_agreements')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_installments');
    }
};
