<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The one email an applicant gets on "Apply now": their application
 * reference and, when the task engine raised an e-signature request in the
 * same breath, the link to sign. Sent through UserMailerJob with the tenant
 * SMTP settings, from both the applicant and the agent portal.
 */
class ApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $applicantName,
        public string $reference,
        public string $siteName,
        public ?string $signUrl = null,
        public ?string $trackingUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application received - reference ' . $this->reference . ' | ' . $this->siteName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-received',
            with: [
                'applicantName' => $this->applicantName,
                'reference' => $this->reference,
                'siteName' => $this->siteName,
                'signUrl' => $this->signUrl,
                'trackingUrl' => $this->trackingUrl,
            ],
        );
    }
}
