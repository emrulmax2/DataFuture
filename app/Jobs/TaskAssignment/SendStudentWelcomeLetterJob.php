<?php

namespace App\Jobs\TaskAssignment;

use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Services\StudentEmail\StudentTaskActions;
use App\Services\StudentEmail\WelcomeLetterSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * The action on the welcome task's rule ("Send welcome email, information pack & text
 * message"): send the welcome letter to the institutional and personal emails and
 * complete the task (WelcomeLetterSender). It needs the student's @lcc.ac.uk address,
 * so until the "create student email" task is done it only notes that it is waiting;
 * CreateStudentEmailJob runs it again once the address exists. Either rule may run
 * first. Registered in config('tasks.student_assignment_jobs').
 */
class SendStudentWelcomeLetterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $studentId, public int $studentTaskId, public ?int $ruleId = null)
    {
    }

    public function handle(WelcomeLetterSender $sender): void
    {
        $student = Student::find($this->studentId);
        $task = StudentTask::find($this->studentTaskId);
        if (!$student || !$task || $task->status === 'Completed') {
            return;
        }
        $actor = (int) config('tasks.system_user_id', 1);

        if (!self::addressReady($student->id)) {
            StudentTaskLog::create([
                'student_tasks_id' => $task->id, 'actions' => 'Welcome letter waiting', 'field_name' => 'letter', 'prev_field_value' => '',
                'current_field_value' => 'Waiting for the student email account; sent once it is created.', 'created_by' => $actor,
            ]);
            return;
        }

        // one send per student, even if both rules' actions run at once from the queue
        Cache::lock('student-welcome-letter-'.$student->id, 120)->get(function () use ($sender, $student, $task, $actor) {
            if ($task->fresh()->status === 'Completed') {
                return;
            }
            $sent = $sender->send($student->fresh(), $task, $actor);
            Log::info('Student welcome letter: '.$sent['status'], ['student_id' => $student->id, 'message' => $sent['message'], 'rule_id' => $this->ruleId]);
        });
    }

    /** The @lcc.ac.uk address exists and, when a "create student email" task was assigned, it is done. */
    public static function addressReady(int $studentId): bool
    {
        $address = (string) optional(StudentContact::where('student_id', $studentId)->orderByDesc('id')->first())->institutional_email;
        $emailTask = StudentTaskActions::task($studentId, CreateStudentEmailJob::class);

        return $address !== '' && (!$emailTask || $emailTask->status === 'Completed');
    }
}
