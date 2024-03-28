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
        Schema::table('employee_attendance_lives', function (Blueprint $table) {
            $table->dropColumn('employee_attendance_machine_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_attendance_lives', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_attendance_machine_id')->nullable()->after('attendance_type');
        });
    }
};
