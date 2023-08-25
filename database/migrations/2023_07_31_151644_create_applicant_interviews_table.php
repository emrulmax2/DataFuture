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
        Schema::create('applicant_interviews', function (Blueprint $table) {
            $table->id();
            $table->index('user_id');
            $table->bigInteger('user_id')->unsigned();
            $table->index('applicant_id');
            $table->bigInteger('applicant_id')->unsigned();
            $table->index('applicant_task_id');
            $table->bigInteger('applicant_task_id')->nullable()->unsigned();
            $table->index('applicant_document_id');
            $table->bigInteger('applicant_document_id')->nullable()->unsigned();
            $table->date('interview_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('interview_result',['Pass', 'Fail', 'Unattainded', 'N/A'])->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('user_id')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->foreign('applicant_id')->nullable()->references('id')->on('applicants')->onDelete('cascade');
            $table->foreign('applicant_task_id')->nullable()->references('id')->on('applicant_tasks')->onDelete('set null');
            $table->foreign('applicant_document_id')->nullable()->references('id')->on('applicant_documents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('applicant_interviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['applicant_id']);
            $table->dropForeign(['applicant_task_id']);
            $table->dropForeign(['applicant_document_id']);
        });
        Schema::dropIfExists('applicant_interviews');
    }
};
