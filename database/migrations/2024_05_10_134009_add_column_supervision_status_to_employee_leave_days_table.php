<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_leave_days', function (Blueprint $table) {
            $table->smallInteger('supervision_status')->default(0)->comment('0=None,1=Yes,2=No')->after('was_absent_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_days', function (Blueprint $table) {
            $table->dropColumn('supervision_status');
        });
    }
};
