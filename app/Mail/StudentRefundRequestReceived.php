<?php

namespace App\Mail;

use App\Models\StudentRefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt a student gets for a refund request.
 *
 * The account is named by its last four digits only. Email is not a safe place
 * for a full account number, and the student does not need telling what they
 * have just typed — they need to know we read it correctly.
 */
class StudentRefundRequestReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentRefundRequest $refund,
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
            subject: 'Refund request received (Ref: '.$this->refund->ticket_ref.')',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.forms.refund-request-received',
            with: [
                'request' => $this->refund,
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
