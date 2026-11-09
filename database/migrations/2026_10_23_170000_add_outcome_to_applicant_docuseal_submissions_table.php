<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the signing did to the application (OfferAcceptanceWorkflow): converted
 * to a student, conversion failed, held for proof of ID, rejected, or left
 * alone because the applicant was no longer at Offer Placed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_docuseal_submissions', function (Blueprint $table) {
            $table->timestamp('opened_at')->nullable()->after('sent_at');
            $table->text('decline_reason')->nullable()->after('completed_at');
            // converting · converted · conversion_failed · held_missing_proof · rejected · status_unchanged
            $table->string('outcome', 30)->nullable()->after('audit_document_id');
            $table->text('outcome_note')->nullable()->after('outcome');
            $table->timestamp('outcome_at')->nullable()->after('outcome_note');
            $table->string('batch_id', 36)->nullable()->after('outcome_at');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_docuseal_submissions', function (Blueprint $table) {
            $table->dropColumn(['opened_at', 'decline_reason', 'outcome', 'outcome_note', 'outcome_at', 'batch_id']);
        });
    }
};
