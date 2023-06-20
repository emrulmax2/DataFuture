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
        Schema::create('applicant_employments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('company_name', 145);
            $table->string('company_phone', 145);
            $table->string('position', 145);
            $table->string('start_date', 10);
            $table->string('end_date', 10)->nullable();
            $table->tinyInteger('continuing')->default('0');
            $table->string('address_line_1', 199);
            $table->string('address_line_2', 199)->nullable();
            $table->string('state', 145)->nullable();
            $table->string('post_code', 145);
            $table->string('city', 145);
            $table->string('country', 199);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('applicant_id')->references('id')->on('applicants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicant_employments');
    }
};
