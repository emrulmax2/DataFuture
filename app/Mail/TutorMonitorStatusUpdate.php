<?php

namespace App\Mail;

use App\Models\Plan;
use App\Models\StudentAwardingBodyDetails;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorMonitorStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;
    protected $plansDateList,$plan,$ReplyToEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct($plansDateList,$plan,$ReplyToEmail)
    {
        $this->plansDateList = $plansDateList;
        $this->plan = $plan;
        $this->ReplyToEmail = $ReplyToEmail;

    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Lecture Material Not Uploaded',
            replyTo: [
                new Address($this->ReplyToEmail, 'LCC Tutor Monitor Team'),
            ],
        );
    }
    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        
         return new Content(
            view: 'tutor-monitoring.index',
            with: ['plansDateList' => $this->plansDateList,'plan'=>$this->plan]
        );
    }

}
