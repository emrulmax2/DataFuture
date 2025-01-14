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
        Schema::table('term_declarations', function (Blueprint $table) {
            $table->integer('stuload')->after('academic_year_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('term_declarations', function (Blueprint $table) {
            $table->dropColumn('stuload');
        });
    }
};
