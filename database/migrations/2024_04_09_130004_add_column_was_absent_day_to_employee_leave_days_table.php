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
        Schema::table('employee_leave_days', function (Blueprint $table) {
            $table->tinyInteger('was_absent_day')->default(0)->nullable()->after('is_taken');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_leave_days', function (Blueprint $table) {
            $table->dropColumn('was_absent_day');
        });
    }
};
