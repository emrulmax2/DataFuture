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
        Schema::table('employments', function (Blueprint $table) {
            $table->bigInteger('employee_work_type_id')->unsigned()->nullable()->change();
            $table->foreign('employee_work_type_id')->references('id')->on('employee_work_types')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employments', function (Blueprint $table) {
            $table->dropForeign(['employee_work_type_id']);
            $table->bigInteger('employee_work_type_id')->unsigned()->nullable()->change();
        });
    }
};
