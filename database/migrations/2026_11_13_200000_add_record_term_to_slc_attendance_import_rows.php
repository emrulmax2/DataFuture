<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk attendance › step 4 (user, 13 Nov 2026): the Session Term and Attendance Term chosen for the attendance
 * Bulk Attendance & Installment records on the SLC tab (null = the one suggested: the year's first free term).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slc_attendance_import_rows', function (Blueprint $table) {
            $table->unsignedTinyInteger('record_session_term')->nullable()->after('code_at');
            $table->unsignedBigInteger('record_term_declaration_id')->nullable()->after('record_session_term');
            $table->unsignedBigInteger('record_term_by')->nullable()->after('record_term_declaration_id');
            $table->timestamp('record_term_at')->nullable()->after('record_term_by');
        });
    }

    public function down(): void
    {
        Schema::table('slc_attendance_import_rows', function (Blueprint $table) {
            $table->dropColumn(['record_session_term', 'record_term_declaration_id', 'record_term_by', 'record_term_at']);
        });
    }
};
