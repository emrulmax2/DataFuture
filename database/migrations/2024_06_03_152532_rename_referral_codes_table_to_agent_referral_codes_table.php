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
        Schema::rename('referral_codes', 'agent_referral_codes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('agent_referral_codes', 'referral_codes');
    }
};
