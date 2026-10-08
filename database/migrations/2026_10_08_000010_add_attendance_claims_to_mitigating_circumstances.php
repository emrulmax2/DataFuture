<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Mitigating circumstances claims about attendance.
 *
 * The same claim in all but what it is about: one lists assignments whose
 * deadlines should move, the other lists the days a student could not attend.
 * They share a table — the reasons, the evidence, the declaration and the
 * panel's 14 working days are identical — and `claim_type` says which it is.
 * Only the rows differ, so the absences get a table of their own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_mitigating_circumstances', function (Blueprint $table) {
            $table->enum('claim_type', ['assignment', 'attendance'])
                ->default('assignment')
                ->after('forms_table_id');
        });

        Schema::create('student_mitigating_circumstance_absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->date('absent_from');
            /* Null for a single day, or while the absence is still going on. */
            $table->date('absent_to')->nullable();
            $table->string('details', 500);
            $table->timestamps();

            $table->index('request_id', 'smcab_request_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_mitigating_circumstance_absences');

        Schema::table('student_mitigating_circumstances', function (Blueprint $table) {
            $table->dropColumn('claim_type');
        });
    }
};
