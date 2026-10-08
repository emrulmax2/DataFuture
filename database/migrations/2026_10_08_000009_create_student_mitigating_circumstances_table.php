<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Mitigating Circumstances (Assignment) claims, the fifth "Do it online" form
 * to move off Google Forms.
 *
 * A claim can cover several assignments at once, so those are rows of their
 * own rather than a list squeezed into a column: the panel considers each
 * deadline separately, and an extension may be granted for one and not another.
 * The reasons are a set of tick boxes, kept as JSON because they are read back
 * as a whole and never queried individually.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_mitigating_circumstances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('forms_table_id')->nullable();

            $table->unsignedBigInteger('course_creation_id')->nullable();
            $table->string('course_name', 191)->nullable();
            $table->string('address', 500)->nullable();

            $table->json('reasons')->nullable();
            $table->string('other_reason', 191)->nullable();
            $table->text('claim_details')->nullable();

            $table->enum('evidence_method', ['upload', 'email', 'none'])->nullable();

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

            $table->index('student_id', 'smc_student_id_index');
            $table->index('ticket_ref', 'smc_ticket_ref_index');
            $table->index('ticket_status', 'smc_ticket_status_index');
        });

        Schema::create('student_mitigating_circumstance_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->string('title', 191);
            $table->date('submission_date')->nullable();
            $table->timestamps();

            $table->index('request_id', 'smca_request_id_index');
        });

        Schema::create('student_mitigating_circumstance_documents', function (Blueprint $table) {
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

            $table->index('request_id', 'smcd_request_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_mitigating_circumstance_documents');
        Schema::dropIfExists('student_mitigating_circumstance_assignments');
        Schema::dropIfExists('student_mitigating_circumstances');
    }
};
