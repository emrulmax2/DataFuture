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
        // Add the new column, copy data, then drop the old column. Avoids requiring doctrine/dbal.
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('lsoa_21')->nullable()->after('polar_4_quantile');
        });

        // Copy existing values from losa_21 to lsoa_21
        DB::statement("UPDATE addresses SET lsoa_21 = losa_21 WHERE losa_21 IS NOT NULL");

        // Drop the old column
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('losa_21');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('losa_21')->nullable()->after('polar_4_quantile');
        });

        // Copy back
        DB::statement("UPDATE addresses SET losa_21 = lsoa_21 WHERE lsoa_21 IS NOT NULL");

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('lsoa_21');
        });
    }
};
