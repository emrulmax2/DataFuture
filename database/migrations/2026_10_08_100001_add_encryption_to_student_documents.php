<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student documents: the same PIN-protected (encrypted) uploads and audit
 * trail that staff documents have (2026_10_06_100001).
 *
 *   student_documents.is_encrypted   the file is stored encrypted and needs
 *                                    the reader's PIN to open
 *   student_document_access_logs     who opened which student's document,
 *                                    when, and who was really behind an
 *                                    impersonated session
 *
 * The PIN itself is the one in user_document_pins: a member of staff has one
 * PIN, and it opens encrypted staff and student documents alike.
 *
 * Every step is guarded and only ever adds, so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if(Schema::hasTable('student_documents') && !Schema::hasColumn('student_documents', 'is_encrypted')):
            Schema::table('student_documents', function (Blueprint $table) {
                $table->tinyInteger('is_encrypted')->default(0)->after('current_file_name')->comment('1 = stored encrypted, needs the reader\'s PIN');
            });
        endif;

        if(!Schema::hasTable('student_document_access_logs')):
            Schema::create('student_document_access_logs', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('student_document_id')->unsigned()->nullable();
                /* Whose record the document is on. */
                $table->bigInteger('student_id')->unsigned()->nullable();
                /* The account that was signed in. */
                $table->bigInteger('user_id')->unsigned();
                /* The person really at the keyboard when user_id was being impersonated. */
                $table->bigInteger('impersonator_id')->unsigned()->nullable();
                $table->string('event', 40);
                $table->tinyInteger('is_encrypted')->default(0);
                /* Kept here so the trail still reads after the document is deleted. */
                $table->string('document_name', 191)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();

                $table->index(['student_id', 'created_at'], 'sdal_student_idx');
                $table->index(['user_id', 'created_at'], 'sdal_user_idx');
                $table->index('student_document_id', 'sdal_document_idx');
            });
        endif;
    }

    public function down(): void
    {
        Schema::dropIfExists('student_document_access_logs');

        if(Schema::hasTable('student_documents') && Schema::hasColumn('student_documents', 'is_encrypted')):
            Schema::table('student_documents', function (Blueprint $table) {
                $table->dropColumn('is_encrypted');
            });
        endif;
    }
};
