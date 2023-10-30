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
        Schema::create('employee_working_pattern_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_working_pattern_id');
            $table->integer('day');
            $table->string('day_name', 50)->nullable();
            $table->string('start', 5);
            $table->string('end', 5);
            $table->string('paid_br', 5);
            $table->string('unpaid_br', 5);
            $table->string('total', 5);
            
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('employee_working_pattern_id', 'ewp_employee_wp_id')->references('id')->on('employee_working_patterns')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_working_pattern_details');
    }
};
