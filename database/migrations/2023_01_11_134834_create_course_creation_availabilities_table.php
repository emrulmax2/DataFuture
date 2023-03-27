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
        Schema::create('course_creation_availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_creation_id');
            $table->date('admission_date');
            $table->date('admission_end_date');
            $table->date('course_start_date');
            $table->date('course_end_date');
            $table->date('last_joinning_date');
            $table->enum('type', ['UK', 'BOTH', 'OVERSEAS']);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('course_creation_id')->references('id')->on('course_creations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_creation_availabilities');
    }
};
