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
        Schema::create('employee_holiday_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('hr_holiday_year_id');
            $table->unsignedBigInteger('employee_working_pattern_id');
            $table->tinyInteger('operator')->default(1)->comment('1 = Plus, 2 = Minus');
            $table->integer('hours');
            
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('hr_holiday_year_id', 'adjustment_holiday_year_id_frn_key')->references('id')->on('hr_holiday_years')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_working_pattern_id', 'emp_pattern_id_frn_key')->references('id')->on('employee_working_patterns')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_holiday_adjustments');
    }
};
