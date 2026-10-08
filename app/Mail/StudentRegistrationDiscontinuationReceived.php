<?php

namespace App\Mail;

use App\Models\StudentRegistrationDiscontinuationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt a student gets for a registration discontinuation request.
 *
 * Sent after the Service Desk ticket exists, never before: the reference in it
 * is the ticket reference, and it is the only thing the student has to quote
 * when they contact Registry.
 */
class StudentRegistrationDiscontinuationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentRegistrationDiscontinuationRequest $discontinuation,
        public string $studentName,
        public ?string $registrationNo = null,
        public ?string $studentEmail = null,
        /** Absolute path to the college logo, embedded rather than linked. */
        public ?string $logoPath = null,
    ) {
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Registration discontinuation request received (Ref: '.$this->discontinuation->ticket_ref.')',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.forms.registration-discontinuation-received',
            with: [
                'request' => $this->discontinuation,
                'studentName' => $this->studentName,
                'registrationNo' => $this->registrationNo,
                'studentEmail' => $this->studentEmail,
                'logoPath' => $this->logoPath,
                'registryEmail' => config('services.registry.email'),
                'registryPhone' => config('services.registry.phone'),
            ],
        );
    }
}
