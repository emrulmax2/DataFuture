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
        Schema::table('acc_transactions', function (Blueprint $table) {
            $table->text('transaction_doc_url')->nullable()->after('transaction_doc_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acc_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_doc_url');
        });
    }
};
