<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantTempEmailVerification extends Mailable
{
    use Queueable, SerializesModels;
    protected $name, $oldEmail, $newEmail, $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $oldEmail, $newEmail, $url)
    {
        $this->name = $name;
        $this->oldEmail = $oldEmail;
        $this->newEmail = $newEmail;
        $this->url = $url;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'New Email ID Verification.',
            replyTo: [
                new Address('no-reply@lcc.ac.uk', 'No Reply'),
            ],
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.varify-temp-email',
            with: [
                'name' => $this->name,
                'oldEmail' => $this->oldEmail,
                'newEmail' => $this->newEmail,
                'url' => $this->url
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
