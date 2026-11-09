<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\StudentTask;
use App\Models\TaskList;
use App\Services\ClassRoutine\ClassRoutineBuilder;
use App\Services\ClassRoutine\ClassRoutineSender;
use Illuminate\Console\Command;

/**
 * The Task Manager's "Send ICP & Mark as Complete" for one student, from the command line
 * (user, 28 Oct 2026): class routine PDF into Uploads, the "LCC Class Routine" letter by
 * email, the text message (production only) and the student's ICP task completed.
 *
 * Off production the email goes only to --to (default MAIL_REDIRECT_TO, else
 * limon@lcc.ac.uk), never to the student. It is sent straight away, not queued.
 *
 *   php artisan class-routine:send 19845
 *   php artisan class-routine:send LCC20260894 --to=limon@lcc.ac.uk
 */
class SendClassRoutine extends Command
{
    protected $signature = 'class-routine:send
        {student : Student id, or registration number (e.g. LCC20260894)}
        {--to= : Email only this address (default off production: MAIL_REDIRECT_TO, else limon@lcc.ac.uk)}
        {--no-complete : Send it without completing the student\'s ICP task}';

    protected $description = 'Send a student their class routine (ICP): PDF to Uploads, the Class Routine letter, email, SMS on production, ICP task completed, Google Calendar';

    public function handle(ClassRoutineSender $sender, ClassRoutineBuilder $builder): int
    {
        $key = trim((string) $this->argument('student'));
        $student = ctype_digit($key) ? Student::find((int) $key) : Student::where('registration_no', $key)->first();
        if (!$student) {
            $this->error('No student with id or registration number "'.$key.'".');

            return self::FAILURE;
        }

        $to = trim((string) $this->option('to'));
        if ($to === '' && !app()->environment('production')) {
            $to = (string) (config('mail.redirect_to') ?: 'limon@lcc.ac.uk');
        }
        if ($to !== '' && !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('"'.$to.'" is not an email address.');

            return self::FAILURE;
        }

        $routine = $builder->forStudent((int) $student->id);
        $this->line('<info>'.$student->registration_no.'</info> '.$student->first_name.' '.$student->last_name.' (student '.$student->id.')');
        if ($routine) {
            $classes = 0;
            foreach ($routine['days'] as $day) {
                foreach ($day['cells'] as $cell) {
                    $classes += count($cell);
                }
            }
            $this->line('  '.$routine['course'].' · '.$routine['group']['name'].' · '.$routine['term']['name'].($routine['term']['code'] ? ' ('.$routine['term']['code'].')' : '')
                .' · '.$classes.' weekly classes · '.count($routine['weeks']).' teaching weeks');
        }

        $task = null;
        if (!$this->option('no-complete')) {
            $lists = TaskList::where('class_routine', 'Yes')->pluck('id');
            $task = StudentTask::where('student_id', $student->id)->whereIn('task_list_id', $lists)->whereIn('status', ['Pending', 'In Progress'])->orderByDesc('id')->first()
                ?: StudentTask::where('student_id', $student->id)->whereIn('task_list_id', $lists)->orderByDesc('id')->first();
            $this->line('  ICP task: '.($task ? '#'.$task->id.' ('.$task->status.')' : 'none found, so nothing to complete'));
        }
        $this->line('  Email to: '.($to !== '' ? $to : 'the student\'s institutional and personal addresses'));
        $this->line('  SMS: '.(ClassRoutineSender::smsEnabled() ? 'on' : 'off on this install'));

        $result = $sender->send($student, $task, (int) config('tasks.system_user_id', 1), ['to' => $to !== '' ? [$to] : [], 'sync' => true]);

        if ($result['status'] !== ClassRoutineSender::DONE) {
            $this->error($result['message']);

            return self::FAILURE;
        }
        $this->info($result['message']);
        $this->line('  Uploads: "'.ClassRoutineSender::DOCUMENT_NAME.'" (document '.$result['document_id'].')');
        $this->line('  Letters: '.(!empty($result['letter_id']) ? 'letter '.$result['letter_id'].' issued' : 'no "Class Routine" letter set on this install, so no letter'));
        $this->line('  Link: '.$result['link']);
        if ($task) {
            $this->line('  ICP task #'.$task->id.': '.$task->fresh()->status);
        }
        if (!empty($result['calendar'])) {
            $this->line('  '.$result['calendar']);
        }
        if ($result['status_note']) {
            $this->line('  '.$result['status_note']);
        }

        return self::SUCCESS;
    }
}
