<?php

namespace App\Mail;

use App\Models\StudentAcademicAppeal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt a student gets for a Stage 1 academic appeal.
 *
 * It repeats the appeal back in full: this is the record of what was submitted
 * and when, and the student may need it if the deadline is ever in question.
 */
class StudentAcademicAppealReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentAcademicAppeal $appeal,
        public string $studentName,
        public ?string $registrationNo = null,
        public ?string $studentEmail = null,
        public ?string $studentMobile = null,
        /** Absolute path to the college logo, embedded rather than linked. */
        public ?string $logoPath = null,
    ) {
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Academic appeal received (Ref: '.$this->appeal->ticket_ref.')',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.forms.academic-appeal-received',
            with: [
                'request' => $this->appeal,
                'studentName' => $this->studentName,
                'registrationNo' => $this->registrationNo,
                'studentEmail' => $this->studentEmail,
                'studentMobile' => $this->studentMobile,
                'logoPath' => $this->logoPath,
                'registryEmail' => config('services.registry.email'),
                'registryPhone' => config('services.registry.phone'),
            ],
        );
    }
}
