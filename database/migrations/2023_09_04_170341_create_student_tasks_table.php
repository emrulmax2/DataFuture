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
        Schema::create('student_tasks', function (Blueprint $table) {
            $table->id();
            $table->index('student_id');
            $table->bigInteger('student_id')->unsigned();
            $table->index('task_list_id');
            $table->bigInteger('task_list_id')->unsigned();
            $table->index('task_status_id');
            $table->bigInteger('task_status_id')->unsigned();
            $table->text('external_link_ref')->nullable();
            $table->enum('status', ['Pending', 'In Progress', 'Completed'])->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('task_list_id')->references('id')->on('task_lists')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('task_status_id')->references('id')->on('task_statuses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        
        Schema::dropIfExists('student_tasks');
    }
};
