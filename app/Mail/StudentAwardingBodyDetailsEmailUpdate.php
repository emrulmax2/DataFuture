<?php

namespace App\Mail;

use App\Models\StudentAwardingBodyDetails;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentAwardingBodyDetailsEmailUpdate extends Mailable
{
    use Queueable, SerializesModels;
    protected $student;

    /**
     * Create a new notification instance.
     */
    public function __construct($student)
    {
        $this->student = $student;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Awarding Body Registration Information - Incorrect.',
            replyTo: [
                new Address('no-reply@lcc.ac.uk', 'No Reply'),
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
        $studentAwardingBody = StudentAwardingBodyDetails::where('student_id',$this->student->id)->where('student_course_relation_id',$this->student->crel->id)->get()->first();     
        return new Content(
            view: 'emails.pearson.verify',
            with: ['student' => $this->student,'studentAwardingBody'=>$studentAwardingBody]
        );
    }
    /**
     * Get the mail representation of the notification.
     */
//     public function toMail(object $notifiable): MailMessage
//     {
//         $studentAwardingBody = StudentAwardingBodyDetails::where('student_id',$this->student->id)->where('student_course_relation_id',$this->student->crel->id)->get()->first();
//         return (new MailMessage)
//         ->from($address = 'no-reply@lcc.ac.uk', $name = 'LCC SMS APP')
//         ->subject('Awarding Body Registration Information - Incorrect**')
//         ->view('emails.pearson.verify', ['student' => $this->student,'studentAwardingBody'=>$studentAwardingBody])
//         ->to('registry@lcc.ac.uk');
// }
//     }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    // public function toArray(object $notifiable): array
    // {
    //     return [
    //         //
    //     ];
    // }
}
