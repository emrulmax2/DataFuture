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
            $table->text('taged_students')->nullable()->after('transfer_bank_id');
            $table->smallInteger('has_receipts')->nullable()->default(0)->after('transfer_bank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acc_transactions', function (Blueprint $table) {
            //
        });
    }
};
