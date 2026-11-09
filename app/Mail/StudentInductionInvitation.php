<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The induction invitation to a newly converted student, in the user's template
 * (emails/student-induction-invitation; user, 30 Oct 2026). $event comes from the Gather
 * event behind the course's booking link (App\Services\StudentEmail\InductionEventDetails).
 */
class StudentInductionInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public const SUBJECT = 'Action required: Confirm your LCC induction attendance';

    public function __construct(public string $firstName, public string $term, public array $event)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: self::SUBJECT);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-induction-invitation',
            with: ['firstName' => $this->firstName, 'term' => $this->term, 'event' => $this->event],
        );
    }
}
