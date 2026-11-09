<?php

namespace App\Services\TaskAssignment;

use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskLog;
use App\Models\Student;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Models\TaskAssignmentRule;
use App\Models\TaskAssignmentRun;
use App\Models\TaskList;
use Illuminate\Support\Facades\DB;

/**
 * Applies the task assignment rules to one applicant (plan / apply) or one
 * student (planStudent / applyStudent).
 *
 * plan() works everything out and touches nothing; apply() records a run
 * and, when live, creates the applicant_tasks rows the plan called for. The
 * student pair does the same with student facts (StudentFacts) and
 * student_tasks; the decisions below are shared.
 *
 * The decisions, in order:
 *  1. every active rule for the trigger is tested - all of its conditions
 *     must hold against the applicant's facts;
 *  2. among matched rules sharing an exclusive group, the lowest priority
 *     number (then lowest id) wins and the rest lose the group;
 *  3. a task the applicant already holds is left alone, and a task a member
 *     of staff removed (soft-deleted) is never put back - that was a human
 *     decision, and this engine does not overrule one.
 */
class TaskAssigner
{
    public const TRIGGER_SUBMITTED = 'application_submitted';
    public const TRIGGER_STUDENT_CREATED = 'student_created';
    public const TRIGGER_STUDENT_STATUS_CHANGED = 'student_status_changed';

    public const ASSIGN = 'assign';
    public const NO_MATCH = 'no_match';
    public const LOST_GROUP = 'lost_group';
    public const EXISTS = 'exists';
    public const REMOVED_BY_STAFF = 'removed_by_staff';
    public const DUPLICATE = 'duplicate';

    public function plan(Applicant $applicant, string $trigger = self::TRIGGER_SUBMITTED): array
    {
        $facts = ApplicationFacts::build($applicant);
        $existing = ApplicantTask::where('applicant_id', $applicant->id)->pluck('task_list_id')->map(fn($v) => (int) $v)->all();
        $removed = ApplicantTask::onlyTrashed()->where('applicant_id', $applicant->id)->pluck('task_list_id')->map(fn($v) => (int) $v)->all();
        // labels for both kinds of applicant action (completion ones run later, TaskCompletionActions)
        $decided = $this->evaluate($trigger, $facts, ApplicationFacts::catalogue(), $existing, $removed, (array) config('tasks.assignment_jobs', []) + (array) config('tasks.completion_jobs', []));

        return [
            'applicant_id' => $applicant->id,
            'trigger' => $trigger,
            'facts' => $facts,
            'evaluations' => $decided['evaluations'],
            'assign' => $decided['assign'], /* task_list_id => rule_id */
        ];
    }

    /** The same plan for a student; $context carries what the trigger knows (previous_status_id). */
    public function planStudent(Student $student, string $trigger = self::TRIGGER_STUDENT_CREATED, array $context = []): array
    {
        $facts = StudentFacts::build($student, $context);
        $existing = StudentTask::where('student_id', $student->id)->pluck('task_list_id')->map(fn($v) => (int) $v)->all();
        $removed = StudentTask::onlyTrashed()->where('student_id', $student->id)->pluck('task_list_id')->map(fn($v) => (int) $v)->all();
        $decided = $this->evaluate($trigger, $facts, StudentFacts::catalogue(), $existing, $removed, (array) config('tasks.student_assignment_jobs', []));

        return [
            'student_id' => $student->id,
            'trigger' => $trigger,
            'facts' => $facts,
            'evaluations' => $decided['evaluations'],
            'assign' => $decided['assign'], /* task_list_id => rule_id */
        ];
    }

    /** Steps 1–3 for one subject's facts and tasks. */
    private function evaluate(string $trigger, array $facts, array $catalogue, array $existing, array $removed, array $jobRegistry): array
    {
        $rules = TaskAssignmentRule::with(['task', 'conditions'])
            ->active()->forTrigger($trigger)
            ->orderBy('priority')->orderBy('id')->get();

        /* 1. test every rule */
        $evaluations = [];
        foreach ($rules as $rule) {
            $conditions = [];
            $matched = true;
            foreach ($rule->conditions as $c) {
                $ok = ApplicationFacts::test($facts, $c->field, $c->operator, $c->value);
                $conditions[] = ['text' => ApplicationFacts::describe($c->field, $c->operator, $c->value, $catalogue), 'ok' => $ok];
                $matched = $matched && $ok;
            }
            $evaluations[] = [
                'rule_id' => $rule->id,
                'task_list_id' => (int) $rule->task_list_id,
                'task' => optional($rule->task)->name,
                'group' => $rule->exclusive_group,
                'priority' => $rule->priority,
                'conditions' => $conditions,
                'matched' => $matched,
                'outcome' => $matched ? self::ASSIGN : self::NO_MATCH,
                'job' => $rule->after_assign_job ?: null,
                'job_label' => $rule->after_assign_job ? ($jobRegistry[$rule->after_assign_job] ?? class_basename($rule->after_assign_job)) : null,
                'job_status' => null,
            ];
        }

        /* 2. one winner per exclusive group (rules arrive in priority, id order) */
        $groupWon = [];
        foreach ($evaluations as &$e) {
            if (!$e['matched'] || $e['group'] === null || $e['group'] === '') {
                continue;
            }
            if (isset($groupWon[$e['group']])) {
                $e['outcome'] = self::LOST_GROUP;
                continue;
            }
            $groupWon[$e['group']] = $e['rule_id'];
        }
        unset($e);

        /* 3. respect what is already there, and what staff took away */
        $assign = [];
        foreach ($evaluations as &$e) {
            if ($e['outcome'] !== self::ASSIGN) {
                continue;
            }
            if (in_array($e['task_list_id'], $existing, true)) {
                $e['outcome'] = self::EXISTS;
            } elseif (in_array($e['task_list_id'], $removed, true)) {
                $e['outcome'] = self::REMOVED_BY_STAFF;
            } elseif (isset($assign[$e['task_list_id']])) {
                $e['outcome'] = self::DUPLICATE;
            } else {
                $assign[$e['task_list_id']] = $e['rule_id'];
            }
        }
        unset($e);

        return ['evaluations' => $evaluations, 'assign' => $assign];
    }

    /**
     * Record a run and, when live, create the tasks. Returns the run.
     * $live overrides config('tasks.mode'); null means "as configured".
     */
    public function apply(Applicant $applicant, string $trigger = self::TRIGGER_SUBMITTED, ?bool $live = null): TaskAssignmentRun
    {
        $live = $live ?? (config('tasks.mode', 'live') === 'live');
        $plan = $this->plan($applicant, $trigger);
        $systemUser = (int) config('tasks.system_user_id', 1);

        $createdByRule = [];
        $run = DB::transaction(function () use ($applicant, $trigger, $live, $plan, $systemUser, &$createdByRule) {
            $createdIds = [];

            if ($live) {
                foreach ($plan['assign'] as $taskListId => $ruleId) {
                    $task = ApplicantTask::create([
                        'applicant_id' => $applicant->id,
                        'task_list_id' => $taskListId,
                        'status' => 'Pending',
                        'assigned_via' => 'auto',
                        'rule_id' => $ruleId,
                        'created_by' => $systemUser,
                    ]);
                    ApplicantTaskLog::create([
                        'applicant_tasks_id' => $task->id,
                        'actions' => 'Assigned',
                        'field_name' => 'rule_id',
                        'prev_field_value' => '',
                        'current_field_value' => (string) $ruleId,
                        'created_by' => $systemUser,
                    ]);
                    $createdIds[] = $task->id;
                    $createdByRule[$ruleId] = $task->id;
                }

                if ($createdIds && config('tasks.advance_status', false) && (int) $applicant->status_id < 3) {
                    Applicant::where('id', $applicant->id)->update(['status_id' => 3]);
                    ApplicantArchive::create([
                        'applicant_id' => $applicant->id,
                        'table' => 'applicants',
                        'field_name' => 'status_id',
                        'field_value' => $applicant->status_id,
                        'field_new_value' => '3',
                        'created_by' => $systemUser,
                    ]);
                }
            }

            return TaskAssignmentRun::create([
                'applicant_id' => $applicant->id,
                'trigger' => $trigger,
                'mode' => $live ? TaskAssignmentRun::MODE_LIVE : TaskAssignmentRun::MODE_DRY,
                'facts' => $plan['facts'],
                'evaluations' => $plan['evaluations'],
                'created_task_ids' => $createdIds,
                'tasks_created' => count($createdIds),
                'created_by' => $systemUser,
            ]);
        });

        /* Follow-up jobs run only for tasks this run actually created, and
           only once the rows are committed: a failing job must never undo an
           assignment. Which rule asked for which job is recorded on the run. */
        if ($live && $createdByRule) {
            $this->runFollowUps($applicant, $run, $createdByRule);
        }

        return $run;
    }

    private function runFollowUps(Applicant $applicant, TaskAssignmentRun $run, array $createdByRule): void
    {
        $this->dispatchFollowUps((int) $applicant->id, $run, $createdByRule, (array) config('tasks.assignment_jobs', []));
    }

    /** A student action that could not run, emailed to CONVERSION_ERROR_REPORT_TO (user, 30 Oct 2026). */
    private function reportStudentAction(bool $forStudent, int $studentId, array $e, int $taskId, string $problem, ?string $detail = null): void
    {
        if (!$forStudent) {
            return; // applicant rules are not part of the conversion report
        }
        \App\Services\Conversion\ConversionErrorReport::report('Action: '.($e['job_label'] ?? class_basename((string) $e['job'])), $problem, [
            'student_id' => $studentId, 'task_id' => $taskId, 'rule_id' => $e['rule_id'] ?? null, 'detail' => $detail,
        ]);
    }

    /** Jobs are constructed as new Job(<applicant or student id>, <task id>, <rule id>). */
    private function dispatchFollowUps(int $subjectId, TaskAssignmentRun $run, array $createdByRule, array $registry, bool $forStudent = false): void
    {
        $queue = config('tasks.jobs_run', 'sync') === 'queue';
        $evaluations = $run->evaluations;
        $touched = false;

        foreach ($evaluations as &$e) {
            if (empty($e['job']) || !isset($createdByRule[$e['rule_id']])) {
                continue;
            }
            $touched = true;
            // an action that runs when the task is completed, not now (config tasks.completion_jobs)
            if (isset(((array) config('tasks.completion_jobs', []))[$e['job']])) {
                $e['job_status'] = 'runs when the task is completed';
                continue;
            }
            if (!isset($registry[$e['job']]) || !class_exists($e['job'])) {
                $e['job_status'] = 'not registered';
                $this->reportStudentAction($forStudent, $subjectId, $e, (int) $createdByRule[$e['rule_id']], 'The rule\'s action is not registered in config tasks.student_assignment_jobs, so it did not run.');
                continue;
            }
            try {
                $job = new $e['job']($subjectId, $createdByRule[$e['rule_id']], $e['rule_id']);
                $queue ? dispatch($job) : dispatch_sync($job);
                $e['job_status'] = $queue ? 'queued' : 'ran';
            } catch (\Throwable $ex) {
                \Illuminate\Support\Facades\Log::error('Task follow-up job failed', ['rule_id' => $e['rule_id'], 'job' => $e['job'], 'error' => $ex->getMessage()]);
                $e['job_status'] = 'failed: ' . $ex->getMessage();
                $this->reportStudentAction($forStudent, $subjectId, $e, (int) $createdByRule[$e['rule_id']], 'The action failed: '.$ex->getMessage(), (string) $ex);
            }
        }
        unset($e);

        if ($touched) {
            $run->update(['evaluations' => $evaluations]);
        }
    }

    /**
     * The student counterpart of apply(): records a run (student_id set) and, when live,
     * creates student_tasks rows marked assigned_via = auto with their rule, each with an
     * "Assigned" line in the task log. $live overrides config('tasks.student_mode').
     */
    public function applyStudent(Student $student, string $trigger = self::TRIGGER_STUDENT_CREATED, array $context = [], ?bool $live = null): TaskAssignmentRun
    {
        $live = $live ?? (config('tasks.student_mode', 'live') === 'live');
        $plan = $this->planStudent($student, $trigger, $context);
        $systemUser = (int) config('tasks.system_user_id', 1);

        $createdByRule = [];
        $run = DB::transaction(function () use ($student, $trigger, $live, $plan, $systemUser, &$createdByRule) {
            $createdIds = [];

            if ($live && $plan['assign']) {
                $taskLists = TaskList::whereIn('id', array_keys($plan['assign']))->get()->keyBy('id');
                foreach ($plan['assign'] as $taskListId => $ruleId) {
                    $list = $taskLists[$taskListId] ?? null;
                    $task = StudentTask::create([
                        'student_id' => $student->id,
                        'task_list_id' => $taskListId,
                        // as the hard-wired assignments did: the task's link when it has one
                        'external_link_ref' => ($list && (int) $list->external_link === 1 && !empty($list->external_link_ref)) ? $list->external_link_ref : null,
                        'status' => 'Pending',
                        'assigned_via' => 'auto',
                        'rule_id' => $ruleId,
                        'created_by' => $systemUser,
                    ]);
                    StudentTaskLog::create([
                        'student_tasks_id' => $task->id,
                        'actions' => 'Assigned',
                        'field_name' => 'rule_id',
                        'prev_field_value' => '',
                        'current_field_value' => (string) $ruleId,
                        'created_by' => $systemUser,
                    ]);
                    $createdIds[] = $task->id;
                    $createdByRule[$ruleId] = $task->id;
                }
            }

            return TaskAssignmentRun::create([
                'applicant_id' => null,
                'student_id' => $student->id,
                'trigger' => $trigger,
                'mode' => $live ? TaskAssignmentRun::MODE_LIVE : TaskAssignmentRun::MODE_DRY,
                'facts' => $plan['facts'],
                'evaluations' => $plan['evaluations'],
                'created_task_ids' => $createdIds,
                'tasks_created' => count($createdIds),
                'created_by' => $systemUser,
            ]);
        });

        if ($live && $createdByRule) {
            $this->dispatchFollowUps((int) $student->id, $run, $createdByRule, (array) config('tasks.student_assignment_jobs', []), true);
        }

        return $run;
    }
}
