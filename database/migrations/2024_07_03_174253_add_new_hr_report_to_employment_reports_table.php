<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('employment_reports')->insert(
            array(
                'report_description' => 'Employment Holiday Hour Report',
                'file_name' => 'Employment Holiday Hour Report Calculation in a Date Range.',
                'last_run' => null,
                'created_by' => 1
            )
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_reports', function (Blueprint $table) {
            //
        });
    }
};
