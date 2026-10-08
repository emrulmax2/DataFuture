<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Academic Appeal Stage 1, the fourth "Do it online" form to move off Google
 * Forms.
 *
 * An appeal is a formal act with a deadline attached — 15 working days from
 * formal notification — so what the student said, when they said it and from
 * where is all kept. The long answers are the appeal itself and are stored
 * whole, never truncated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_academic_appeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('forms_table_id')->nullable();

            $table->unsignedBigInteger('course_creation_id')->nullable();
            $table->string('course_name', 191)->nullable();

            $table->enum('notified', ['yes', 'no'])->nullable();
            $table->enum('grounds', ['mitigating_circumstances', 'procedural_irregularity'])->nullable();
            $table->text('early_resolution')->nullable();
            $table->text('decision')->nullable();
            $table->text('outcome')->nullable();

            $table->enum('evidence_method', ['upload', 'email', 'none'])->nullable();
            $table->text('evidence_statement')->nullable();

            $table->boolean('declaration_accepted')->default(0);
            $table->string('declaration_name', 191)->nullable();

            $table->string('device_type', 32)->nullable();
            $table->string('device_os', 64)->nullable();
            $table->string('device_browser', 64)->nullable();
            $table->string('device_screen', 32)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Canceled'])->default('Pending');
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->string('ticket_ref', 32)->nullable();
            $table->string('ticket_status', 16)->default('queued');
            $table->text('ticket_failed_reason')->nullable();
            $table->timestamp('ticket_raised_at')->nullable();
            $table->timestamp('student_notified_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id', 'saa_student_id_index');
            $table->index('ticket_ref', 'saa_ticket_ref_index');
            $table->index('ticket_status', 'saa_ticket_status_index');
        });

        Schema::create('student_academic_appeal_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->text('path')->nullable();
            $table->string('storage_key', 512)->nullable();
            $table->string('display_file_name', 191)->nullable();
            $table->string('current_file_name', 191)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('request_id', 'saad_request_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic_appeal_documents');
        Schema::dropIfExists('student_academic_appeals');
    }
};
