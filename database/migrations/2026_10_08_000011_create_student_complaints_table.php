<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Complaint Form Stage 1, the seventh "Do it online" form to move off Google
 * Forms.
 *
 * A complaint is read and investigated in the student's own words, so the three
 * long answers are stored whole. The declaration matters here more than on most
 * forms - it is the student's consent to the complaint being discussed inside
 * the College - so who confirmed it and when is recorded with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_complaints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('forms_table_id')->nullable();

            $table->unsignedBigInteger('course_creation_id')->nullable();
            $table->string('course_name', 191)->nullable();
            /* Always "Student" from the portal; the paper form also serves
               staff and visitors, so the answer is recorded rather than
               assumed. */
            $table->string('relationship', 64)->default('Student');

            $table->text('complaint_details')->nullable();
            $table->enum('spoken_to_anyone', ['yes', 'no'])->nullable();
            $table->string('spoken_to_details', 191)->nullable();
            $table->text('attempts')->nullable();
            $table->text('desired_outcome')->nullable();

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

            $table->index('student_id', 'scp_student_id_index');
            $table->index('ticket_ref', 'scp_ticket_ref_index');
            $table->index('ticket_status', 'scp_ticket_status_index');
        });

        Schema::create('student_complaint_documents', function (Blueprint $table) {
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

            $table->index('request_id', 'scpd_request_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_complaint_documents');
        Schema::dropIfExists('student_complaints');
    }
};
