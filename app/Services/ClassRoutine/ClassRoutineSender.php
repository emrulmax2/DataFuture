<?php

namespace App\Services\ClassRoutine;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\LetterSet;
use App\Models\Signatory;
use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentDocument;
use App\Models\StudentLetter;
use App\Models\StudentLettersDocument;
use App\Models\StudentSms;
use App\Models\StudentSmsContent;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Services\TaskAssignment\StudentProcessWrapUp;
use App\Support\ApplicantDocumentDisk;
use App\Traits\GenerateStudentLetterTrait;
use App\Traits\SendSmsTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Task Manager › "Send ICP & Mark as Complete" for one student (user, 28 Oct 2026):
 *
 *   1. the class routine PDF from the student's class plans (ClassRoutineBuilder), stored in
 *      the student's Uploads as "Class Routine" (S3 on the servers, the local disk here);
 *   2. the "LCC Class Routine" letter, issued as staff issue it from Communication (letters
 *      row + letter PDF), its [Link] filled with the routine's download link and the group.
 *      Taken out on 28 Oct 2026 and brought back on 29 Oct 2026 (user);
 *   3. an email to the institutional and personal addresses with the covering text every other
 *      letter gets ("Please Find the letter attached herewith", signatory), the letter PDF and the
 *      routine PDF attached (user, 29 Oct 2026; MAIL_REDIRECT_TO still applies on a local install);
 *   4. a text message with the link, on production only (smsEnabled(); CLASS_ROUTINE_SMS overrides);
 *   5. the task completed, then the "Change student status to SLC New" check;
 *   6. the class dates queued for the student's Google Calendar (SyncStudentClassCalendar).
 *
 * Nothing is completed when the routine cannot be built, stored or emailed.
 *
 * emailOnly() is the plan tree's "Send Class ICP": the routine PDF emailed and nothing else.
 */
class ClassRoutineSender
{
    use GenerateStudentLetterTrait, SendSmsTrait;

    public const DOCUMENT_NAME = 'Class Routine';
    public const DONE = 'done';
    public const FAILED = 'failed';

    public function __construct(private ClassRoutineBuilder $builder)
    {
    }

    /**
     * The routine as PDF bytes: the detailed term timetable, shared by everyone with the same
     * routine and rendered once for them (ClassRoutineGroupPdf; user, 30 Oct 2026).
     */
    public function pdf(array $routine): string
    {
        return app(ClassRoutineGroupPdf::class)->bytes($routine);

        /* the one-page routine before it (28 Oct 2026)
        $logo = resource_path('images/pdf/lcc-crest.png');
        $html = view('pages.students.class-routine.pdf', ['routine' => $routine, 'logo' => is_file($logo) ? $logo : null])->render();

        return Pdf::loadHTML($html)
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false)
            ->output();
        */
    }

    /**
     * @param array{to?:string[], sync?:bool} $options to: email only these addresses (the command's --to);
     *                                                 sync: send the email now rather than through the queue
     * @return array{status:string, message:string, student:string, status_note:?string, emailed_to?:string[], sms?:string, document_id?:int, letter_id?:?int, link?:string}
     */
    public function send(Student $student, ?StudentTask $task, int $actorId, array $options = []): array
    {
        $label = $student->registration_no ?: (string) $student->id;
        $routine = $this->builder->forStudent((int) $student->id);
        if (!$routine) {
            return $this->fail($task, $label, 'No class plan is assigned to this student yet, so there is no routine to send.', $actorId);
        }

        $contact = StudentContact::where('student_id', $student->id)->orderByDesc('id')->first();
        $sendTo = array_values(array_unique(array_filter([optional($contact)->institutional_email, optional($contact)->personal_email])));
        if (!empty($options['to'])) {
            $sendTo = array_values(array_unique(array_filter((array) $options['to'])));
        }
        if (!$sendTo) {
            return $this->fail($task, $label, 'No institutional or personal email to send the routine to.', $actorId);
        }

        $disk = ApplicantDocumentDisk::name(); // local on a local install, S3 on the servers
        $fileName = time().'_Class_Routine_'.preg_replace('/[^A-Za-z0-9_-]+/', '-', $routine['group']['name'] ?: 'Term').'.pdf';
        $path = 'public/students/'.$student->id.'/'.$fileName;

        try {
            if (!Storage::disk($disk)->put($path, $this->pdf($routine))) {
                return $this->fail($task, $label, 'The routine PDF could not be stored. Please try again.', $actorId);
            }
            $document = StudentDocument::create([
                'student_id' => $student->id, 'document_setting_id' => 0, 'hard_copy_check' => 0, 'doc_type' => 'pdf',
                'disk_type' => $disk, 'path' => Storage::disk($disk)->url($path), 'display_file_name' => self::DOCUMENT_NAME,
                'current_file_name' => $fileName, 'is_encrypted' => 0, 'created_by' => $actorId,
            ]);
            $link = ClassRoutineLink::url($document);

            $letterId = $this->email($student, $routine, $link, $sendTo, $path, $fileName, $disk, $actorId, !empty($options['sync']));
        } catch (Throwable $e) {
            Log::error('Class routine failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);

            return $this->fail($task, $label, 'The routine could not be sent: '.$e->getMessage(), $actorId);
        }

        $sms = $this->sms($student, $contact, $routine, $link, $actorId);

        $statusNote = null;
        if ($task) {
            StudentTaskLog::create([
                'student_tasks_id' => $task->id, 'actions' => 'Class routine sent', 'field_name' => 'student_documents',
                'prev_field_value' => '', 'current_field_value' => (string) $document->id, 'created_by' => $actorId,
            ]);
            if ($task->status !== 'Completed') {
                StudentTaskLog::create([
                    'student_tasks_id' => $task->id, 'actions' => 'Status Changed', 'field_name' => 'status',
                    'prev_field_value' => $task->status ?: 'Pending', 'current_field_value' => 'Completed', 'created_by' => $actorId,
                ]);
                $task->update(['status' => 'Completed', 'updated_by' => $actorId]);
            }
            // the last open New Student task changes the status to SLC New
            $wrapUp = app(StudentProcessWrapUp::class)->check((int) $student->id);
            $statusNote = ($wrapUp['status'] ?? null) === StudentProcessWrapUp::DONE ? $wrapUp['message'] : null;
        }

        $calendar = $this->calendar((int) $student->id, !empty($options['sync']));

        $smsNote = ['sent' => '', 'off' => ' (text message not sent: SMS is off on this install)', 'no_mobile' => ' (no mobile number, so no text message)', 'failed' => ' (the text message failed)'];

        return [
            'status' => self::DONE,
            'student' => $label,
            'message' => 'Routine for '.$routine['group']['name'].' emailed to '.implode(', ', $sendTo).($smsNote[$sms] ?? '').'.',
            'status_note' => $statusNote,
            'emailed_to' => $sendTo,
            'sms' => $sms,
            'document_id' => (int) $document->id,
            'letter_id' => $letterId,
            'link' => $link,
            'calendar' => $calendar,
        ];
    }

    /**
     * Course Management › Plan tree › "Send Class ICP" for one student of the group (user, 30 Oct 2026):
     * the routine PDF emailed to the institutional and personal addresses, and nothing else. No
     * letter, no copy in the student's Uploads, no text message, no task, no calendar.
     *
     * The PDF is the group's: everyone with the same routine gets the same file, kept once on the
     * document disk and attached to each email (ClassRoutineGroupPdf::stored). The routine is the
     * one for the plan tree's term, not the student's newest. MAIL_REDIRECT_TO still diverts every
     * email on a local install (limon@lcc.ac.uk).
     *
     * @param array{to?:string[], sync?:bool} $options to: email only these addresses; sync: send now, not through the queue
     * @return array{status:string, message:string, student:string, status_note:null, emailed_to?:string[], group?:string}
     */
    public function emailOnly(Student $student, int $actorId, ?int $termDeclarationId = null, array $options = []): array
    {
        $label = $student->registration_no ?: (string) $student->id;
        $routine = $this->builder->forStudent((int) $student->id, $termDeclarationId);
        if (!$routine) {
            return $this->fail(null, $label, 'No class plan is assigned to this student for this term, so there is no routine to send.', $actorId);
        }

        $contact = StudentContact::where('student_id', $student->id)->orderByDesc('id')->first();
        $sendTo = array_values(array_unique(array_filter([optional($contact)->institutional_email, optional($contact)->personal_email])));
        if (!empty($options['to'])) {
            $sendTo = array_values(array_unique(array_filter((array) $options['to'])));
        }
        if (!$sendTo) {
            return $this->fail(null, $label, 'No institutional or personal email to send the routine to.', $actorId);
        }

        try {
            $pdf = app(ClassRoutineGroupPdf::class)->stored($routine);
            $attachment = ['pathinfo' => $pdf['path'], 'nameinfo' => $pdf['name'], 'mimeinfo' => 'application/pdf', 'disk' => $pdf['disk']];
            $mail = new CommunicationSendMail('Your class routine for '.$routine['term']['name'], $this->routineEmail($student, $routine), [$attachment]);
            $job = new UserMailerJob($this->smtpConfig(ComonSmtp::where('is_default', 1)->first()), $sendTo, $mail, [], false);
            !empty($options['sync']) ? dispatch_sync($job) : dispatch($job);
        } catch (Throwable $e) {
            Log::error('Class routine (plan tree) failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);

            return $this->fail(null, $label, 'The routine could not be sent: '.$e->getMessage(), $actorId);
        }

        Log::info('Class routine emailed from the plan tree', [
            'student_id' => $student->id, 'term_declaration_id' => $routine['term']['id'], 'group' => $routine['group']['name'],
            'to' => $sendTo, 'pdf' => $pdf['path'], 'by' => $actorId,
        ]);
        $redirect = config('mail.redirect_to') && !app()->environment('production') ? (string) config('mail.redirect_to') : null;

        return [
            'status' => self::DONE,
            'student' => $label,
            'message' => 'Emailed to '.implode(', ', $sendTo).($redirect ? ' (this install delivers it to '.$redirect.')' : '').'.',
            'status_note' => null,
            'emailed_to' => $sendTo,
            'group' => $routine['group']['name'],
        ];
    }

    /** The email that carries the routine on its own (no letter): what it is, and who to ask. */
    private function routineEmail(Student $student, array $routine): string
    {
        return 'Dear '.e($student->first_name.' '.$student->last_name).', <br/>'
            .'<p>Please find attached your class routine for <strong>'.e($routine['term']['name']).'</strong>'
            .($routine['group']['name'] ? ' (group <strong>'.e($routine['group']['name']).'</strong>)' : '').'. '
            .'It shows your class days and times, where each class takes place or its online link, every week of the term '
            .'with any cancelled classes, the revision week and your assessment deadlines.</p>'
            .'<p>Please keep it to hand. If you have any questions about it, please do not hesitate to contact us on '.e(config('class_routine.phone')).'.</p>'
            .$this->signature((int) config('class_routine.signatory_id', 23));
    }

    /**
     * The "LCC Class Routine" letter (a letters row and PDF), emailed the way other letters are.
     *
     * @return int|null the letter issued; null when this install has no such letter set
     */
    private function email(Student $student, array $routine, string $link, array $sendTo, string $path, string $fileName, string $disk, int $actorId, bool $sync = false): ?int
    {
        $letter = null;
        $attachments = [];
        $letterSet = LetterSet::find((int) config('class_routine.letter_set_id'));
        if (!$letterSet || stripos((string) $letterSet->letter_title, 'class routine') === false) {
            $letterSet = LetterSet::where('letter_title', config('class_routine.letter_title'))->where('status', 1)->orderByDesc('id')->first();
        }
        $signatoryId = (int) config('class_routine.signatory_id', 23);
        $smtp = ComonSmtp::where('is_default', 1)->first();
        $title = $letterSet->letter_title ?? 'Class Routine';
        $subject = 'Your class routine for '.$routine['term']['name'];
        $summary = $this->summary($routine, $link);

        if ($letterSet && !empty($letterSet->description)) {
            $content = $this->fill((string) $letterSet->description, $routine, $link);
            $issued = date('Y-m-d');
            // the letter, as staff issue it from Communication (taken out 28 Oct, back 29 Oct 2026, user)
            $letter = StudentLetter::create([
                'student_id' => $student->id, 'letter_set_id' => $letterSet->id, 'signatory_id' => $signatoryId,
                'comon_smtp_id' => optional($smtp)->id, 'is_email_or_attachment' => 2, 'issued_by' => $actorId,
                'issued_date' => $issued, 'created_by' => $actorId,
            ]);
            $pdf = $this->generateLetter($student->id, $title, str_replace('<!--class-routine-summary-->', '', $content), $issued, time(), $signatoryId, $disk);
            StudentLettersDocument::create([
                'student_id' => $student->id, 'student_letter_id' => $letter->id, 'hard_copy_check' => 0, 'doc_type' => 'pdf',
                'disk_type' => $disk, 'path' => $pdf['path'], 'display_file_name' => $title, 'current_file_name' => $pdf['filename'],
                'created_by' => $actorId,
            ]);
            /* The email used to be the letter itself with the week at a glance (28 Oct 2026). Now it is
               the covering email other letters get, with the letter attached (user, 29 Oct 2026).
            $body = str_replace('<!--class-routine-summary-->', $summary, $this->parseLetterContent($student->id, $title, $content, $issued, $signatoryId));
            */
            $body = $this->coveringEmail($student, $signatoryId);
            $attachments[] = [
                'pathinfo' => 'public/students/'.$student->id.'/'.$pdf['filename'], 'nameinfo' => $pdf['filename'], 'mimeinfo' => 'application/pdf', 'disk' => $disk,
            ];
        } else {
            // no letter set on this install: the same message, written out
            $body = '<p>Dear '.e($student->first_name.' '.$student->last_name).',</p>'
                .'<p>A personalised timetable has been created for you for <strong>'.e($routine['term']['name']).'</strong>. You can also see it on the student portal.</p>'
                .'<p><a href="'.e($link).'">'.e($link).'</a></p><p><strong>'.e($routine['group']['name']).'</strong></p>'.$summary
                .'<p>If you require any further information, please do not hesitate to contact us on '.e(config('class_routine.phone')).'.</p><p>London Churchill College</p>';
        }

        $attachments[] = [
            'pathinfo' => $path, 'nameinfo' => 'Class_Routine_'.($routine['group']['name'] ?: 'Term').'.pdf', 'mimeinfo' => 'application/pdf', 'disk' => $disk,
        ];
        $job = new UserMailerJob($this->smtpConfig($smtp), $sendTo, new CommunicationSendMail($subject, $body, $attachments), [], false);
        $sync ? dispatch_sync($job) : dispatch($job);

        return $letter ? (int) $letter->id : null;
    }

    /** The covering email for a letter, word for word what Student\LetterController@store sends. */
    private function coveringEmail(Student $student, int $signatoryId): string
    {
        return 'Dear '.e($student->first_name.' '.$student->last_name).', <br/><p>Please Find the letter attached herewith. </p>'.$this->signature($signatoryId);
    }

    /** "Best Regards," and the signatory, as the letters' covering emails sign off. */
    private function signature(int $signatoryId): string
    {
        $signatory = $signatoryId > 0 ? Signatory::find($signatoryId) : null;

        return $signatory
            ? '<p><strong>Best Regards,</strong><br/>'.e($signatory->signatory_name).'<br/>'.e($signatory->signatory_post).'<br/>London Churchill College</p>'
            : '<p><strong>Best Regards,</strong><br/>The Academic Admin Dept.<br/>London Churchill College</p>';
    }

    /** The letter set's [Link] becomes the link and the group, and its "class routine for …" the current term. */
    private function fill(string $content, array $routine, string $link): string
    {
        $linkHtml = '<a href="'.e($link).'">'.e($link).'</a></p><p><strong>'.e($routine['group']['name']).'</strong></p><!--class-routine-summary--><p>';
        $content = str_contains($content, '[Link]') ? str_replace('[Link]', $linkHtml, $content) : $content.'<p>'.$linkHtml.'</p>';
        $content = str_replace('<p></p>', '', $content);

        return (string) preg_replace('/(class routine for\s*(?:&nbsp;|\s)*<strong>)(.*?)(<\/strong>)/i', '${1}'.e($routine['term']['name']).'${3}', $content, 1);
    }

    /** The week at a glance and the deadlines, for the email (the PDF has the full routine). */
    private function summary(array $routine, string $link): string
    {
        // the mail template justifies text, so every cell says left
        $cell = 'padding:6px 8px;border-bottom:1px solid #ebebe6;font-size:13px;vertical-align:top;text-align:left;';
        $head = 'padding:6px 8px;border-bottom:1px solid #15201e;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#7a847f;text-align:left;';
        $rows = '';
        foreach ($routine['days'] as $day => $info) {
            if ($info['type'] !== 'classes') {
                continue;
            }
            foreach ($info['cells'] as $sessions) {
                foreach ($sessions as $s) {
                    $where = $s['mode'] === 'online'
                        ? 'Online'.($s['link'] ? ' · <a href="'.e($s['link']).'">'.e(preg_replace('#^https?://#i', '', $s['link'])).'</a>' : '')
                        : e($s['room'] ?: 'Campus');
                    $rows .= '<tr><td style="'.$cell.'"><strong>'.e($day).'</strong></td><td style="'.$cell.'white-space:nowrap;">'.e($s['start'].' – '.$s['end']).'</td>'
                        .'<td style="'.$cell.'">'.e($s['kind']).' · '.e($s['title']).'</td><td style="'.$cell.'">'.$where.'</td><td style="'.$cell.'">'.e((string) $s['person']).'</td></tr>';
                }
            }
        }
        $html = '<p style="margin:18px 0 6px;"><strong>Your week at a glance</strong></p>'
            .'<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:680px;">'
            .'<tr><th style="'.$head.'width:14%;">Day</th><th style="'.$head.'width:15%;">Time</th><th style="'.$head.'width:35%;">Class</th><th style="'.$head.'width:17%;">Where</th><th style="'.$head.'width:19%;">Tutor</th></tr>'.$rows.'</table>';
        if ($routine['support_days']) {
            $html .= '<p style="font-size:13px;color:#3d4a46;text-align:left;">'.e(implode(', ', $routine['support_days'])).': independent study and tutor-led academic support ('.e(config('class_routine.support_hours')).', pre-booking required).</p>';
        }
        if ($routine['assessments']) {
            $html .= '<p style="margin:14px 0 6px;"><strong>Assessment deadlines</strong></p><ul style="margin:0;padding-left:18px;font-size:13px;text-align:left;">';
            foreach ($routine['assessments'] as $as) {
                $html .= '<li>'.e($as['module']).': '.($as['date'] ? e(Carbon::parse($as['date'])->format('l j F Y')) : 'to be confirmed').'</li>';
            }
            $html .= '</ul>';
        }

        return $html.'<p style="margin:16px 0;"><a href="'.e($link).'" style="display:inline-block;background:#0f2d2a;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:600;">Download your class routine (PDF)</a></p>';
    }

    /**
     * The class dates into the student's Google Calendar (SyncStudentClassCalendar): queued, or run
     * now for the command. A calendar problem never undoes the routine that was sent.
     */
    private function calendar(int $studentId, bool $now): string
    {
        if (!config('class_routine.calendar.enabled')) {
            return 'Calendar sync is off.';
        }
        if (!$now) {
            \App\Jobs\SyncStudentClassCalendar::dispatch($studentId);

            return 'Calendar update queued.';
        }
        try {
            return app(ClassCalendarSync::class)->sync($studentId)['message'];
        } catch (Throwable $e) {
            Log::error('[Class calendar] not updated', ['student_id' => $studentId, 'error' => $e->getMessage()]);

            return 'Calendar not updated: '.$e->getMessage();
        }
    }

    /** SMS goes out on production only, unless CLASS_ROUTINE_SMS says otherwise (user, 28 Oct 2026: off locally). */
    public static function smsEnabled(): bool
    {
        $setting = config('class_routine.sms');

        return $setting === null ? app()->environment('production') : (bool) $setting;
    }

    /**
     * The text message, recorded as the student's SMS page records one.
     * @return string sent | off (SMS disabled on this install) | no_mobile | failed
     */
    private function sms(Student $student, ?StudentContact $contact, array $routine, string $link, int $actorId): string
    {
        $mobile = trim((string) optional($contact)->mobile);
        if ($mobile === '') {
            return 'no_mobile';
        }
        // one SMS (160 characters) with a typical name and the live link
        $text = 'Dear '.$student->first_name.', your class routine for '.$routine['term']['name'].' ('.$routine['group']['name'].') is ready: '.$link.' - LCC';
        if (!self::smsEnabled()) {
            Log::info('Class routine SMS not sent (SMS is off on this install)', ['student_id' => $student->id, 'phone' => $mobile, 'text' => $text]);

            return 'off';
        }
        try {
            $content = StudentSmsContent::create(['sms_template_id' => null, 'subject' => self::DOCUMENT_NAME, 'sms' => $text]);
            StudentSms::create(['student_id' => $student->id, 'student_sms_content_id' => $content->id, 'phone' => $mobile, 'show_as_news' => 0, 'created_by' => $actorId]);
            $this->sendSms($mobile, $text);
        } catch (Throwable $e) {
            Log::warning('Class routine text message failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);

            return 'failed';
        }

        return 'sent';
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

    private function fail(?StudentTask $task, string $label, string $message, int $actorId): array
    {
        if ($task) {
            StudentTaskLog::create([
                'student_tasks_id' => $task->id, 'actions' => 'Class routine failed', 'field_name' => 'student_documents',
                'prev_field_value' => '', 'current_field_value' => mb_substr($message, 0, 145), 'created_by' => $actorId,
            ]);
        }

        return ['status' => self::FAILED, 'student' => $label, 'message' => $message, 'status_note' => null];
    }
}
