<?php

namespace App\Jobs\TaskAssignment;

use App\Models\Student;
use App\Models\StudentTask;
use App\Services\StudentEmail\StudentEmailProvisioner;
use App\Services\StudentEmail\StudentTaskActions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs after a student rule assigns "Student Email Creation and Notification":
 * creates the student's @lcc.ac.uk account and completes the task
 * (StudentEmailProvisioner). Once that worked it runs the welcome-letter action
 * (SendStudentWelcomeLetterJob) on the student's task whose rule has that action, since
 * both rules fire together and the letter needs the new address. Tasks are found by
 * their rule's action, never by task id. Registered in config('tasks.student_assignment_jobs').
 */
class CreateStudentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $studentId, public int $studentTaskId, public ?int $ruleId = null)
    {
    }

    public function handle(StudentEmailProvisioner $provisioner): void
    {
        $student = Student::find($this->studentId);
        if (!$student) {
            return;
        }
        $actor = (int) config('tasks.system_user_id', 1);
        $task = StudentTask::find($this->studentTaskId);
        $result = $provisioner->provision($student, $task, $actor);
        Log::info('Student email: '.$result['status'], ['student_id' => $student->id, 'email' => $result['email'], 'message' => $result['message'], 'rule_id' => $this->ruleId]);

        if ($result['status'] !== StudentEmailProvisioner::DONE) {
            return; // no address yet: the welcome letter waits (student-email:create retries both)
        }
        $welcome = StudentTaskActions::task($student->id, SendStudentWelcomeLetterJob::class, true);
        if ($welcome) {
            // inline, right after the address exists (not via the queue)
            app()->call([new SendStudentWelcomeLetterJob($student->id, $welcome->id, $welcome->rule_id), 'handle']);
        }
        // the induction invitation waits for the address too (user, 30 Oct 2026)
        $induction = StudentTaskActions::task($student->id, SendInductionInvitationJob::class, true);
        if ($induction) {
            app()->call([new SendInductionInvitationJob($student->id, $induction->id, $induction->rule_id), 'handle']);
        }
    }
}
