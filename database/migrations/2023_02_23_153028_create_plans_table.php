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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('module_creation_id');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->unsignedBigInteger('rooms_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('name', 191)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('label', 191)->nullable();
            $table->tinyInteger('sat')->default(0)->nullable();
            $table->tinyInteger('sun')->default(0)->nullable();
            $table->tinyInteger('mon')->default(0)->nullable();
            $table->tinyInteger('tue')->default(0)->nullable();
            $table->tinyInteger('wed')->default(0)->nullable();
            $table->tinyInteger('thu')->default(0)->nullable();
            $table->tinyInteger('fri')->default(0)->nullable();
            $table->string('module_enrollment_key', 191)->nullable();
            $table->date('submission_date')->nullable();
            $table->unsignedBigInteger('tutor_id')->nullable();
            $table->unsignedBigInteger('personal_tutor_id')->nullable();
            $table->text('virtual_room')->nullable();
            $table->text('note', 255)->nullable();
            
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('course_id')->references('id')->on('courses');
            $table->foreign('module_creation_id')->references('id')->on('module_creations');
            $table->foreign('venue_id')->references('id')->on('venues');
            $table->foreign('rooms_id')->references('id')->on('rooms');
            $table->foreign('group_id')->references('id')->on('groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
