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
        Schema::create('employment_references', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_employment_id');
            $table->string('name', 145);
            $table->string('position', 145);
            $table->string('phone', 145);
            $table->string('email', 145)->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('applicant_employment_id')->references('id')->on('applicant_employments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employment_references');
    }
};
