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
        Schema::table('agent_comission_details', function (Blueprint $table) {
            $table->enum('comission_for', ['Course Fee', 'Refund'])->default('Course Fee')->nullable()->after('slc_money_receipt_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_comission_details', function (Blueprint $table) {
            //
        });
    }
};
