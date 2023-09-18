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
            $table->dropForeign(['task_list_id']);
            $table->bigInteger('task_list_id')->unsigned()->nullable()->change();
            $table->foreign('task_list_id')->references('id')->on('task_lists')->onDelete('set null')->onUpdate('set null');

            $table->dropForeign(['task_status_id']);
            $table->bigInteger('task_status_id')->unsigned()->nullable()->change();
            $table->foreign('task_status_id')->references('id')->on('task_statuses')->onDelete('set null')->onUpdate('set null');
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
            $table->dropForeign(['task_list_id']);
            $table->bigInteger('task_list_id')->unsigned()->change();
            $table->foreign('task_list_id')->references('id')->on('task_lists')->onDelete('cascade')->onUpdate('cascade');

            $table->dropForeign(['task_status_id']);
            $table->bigInteger('task_status_id')->unsigned()->change();
            $table->foreign('task_status_id')->references('id')->on('task_statuses')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
