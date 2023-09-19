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
        Schema::table('student_tasks', function (Blueprint $table) {
           
            $table->bigInteger('applicant_task_id')->unsigned()->nullable()->after("student_id");
            $table->foreign('applicant_task_id')->references('id')->on('applicant_tasks')->onDelete('set null')->onUpdate('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_tasks', function (Blueprint $table) {
            $table->dropForeign(['applicant_task_id']);
            $table->dropColumn(['applicant_task_id']);
        });
    }
};
