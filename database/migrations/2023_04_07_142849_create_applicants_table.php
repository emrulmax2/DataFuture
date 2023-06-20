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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_user_id');
            $table->string('application_no', 45)->nullable();
            $table->unsignedBigInteger('title_id');
            $table->string('first_name', 145);
            $table->string('last_name', 145);
            $table->date('date_of_birth');
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHERS']);
            $table->date('submission_date')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('nationality_id');
            $table->unsignedBigInteger('country_id');
            $table->string('referral_code', 20)->nullable();
            $table->tinyInteger('is_referral_varified')->default('0');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('applicant_user_id')->references('id')->on('applicant_users');
            $table->foreign('title_id')->references('id')->on('titles');
            $table->foreign('status_id')->references('id')->on('statuses');
            $table->foreign('nationality_id')->references('id')->on('countries');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicants');
    }
};
