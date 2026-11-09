<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance Integrity Monitoring (aim_*): detection results, scans, cases and
 * the access log. New tables only; nothing existing is altered.
 *
 * No foreign keys, so removing a student, user or class never deletes findings,
 * cases or their history. Times are UK clock time in DATETIME columns.
 */
return new class extends Migration
{
    public function up()
    {
        // One row per anomaly a detection rule found. Re-scans update the same
        // row (unique fingerprint), so a finding is never duplicated.
        if (! Schema::hasTable('aim_findings')) {
            Schema::create('aim_findings', function (Blueprint $table) {
                $table->id();
                $table->string('rule', 30);
                $table->string('type', 60);
                // fact | anomaly | unavailable — how much the finding proves.
                $table->string('basis', 20)->default('anomaly');
                $table->unsignedSmallInteger('weight')->default(0);
                // Findings sharing an event key describe the same underlying event;
                // scoring counts only the highest weight per event.
                $table->string('event_key', 120);
                $table->char('fingerprint', 40)->unique('aim_findings_fingerprint_unique');

                $table->bigInteger('term_declaration_id')->unsigned()->nullable();
                $table->bigInteger('staff_user_id')->unsigned()->nullable();
                $table->bigInteger('student_id')->unsigned()->nullable();
                $table->bigInteger('plan_id')->unsigned()->nullable();
                $table->bigInteger('plans_date_list_id')->unsigned()->nullable();
                $table->bigInteger('attendance_id')->unsigned()->nullable();

                $table->dateTime('event_at')->nullable();
                $table->unsignedInteger('rows_count')->default(1);
                $table->string('summary', 500);
                $table->json('evidence')->nullable();

                // open | dismissed | case
                $table->string('status', 20)->default('open');
                $table->bigInteger('case_id')->unsigned()->nullable();
                $table->bigInteger('dismissed_by')->unsigned()->nullable();
                $table->dateTime('dismissed_at')->nullable();
                $table->string('dismiss_note', 500)->nullable();

                $table->dateTime('first_detected_at');
                $table->dateTime('last_detected_at');
                // Set when a later scan no longer finds it (the data changed).
                $table->dateTime('cleared_at')->nullable();
                $table->bigInteger('scan_run_id')->unsigned()->nullable();

                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->index(['term_declaration_id', 'rule'], 'aim_findings_term_rule_index');
                $table->index(['staff_user_id', 'term_declaration_id'], 'aim_findings_staff_index');
                $table->index(['student_id', 'term_declaration_id'], 'aim_findings_student_index');
                $table->index('attendance_id', 'aim_findings_attendance_index');
                $table->index('plans_date_list_id', 'aim_findings_session_index');
                $table->index(['status', 'cleared_at'], 'aim_findings_status_index');
                $table->index('event_at', 'aim_findings_event_at_index');
            });
        }

        // Each historical or daily scan; the UI's "Scan term" button queues one
        // here and the scheduler picks it up.
        if (! Schema::hasTable('aim_scan_runs')) {
            Schema::create('aim_scan_runs', function (Blueprint $table) {
                $table->id();
                // term | daily
                $table->string('scope', 20);
                $table->bigInteger('term_declaration_id')->unsigned()->nullable();
                // pending | running | completed | failed
                $table->string('status', 20)->default('pending');
                $table->bigInteger('requested_by')->unsigned()->nullable();
                $table->dateTime('requested_at');
                $table->dateTime('started_at')->nullable();
                $table->dateTime('finished_at')->nullable();
                $table->unsignedInteger('findings_new')->default(0);
                $table->unsignedInteger('findings_updated')->default(0);
                $table->unsignedInteger('findings_cleared')->default(0);
                $table->json('stats')->nullable();
                $table->text('error')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->index(['status', 'requested_at'], 'aim_scan_runs_status_index');
                $table->index('term_declaration_id', 'aim_scan_runs_term_index');
            });
        }

        if (! Schema::hasTable('aim_cases')) {
            Schema::create('aim_cases', function (Blueprint $table) {
                $table->id();
                $table->string('case_number', 30)->unique('aim_cases_number_unique');
                $table->bigInteger('term_declaration_id')->unsigned()->nullable();
                // staff | student | class | other
                $table->string('subject_type', 20);
                $table->bigInteger('subject_id')->unsigned()->nullable();
                $table->string('subject_label', 255)->nullable();
                $table->integer('risk_score')->default(0);
                $table->string('summary', 500);
                $table->text('evidence')->nullable();
                $table->bigInteger('assigned_investigator')->unsigned()->nullable();
                $table->string('investigation_status', 40)->default('New');
                $table->text('investigator_notes')->nullable();
                $table->bigInteger('reviewed_by')->unsigned()->nullable();
                $table->dateTime('reviewed_at')->nullable();
                $table->text('outcome')->nullable();
                $table->bigInteger('created_by')->unsigned()->nullable();
                $table->bigInteger('updated_by')->unsigned()->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->index(['investigation_status', 'created_at'], 'aim_cases_status_index');
                $table->index(['subject_type', 'subject_id'], 'aim_cases_subject_index');
                $table->index('assigned_investigator', 'aim_cases_investigator_index');
            });
        }

        if (! Schema::hasTable('aim_case_findings')) {
            Schema::create('aim_case_findings', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('case_id')->unsigned();
                $table->bigInteger('finding_id')->unsigned();
                $table->bigInteger('added_by')->unsigned()->nullable();
                $table->dateTime('created_at')->nullable();

                $table->unique(['case_id', 'finding_id'], 'aim_case_findings_unique');
                $table->index('finding_id', 'aim_case_findings_finding_index');
            });
        }

        // The case's history. Append-only: the model refuses updates and deletes.
        if (! Schema::hasTable('aim_case_activities')) {
            Schema::create('aim_case_activities', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('case_id')->unsigned();
                // created | status | assigned | note | evidence | findings | outcome | review
                $table->string('type', 30);
                $table->text('body')->nullable();
                $table->json('meta')->nullable();
                $table->bigInteger('created_by')->unsigned()->nullable();
                $table->string('created_by_name', 191)->nullable();
                $table->dateTime('created_at')->nullable();

                $table->index(['case_id', 'id'], 'aim_case_activities_case_index');
            });
        }

        // Files added to a case. Append-only; kept on the private local disk.
        if (! Schema::hasTable('aim_case_evidence')) {
            Schema::create('aim_case_evidence', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('case_id')->unsigned();
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->string('file_path', 500)->nullable();
                $table->string('file_name', 255)->nullable();
                $table->string('mime', 120)->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->char('sha256', 64)->nullable();
                $table->bigInteger('uploaded_by')->unsigned()->nullable();
                $table->dateTime('created_at')->nullable();

                $table->index('case_id', 'aim_case_evidence_case_index');
            });
        }

        // Who looked at or exported what (UK GDPR access logging).
        if (! Schema::hasTable('aim_access_logs')) {
            Schema::create('aim_access_logs', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id')->unsigned()->nullable();
                // view | export | scan | settings | case
                $table->string('action', 20);
                $table->string('route', 150)->nullable();
                $table->string('subject_type', 20)->nullable();
                $table->bigInteger('subject_id')->unsigned()->nullable();
                $table->json('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->dateTime('created_at')->nullable();

                $table->index(['user_id', 'created_at'], 'aim_access_logs_user_index');
                $table->index(['subject_type', 'subject_id'], 'aim_access_logs_subject_index');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('aim_access_logs');
        Schema::dropIfExists('aim_case_evidence');
        Schema::dropIfExists('aim_case_activities');
        Schema::dropIfExists('aim_case_findings');
        Schema::dropIfExists('aim_cases');
        Schema::dropIfExists('aim_scan_runs');
        Schema::dropIfExists('aim_findings');
    }
};
