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
        Schema::table('applicant_e_signature_events', function (Blueprint $table) {
            $table->string('user_type')->nullable()->after('applicant_id');
            $table->json('extra_field')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_e_signature_events', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'extra_field']);
        });
    }
};
