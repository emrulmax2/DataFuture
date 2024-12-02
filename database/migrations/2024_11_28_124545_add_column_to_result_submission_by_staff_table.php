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
        Schema::table('result_submission_by_staff', function (Blueprint $table) {
            $table->smallInteger('is_excel_missing')->default(0)->after('is_it_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_submission_by_staff', function (Blueprint $table) {
            //
        });
    }
};
