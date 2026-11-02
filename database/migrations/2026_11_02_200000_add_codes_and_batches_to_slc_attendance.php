<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk attendance, steps 5–7 (user, 2 Nov 2026): the code chosen for each line, its confirmation
 * into a batch, the CoC queued for a code that needs one, and the batches downloaded as the SIS
 * attendance import file.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slc_attendance_batches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->enum('status', ['awaiting', 'downloaded', 'uploaded'])->default('awaiting');
            $table->string('header');
            $table->string('term', 10)->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
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

        Schema::table('slc_attendance_import_rows', function (Blueprint $table) {
            $table->string('attendance_code', 2)->nullable()->after('linked_at');     // staff's choice (else the suggestion)
            $table->unsignedBigInteger('code_by')->nullable()->after('attendance_code');
            $table->timestamp('code_at')->nullable()->after('code_by');
            $table->string('confirmed_code', 2)->nullable()->after('code_at');       // the code that went into the batch
            $table->unsignedBigInteger('confirmed_by')->nullable()->after('confirmed_code');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            $table->foreignId('slc_attendance_batch_id')->nullable()->after('confirmed_at')->constrained('slc_attendance_batches')->nullOnDelete();
            $table->unsignedBigInteger('earlier_batch_id')->nullable()->after('slc_attendance_batch_id');
            $table->unsignedBigInteger('slc_coc_id')->nullable()->after('earlier_batch_id'); // the CoC queued for the code
            $table->unsignedBigInteger('carried_from_id')->nullable()->after('slc_coc_id');
        });
    }

    public function down(): void
    {
        Schema::table('slc_attendance_import_rows', function (Blueprint $table) {
            $table->dropConstrainedForeignId('slc_attendance_batch_id');
            $table->dropColumn(['attendance_code', 'code_by', 'code_at', 'confirmed_code', 'confirmed_by', 'confirmed_at', 'earlier_batch_id', 'slc_coc_id', 'carried_from_id']);
        });
        Schema::dropIfExists('slc_attendance_batches');
    }
};
