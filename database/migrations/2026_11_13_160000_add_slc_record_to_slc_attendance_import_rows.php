<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk attendance › Batches › View students › Bulk Attendance Confirmation (user, 13 Nov 2026): what
 * was recorded for each batch line on the student's SLC tab, so a student is not ticked twice — the SLC
 * attendance and the instalment (code A) it created, and how (created / created_anyway = with permission,
 * the student already had an attendance for that term).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slc_attendance_import_rows', function (Blueprint $table) {
            $table->unsignedBigInteger('slc_attendance_id')->nullable()->after('slc_coc_id');
            $table->unsignedBigInteger('slc_installment_id')->nullable()->after('slc_attendance_id');
            $table->string('slc_recorded', 20)->nullable()->after('slc_installment_id');
            $table->unsignedBigInteger('slc_recorded_by')->nullable()->after('slc_recorded');
            $table->timestamp('slc_recorded_at')->nullable()->after('slc_recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('slc_attendance_import_rows', function (Blueprint $table) {
            $table->dropColumn(['slc_attendance_id', 'slc_installment_id', 'slc_recorded', 'slc_recorded_by', 'slc_recorded_at']);
        });
    }
};
