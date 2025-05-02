<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class CommunicationSendMail extends Mailable
{
    use Queueable, SerializesModels;
    public $subject,$content,$attachmentList;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject,$content,$attachmentList)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->attachmentList = $attachmentList;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->subject,
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
            view: 'emails.communication-email',
            with: [
                'content' => $this->content,
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
        $attachmentArray = [];
        $i =0 ;
        if(empty($this->attachmentList)) {
            return $attachmentArray;
        }
        if(!is_array($this->attachmentList)) {
            return $attachmentArray;
        }
        if(count($this->attachmentList) == 0) {
            return $attachmentArray;
        }
        if(!isset($this->attachmentList[0])) {
            return $attachmentArray;
        }
        
        foreach ($this->attachmentList as $attachment) {     
            $disk = (isset($attachment['disk']) && !empty($attachment['disk']) ? $attachment['disk'] : 'local');      
            $attachmentArray[$i++] = Attachment::fromStorageDisk($disk, $attachment["pathinfo"])
            ->as($attachment["nameinfo"])
            ->withMime($attachment["mimeinfo"]);
        }
        
        return $attachmentArray;
    }
}
