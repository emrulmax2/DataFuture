<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk registration: nothing is lost when a newer SIS file is uploaded (user, 2 Nov 2026).
 * Each line remembers the line of the earlier upload it took its work from, and keeps the history
 * of decisions that no longer apply (SLC's value changed, or it now matches).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slc_bulk_import_rows', function (Blueprint $table) {
            $table->json('history')->nullable()->after('slc_registration_id');
            $table->unsignedBigInteger('carried_from_id')->nullable()->after('history');
        });
        Schema::table('slc_bulk_row_fields', function (Blueprint $table) {
            $table->unsignedBigInteger('carried_from_id')->nullable()->after('slc_coc_id');
        });
    }

    public function down(): void
    {
        Schema::table('slc_bulk_row_fields', function (Blueprint $table) {
            $table->dropColumn('carried_from_id');
        });
        Schema::table('slc_bulk_import_rows', function (Blueprint $table) {
            $table->dropColumn(['history', 'carried_from_id']);
        });
    }
};
