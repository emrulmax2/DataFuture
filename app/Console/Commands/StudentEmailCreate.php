<?php

namespace App\Console\Commands;

use App\Jobs\TaskAssignment\CreateStudentEmailJob;
use App\Jobs\TaskAssignment\SendStudentWelcomeLetterJob;
use App\Models\Student;
use App\Models\StudentContact;
use App\Services\StudentEmail\StudentEmailProvisioner;
use App\Services\StudentEmail\StudentTaskActions;
use App\Services\StudentEmail\WelcomeLetterSender;
use Illuminate\Console\Command;

/**
 * Re-run a student's email steps when the automatic run did not finish (e.g. Google
 * refused): create the @lcc.ac.uk account, update the records, email the details and
 * complete "Student Email Creation and Notification", then send the welcome letter and
 * complete "Send welcome email, information pack & text message". Steps already
 * completed are skipped. The two tasks are the student's tasks whose rules run the
 * "create student email" and "send the welcome letter" actions (never a task id).
 *
 *   php artisan student-email:create 20167
 *   php artisan student-email:create 20167 --welcome-only
 */
class StudentEmailCreate extends Command
{
    protected $signature = 'student-email:create
        {student : students.id}
        {--welcome-only : only send the welcome letter (the address must already exist)}
        {--no-welcome : only the email account step}';

    protected $description = 'Create a student\'s @lcc.ac.uk account and send the welcome letter, completing their two tasks';

    public function handle(StudentEmailProvisioner $provisioner, WelcomeLetterSender $welcome): int
    {
        $student = Student::find((int) $this->argument('student'));
        if (!$student) {
            $this->error('No student #'.$this->argument('student').'.');
            return self::FAILURE;
        }
        $actor = (int) config('tasks.system_user_id', 1);
        $this->line('Student #'.$student->id.' '.$student->registration_no.' '.$student->first_name.' '.$student->last_name);

        $emailTask = StudentTaskActions::task($student->id, CreateStudentEmailJob::class);
        $hasAddress = fn () => (string) optional(StudentContact::where('student_id', $student->id)->orderByDesc('id')->first())->institutional_email !== '';

        if (!$this->option('welcome-only')) {
            if ($emailTask && $emailTask->status === 'Completed' && $hasAddress()) {
                $this->line('Email account: already done ('.optional(StudentContact::where('student_id', $student->id)->orderByDesc('id')->first())->institutional_email.').');
            } else {
                $result = $provisioner->provision($student, $emailTask, $actor);
                $result['status'] === StudentEmailProvisioner::DONE ? $this->info('Email account: '.$result['message']) : $this->error('Email account: '.$result['message']);
                if ($result['status'] !== StudentEmailProvisioner::DONE) {
                    return self::FAILURE;
                }
            }
        }

        if ($this->option('no-welcome')) {
            return self::SUCCESS;
        }
        if (!$hasAddress()) {
            $this->error('Welcome letter: the student has no institutional email yet; run without --welcome-only first.');
            return self::FAILURE;
        }
        $welcomeTask = StudentTaskActions::task($student->id, SendStudentWelcomeLetterJob::class, true);
        if (!$welcomeTask) {
            $any = StudentTaskActions::task($student->id, SendStudentWelcomeLetterJob::class);
            $this->line($any ? 'Welcome letter: already sent (task completed).' : 'Welcome letter: the student has no task whose rule runs the welcome-letter action; nothing sent.');
            return self::SUCCESS;
        }
        $sent = $welcome->send($student->fresh(), $welcomeTask, $actor);
        $sent['status'] === WelcomeLetterSender::DONE ? $this->info('Welcome letter: '.$sent['message']) : $this->error('Welcome letter: '.$sent['message']);

        return $sent['status'] === WelcomeLetterSender::DONE ? self::SUCCESS : self::FAILURE;
    }
}
