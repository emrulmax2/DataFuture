<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Models\Applicant;
use App\Services\TaskAssignment\TaskAssigner;
use Illuminate\Support\Facades\Log;

/**
 * Runs the task engine when an application is submitted.
 *
 * Synchronous on purpose: the queue is the database driver, which only
 * moves when a worker is running, and a submitted application with no tasks
 * is exactly the failure this exists to remove. The work is a few queries.
 * Fail-safe as well: nothing here may break the applicant's "Apply now".
 */
class AssignTasksOnSubmission
{
    public function __construct(private TaskAssigner $assigner)
    {
    }

    public function handle(ApplicationSubmitted $event): void
    {
        try {
            $applicant = Applicant::find($event->applicantId);
            if ($applicant) {
                $this->assigner->apply($applicant, TaskAssigner::TRIGGER_SUBMITTED);
            }
        } catch (\Throwable $e) {
            Log::error('Task assignment failed on submission', ['applicant_id' => $event->applicantId, 'error' => $e->getMessage()]);
        }
    }
}
