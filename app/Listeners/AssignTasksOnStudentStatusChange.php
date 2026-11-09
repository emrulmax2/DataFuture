<?php

namespace App\Listeners;

use App\Events\StudentStatusChanged;
use App\Models\Student;
use App\Services\TaskAssignment\TaskAssigner;
use Illuminate\Support\Facades\Log;

/**
 * Runs Task Automation's "Student status changed" rules. Synchronous, like the
 * applicant listener, so the tasks are there when the page reloads; fail-safe,
 * so a rule problem never breaks the status change itself. Replaces the old
 * "process attached to the status" assignment (that became rules).
 */
class AssignTasksOnStudentStatusChange
{
    public function __construct(private TaskAssigner $assigner)
    {
    }

    public function handle(StudentStatusChanged $event): void
    {
        try {
            $student = Student::find($event->studentId);
            if ($student) {
                $this->assigner->applyStudent($student, TaskAssigner::TRIGGER_STUDENT_STATUS_CHANGED, ['previous_status_id' => $event->previousStatusId]);
            }
        } catch (\Throwable $e) {
            Log::error('Task assignment failed on student status change', ['student_id' => $event->studentId, 'status_id' => $event->statusId, 'error' => $e->getMessage()]);
        }
    }
}
