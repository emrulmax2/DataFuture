<?php

namespace App\Mail;

use App\Models\StudentItReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt a student gets for an IT issue reported from campus.
 *
 * Short by design: the useful part is the reference and what IT were told, so
 * the student can quote it at the desk or in a corridor.
 */
class StudentItReportReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentItReport $report,
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
            subject: 'IT issue reported (Ref: '.$this->report->ticket_ref.')',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.forms.it-report-received',
            with: [
                'request' => $this->report,
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
