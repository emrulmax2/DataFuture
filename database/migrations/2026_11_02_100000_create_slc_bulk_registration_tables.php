<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk registration (/slc-registration, user, 2 Nov 2026). These tables only track the work
 * on an upload: the student records, SLC registrations, agreements and CoCs it writes are the
 * app's existing ones.
 */
return new class extends Migration
{
    public function up(): void
    {
        // one SIS "Registration Confirmation Report" upload
        Schema::create('slc_bulk_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('disk', 20);
            $table->string('path');
            $table->string('header', 60);
            $table->string('footer', 60)->nullable();
            $table->string('hei', 10)->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->json('error_lines')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        // a confirmed-Yes batch, downloaded as the SIS import file
        Schema::create('slc_bulk_batches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->enum('status', ['awaiting', 'downloaded', 'uploaded'])->default('awaiting');
            $table->string('header', 60);
            $table->string('file_name')->nullable();
            $table->string('disk', 20)->nullable();
            $table->string('path')->nullable();
            $table->date('file_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('downloaded_by')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        // one student line of an upload
        Schema::create('slc_bulk_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slc_bulk_import_id')->constrained('slc_bulk_imports')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->text('raw');
            $table->string('ssn', 30)->index();
            $table->json('data');
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->enum('match_method', ['ssn', 'linked'])->nullable();
            $table->unsignedBigInteger('linked_by')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->enum('decision', ['confirm', 'notreg', 'hold'])->nullable();
            $table->string('decision_reason')->nullable();
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->boolean('attendance_checked')->default(false);
            $table->foreignId('slc_bulk_batch_id')->nullable()->constrained('slc_bulk_batches')->nullOnDelete();
            // the SSN was already confirmed in an earlier batch when this file was uploaded
            $table->unsignedBigInteger('earlier_batch_id')->nullable();
            $table->unsignedBigInteger('slc_registration_id')->nullable();
            $table->timestamps();
        });

        // what was decided about one field that differs from SLC (route) and how far it got (state)
        Schema::create('slc_bulk_row_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slc_bulk_import_row_id')->constrained('slc_bulk_import_rows')->cascadeOnDelete();
            $table->string('field', 20);
            $table->enum('route', ['coc', 'contact', 'accept']);
            $table->string('state', 20)->nullable();
            $table->unsignedBigInteger('state_by')->nullable();
            $table->timestamp('state_at')->nullable();
            $table->unsignedBigInteger('slc_coc_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->unique(['slc_bulk_import_row_id', 'field']);
        });

        // changes made to our records from SLC data ("Our record updated")
        Schema::create('slc_bulk_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slc_bulk_import_row_id')->nullable()->constrained('slc_bulk_import_rows')->nullOnDelete();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('field', 40);
            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        // the Activity page: every upload, identification, change, action and confirmation
        Schema::create('slc_bulk_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slc_bulk_import_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slc_bulk_activities');
        Schema::dropIfExists('slc_bulk_updates');
        Schema::dropIfExists('slc_bulk_row_fields');
        Schema::dropIfExists('slc_bulk_import_rows');
        Schema::dropIfExists('slc_bulk_batches');
        Schema::dropIfExists('slc_bulk_imports');
    }
};
