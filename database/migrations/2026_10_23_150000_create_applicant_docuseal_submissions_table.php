<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every DocuSeal offer acceptance form sent from Offer Placed, so the signed
 * form and its audit trail can be fetched back into the applicant's uploads
 * once DocuSeal reports the submission completed
 * (docuseal:collect-signed-offers).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_docuseal_submissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('applicant_id')->unsigned();
            $table->unsignedBigInteger('submission_id')->unique();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('applicant_email', 191)->nullable();
            $table->string('signing_url', 500)->nullable();
            // Ours: waiting (not signed yet) · stored · skipped (another signed form is already stored) · declined · expired · failed (retried)
            $table->string('state', 20)->default('waiting');
            // DocuSeal's own submission status at the last check: pending · completed · declined · expired
            $table->string('docuseal_status', 20)->nullable();
            $table->bigInteger('sent_by')->unsigned()->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('stored_at')->nullable();
            $table->bigInteger('offer_document_id')->unsigned()->nullable();
            $table->bigInteger('audit_document_id')->unsigned()->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->unsignedInteger('checks')->default(0);
            $table->unsignedSmallInteger('failures')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['applicant_id'], 'applicant_docuseal_submissions_applicant_index');
            $table->index(['state'], 'applicant_docuseal_submissions_state_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_docuseal_submissions');
    }
};
