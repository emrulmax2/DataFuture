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
        Schema::create('results', function (Blueprint $table) {
            
            $table->id();
            $table->bigInteger('plan_id')->unsigned();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('assessment_plan_id')->unsigned();
            $table->bigInteger('grade_id')->unsigned();
            $table->timestamp('published_at')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
            
            
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('assessment_plan_id')->references('id')->on('assessment_plans')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('results');
    }
};
