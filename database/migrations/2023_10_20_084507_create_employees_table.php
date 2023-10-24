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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('title_id')->unsigned();
            $table->string('first_name',145)->nullable(); 
            $table->string('last_name',145)->nullable(); 
            $table->string('telephone',145)->nullable(); 
            $table->string('mobile',145)->nullable(); 
            $table->string('email',145)->nullable(); 
            $table->bigInteger('sex_identifier_id')->unsigned()->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('ni_number',145)->nullable(); 
            $table->bigInteger('disability_id')->unsigned()->nullable();
            $table->bigInteger('nationality_id')->unsigned()->nullable();
            $table->bigInteger('ethnicity_id')->unsigned()->nullable();
            $table->string('car_reg_number',145)->nullable();
            $table->string('drive_license_number',145)->nullable();
            $table->bigInteger('address_id')->unsigned()->nullable();
            $table->enum('disability_status',['Yes','No'])->default('No');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null')->onUpdate('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
