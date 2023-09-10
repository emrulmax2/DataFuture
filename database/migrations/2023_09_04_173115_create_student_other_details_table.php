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
        Schema::create('student_other_details', function (Blueprint $table) {
            $table->id();
            $table->index('student_id');
            $table->bigInteger('student_id')->unsigned();
            $table->index('ethnicity_id');
            $table->bigInteger('ethnicity_id')->unsigned();
            $table->index('sexual_orientation_id');
            $table->bigInteger('sexual_orientation_id')->unsigned();
            $table->index('religion_id');
            $table->bigInteger('religion_id')->unsigned();
            $table->tinyInteger('disability_status')->default('0');
            $table->tinyInteger('disabilty_allowance')->default('0');
            $table->tinyInteger('is_education_qualification')->default('0');
            $table->string('employment_status',145)->nullable();
            $table->enum('college_introduction',['Self', 'Referred', 'Agent'])->nullable();
            $table->enum('gender_identity',['Yes', 'No', 'Refused'])->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('ethnicity_id')->references('id')->on('ethnicities')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('sexual_orientation_id')->references('id')->on('sexual_orientations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('religion_id')->references('id')->on('religions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('student_other_details');
    }
};
