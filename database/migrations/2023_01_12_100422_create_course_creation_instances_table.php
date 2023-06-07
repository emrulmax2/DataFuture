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
        Schema::create('course_creation_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_creation_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_teaching_week');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('course_creation_id')->references('id')->on('course_creations');
            $table->foreign('academic_year_id')->references('id')->on('academic_years');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_creation_instances');
    }
};
