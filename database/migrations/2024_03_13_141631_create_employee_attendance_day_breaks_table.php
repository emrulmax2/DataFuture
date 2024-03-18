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
        Schema::create('employee_attendance_day_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_attendance_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->string('start', 191);
            $table->string('end', 191);
            $table->integer('total')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_attendance_id')->references('id')->on('employee_attendances')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_attendance_day_breaks');
    }
};
