<?php

namespace App\Jobs\TaskAssignment;

use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Services\TaskAssignment\StudentProcessWrapUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * The action on the "Change student status (SLC New)" rule (user, 28 Oct 2026). When the rule
 * assigns the task it checks straight away (StudentProcessWrapUp) and, while other tasks in the
 * process are still open, notes on the task that it is waiting. After that, every student task
 * update runs the same check, and the last one to finish changes the status to SLC New and
 * completes this task. Registered in config('tasks.student_assignment_jobs').
 */
class UpdateStudentStatusToSlcNewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $studentId, public int $studentTaskId, public ?int $ruleId = null)
    {
    }

    public function handle(StudentProcessWrapUp $wrapUp): void
    {
        $result = $wrapUp->check($this->studentId);
        $task = StudentTask::find($this->studentTaskId);
        if (!$task || !$result || $result['status'] === StudentProcessWrapUp::DONE) {
            return;
        }

        StudentTaskLog::create([
            'student_tasks_id' => $task->id,
            'actions' => 'Status change waiting',
            'field_name' => 'status_id',
            'prev_field_value' => '',
            'current_field_value' => $result['message'],
            'created_by' => (int) config('tasks.system_user_id', 1),
        ]);
    }
}
