<?php

namespace App\Mail;

use App\Models\LibraryBookIssue;
use App\Models\LibraryDeposit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The payment link for an overdue charge, sent to the student.
 *
 * Time-critical, and the subject says so: the desk has held their return open
 * until midnight, and an unpaid link means the book counts as still out and
 * tomorrow costs another day.
 */
class LibraryFineLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected LibraryDeposit $deposit,
        protected ?LibraryBookIssue $loan,
        protected string $studentName,
        protected string $payUrl,
    ) {
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Pay £'.number_format($this->deposit->amount, 2).' today to complete your library return',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.library.fine-link',
            with: [
                'deposit' => $this->deposit,
                'loan' => $this->loan,
                'studentName' => $this->studentName,
                'payUrl' => $this->payUrl,
                'deadline' => $this->deposit->expires_at,
            ],
        );
    }
}
