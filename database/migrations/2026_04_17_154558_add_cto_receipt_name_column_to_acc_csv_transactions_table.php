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
        Schema::table('acc_csv_transactions', function (Blueprint $table) {
            $table->smallInteger('has_receipts')->default(0)->after('flow');
            $table->string('cto_receipt_name')->nullable()->after('has_receipts');
            $table->smallInteger('cto_receipt_error')->nullable()->after('cto_receipt_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acc_csv_transactions', function (Blueprint $table) {
            //
        });
    }
};
