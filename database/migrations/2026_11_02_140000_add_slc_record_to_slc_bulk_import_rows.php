<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk registration › Bulk SLC Registration (user, 2 Nov 2026): what was done for each batch line,
 * so a student is not ticked twice — the registration and agreement it points to, and how they came
 * about (created / completed one that existed / both existed, ignored / registration only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slc_bulk_import_rows', function (Blueprint $table) {
            $table->unsignedBigInteger('slc_agreement_id')->nullable()->after('slc_registration_id');
            $table->string('slc_recorded', 20)->nullable()->after('slc_agreement_id');
            $table->unsignedBigInteger('slc_recorded_by')->nullable()->after('slc_recorded');
            $table->timestamp('slc_recorded_at')->nullable()->after('slc_recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('slc_bulk_import_rows', function (Blueprint $table) {
            $table->dropColumn(['slc_agreement_id', 'slc_recorded', 'slc_recorded_by', 'slc_recorded_at']);
        });
    }
};
