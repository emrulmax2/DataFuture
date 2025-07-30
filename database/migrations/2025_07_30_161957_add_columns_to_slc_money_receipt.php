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
        Schema::table('slc_money_receipts', function (Blueprint $table) {
            $table->smallInteger('mailed_pdf_file')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slc_money_receipts', function (Blueprint $table) {
            $table->dropColumn('mailed_pdf_file');
        });
    }
};
