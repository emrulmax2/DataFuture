<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff documents: PIN-protected (encrypted) uploads and their audit trail.
 *
 *   employee_documents.is_encrypted   the file is stored encrypted and needs
 *                                     the reader's document PIN to open
 *   user_document_pins                one PIN per user who holds the
 *                                     "PIN Enabled" privilege
 *   employee_document_access_logs     who opened whose document, when, and
 *                                     who was really behind an impersonated
 *                                     session
 *
 * Every step is guarded and only ever adds, so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if(Schema::hasTable('employee_documents') && !Schema::hasColumn('employee_documents', 'is_encrypted')):
            Schema::table('employee_documents', function (Blueprint $table) {
                $table->tinyInteger('is_encrypted')->default(0)->after('type')->comment('1 = stored encrypted, needs the reader\'s document PIN');
            });
        endif;

        if(!Schema::hasTable('user_document_pins')):
            Schema::create('user_document_pins', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id')->unsigned();
                /* The hash of a PIN the user chose (6 to 8 digits). Never the PIN itself. */
                $table->text('pin');
                $table->unsignedTinyInteger('failed_attempts')->default(0);
                /* Set after too many wrong PINs in a row; NULL when not locked. */
                $table->dateTime('locked_until')->nullable();
                /* When the PIN was last set. */
                $table->dateTime('generated_at')->nullable();
                $table->bigInteger('created_by')->nullable();
                $table->bigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->unique('user_id', 'udp_user_unique');
            });
        endif;

        if(!Schema::hasTable('employee_document_access_logs')):
            Schema::create('employee_document_access_logs', function (Blueprint $table) {
                $table->id();
                /* NULL for PIN events (generated, reset, ...), which concern no single document. */
                $table->bigInteger('employee_document_id')->unsigned()->nullable();
                /* Whose record this is about: the document's owner, or the PIN's owner. */
                $table->bigInteger('employee_id')->unsigned()->nullable();
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

                $table->index(['employee_id', 'created_at'], 'edal_employee_idx');
                $table->index(['user_id', 'created_at'], 'edal_user_idx');
                $table->index('employee_document_id', 'edal_document_idx');
            });
        endif;
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_document_access_logs');
        Schema::dropIfExists('user_document_pins');

        if(Schema::hasTable('employee_documents') && Schema::hasColumn('employee_documents', 'is_encrypted')):
            Schema::table('employee_documents', function (Blueprint $table) {
                $table->dropColumn('is_encrypted');
            });
        endif;
    }
};
