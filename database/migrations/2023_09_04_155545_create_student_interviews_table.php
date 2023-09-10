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
        Schema::create('student_interviews', function (Blueprint $table) {
            $table->id();
            $table->index('student_id');
            $table->bigInteger('student_id')->unsigned();
            $table->index('applicant_task_id');
            $table->bigInteger('applicant_task_id')->unsigned();
            $table->index('applicant_document_id');
            $table->bigInteger('applicant_document_id')->unsigned();
            $table->date('interview_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('interview_result', ['Pass', 'Fail', 'Unattainded', 'N/A'])->nullable();
            $table->enum('interview_status', ['In progress', 'Completed'])->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('applicant_task_id')->references('id')->on('applicant_tasks')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('applicant_document_id')->references('id')->on('applicant_documents')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('student_interviews');
    }
};
