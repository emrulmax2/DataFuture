<?php

namespace App\Jobs\TaskAssignment;

use App\Models\Student;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Services\StudentEmail\InductionInvitationSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * The action on the induction task's rule ("Send induction invitation email to all
 * eligible students"): email the student the induction invitation for their course's
 * Gather event (booking link) and complete the task (InductionInvitationSender). It waits
 * for the student's @lcc.ac.uk address, like the welcome letter.
 *
 * Which students are eligible is the rule's business, not this job's — the conditions
 * in Site settings > Task Automation decide who is given the task in the first place,
 * and this runs once per student who was.
 *
 * Registered in config('tasks.student_assignment_jobs').
 */
class SendInductionInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $studentId, public int $studentTaskId, public ?int $ruleId = null)
    {
    }

    public function handle(InductionInvitationSender $sender): void
    {
        $student = Student::find($this->studentId);
        $task = StudentTask::find($this->studentTaskId);

        if (!$student || !$task || $task->status === 'Completed') {
            return;
        }

        /* It goes to the new @lcc.ac.uk address as well as the personal one (user, 30 Oct 2026),
           so until the "create student email" task is done it only notes that it is waiting;
           CreateStudentEmailJob runs it again once the address exists. */
        if (!SendStudentWelcomeLetterJob::addressReady($student->id)) {
            StudentTaskLog::create([
                'student_tasks_id' => $task->id, 'actions' => 'Induction invitation waiting', 'field_name' => 'induction', 'prev_field_value' => '',
                'current_field_value' => 'Waiting for the student email account; sent once it is created.', 'created_by' => (int) config('tasks.system_user_id', 1),
            ]);

            return;
        }

        /* One invitation per student, even if the same action is reached twice at
           once — from the queue, or from a second rule assigning the same task. */
        Cache::lock('student-induction-invitation-'.$student->id, 120)->get(function () use ($sender, $student, $task) {
            if ($task->fresh()->status === 'Completed') {
                return;
            }

            $sent = $sender->send($student->fresh(), $task, (int) config('tasks.system_user_id', 1));

            Log::info('Student induction invitation: '.$sent['status'], [
                'student_id' => $student->id, 'message' => $sent['message'], 'rule_id' => $this->ruleId,
            ]);
        });
    }
}
