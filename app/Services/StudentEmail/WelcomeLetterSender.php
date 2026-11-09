<?php

namespace App\Services\StudentEmail;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\LetterSet;
use App\Models\Signatory;
use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentLetter;
use App\Models\StudentLettersDocument;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Support\ApplicantDocumentDisk;
use App\Traits\GenerateStudentLetterTrait;
use Illuminate\Support\Facades\Log;

/**
 * The welcome letter ("Welcome Message to Students"), sent the way staff send it from
 * the student's Communication page (Student\LetterController@store): a student_letters
 * row, the PDF from the letter set with its tags filled in, a letters document, and an
 * email with the PDF to the institutional and personal addresses through the default
 * SMTP account. Then "Send welcome email, information pack & text message" is completed.
 * Runs after StudentEmailProvisioner has created the address (user, 26 Oct 2026).
 */
class WelcomeLetterSender
{
    use GenerateStudentLetterTrait;

    public const DONE = 'done';
    public const FAILED = 'failed';

    /** @return array{status:string, message:string} */
    public function send(Student $student, ?StudentTask $task, int $actorId): array
    {
        $letterSet = LetterSet::find((int) config('student_email.welcome_letter_set_id', 116));
        if (!$letterSet || empty($letterSet->description)) {
            return $this->finish($task, self::FAILED, 'Welcome letter set #'.config('student_email.welcome_letter_set_id').' not found.', $actorId);
        }
        $contact = StudentContact::where('student_id', $student->id)->orderByDesc('id')->first();
        $sendTo = array_values(array_unique(array_filter([optional($contact)->institutional_email, optional($contact)->personal_email])));
        if (!$sendTo) {
            return $this->finish($task, self::FAILED, 'No institutional or personal email to send the welcome letter to.', $actorId);
        }

        $signatoryId = (int) config('student_email.welcome_signatory_id', 23);
        $smtp = ComonSmtp::where('is_default', 1)->first();
        $issued = date('Y-m-d');
        $title = $letterSet->letter_title ?: 'Letter from LCC';
        $disk = ApplicantDocumentDisk::name(); // local on a local install, S3 on the servers

        try {
            $letter = StudentLetter::create([
                'student_id' => $student->id, 'letter_set_id' => $letterSet->id, 'signatory_id' => $signatoryId,
                'comon_smtp_id' => optional($smtp)->id, 'is_email_or_attachment' => 2, 'issued_by' => $actorId,
                'issued_date' => $issued, 'created_by' => $actorId,
            ]);
            $pdf = $this->generateLetter($student->id, $title, $letterSet->description, $issued, time(), $signatoryId, $disk);
            $path = 'public/students/'.$student->id.'/'.$pdf['filename'];
            StudentLettersDocument::create([
                'student_id' => $student->id, 'student_letter_id' => $letter->id, 'hard_copy_check' => 0, 'doc_type' => 'pdf',
                'disk_type' => $disk, 'path' => $pdf['path'], 'display_file_name' => $title, 'current_file_name' => $pdf['filename'],
                'created_by' => $actorId,
            ]);

            UserMailerJob::dispatch($this->smtpConfig($smtp), $sendTo, new CommunicationSendMail($title, $this->body($student, $signatoryId), [[
                'pathinfo' => $path, 'nameinfo' => $pdf['filename'], 'mimeinfo' => 'application/pdf', 'disk' => $disk,
            ]]), [], (bool) config('student_email.ignore_mail_redirect')); // to the student's own addresses (STUDENT_EMAIL_IGNORE_MAIL_REDIRECT)
        } catch (\Throwable $e) {
            Log::error('Student welcome letter failed', ['student_id' => $student->id, 'error' => $e->getMessage()]);
            return $this->finish($task, self::FAILED, 'Welcome letter failed: '.$e->getMessage(), $actorId);
        }

        return $this->finish($task, self::DONE, 'Welcome letter sent to '.implode(', ', $sendTo).'.', $actorId, true);
    }

    /** The covering email, word for word what LetterController@store sends. */
    private function body(Student $student, int $signatoryId): string
    {
        $signatory = $signatoryId > 0 ? Signatory::find($signatoryId) : null;
        $sign = $signatory
            ? '<p><strong>Best Regards,</strong><br/>'.e($signatory->signatory_name).'<br/>'.e($signatory->signatory_post).'<br/>London Churchill College</p>'
            : '<p><strong>Best Regards,</strong><br/>The Academic Admin Dept.<br/>London Churchill College</p>';

        return 'Dear '.e($student->first_name.' '.$student->last_name).', <br/><p>Please Find the letter attached herewith. </p>'.$sign;
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
            \App\Services\Conversion\ConversionErrorReport::report('Action: Welcome letter', $message, ['student_id' => $task->student_id, 'task_id' => $task->id, 'rule_id' => $task->rule_id]);
        }
        if ($task) {
            StudentTaskLog::create([
                'student_tasks_id' => $task->id,
                'actions' => $status === self::DONE ? 'Welcome letter sent' : 'Welcome letter failed',
                'field_name' => 'letter',
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
