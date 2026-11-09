<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance Integrity: the change history of `attendances`.
 *
 * One row per create / update / delete / restore of an attendance row, written
 * by App\Services\AttendanceIntegrity\AttendanceAudit in the same database
 * transaction as the change. History starts the day this is deployed; nothing
 * before that is invented or back-filled.
 *
 * Append-only: the model refuses updates and deletes, and there are no foreign
 * keys, so deleting a student, user or class can never cascade into the history.
 * Times are UK clock time in DATETIME columns (no time-zone conversion).
 *
 * `attendance_audit_seals` holds the nightly hash chain over the log, so a row
 * edited or removed directly in the database afterwards shows up on verification.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('attendance_audit_logs')) {
            Schema::create('attendance_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('attendance_id')->unsigned();
                $table->bigInteger('student_id')->unsigned()->nullable();
                $table->bigInteger('plan_id')->unsigned()->nullable();
                $table->bigInteger('plans_date_list_id')->unsigned()->nullable();

                // created | updated | deleted | restored
                $table->string('action', 20);
                // Which screen or process made the change (see AttendanceAudit::SOURCES).
                $table->string('source', 40)->nullable();

                $table->unsignedSmallInteger('old_status_id')->nullable();
                $table->unsignedSmallInteger('new_status_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('changed_fields', 500)->nullable();

                // Every row written by one request or process shares a batch id.
                $table->char('batch_id', 36)->nullable();

                $table->bigInteger('changed_by')->unsigned()->nullable();
                $table->dateTime('changed_at');
                $table->dateTime('class_ends_at')->nullable();
                $table->integer('minutes_after_class')->nullable();
                $table->boolean('is_late_correction')->default(false);
                $table->text('change_reason')->nullable();

                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->string('route', 150)->nullable();

                $table->dateTime('created_at')->nullable();

                $table->index('attendance_id', 'aal_attendance_index');
                $table->index(['student_id', 'changed_at'], 'aal_student_index');
                $table->index('plans_date_list_id', 'aal_session_index');
                $table->index(['changed_by', 'changed_at'], 'aal_changed_by_index');
                $table->index('changed_at', 'aal_changed_at_index');
                $table->index('batch_id', 'aal_batch_index');
            });
        }

        if (! Schema::hasTable('attendance_audit_seals')) {
            Schema::create('attendance_audit_seals', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('from_log_id')->unsigned();
                $table->bigInteger('to_log_id')->unsigned();
                $table->unsignedInteger('row_count');
                $table->char('previous_hash', 64)->nullable();
                $table->char('chain_hash', 64);
                $table->dateTime('sealed_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('attendance_audit_seals');
        Schema::dropIfExists('attendance_audit_logs');
    }
};
