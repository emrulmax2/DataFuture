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
            $table->unsignedBigInteger('agent_comission_payment_id')->nullable()->after('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_comissions', function (Blueprint $table) {
            //
        });
    }
};
