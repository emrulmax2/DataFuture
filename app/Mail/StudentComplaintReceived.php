<?php

namespace App\Mail;

use App\Models\StudentComplaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt a student gets for a Stage 1 complaint.
 *
 * It repeats the complaint back in full, including the declaration they agreed
 * to — a complaint is investigated by people the student has not met, and this
 * is their record of exactly what they said and consented to.
 */
class StudentComplaintReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentComplaint $complaint,
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
            subject: 'Complaint received (Ref: '.$this->complaint->ticket_ref.')',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.forms.complaint-received',
            with: [
                'request' => $this->complaint,
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
