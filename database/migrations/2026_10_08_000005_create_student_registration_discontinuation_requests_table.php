<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Registration Discontinuation Form, the second "Do it online" form to move off
 * Google Forms.
 *
 * A student saying they are leaving is a statement with consequences — funding,
 * attendance, the awarding body — so what they said, when, and from where is
 * kept here in full, alongside the Service Desk ticket it was raised as. The
 * course is snapshotted because their enrolment can change afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_registration_discontinuation_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('forms_table_id')->nullable();

            $table->unsignedBigInteger('course_creation_id')->nullable();
            $table->string('course_name', 191)->nullable();

            $table->date('effective_from')->nullable();
            $table->text('reason')->nullable();

            $table->enum('discussed_with_staff', ['yes', 'no'])->nullable();
            $table->string('staff_details', 191)->nullable();
            $table->enum('offer_from_other_institute', ['yes', 'no'])->nullable();
            $table->string('institute_name', 191)->nullable();
            $table->date('institute_start_date')->nullable();

            $table->boolean('declaration_accepted')->default(0);
            $table->string('declaration_name', 191)->nullable();

            /* Recorded with the submission, as the form tells the student. The
               browser's own reading of the device is a convenience; the IP and
               the user agent are taken from the request, which it cannot edit. */
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

            /* Named by hand: the generated names run past MySQL's 64-character
               limit on a table name this long. */
            $table->index('student_id', 'srdr_student_id_index');
            $table->index('ticket_ref', 'srdr_ticket_ref_index');
            $table->index('ticket_status', 'srdr_ticket_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_registration_discontinuation_requests');
    }
};
