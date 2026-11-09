<?php

namespace App\Services\StudentEmail;

use App\Jobs\UserMailerJob;
use App\Mail\StudentInductionInvitation;
use App\Services\Conversion\ConversionErrorReport;
use App\Services\OperationsGatherClient;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\LetterSet;
use App\Models\Signatory;
use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentLetter;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Traits\GenerateStudentLetterTrait;
use Illuminate\Support\Facades\Log;

/**
 * The induction invitation ("Send induction invitation email to all eligible students";
 * user, 30 Oct 2026). The student's course creation (student_course_relations →
 * course_creations) holds an Induction Booking Link to a Gather event in Operations; the
 * event is read over the Operations API (OperationsGatherClient) and the user's email
 * template (StudentInductionInvitation) goes to the student's institutional and personal
 * addresses with "Confirm my attendance" opening the event's booking page. Then the task
 * is completed.
 *
 * Who is eligible is not decided here: the rule's conditions in Site settings > Task
 * Automation decide who gets the task. Anything that stops the invitation (no link, the
 * event not found, not live or with no sessions left, Operations unreachable) leaves the
 * task open with a note and is emailed to CONVERSION_ERROR_REPORT_TO.
 */
class InductionInvitationSender
{
    use GenerateStudentLetterTrait;

    public const DONE = 'done';
    public const WAITING = 'waiting';
    public const FAILED = 'failed';

    /**
     * The invitation in the user's template (user, 30 Oct 2026): the course's booking link →
     * its Gather event in Operations → the email, then the task is completed.
     *
     * @param array{to?:string[], sync?:bool} $options to: email only these addresses (the
     *        command's --to); sync: send now rather than through the queue
     * @return array{status:string, message:string}
     */
    public function send(Student $student, ?StudentTask $task, int $actorId, array $options = []): array
    {
        $creation = optional($student->activeCR)->creation;
        $course = trim(optional(optional($creation)->course)->name.' '.optional(optional($creation)->semester)->name) ?: 'this student\'s course';
        $link = trim((string) optional($creation)->induction_booking_link);

        if ($link === '') {
            return $this->finish($task, self::WAITING, 'No induction booking link on '.$course.($creation ? ' (course creation #'.$creation->id.')' : '').' — set it in Course Creation, then send the invitation again.', $actorId);
        }

        $slug = OperationsGatherClient::slugFromLink($link);
        if (!$slug) {
            return $this->finish($task, self::FAILED, 'The induction booking link of '.$course.' is not a Gather event link: '.$link, $actorId);
        }

        $found = app(OperationsGatherClient::class)->event($slug);
        if ($found['status'] !== OperationsGatherClient::OK) {
            return $this->finish($task, self::FAILED, 'Induction event for '.$course.': '.$found['message'], $actorId);
        }

        $event = InductionEventDetails::fromEvent($found['data']);
        if (!$event['bookable']) {
            return $this->finish($task, self::WAITING, 'The Gather event "'.$event['title'].'" is '.($found['data']['status'] ?? 'not live').', so its booking page is closed. Make it live, then send the invitation again.', $actorId);
        }
        if (!$event['sessions']) {
            return $this->finish($task, self::WAITING, 'The Gather event "'.$event['title'].'" has no sessions still to come.', $actorId);
        }
        if ($event['booking_url'] === '') {
            $event['booking_url'] = $link;
        }

        $contact = StudentContact::where('student_id', $student->id)->orderByDesc('id')->first();
        $sendTo = array_values(array_unique(array_filter([optional($contact)->institutional_email, optional($contact)->personal_email])));
        if (!empty($options['to'])) {
            $sendTo = array_values(array_unique(array_filter((array) $options['to'])));
        }
        if (!$sendTo) {
            return $this->finish($task, self::FAILED, 'No institutional or personal email to send the induction invitation to.', $actorId);
        }

        try {
            $mail = new StudentInductionInvitation(self::firstName($student), self::term($creation), $event);
            $smtp = ComonSmtp::where('is_default', 1)->first();
            // to the student's own addresses when STUDENT_EMAIL_IGNORE_MAIL_REDIRECT is on, as the welcome letter does
            $direct = empty($options['to']) && (bool) config('student_email.ignore_mail_redirect');
            $job = new UserMailerJob($this->smtpConfig($smtp), $sendTo, $mail, [], $direct);
            !empty($options['sync']) ? dispatch_sync($job) : dispatch($job);
        } catch (\Throwable $e) {
            Log::error('Student induction invitation failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);

            return $this->finish($task, self::FAILED, 'Induction invitation failed: '.$e->getMessage(), $actorId);
        }

        return $this->finish($task, self::DONE, 'Induction invitation ('.$event['title'].') sent to '.implode(', ', $sendTo).'.', $actorId, true);
    }

    /** First name as written to the student; names typed in capitals are title-cased. */
    public static function firstName(Student $student): string
    {
        $name = trim((string) $student->first_name);

        return $name !== '' && mb_strtoupper($name) === $name ? mb_convert_case(mb_strtolower($name), MB_CASE_TITLE) : ($name ?: 'Student');
    }

    /** The intake as the template words it: "2026 September" → "September 2026". */
    public static function term($creation): string
    {
        $name = trim((string) optional(optional($creation)->semester)->name);

        return preg_match('/^(\d{4})\s+(.+)$/', $name, $m) ? trim($m[2]).' '.$m[1] : $name;
    }

    /* The first version (29 Oct 2026): a letter set's wording with the booking button as a tag.
       Replaced by the user's template and the Gather event on 30 Oct 2026.
    /** @return array{status:string, message:string} * /
    public function send(Student $student, ?StudentTask $task, int $actorId): array
    {
        $link = trim((string) optional(optional($student->activeCR)->creation)->induction_booking_link);

        if ($link === '') {
            return $this->finish($task, self::WAITING, 'No induction booking link on this student\'s course yet — set it in Course Creation and the invitation can go out.', $actorId);
        }

        $letterSet = LetterSet::find((int) config('tasks.induction.letter_set_id'));

        if (!$letterSet || empty($letterSet->description)) {
            return $this->finish($task, self::FAILED, 'Induction letter set #'.config('tasks.induction.letter_set_id').' not found.', $actorId);
        }

        $contact = StudentContact::where('student_id', $student->id)->orderByDesc('id')->first();
        $sendTo = array_values(array_unique(array_filter([optional($contact)->institutional_email, optional($contact)->personal_email])));

        if (!$sendTo) {
            return $this->finish($task, self::FAILED, 'No institutional or personal email to send the induction invitation to.', $actorId);
        }

        $signatoryId = (int) config('tasks.induction.signatory_id');
        $smtp = ComonSmtp::where('is_default', 1)->first();
        $title = $letterSet->letter_title ?: 'Your induction at London Churchill College';

        try {
            /* The same record staff see on the student's Communication page when they
               send a letter by hand. Type 1: the letter is the email, not an attachment. * /
            StudentLetter::create([
                'student_id' => $student->id, 'letter_set_id' => $letterSet->id, 'signatory_id' => $signatoryId,
                'comon_smtp_id' => optional($smtp)->id, 'is_email_or_attachment' => 1, 'issued_by' => $actorId,
                'issued_date' => date('Y-m-d'), 'created_by' => $actorId,
            ]);

            $body = $this->parseLetterContent($student->id, $title, $letterSet->description, date('Y-m-d'), $signatoryId)
                . $this->signature($signatoryId);

            UserMailerJob::dispatch($this->smtpConfig($smtp), $sendTo, new CommunicationSendMail($title, $body, []), [], (bool) config('student_email.ignore_mail_redirect'));
        } catch (\Throwable $e) {
            Log::error('Student induction invitation failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);

            return $this->finish($task, self::FAILED, 'Induction invitation failed: '.$e->getMessage(), $actorId);
        }

        return $this->finish($task, self::DONE, 'Induction invitation sent to '.implode(', ', $sendTo).'.', $actorId, true);
    }

    */

    /** The sign-off, worded as the Communication page words it. */
    private function signature(int $signatoryId): string
    {
        $signatory = $signatoryId > 0 ? Signatory::find($signatoryId) : null;

        return $signatory
            ? '<p><strong>Best Regards,</strong><br/>'.e($signatory->signatory_name).'<br/>'.e($signatory->signatory_post).'<br/>London Churchill College</p>'
            : '<p><strong>Best Regards,</strong><br/>The Academic Admin Dept.<br/>London Churchill College</p>';
    }

    private function smtpConfig(?ComonSmtp $smtp): array
    {
        return [
            'smtp_host' => $smtp->smtp_host ?? 'smtp.gmail.com',
            'smtp_port' => $smtp->smtp_port ?? '587',
            'smtp_username' => $smtp->smtp_user ?? 'no-reply@lcc.ac.uk',
            'smtp_password' => $smtp->smtp_pass ?? '',
            'smtp_encryption' => $smtp->smtp_encryption ?? 'tls',
            'from_email' => 'no-reply@lcc.ac.uk',
            'from_name' => 'London Churchill College',
        ];
    }

    private function finish(?StudentTask $task, string $status, string $message, int $actorId, bool $complete = false): array
    {
        if ($status !== self::DONE && $task) {
            // emailed to CONVERSION_ERROR_REPORT_TO (user, 30 Oct 2026)
            ConversionErrorReport::report('Action: Induction invitation', $message, ['student_id' => $task->student_id, 'task_id' => $task->id, 'rule_id' => $task->rule_id]);
        }
        if ($task) {
            StudentTaskLog::create([
                'student_tasks_id' => $task->id,
                'actions' => match ($status) {
                    self::DONE => 'Induction invitation sent',
                    self::WAITING => 'Induction invitation waiting',
                    default => 'Induction invitation failed',
                },
                'field_name' => 'induction',
                'prev_field_value' => '',
                'current_field_value' => mb_substr($message, 0, 145),
                'created_by' => $actorId,
            ]);

            if ($complete && $task->status !== 'Completed') {
                StudentTaskLog::create([
                    'student_tasks_id' => $task->id, 'actions' => 'Status Changed', 'field_name' => 'status',
                    'prev_field_value' => $task->status, 'current_field_value' => 'Completed', 'created_by' => $actorId,
                ]);
                $task->update(['status' => 'Completed', 'updated_by' => $actorId]);
            }
        }

        return ['status' => $status, 'message' => $message];
    }
}
