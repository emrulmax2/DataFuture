<?php

namespace App\Listeners;

use App\Services\Conversion\ConversionErrorReport;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Str;
use Throwable;

/**
 * A Task Automation student action that failed on the queue (TASK_ASSIGNMENT_JOBS=queue),
 * emailed to CONVERSION_ERROR_REPORT_TO like the other conversion problems (user, 30 Oct
 * 2026). Run inline (the default "sync"), TaskAssigner reports the failure itself.
 */
class StudentActionFailureSubscriber
{
    public function handleJobFailed(JobFailed $event): void
    {
        try {
            $jobClass = $event->job->payload()['data']['commandName'] ?? null;
            $actions = (array) config('tasks.student_assignment_jobs', []);
            if (!$jobClass || !isset($actions[$jobClass])) {
                return;
            }
            $command = unserialize($event->job->payload()['data']['command']);

            ConversionErrorReport::report('Action: '.$actions[$jobClass], 'The action failed on the queue: '.Str::limit($event->exception->getMessage(), 300), [
                'student_id' => $command->studentId ?? null,
                'task_id' => $command->studentTaskId ?? null,
                'rule_id' => $command->ruleId ?? null,
                'detail' => Str::limit((string) $event->exception, 4000),
            ]);
        } catch (Throwable $e) {
            // never disturb the worker over a report
        }
    }

    public function subscribe($events): array
    {
        return [JobFailed::class => 'handleJobFailed'];
    }
}
