<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Refund Request Form, the third "Do it online" form to move off Google Forms.
 *
 * The bank details are the reason this table is different from the others:
 * they are written encrypted (see the model's casts), which is why the columns
 * are text rather than the 8 and 6 characters the values actually are. The last
 * four digits are kept in the clear so a refund can be matched and discussed
 * without decrypting anything.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_refund_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('forms_table_id')->nullable();

            $table->unsignedBigInteger('course_creation_id')->nullable();
            $table->string('course_name', 191)->nullable();
            $table->string('intake_semester', 191)->nullable();

            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('reason')->nullable();

            $table->string('account_holder_name', 191)->nullable();
            $table->text('account_number')->nullable();
            $table->text('sort_code')->nullable();
            $table->string('account_number_last4', 4)->nullable();

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

            $table->index('student_id', 'srr_student_id_index');
            $table->index('ticket_ref', 'srr_ticket_ref_index');
            $table->index('ticket_status', 'srr_ticket_status_index');
        });

        Schema::create('student_refund_request_documents', function (Blueprint $table) {
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

            $table->index('request_id', 'srrd_request_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_refund_request_documents');
        Schema::dropIfExists('student_refund_requests');
    }
};
