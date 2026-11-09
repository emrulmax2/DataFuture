<?php

namespace App\Services\StudentEmail;

use App\Models\StudentTask;

/**
 * Finds a student's task by the action its Task Automation rule runs ("After assigning,
 * run"), not by task list id, which differs between installs (user, 26 Oct 2026).
 * student_tasks.rule_id records the rule that assigned the task.
 */
class StudentTaskActions
{
    /** The student's newest task assigned by a rule whose action is $jobClass; only an unfinished one if asked. */
    public static function task(int $studentId, string $jobClass, bool $openOnly = false): ?StudentTask
    {
        $query = StudentTask::query()
            ->join('task_assignment_rules', 'task_assignment_rules.id', '=', 'student_tasks.rule_id')
            ->where('student_tasks.student_id', $studentId)
            ->where('task_assignment_rules.after_assign_job', $jobClass)
            ->select('student_tasks.*')
            ->orderByDesc('student_tasks.id');
        if ($openOnly) {
            $query->where('student_tasks.status', '!=', 'Completed');
        }

        return $query->first();
    }
}
