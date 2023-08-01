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
        Schema::create('applicant_proposed_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('course_creation_id');
            $table->unsignedBigInteger('semester_id');
            $table->enum('student_loan', ['Independently/Private', 'Funding Body', 'Sponsor', 'Student Loan', 'Others'])->default('Independently/Private');
            $table->tinyInteger('student_finance_england')->default('0');
            $table->tinyInteger('fund_receipt')->default('0');
            $table->tinyInteger('applied_received_fund')->default('0');
            $table->string('other_funding', 191)->nullable();
            $table->tinyInteger('full_time')->default('0');


            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('applicant_id')->references('id')->on('applicants');
            $table->foreign('course_creation_id')->references('id')->on('course_creations');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicant_proposed_courses', function (Blueprint $table) {
            $table->dropForeign(['applicant_id']);
            $table->dropForeign(['course_creation_id']);
            $table->dropForeign(['semester_id']);
        });
        Schema::dropIfExists('applicant_proposed_courses');
    }
};
