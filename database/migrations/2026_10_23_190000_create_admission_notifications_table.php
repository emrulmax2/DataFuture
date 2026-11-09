<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notifications the admission side sends about an applicant (first user: the DocuSeal
 * offer acceptance workflow — accepted / conversion stopped / proof of ID missing /
 * rejected), kept with what was sent, to whom and whether it left. user_id is the
 * associated staff user (the one who sent the form), nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('applicant_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('source', 30)->default('docuseal');
            $table->bigInteger('docuseal_submission_id')->unsigned()->nullable(); // applicant_docuseal_submissions.id
            // offer_accepted · conversion_failed · proof_of_id_missing · offer_rejected
            $table->string('type', 40);
            $table->string('channel', 20)->default('email');
            $table->text('recipients')->nullable();
            $table->string('subject', 255)->nullable();
            $table->longText('body')->nullable();
            $table->json('data')->nullable();
            // pending · sent · failed · not_sent (no recipient configured)
            $table->string('status', 20)->default('pending');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['applicant_id'], 'admission_notifications_applicant_index');
            $table->index(['user_id'], 'admission_notifications_user_index');
            $table->index(['type'], 'admission_notifications_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_notifications');
    }
};
