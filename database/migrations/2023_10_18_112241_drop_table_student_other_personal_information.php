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
        Schema::dropIfExists('student_other_personal_information');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('student_other_personal_information', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('sexual_orientation_id')->unsigned()->nullable();
            $table->bigInteger('hesa_gender_id')->unsigned()->nullable();
            $table->bigInteger('religion_id')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('sexual_orientation_id')->references('id')->on('sexual_orientations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('hesa_gender_id')->references('id')->on('hesa_genders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('religion_id')->references('id')->on('religions')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
