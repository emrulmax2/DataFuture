<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** One conversion / Task Automation problem for a student (App\Services\Conversion\ConversionErrorReport). */
class ConversionErrorReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $step, public string $problem, public array $info = [])
    {
    }

    public function envelope(): Envelope
    {
        $who = $this->info['student']->registration_no ?? (isset($this->info['applicant']) && $this->info['applicant'] ? 'applicant '.($this->info['applicant']->application_no ?: $this->info['applicant']->id) : 'a student');

        return new Envelope(subject: '[Conversion problem] '.$this->step.' – '.$who);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.conversion-error-report');
    }
}
