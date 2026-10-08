<?php

namespace App\Mail;

use App\Models\StudentCourseChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt a student gets for a course change request.
 *
 * Sent after the Service Desk ticket exists, never before: the reference in it
 * is the ticket reference, and it is the only thing the student has to quote
 * when they ring Registry. A request that never reached Operations therefore
 * sends no mail — which is the point, not an omission.
 */
class StudentCourseChangeReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentCourseChangeRequest $courseChange,
        public string $studentName,
        public ?string $registrationNo = null,
        /** Absolute path to the college logo, embedded rather than linked. */
        public ?string $logoPath = null,
        /** Stands in for a start date, which most enrolments do not carry. */
        public ?string $currentIntake = null,
    ) {
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Course change request received (Ref: '.$this->courseChange->ticket_ref.')',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.forms.course-change-received',
            with: [
                'request' => $this->courseChange,
                'studentName' => $this->studentName,
                'registrationNo' => $this->registrationNo,
                'logoPath' => $this->logoPath,
                'currentIntake' => $this->currentIntake,
                'registryEmail' => config('services.registry.email'),
                'registryPhone' => config('services.registry.phone'),
            ],
        );
    }
}
