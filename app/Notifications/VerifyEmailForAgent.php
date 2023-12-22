<?php

namespace App\Notifications;


use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailForAgent extends BaseVerifyEmail  implements ShouldQueue
{
    use Queueable;
    protected $user;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        $veficationUrl = URL::temporarySignedRoute('agent.verification.verify', now()->addHour(), [
                            'id' => $this->user->id,
                            'hash' => sha1($this->user->getEmailForVerification()),
        ]);
        return (new MailMessage)
        ->from($address = 'no-reply@lcc.ac.uk', $name = 'London Churcill College Agent Verification Mail')
        ->subject('Verification for London Churchill College agent account')
        ->view('emails.agent.verify', ['user' => $this->user,'url'=>$veficationUrl]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
