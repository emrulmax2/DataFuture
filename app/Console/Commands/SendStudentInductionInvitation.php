<?php

namespace App\Console\Commands;

use App\Jobs\TaskAssignment\SendInductionInvitationJob;
use App\Models\Student;
use App\Services\OperationsGatherClient;
use App\Services\StudentEmail\InductionEventDetails;
use App\Services\StudentEmail\InductionInvitationSender;
use App\Services\StudentEmail\StudentTaskActions;
use Illuminate\Console\Command;

/**
 * The "Send induction invitation email to all eligible students" action for one student,
 * from the command line (user, 30 Oct 2026): course booking link → Gather event in
 * Operations → the invitation email, then the student's induction task completed.
 *
 * Off production the email goes only to --to (default MAIL_REDIRECT_TO, else
 * limon@lcc.ac.uk), never to the student. It is sent straight away. --check only reads the
 * link and the event and says what would be sent.
 *
 *   php artisan student-induction:send LCC20260894
 *   php artisan student-induction:send LCC20260894 --check
 */
class SendStudentInductionInvitation extends Command
{
    protected $signature = 'student-induction:send
        {student : Student id, or registration number (e.g. LCC20260894)}
        {--to= : Email only this address (default off production: MAIL_REDIRECT_TO, else limon@lcc.ac.uk)}
        {--check : Only look up the link and the event; send nothing}
        {--no-complete : Send it without completing the student\'s induction task}';

    protected $description = 'Send a student the induction invitation for their course\'s Gather event and complete their induction task';

    public function handle(InductionInvitationSender $sender, OperationsGatherClient $gather): int
    {
        $key = trim((string) $this->argument('student'));
        $student = ctype_digit($key) ? Student::find((int) $key) : Student::where('registration_no', $key)->first();
        if (!$student) {
            $this->error('No student with id or registration number "'.$key.'".');

            return self::FAILURE;
        }

        $creation = optional($student->activeCR)->creation;
        $link = trim((string) optional($creation)->induction_booking_link);
        $this->line('<info>'.$student->registration_no.'</info> '.$student->first_name.' '.$student->last_name.' (student '.$student->id.')');
        $this->line('  Course: '.trim(optional(optional($creation)->course)->name.' · '.optional(optional($creation)->semester)->name, ' ·').($creation ? ' (course creation #'.$creation->id.')' : ''));
        $this->line('  Booking link: '.($link !== '' ? $link : 'none set'));

        if ($this->option('check')) {
            $slug = OperationsGatherClient::slugFromLink($link);
            if (!$slug) {
                $this->warn('  Not a Gather event link.');

                return self::FAILURE;
            }
            $found = $gather->event($slug);
            if ($found['status'] !== OperationsGatherClient::OK) {
                $this->error('  '.$found['message']);

                return self::FAILURE;
            }
            $event = InductionEventDetails::fromEvent($found['data']);
            $this->line('  Event: '.$event['title'].' · '.($found['data']['status'] ?? '').'/'.($found['data']['visibility'] ?? '').' · '.($event['bookable'] ? 'bookable' : 'NOT bookable'));
            $this->line('  Date: '.$event['date_label'].' · Venue: '.trim($event['venue_name'].', '.$event['venue_address'], ', '));
            foreach ($event['sessions'] as $i => $session) {
                $this->line('  Session '.($i + 1).': '.$session['time'].($event['same_day'] ? '' : ' ('.$session['date'].')').($session['sold_out'] ? ' FULL' : '').' · '.($session['courses'] ? implode('; ', $session['courses']) : 'no courses named'));
            }
            $this->line('  Confirm button: '.$event['booking_url']);

            return self::SUCCESS;
        }

        $to = trim((string) $this->option('to'));
        if ($to === '' && !app()->environment('production')) {
            $to = (string) (config('mail.redirect_to') ?: 'limon@lcc.ac.uk');
        }
        if ($to !== '' && !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('"'.$to.'" is not an email address.');

            return self::FAILURE;
        }

        $task = $this->option('no-complete') ? null : (StudentTaskActions::task((int) $student->id, SendInductionInvitationJob::class, true)
            ?: StudentTaskActions::task((int) $student->id, SendInductionInvitationJob::class));
        if (!$this->option('no-complete')) {
            $this->line('  Induction task: '.($task ? '#'.$task->id.' ('.$task->status.')' : 'none found, so nothing to complete'));
        }
        $this->line('  Email to: '.($to !== '' ? $to : 'the student\'s institutional and personal addresses'));

        $result = $sender->send($student, $task, (int) config('tasks.system_user_id', 1), ['to' => $to !== '' ? [$to] : [], 'sync' => true]);
        if ($result['status'] !== InductionInvitationSender::DONE) {
            $this->error($result['message']);

            return self::FAILURE;
        }
        $this->info($result['message']);

        return self::SUCCESS;
    }
}
