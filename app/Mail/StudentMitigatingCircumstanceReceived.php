<?php

namespace App\Mail;

use App\Models\StudentMitigatingCircumstance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt a student gets for a mitigating circumstances claim.
 *
 * It lists every assignment claimed for with its deadline: a claim made before
 * a deadline and one made after it are judged differently, so the student's
 * copy has to show what was said and when.
 */
class StudentMitigatingCircumstanceReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public StudentMitigatingCircumstance $claim,
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
            subject: 'Mitigating circumstances claim received (Ref: '.$this->claim->ticket_ref.')',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.forms.mitigating-circumstances-received',
            with: [
                'request' => $this->claim,
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
