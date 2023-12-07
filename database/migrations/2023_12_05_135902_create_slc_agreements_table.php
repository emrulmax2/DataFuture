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
        Schema::create('slc_agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_course_relation_id');
            $table->unsignedBigInteger('course_creation_instance_id');
            $table->unsignedBigInteger('slc_registration_id')->nullable();
            $table->string('slc_coursecode')->nullable();
            $table->tinyInteger('is_self_funded')->default(0);
            $table->date('date')->nullable();
            $table->integer('year');
            $table->integer('no_of_installment')->nullable();
            $table->double('fees', 10, 2);
            $table->double('discount', 10, 2)->default(0);
            $table->double('total', 10, 2)->default(0);
            $table->text('note')->nullable();

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_course_relation_id', 'slc_agr_std_crel_id_fnk')->references('id')->on('student_course_relations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_creation_instance_id', 'slc_agr_cci_id_fnk')->references('id')->on('course_creation_instances')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('slc_registration_id')->references('id')->on('slc_registrations')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slc_agreements');
    }
};
