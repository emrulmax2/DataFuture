<?php

namespace App\Mail;

use App\Models\Option;
use App\Models\Student;
use App\Support\EmailLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to a new student's personal email when their @lcc.ac.uk account is created:
 * the address and the first password (StudentEmailProvisioner). Same look as the
 * admission notifications. Locally the default mailer is redirected (MAIL_REDIRECT_TO).
 */
class StudentWorkspaceAccount extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Student $student, public string $email, public string $password, public bool $mustChange = true)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your new London Churchill College account is ready');
    }

    public function content(): Content
    {
        $site = Option::where('category', 'SITE_SETTINGS')->whereIn('name', ['site_logo', 'company_name', 'company_address', 'company_phone'])->pluck('value', 'name');

        return new Content(view: 'emails.students.workspace-account', with: [
            'logoPath' => EmailLogo::path(),
            'logoUrl' => !empty($site['site_logo']) ? url('storage/'.$site['site_logo']) : null,
            'companyName' => $site['company_name'] ?? 'London Churchill College',
            'companyAddress' => $site['company_address'] ?? 'Barclay Hall, 156B Green Street, London, E7 8JQ',
            // printed as on the other college emails: "+44 (0) 207 377 1077"
            'companyPhone' => OfferAcceptanceUpdate::displayPhone((string) ($site['company_phone'] ?? '02073771077')),
            'companyPhoneTel' => OfferAcceptanceUpdate::telPhone((string) ($site['company_phone'] ?? '02073771077')),
            'name' => trim(($this->student->first_name ?? '').' '.($this->student->last_name ?? '')),
            'firstName' => trim((string) ($this->student->first_name ?? '')),
            // where this email goes, for the footer ("This email was sent to …")
            'sentTo' => optional(\App\Models\StudentContact::where('student_id', $this->student->id)->orderByDesc('id')->first())->personal_email,
            // Google's sign-in page with the new address filled in, landing in Gmail
            'signInUrl' => 'https://accounts.google.com/AccountChooser?Email='.rawurlencode($this->email).'&continue='.rawurlencode('https://mail.google.com/mail/'),
        ]);
    }
}
