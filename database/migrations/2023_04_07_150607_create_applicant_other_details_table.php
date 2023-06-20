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
        Schema::create('applicant_other_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('ethnicity_id')->nullable();
            $table->tinyInteger('disability_status')->default('0');
            $table->tinyInteger('disabilty_allowance')->default('0');
            $table->tinyInteger('is_edication_qualification')->default('0');
            $table->string('employment_status', 145)->nullable();
            $table->enum('college_introduction', ['Self', 'Referred', 'Agent'])->nullable();
            $table->enum('gender_identity', ['Yes', 'No', 'Refused'])->nullable();
            $table->unsignedBigInteger('sexual_orientation_id')->nullable();
            $table->unsignedBigInteger('religion_id')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('applicant_id')->references('id')->on('applicants');
            $table->foreign('ethnicity_id')->references('id')->on('ethnicities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicant_other_details');
    }
};
