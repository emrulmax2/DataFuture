<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk attendance (/slc-registration › Bulk attendance, user, 2 Nov 2026), steps 1–2: the SIS
 * "Attendance Confirmation Report" upload and each student line, matched to our student by SSN.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slc_attendance_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('disk', 20);
            $table->string('path');
            $table->string('header');
            $table->string('hei', 10)->nullable();
            $table->string('hei_name')->nullable();
            $table->date('report_date')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('term', 10)->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->json('totals')->nullable();
            $table->json('error_lines')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('slc_attendance_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slc_attendance_import_id')->constrained('slc_attendance_imports')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->text('raw');
            $table->string('ssn', 30)->index();
            $table->json('data');
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->enum('match_method', ['ssn', 'linked'])->nullable();
            $table->unsignedBigInteger('linked_by')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slc_attendance_import_rows');
        Schema::dropIfExists('slc_attendance_imports');
    }
};
