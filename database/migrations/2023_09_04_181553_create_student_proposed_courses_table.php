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
        Schema::create('student_proposed_courses', function (Blueprint $table) {
            $table->id();
            $table->index('student_id');
            $table->bigInteger('student_id')->unsigned();
            $table->index('academic_year_id');
            $table->bigInteger('academic_year_id')->unsigned();
            $table->index('course_creations_id');
            $table->bigInteger('course_creations_id')->unsigned();
            $table->enum('student_loan',['Independently/Private', 'Funding Body', 'Sponsor', 'Student Loan', 'Others'])->default('Independently/Private');
            $table->tinyInteger('student_finance_england')->nullable();
            $table->tinyInteger('fund_receipt')->nullable();
            $table->tinyInteger('applied_received_fund')->nullable();
            $table->string('other_funding',191)->nullable();
            $table->tinyInteger('full_time')->default('0');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('course_creations_id')->references('id')->on('course_creations')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('student_proposed_courses');
    }
};
