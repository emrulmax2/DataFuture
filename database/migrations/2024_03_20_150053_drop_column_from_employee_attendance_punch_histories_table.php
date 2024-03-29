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
        Schema::table('employee_attendance_punch_histories', function (Blueprint $table) {
            $table->dropForeign('eaph_machine_id_fk');
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
        Schema::table('employee_attendance_punch_histories', function (Blueprint $table) {
            //
        });
    }
};
