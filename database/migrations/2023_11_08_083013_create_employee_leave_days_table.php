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
        Schema::create('employee_leave_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_leave_id');
            $table->date('leave_date');
            $table->integer('hour');
            $table->tinyInteger('is_fraction')->default(0);
            $table->enum('status', ['Active', 'In Active']);

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('employee_leave_id')->references('id')->on('employee_leaves')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_leave_days');
    }
};
