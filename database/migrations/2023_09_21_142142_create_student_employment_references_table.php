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
        Schema::create('student_employment_references', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_employment_id');
            $table->string('name', 145);
            $table->string('position', 145);
            $table->string('phone', 145);
            $table->string('email', 145)->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_employment_id')->references('id')->on('student_employments')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_employment_references');
    }
};
