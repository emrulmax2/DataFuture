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
        Schema::table('applicant_criminal_convictions', function (Blueprint $table) {
            $table->boolean('criminal_declaration')->default(0)->after('criminal_conviction_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_criminal_convictions', function (Blueprint $table) {
            $table->dropColumn('criminal_declaration');
        });
    }
};
