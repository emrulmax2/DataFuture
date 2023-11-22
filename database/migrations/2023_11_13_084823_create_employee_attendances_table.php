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
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('employee_working_pattern_id')->nullable();
            $table->unsignedBigInteger('employee_working_pattern_pay_id')->nullable();
            $table->date('date');
            $table->string('clockin_contract', 10)->nullable();
            $table->string('clockin_punch', 10)->nullable();
            $table->string('clockin_system', 10)->nullable();
            $table->string('clockout_contract', 10)->nullable();
            $table->string('clockout_punch', 10)->nullable();
            $table->string('clockout_system', 10)->nullable();
            $table->integer('total_break')->nullable();
            $table->integer('break_details_html')->nullable();
            $table->string('paid_break', 10)->nullable();
            $table->string('unpadi_break', 10)->nullable();
            $table->string('adjustment', 10)->nullable();
            $table->integer('total_work_hour')->nullable();
            $table->unsignedBigInteger('employee_leave_day_id')->nullable();
            $table->integer('leave_status')->nullable()->default(0)->comment('1=Holiday/Vacation,2=Meeting / Training,3=Sick Leave,4=Authorised Unpaid,5=Authorised Paid');
            $table->string('leave_adjustment', 10)->nullable();
            $table->integer('leave_hour')->nullable();
            $table->text('note')->nullable();
            $table->integer('user_issues')->default(0)->nullable();
            $table->text('isses_field')->nullable();
            $table->tinyInteger('overtime_status')->comment('1=Yes,0=No')->default(0);
            $table->tinyInteger('status')->comment('1=Approved,2=Pending,3=Canceled')->default(1);

            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_leave_day_id', 'eald_id_fk')->references('id')->on('employee_leave_days')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_attendances');
    }
};
