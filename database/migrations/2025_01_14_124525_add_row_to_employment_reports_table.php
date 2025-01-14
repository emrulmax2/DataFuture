<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('employment_reports')->insert(
            array(
                'report_description' => 'Outstanding Holiday Report',
                'file_name' => 'Employee holiday years outstanding holiday report.',
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
