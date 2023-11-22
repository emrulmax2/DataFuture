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
        Schema::create('hr_conditions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Clock In', 'Clock Out']);
            $table->tinyInteger('time_frame');
            $table->integer('minutes');
            $table->tinyInteger('notify')->comment('1=Yes,0=No');
            $table->tinyInteger('action')->comment('1=Contract,2=Actual,3=Blank');

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
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
        Schema::dropIfExists('hr_conditions');
    }
};
