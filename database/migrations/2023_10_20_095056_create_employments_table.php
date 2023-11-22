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
        Schema::create('employments', function (Blueprint $table) {

            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->string('punch_number',145)->nullable();
            $table->string('works_number',145)->nullable();
            $table->date('started_on')->nullable();
            $table->bigInteger('employee_work_type_id')->unsigned()->nullable();
            $table->bigInteger('employee_job_title_id')->unsigned()->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->string('office_telephone',145)->nullable();
            $table->string('mobile',145)->nullable();
            $table->string('email',145)->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_job_title_id')->references('id')->on('employee_job_titles')->onDelete('set null')->onUpdate('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null')->onUpdate('set null');
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employments');
    }
};
