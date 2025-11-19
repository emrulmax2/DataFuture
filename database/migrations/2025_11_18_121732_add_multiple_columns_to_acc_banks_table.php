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
        Schema::table('acc_banks', function (Blueprint $table) {
            $table->string('ac_name')->nullable()->after('opening_date');
            $table->string('sort_code', 20)->nullable()->after('ac_name');
            $table->string('ac_number', 20)->nullable()->after('sort_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acc_banks', function (Blueprint $table) {
            //
        });
    }
};
