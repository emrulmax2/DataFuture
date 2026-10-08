<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * "Report an IT issue on campus", rebuilt as a portal form that raises a
 * Service Desk ticket in IT & Monitoring.
 *
 * Unlike the other forms, the student chooses the issue type: IT answers for
 * many kinds of request and the right queue depends on which. The chosen type
 * is stored by id and by name — the id is what Operations was told, the name is
 * what the student saw, and a renamed type must not rewrite history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_it_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('forms_table_id')->nullable();

            $table->unsignedBigInteger('course_creation_id')->nullable();
            $table->string('course_name', 191)->nullable();

            /* The issue type as Operations knows it. */
            $table->unsignedBigInteger('issue_type_id')->nullable();
            $table->string('issue_type_name', 191)->nullable();

            $table->unsignedBigInteger('venue_id')->nullable();
            $table->string('campus', 191)->nullable();
            $table->string('location', 191)->nullable();
            $table->text('description')->nullable();

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

            $table->index('student_id', 'sitr_student_id_index');
            $table->index('ticket_ref', 'sitr_ticket_ref_index');
            $table->index('ticket_status', 'sitr_ticket_status_index');
        });

        Schema::create('student_it_report_documents', function (Blueprint $table) {
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

            $table->index('request_id', 'sitrd_request_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_it_report_documents');
        Schema::dropIfExists('student_it_reports');
    }
};
