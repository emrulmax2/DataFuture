<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * /slc-registration › Data check › "No action" is remembered for later uploads (user, 5 Nov 2026):
 * one row per student and field with what SLC sent and what we held when someone chose it. A later
 * upload with the same difference (both values the same) gets No action by itself. "Change" on the
 * field ends it (revoked_*), so the field is checked again from then on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slc_bulk_no_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('field', 20);
            $table->text('slc_value');      // CarryOver::slcValue — as two files are compared
            $table->text('ours_value');     // Text::norm of what we held
            $table->text('slc_display')->nullable();
            $table->text('ours_display')->nullable();
            $table->unsignedBigInteger('slc_bulk_import_row_id')->nullable(); // the line it was chosen on
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('revoked_by')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'field'], 'slc_bulk_no_actions_student_field');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slc_bulk_no_actions');
    }
};
