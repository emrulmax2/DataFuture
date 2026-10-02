<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Policy Assessments: the question bank new staff are tested on.
 *
 * The college publishes its policies as PDFs, grouped into categories. HR
 * assigns policies to a member of staff, who reads each one and then passes a
 * short multiple-choice test on it. Passing is how HR knows the policy was
 * actually read, not just linked to.
 *
 *   policy_categories        Corporate / Academic / Student Policies
 *   policy_documents         one row per policy (the PDF), with its test rules
 *   policy_questions         the question bank for a policy, each question at
 *                            one level (beginner / intermediate / expert)
 *   policy_question_options  the choices for a question, exactly one correct
 *   policy_assignments       "this person must pass this policy at this level",
 *                            one live row per (employee, policy, level) —
 *                            enforced in code, since a unique index would also
 *                            block soft-deleted rows
 *   policy_attempts          one sitting of the test
 *   policy_attempt_events    each time the test tab or window was left during a sitting
 *   policy_attempt_answers   the questions drawn for a sitting, snapshotted so
 *                            the attempt can be reviewed exactly as shown even
 *                            after HR edits the bank
 *   policy_badges            bronze / silver / gold, earned by passing a policy
 *                            at a level; one live badge per (employee, policy,
 *                            level), revoked by soft delete
 *   policy_roles             a job role as HR assigns by it ("Lecturer",
 *                            "Finance") — nothing to do with the roles table
 *                            of the permission system
 *   policy_role_documents    the policies ticked for a role
 *
 * The level columns and policy_badges, the time limit columns, and the roles
 * were added after this migration first ran on development databases;
 * 2026_09_30_100003, 2026_10_01_100001 and 2026_10_01_100002 bring those up
 * to date and do nothing on a database this file created.
 *
 * No foreign key constraints, as elsewhere in this schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(1);

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('sort_order', 'pc_sort_idx');
        });

        Schema::create('policy_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('policy_category_id')->unsigned();
            $table->string('title', 191);
            /* The website's own slug. The seeder matches on it so a re-run
               finds the policy it created instead of adding a second one. */
            $table->string('slug', 191)->nullable();
            $table->string('version', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('pdf_url', 500)->nullable();
            $table->string('page_url', 500)->nullable();
            $table->string('thumbnail_url', 500)->nullable();

            /* Test rules. Each attempt snapshots the pass mark, so changing it
               later does not rewrite anyone's past result. */
            $table->unsignedTinyInteger('pass_mark')->default(80);
            $table->unsignedSmallInteger('questions_per_attempt')->default(10);
            /* NULL means unlimited attempts. */
            $table->unsignedSmallInteger('max_attempts')->nullable();
            /* Minutes allowed per attempt. NULL means the test is not timed. */
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(1);

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('policy_category_id', 'pd_category_idx');
            $table->index('slug', 'pd_slug_idx');
        });

        Schema::create('policy_questions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('policy_document_id')->unsigned();
            $table->text('question');
            /* HR-only: never sent to staff. */
            $table->text('explanation')->nullable();
            $table->text('source_excerpt')->nullable();
            /* 'manual' or 'ai_draft'. Drafts arrive inactive for HR to review. */
            $table->string('origin', 20)->default('manual');
            /* 'beginner' | 'intermediate' | 'expert' (App\Support\PolicyLevel).
               A test at a level draws only that level's questions. */
            $table->string('level', 20)->default('beginner');
            /* Stable identity of a seeded question, '<policy slug>#<n>', so the
               seeder can add new questions without duplicating or reviving
               ones HR edited or deleted. NULL for questions HR wrote. */
            $table->string('seed_key', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(1);

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['policy_document_id', 'is_active'], 'pq_policy_idx');
            $table->index(['policy_document_id', 'level', 'is_active'], 'pq_policy_level_idx');
            $table->index('seed_key', 'pq_seed_key_idx');
        });

        Schema::create('policy_question_options', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('policy_question_id')->unsigned();
            $table->text('option_text');
            $table->boolean('is_correct')->default(0);
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('policy_question_id', 'pqo_question_idx');
        });

        Schema::create('policy_assignments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->bigInteger('policy_document_id')->unsigned();
            /* The level the test is set at; chosen by HR when assigning. */
            $table->string('level', 20)->default('beginner');
            /* The policy role it was assigned through. NULL when HR picked the
               policy by hand. Never rewritten when the role's ticks change. */
            $table->bigInteger('policy_role_id')->unsigned()->nullable();
            /* users.id of whoever assigned it. */
            $table->bigInteger('assigned_by')->unsigned()->nullable();
            $table->dateTime('assigned_at');
            $table->date('due_date')->nullable();

            /* 'pending' | 'failed' | 'passed'. "In progress", "overdue" and
               "no attempts left" are derived on the model, not stored. */
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('attempts_count')->default(0);
            /* Retakes HR has granted on top of the policy's max_attempts. */
            $table->unsignedSmallInteger('extra_attempts')->default(0);
            $table->decimal('best_score', 5, 2)->nullable();
            $table->decimal('last_score', 5, 2)->nullable();
            $table->dateTime('last_attempt_at')->nullable();
            $table->dateTime('passed_at')->nullable();

            /* Evidence of reading: when the PDF link was opened, and when the
               "I have read and understood" declaration was ticked. */
            $table->dateTime('policy_opened_at')->nullable();
            $table->dateTime('acknowledged_at')->nullable();
            $table->dateTime('last_reminded_at')->nullable();
            $table->text('note')->nullable();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'policy_document_id'], 'pa_emp_policy_idx');
            $table->index(['employee_id', 'policy_document_id', 'level'], 'pa_emp_policy_level_idx');
            $table->index('policy_document_id', 'pa_policy_idx');
            $table->index(['status', 'due_date'], 'pa_status_due_idx');
            $table->index('policy_role_id', 'pa_role_idx');
        });

        Schema::create('policy_attempts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('policy_assignment_id')->unsigned();
            $table->bigInteger('employee_id')->unsigned();
            $table->bigInteger('policy_document_id')->unsigned();
            /* Snapshot of the assignment's level: the level drawn from. */
            $table->string('level', 20)->default('beginner');
            $table->unsignedSmallInteger('attempt_no');
            /* 'in_progress' | 'submitted'. */
            $table->string('status', 20)->default('in_progress');
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('correct_count')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            /* Snapshot of the policy's pass mark when the attempt started. */
            $table->unsignedTinyInteger('pass_mark');
            $table->boolean('passed')->nullable();
            /* Snapshot of the policy's time limit when the attempt started, and
               the moment it runs out. Both NULL for an untimed test. */
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();
            $table->dateTime('expires_at')->nullable();
            /* The clock ran out before every question was answered. */
            $table->boolean('timed_out')->default(0);
            /* How often the test tab or window was left during the attempt, and
               for how long in total (see policy_attempt_events). */
            $table->unsignedSmallInteger('tab_exits')->default(0);
            $table->unsignedInteger('away_seconds')->default(0);
            $table->string('ip_address', 45)->nullable();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['policy_assignment_id', 'status'], 'patt_assignment_idx');
            $table->index(['status', 'expires_at'], 'patt_status_expires_idx');
            $table->index('employee_id', 'patt_employee_idx');
        });

        Schema::create('policy_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('policy_attempt_id')->unsigned();
            $table->bigInteger('policy_question_id')->unsigned();
            $table->unsignedSmallInteger('sort_order')->default(0);

            /* Everything below the question id is copied at draw time, so the
               attempt still reads as it was shown if the bank is edited. */
            $table->text('question_text');
            /* The drawn question's level: a test mixes the three levels. */
            $table->string('question_level', 20)->nullable();
            /* JSON array of option ids in the shuffled display order. */
            $table->text('option_order')->nullable();
            /* JSON [{id, text}] in display order. */
            $table->longText('options_snapshot')->nullable();
            $table->bigInteger('correct_option_id')->unsigned()->nullable();
            $table->text('correct_option_text')->nullable();

            $table->bigInteger('selected_option_id')->unsigned()->nullable();
            $table->text('selected_option_text')->nullable();
            $table->boolean('is_correct')->nullable();

            $table->timestamps();

            $table->index('policy_attempt_id', 'paa_attempt_idx');
        });

        Schema::create('policy_attempt_events', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('policy_attempt_id')->unsigned();
            /* 'tab' = switched tab or minimised the browser; 'window' = clicked outside the test window. */
            $table->string('type', 20)->default('window');
            /* The question on screen when they left; NULL on the review screen. */
            $table->unsignedSmallInteger('question_no')->nullable();
            $table->dateTime('left_at');
            /* NULL while they are still away. */
            $table->dateTime('returned_at')->nullable();
            $table->unsignedInteger('seconds')->nullable();
            $table->timestamps();

            $table->index(['policy_attempt_id', 'returned_at'], 'pae_attempt_idx');
        });

        Schema::create('policy_badges', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->bigInteger('policy_document_id')->unsigned();
            /* 'beginner' (bronze) | 'intermediate' (silver) | 'expert' (gold). */
            $table->string('level', 20);
            /* Where it was earned. Kept after the assignment is archived. */
            $table->bigInteger('policy_assignment_id')->unsigned()->nullable();
            $table->bigInteger('policy_attempt_id')->unsigned()->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->dateTime('awarded_at');
            /* users.id of whoever revoked it (the row is then soft-deleted). */
            $table->bigInteger('revoked_by')->unsigned()->nullable();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('employee_id', 'pb_employee_idx');
            $table->index(['employee_id', 'policy_document_id', 'level'], 'pb_emp_policy_level_idx');
        });

        Schema::create('policy_roles', function (Blueprint $table) {
            $table->id();
            /* Unique among live roles — checked in the request, since a unique
               index would also count archived ones. */
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            /* An inactive role cannot be picked when assigning. */
            $table->boolean('is_active')->default(1);

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('sort_order', 'pr_sort_idx');
        });

        /* Plain pivot: a tick is removed by deleting its row. Archiving a role
           leaves its rows here, so restoring it brings the ticks back. One row
           per (role, policy) — kept so in code, not by a unique index. */
        Schema::create('policy_role_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('policy_role_id')->unsigned();
            $table->bigInteger('policy_document_id')->unsigned();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();

            $table->index(['policy_role_id', 'policy_document_id'], 'prd_role_policy_idx');
            $table->index('policy_document_id', 'prd_policy_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_role_documents');
        Schema::dropIfExists('policy_roles');
        Schema::dropIfExists('policy_badges');
        Schema::dropIfExists('policy_attempt_events');
        Schema::dropIfExists('policy_attempt_answers');
        Schema::dropIfExists('policy_attempts');
        Schema::dropIfExists('policy_assignments');
        Schema::dropIfExists('policy_question_options');
        Schema::dropIfExists('policy_questions');
        Schema::dropIfExists('policy_documents');
        Schema::dropIfExists('policy_categories');
    }
};
