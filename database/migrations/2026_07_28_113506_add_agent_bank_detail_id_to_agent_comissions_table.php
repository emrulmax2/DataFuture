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
        Schema::table('agent_comissions', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_bank_detail_id')->nullable()->after('agent_comission_payment_id');

            $table->foreign('agent_bank_detail_id')->references('id')->on('agent_bank_details')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_comissions', function (Blueprint $table) {
            $table->dropForeign(['agent_bank_detail_id']);
            $table->dropColumn('agent_bank_detail_id');
        });
    }
};
