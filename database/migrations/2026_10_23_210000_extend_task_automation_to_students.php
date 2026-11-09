<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Task Automation for students (Site settings > Task Automation > Student).
 *
 *  - student_tasks gets the same assigned_via / rule_id pair applicant_tasks has, so an
 *    automatically assigned student task says which rule gave it;
 *  - a run can now be for a student: task_assignment_runs.applicant_id becomes nullable
 *    and student_id is added;
 *  - the two hard-wired student assignments (now commented out in the code) used to be
 *    seeded as rules; that seeding is switched off below, staff add their own rules:
 *      1. every task of the Live process marked "Auto feed" (ProcessStudentTask gave a new
 *         student all of them) → one "Student created" rule per task, no conditions;
 *      2. every task of the process attached to a student status (studentUpdateStatus gave
 *         them on a change to that status) → one "Student status changed" rule per task,
 *         with the condition "Status is <that status>".
 */
return new class extends Migration
{
    private const NOTE_AUTO_FEED = 'Migrated from the "%s" process (Auto feed): every new student gets this task.';
    private const NOTE_STATUS = 'Migrated from the "%s" process attached to the %s status.';

    public function up(): void
    {
        Schema::table('student_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('student_tasks', 'assigned_via')) {
                $table->string('assigned_via', 20)->nullable()->after('status');
            }
            if (!Schema::hasColumn('student_tasks', 'rule_id')) {
                $table->unsignedBigInteger('rule_id')->nullable()->after('assigned_via');
            }
        });

        Schema::table('task_assignment_runs', function (Blueprint $table) {
            $table->unsignedBigInteger('applicant_id')->nullable()->change();
            if (!Schema::hasColumn('task_assignment_runs', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('applicant_id');
                $table->index(['student_id', 'created_at'], 'task_assignment_runs_student_index');
            }
        });

        /*
         * Seeding switched off (user, 23 Oct 2026: "clear them, I will add rules"): the
         * student half starts empty and staff add their own rules. Kept for reference,
         * not run — this is what reproduced the old Auto feed / status-process tasks.
         *
        $now = now();
        $systemUser = (int) config('tasks.system_user_id', 1);
        $taskIds = fn ($processId) => DB::table('task_lists')->where('process_list_id', $processId)->whereNull('deleted_at')->orderBy('id')->pluck('id');
        $exists = function (string $trigger, int $taskId, ?int $statusId = null) {
            $q = DB::table('task_assignment_rules')->where('trigger', $trigger)->where('task_list_id', $taskId)->whereNull('deleted_at');
            if ($statusId !== null) {
                $q->whereExists(fn ($c) => $c->select(DB::raw(1))->from('task_assignment_rule_conditions')
                    ->whereColumn('task_assignment_rule_conditions.rule_id', 'task_assignment_rules.id')
                    ->where('field', 'status_id')->where('operator', 'is')->whereRaw('JSON_UNQUOTE(`value`) = ?', [(string) $statusId]));
            }

            return $q->exists();
        };

        // 1. the Auto feed process (ProcessStudentTask picked the newest one)
        $auto = DB::table('process_lists')->where('auto_feed', 'Yes')->where('phase', 'Live')->whereNull('deleted_at')->orderByDesc('id')->first();
        if ($auto) {
            foreach ($taskIds($auto->id)->values() as $i => $taskId) {
                if ($exists('student_created', (int) $taskId)) {
                    continue;
                }
                DB::table('task_assignment_rules')->insert([
                    'task_list_id' => $taskId, 'trigger' => 'student_created', 'exclusive_group' => null,
                    'priority' => ($i + 1) * 10, 'active' => 1, 'notes' => sprintf(self::NOTE_AUTO_FEED, $auto->name),
                    'after_assign_job' => null, 'created_by' => $systemUser, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // 2. processes attached to student statuses
        $statuses = DB::table('statuses')->where('type', 'Student')->where('process_list_id', '>', 0)->whereNull('deleted_at')->get(['id', 'name', 'process_list_id']);
        foreach ($statuses as $status) {
            $process = DB::table('process_lists')->where('id', $status->process_list_id)->first();
            foreach ($taskIds($status->process_list_id)->values() as $i => $taskId) {
                if ($exists('student_status_changed', (int) $taskId, (int) $status->id)) {
                    continue;
                }
                $ruleId = DB::table('task_assignment_rules')->insertGetId([
                    'task_list_id' => $taskId, 'trigger' => 'student_status_changed', 'exclusive_group' => null,
                    'priority' => ($i + 1) * 10, 'active' => 1, 'notes' => sprintf(self::NOTE_STATUS, $process->name ?? 'process', $status->name),
                    'after_assign_job' => null, 'created_by' => $systemUser, 'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('task_assignment_rule_conditions')->insert([
                    'rule_id' => $ruleId, 'field' => 'status_id', 'operator' => 'is', 'value' => json_encode((string) $status->id),
                    'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
         */
    }

    public function down(): void
    {
        $ids = DB::table('task_assignment_rules')->where('notes', 'like', 'Migrated from the %')->whereIn('trigger', ['student_created', 'student_status_changed'])->pluck('id');
        DB::table('task_assignment_rule_conditions')->whereIn('rule_id', $ids)->delete();
        DB::table('task_assignment_rules')->whereIn('id', $ids)->delete();

        Schema::table('task_assignment_runs', function (Blueprint $table) {
            if (Schema::hasColumn('task_assignment_runs', 'student_id')) {
                $table->dropIndex('task_assignment_runs_student_index');
                $table->dropColumn('student_id');
            }
        });
        Schema::table('student_tasks', function (Blueprint $table) {
            $table->dropColumn(['assigned_via', 'rule_id']);
        });
    }
};
