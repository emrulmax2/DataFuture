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
        Schema::table('student_interviews', function (Blueprint $table) {
            $table->dropForeign(['applicant_document_id']);
            $table->dropColumn('applicant_document_id');
            $table->bigInteger('student_document_id')->unsigned()->after('student_task_id');
            $table->foreign('student_document_id')->references('id')->on('student_documents')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_interviews', function (Blueprint $table) {
            $table->dropForeign(['student_document_id']);
            $table->dropColumn('student_document_id');
            $table->bigInteger('applicant_document_id')->unsigned()->after('student_task_id');
            $table->foreign('applicant_document_id')->references('id')->on('applicant_documents')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
