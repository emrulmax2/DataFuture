<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk attendance › Batches › Rejected by SIS (user, 13 Nov 2026 — spec v9.0 §7.3, the Import Results screen):
 * a line of an uploaded batch that SIS rejected goes back to step 4 to be sent again in a new batch. The batch it
 * was in, its code, SIS's reason, who and when; sis_rejected_item marks the batch's own line (a newer upload's copy
 * of it, freed with it, has 0).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slc_attendance_import_rows', function (Blueprint $table) {
            $table->unsignedBigInteger('sis_rejected_batch_id')->nullable()->after('slc_recorded_at');
            $table->boolean('sis_rejected_item')->default(false)->after('sis_rejected_batch_id');
            $table->string('sis_rejected_code', 2)->nullable()->after('sis_rejected_item');
            $table->string('sis_rejected_reason', 255)->nullable()->after('sis_rejected_code');
            $table->unsignedBigInteger('sis_rejected_by')->nullable()->after('sis_rejected_reason');
            $table->timestamp('sis_rejected_at')->nullable()->after('sis_rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('slc_attendance_import_rows', function (Blueprint $table) {
            $table->dropColumn(['sis_rejected_batch_id', 'sis_rejected_item', 'sis_rejected_code', 'sis_rejected_reason', 'sis_rejected_by', 'sis_rejected_at']);
        });
    }
};
