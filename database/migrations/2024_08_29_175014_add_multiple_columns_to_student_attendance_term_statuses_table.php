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
        Schema::table('student_attendance_term_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('term_declaration_id')->after('student_id')->nullable()->change();
            $table->text('status_change_reason')->nullable()->after('status_id');
            $table->dateTime('status_change_date')->nullable()->after('status_change_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendance_term_statuses', function (Blueprint $table) {
            $table->dropColumn(['status_change_reason', 'status_change_date']);
        });
    }
};
