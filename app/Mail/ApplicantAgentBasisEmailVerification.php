<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantAgentBasisEmailVerification extends Mailable
{
    use Queueable, SerializesModels;
    protected $name, $email, $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email,$code)
    {
        $this->name = $name;
        $this->email = $email;
        $this->code = $code;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'LCC email verification code for applicant.',
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
            view: 'emails.agentBaseApplicantEmailVerify',
            with: [
                'name' => $this->name,
                'email' => $this->email,
                'code' => $this->code,
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
