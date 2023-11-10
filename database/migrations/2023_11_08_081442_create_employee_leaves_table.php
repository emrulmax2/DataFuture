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
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('hr_holiday_year_id');
            $table->unsignedBigInteger('employee_working_pattern_id');
            $table->tinyInteger('leave_type');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->integer('days')->default(0);
            $table->tinyInteger('is_fraction')->default(0);
            $table->text('note')->nullable();

            $table->enum('status', ['Pending', 'Approved', 'Canceled'])->default('Pending');

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('approver_note')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->unsignedBigInteger('canceled_by')->nullable();
            $table->text('canceled_note')->nullable();
            $table->dateTime('canceled_at')->nullable();

            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('hr_holiday_year_id', 'emp_leave_year_id_fk')->references('id')->on('hr_holiday_years')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_working_pattern_id', 'emp_leave_pattern_id_fk')->references('id')->on('employee_working_patterns')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_leaves');
    }
};
