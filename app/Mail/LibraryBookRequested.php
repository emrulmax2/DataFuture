<?php

namespace App\Mail;

use App\Models\LibraryBookIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the library desk a student has reserved a copy.
 *
 * Sent the moment the hold is placed, because the copy is already off the
 * shelf in Operations by then — the desk needs to know to put it aside, and
 * to expect the student before the hold expires.
 */
class LibraryBookRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected LibraryBookIssue $loan,
        protected string $studentName,
        protected ?string $registrationNo = null,
    ) {
    }

    public function envelope()
    {
        /* The reference leads, so a reply or a desk search starts from the one
           value that identifies this reservation everywhere. */
        return new Envelope(
            subject: 'Library reservation '.$this->loan->reference.' — '.$this->loan->title,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.library.book-requested',
            with: [
                'loan' => $this->loan,
                'studentName' => $this->studentName,
                'registrationNo' => $this->registrationNo,
            ],
        );
    }
}
