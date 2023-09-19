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
            $table->dropForeign(['applicant_task_id']);
            $table->dropColumn('applicant_task_id');
            $table->bigInteger('student_task_id')->unsigned()->after('student_id');
            $table->foreign('student_task_id')->references('id')->on('student_tasks')->onDelete('cascade')->onUpdate('cascade');
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
            $table->dropForeign(['student_task_id']);
            $table->dropColumn('student_task_id');
            $table->bigInteger('applicant_task_id')->unsigned()->after('student_id');
            $table->foreign('applicant_task_id')->references('id')->on('applicant_documents')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
