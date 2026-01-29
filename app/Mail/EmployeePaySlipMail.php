<?php

namespace App\Mail;

use App\Models\PaySlipUploadSync;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class EmployeePaySlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public PaySlipUploadSync $paySlip;
    protected array $attachment;

    /**
     * Create a new message instance.
     */
    public function __construct(PaySlipUploadSync $paySlip, array $attachment)
    {
        $this->paySlip = $paySlip;
        $this->attachment = $attachment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        $subjectMonth = $this->paySlip->month_year ?? 'Payslip';

        return new Envelope(
            subject: 'LCC: Your Payslip - ' . $subjectMonth,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            view: 'emails.employee-payslip',
            with: [
                'employeeName' => optional($this->paySlip->employee)->full_name,
                'monthYear' => $this->paySlip->month_year,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        if (empty($this->attachment)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk($this->attachment['disk'], $this->attachment['path'])
                ->as($this->attachment['name'])
                ->withMime($this->attachment['mime'] ?? 'application/pdf'),
        ];
    }
}
