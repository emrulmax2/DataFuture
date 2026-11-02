<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * /slc-registration › Bulk payment upload (user, 2 Nov 2026): the SLC remittance file as uploaded
 * (kept on the documents disk) and one row per payment line, with what staff decided for it and the
 * money receipt it became. Replaces nothing: Reports › Accounts › Payments Upload stays as it is.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slc_payment_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('disk', 20);
            $table->string('path');
            $table->string('header')->nullable();
            $table->string('hei', 20)->nullable();
            $table->string('hei_name')->nullable();
            $table->date('remittance_date')->nullable();
            $table->unsignedInteger('header_count')->nullable();
            $table->decimal('header_total', 12, 2)->nullable();
            $table->unsignedInteger('line_count')->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->json('error_lines')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        Schema::create('slc_payment_upload_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slc_payment_upload_id')->constrained('slc_payment_uploads')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->text('raw');
            $table->json('data');
            $table->string('ssn', 50)->index();
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('match_method', 10)->nullable();          // ssn | linked
            $table->unsignedBigInteger('linked_by')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->unsignedBigInteger('chosen_agreement_id')->nullable(); // staff's choice (recorded as a forced entry)
            $table->unsignedBigInteger('agreement_by')->nullable();
            $table->timestamp('agreement_at')->nullable();
            $table->string('skip_reason')->nullable();
            $table->unsignedBigInteger('skipped_by')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->unsignedBigInteger('slc_money_receipt_id')->nullable();
            $table->unsignedBigInteger('slc_installment_id')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slc_payment_upload_rows');
        Schema::dropIfExists('slc_payment_uploads');
    }
};
