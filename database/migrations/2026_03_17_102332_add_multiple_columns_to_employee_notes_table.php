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
        Schema::table('employee_notes', function (Blueprint $table) {
            $table->smallInteger('reminder')->default(0)->after('employee_appraisal_id');
            $table->date('reminder_date')->nullable()->after('reminder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_notes', function (Blueprint $table) {
            //
        });
    }
};
