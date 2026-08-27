<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for the applicant-to-student conversion: the chain of
 * ProcessStudent* jobs dispatched from AdmissionController when an applicant
 * reaches status 7 (Offer Accepted). One row per job per batch — seeded as
 * "queued" at dispatch and moved through processing/completed/failed/cancelled
 * by StudentConversionLogSubscriber.
 *
 * Deliberately no foreign keys: the log must survive the applicant or student
 * rows being removed later.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('student_conversion_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('applicant_id')->unsigned()->nullable();
            $table->bigInteger('student_id')->unsigned()->nullable();
            // Sized so the composite unique below stays under the 767-byte
            // utf8mb4 index limit (batch ids are 36-char UUIDs; the longest
            // job class name is 54 chars).
            $table->string('batch_id', 36);
            $table->string('job_class', 150);
            $table->string('job_name', 191);
            $table->string('status', 20)->default('queued');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('message')->nullable();
            $table->longText('exception')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            // One row per job per batch; also what the subscriber and the
            // progress endpoint look rows up by.
            $table->unique(['batch_id', 'job_class'], 'student_conversion_logs_batch_job_unique');
            // The Conversion Log tab reads "all rows for this applicant".
            $table->index(['applicant_id'], 'student_conversion_logs_applicant_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_conversion_logs');
    }
};
