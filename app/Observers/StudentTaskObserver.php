<?php

namespace App\Observers;

use App\Models\StudentTask;
use App\Services\TaskAssignment\StudentProcessWrapUp;

/**
 * Student tasks saved through the model (e.g. the automatic student email and welcome letter
 * tasks, document requests) run the "Change student status to SLC New" check. Bulk updates in the
 * task manager and the student process page call it themselves. After commit, so the check sees
 * the saved status.
 */
class StudentTaskObserver
{
    public bool $afterCommit = true;

    public function updated(StudentTask $task): void
    {
        if ($task->wasChanged('status')) {
            StudentProcessWrapUp::checkMany((int) $task->student_id);
        }
    }

    public function deleted(StudentTask $task): void
    {
        StudentProcessWrapUp::checkMany((int) $task->student_id);
    }
}
