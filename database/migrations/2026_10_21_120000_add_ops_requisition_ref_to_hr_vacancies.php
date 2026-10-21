<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A vacancy published in the Operations recruitment module is pushed here
     * and carries its requisition reference (REQ-2026-003), which is what makes
     * the push safe to repeat: the same reference updates the same row.
     */
    public function up(): void
    {
        Schema::table('hr_vacancies', function (Blueprint $table) {
            $table->string('ops_requisition_ref', 32)->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_vacancies', function (Blueprint $table) {
            $table->dropUnique(['ops_requisition_ref']);
            $table->dropColumn('ops_requisition_ref');
        });
    }
};
