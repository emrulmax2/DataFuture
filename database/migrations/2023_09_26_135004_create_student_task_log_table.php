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
        Schema::create('student_task_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_tasks_id');
            $table->string('actions', 191)->nullable();
            $table->string('field_name', 145)->nullable();
            $table->string('prev_field_value', 145)->nullable();
            $table->string('current_field_value', 145)->nullable();
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_task_log');
    }
};
