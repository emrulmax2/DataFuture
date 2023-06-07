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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('degree_offered', 191);
            $table->string('pre_qualification', 191);
            $table->unsignedBigInteger('awarding_body_id');
            $table->unsignedBigInteger('source_tuition_fee_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('awarding_body_id')->references('id')->on('awarding_bodies');
            $table->foreign('source_tuition_fee_id')->references('id')->on('source_tuition_fees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
