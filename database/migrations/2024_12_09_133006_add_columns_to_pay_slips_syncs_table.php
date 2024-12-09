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
        Schema::table('pay_slip_upload_syncs', function (Blueprint $table) {
            $table->unsignedBigInteger('holiday_year_id')->nullable()->after('employee_id');
            //$table->foreignId('holiday_year_id')->constrained('hr_holiday_years')->onUpdate('set nul')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_slip_upload_syncs', function (Blueprint $table) {
            $table->dropColumn('holiday_year_id');
        });
    }
};
