<?php

namespace App\Mail;

use App\Models\PaySlipUploadSync;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
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

        $subjectData = date('F Y', strtotime($this->paySlip->month_year.'-01')) ?? '';

        if($this->paySlip->type == 'P60') {
            $subjectData = $this->paySlip->holidayYear->holiday_year ?? '';

            $subject = 'Notification of '. $subjectData .' '. ucfirst($this->paySlip->type) .' Availability';
        } else if($this->paySlip->type == 'P45'){
            $subject = 'Notification of '. ucfirst($this->paySlip->type) .' Availability';
        }else {
            
             $subject = 'Notification of '. $subjectData .' '. ucfirst($this->paySlip->type) .' Available';
        }
        
        

        return new Envelope(
            subject: $subject,
            replyTo: [
                new Address('hr@lcc.ac.uk', 'HR Department'),
            ],
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
                    'monthYear' => ($this->paySlip->type == 'Payslips') ? date('F Y', strtotime($this->paySlip->month_year.'-01')) : (($this->paySlip->type == 'P60')? $this->paySlip->holidayYear->holiday_year : ''),
                    'type' => ($this->paySlip->type == 'Payslips') ? 'Payslip' : ucfirst($this->paySlip->type),
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
