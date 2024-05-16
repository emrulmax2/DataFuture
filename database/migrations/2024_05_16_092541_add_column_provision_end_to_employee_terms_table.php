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
        Schema::table('employee_terms', function (Blueprint $table) {
            $table->date('provision_end')->nullable()->after('employment_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_terms', function (Blueprint $table) {
            $table->dropColumn('provision_end');
        });
    }
};
