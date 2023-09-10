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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('applicant_id')->unsigned()->nullable();
            $table->bigInteger('applicant_user_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('title_id')->unsigned()->nullable();
            $table->bigInteger('status_id')->unsigned()->nullable();
            $table->bigInteger('country_id')->unsigned()->nullable();
            $table->string('registration_no',145)->nullable();
            $table->string('application_no',145)->nullable();
            $table->string('first_name',145)->nullable();
            $table->string('last_name',145)->nullable();
            $table->string('photo',191)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorce'])->nullable();
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHERS'])->nullable();
            $table->date('submission_date')->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('set null')->onUpdate('set null');
            $table->foreign('applicant_user_id')->references('id')->on('applicant_users')->onDelete('set null')->onUpdate('set null');
            $table->foreign('title_id')->references('id')->on('titles')->onDelete('set null')->onUpdate('set null');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('set null')->onUpdate('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null')->onUpdate('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
