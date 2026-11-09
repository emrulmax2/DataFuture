<?php

namespace App\Services\SlcRegistration;

use App\Models\ComonSmtp;
use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\Student;

/**
 * Email and SMS to a student from /slc-registration › Actions (user, 6 Nov 2026), with the form of
 * the student's Communication tab (/student/communication/{id}). Sending goes through that page's
 * own senders (Student\EmailController / SmsController, see SlcRegistrationController), so each
 * message is saved on the student's Communication tab exactly as if it was sent from there.
 */
class StudentComms
{
    /** What the Email / SMS dialog needs: who it goes to, the SMTP accounts and the templates. */
    public function options(int $studentId): array
    {
        $st = Student::with('contact', 'users')->find($studentId);
        if (!$st) {
            throw new SlcRegistrationException('That student could not be found.');
        }
        // the same addresses Student\EmailController::store sends to
        $emails = array_values(array_filter([$st->contact->institutional_email ?? null, $st->contact->personal_email ?? null]));
        if (!$emails && !empty($st->users->email)) {
            $emails = [$st->users->email];
        }

        return [
            'student' => [
                'id' => $st->id,
                'name' => trim($st->first_name.' '.$st->last_name),
                'regNo' => (string) $st->registration_no,
                'emails' => $emails,
                'mobile' => (string) ($st->contact->mobile ?? ''),
                'hasContact' => (bool) $st->contact,
                'communicationUrl' => route('student.communication', $st->id),
            ],
            'smtps' => ComonSmtp::orderBy('smtp_user')->get(['id', 'smtp_user'])->map(fn ($s) => ['id' => $s->id, 'name' => $s->smtp_user])->all(),
            'emailTemplates' => EmailTemplate::where('live', 1)->where('status', 1)->orderBy('email_title')->get(['id', 'email_title'])->map(fn ($t) => ['id' => $t->id, 'name' => $t->email_title])->all(),
            'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title')->get(['id', 'sms_title'])->map(fn ($t) => ['id' => $t->id, 'name' => $t->sms_title])->all(),
            // a test site doesn't reach the student: mail goes to MAIL_REDIRECT_TO (UserMailerJob), SMS to the log (SmsController)
            'mailDivertedTo' => !app()->environment('production') ? (config('mail.redirect_to') ?: null) : null,
            'smsToLog' => app()->environment(['development', 'local']),
        ];
    }

    /** A template's text, as the Communication tab fills the form with it (email: subject and body; SMS: the text). */
    public function template(string $type, int $id): array
    {
        if ($type === 'email') {
            $t = EmailTemplate::find($id);

            return ['subject' => (string) ($t->email_title ?? ''), 'body' => (string) ($t->description ?? '')];
        }
        $t = SmsTemplate::find($id);

        return ['body' => (string) ($t->description ?? '')];
    }
}
