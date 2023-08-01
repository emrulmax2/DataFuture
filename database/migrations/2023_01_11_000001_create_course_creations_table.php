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
        Schema::create('course_creations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('course_id');
            $table->tinyInteger('duration');
            $table->enum('unit_length', ['Years','Months','Days','Hours','Not applicable']);
            $table->string('slc_code', 45)->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('semester_id')->references('id')
            ->on('semesters')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('course_id')->references('id')
            ->on('courses')
            ->onDelete('cascade')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_creations');
    }
};
