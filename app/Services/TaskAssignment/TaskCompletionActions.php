<?php

namespace App\Services\TaskAssignment;

use App\Models\ApplicantTask;
use App\Models\TaskAssignmentRule;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Applicant rule actions that run when a task is marked Completed (config('tasks.completion_jobs'),
 * e.g. "Update student photo"). The task's rule is found by the task itself: the rule that
 * assigned it (applicant_tasks.rule_id), or an active applicant rule for the same task, so a
 * task staff added by hand runs the action too. Never by a fixed task id.
 */
class TaskCompletionActions
{
    /** @return string[] what each action did, for the completion message */
    public function applicantTaskCompleted(int $applicantTaskId): array
    {
        $registry = (array) config('tasks.completion_jobs', []);
        $task = ApplicantTask::find($applicantTaskId);
        if (!$task || !$registry) {
            return [];
        }

        $rules = TaskAssignmentRule::forSubject(TaskAssignmentRule::SUBJECT_APPLICANT)
            ->whereIn('after_assign_job', array_keys($registry))
            ->where(function ($q) use ($task) {
                $q->where('id', (int) $task->rule_id)
                    ->orWhere(fn ($r) => $r->where('task_list_id', (int) $task->task_list_id)->where('active', 1));
            })
            ->orderByRaw('id = ? desc', [(int) $task->rule_id])->orderBy('priority')->orderBy('id')
            ->get();

        $messages = [];
        foreach ($rules->unique('after_assign_job') as $rule) {
            $class = $rule->after_assign_job;
            if (!class_exists($class)) {
                continue;
            }
            try {
                $result = app()->call([new $class((int) $task->applicant_id, (int) $task->id, (int) $rule->id), 'handle']);
                if (is_array($result) && !empty($result['message'])) {
                    $messages[] = $result['message'];
                }
            } catch (Throwable $e) {
                Log::error('[Task Automation] Completion action failed', ['applicant_task_id' => $task->id, 'rule_id' => $rule->id, 'job' => $class, 'error' => $e->getMessage()]);
                $messages[] = '"'.($registry[$class] ?? class_basename($class)).'" did not finish: '.$e->getMessage();
            }
        }

        return $messages;
    }
}
