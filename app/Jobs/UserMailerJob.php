<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;

class UserMailerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
 
    public $configuration;
    public $to;
    public $mailable;
    public $bcc;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    /** true: send to the real recipients even where MAIL_REDIRECT_TO diverts mail (see handle). */
    public bool $direct = false;

    public function __construct(array $configuration, array $to, Mailable $mailable, array $bcc = [], bool $direct = false)
    {
        $this->direct = $direct;
        
        $this->configuration = $configuration;
        // $this->to = $to;
        // $this->bcc = (!empty($bcc) ? $bcc : []);
        // Drop empty addresses: a sender with no email (e.g. the System account on a status
        // letter) put null in the list and the whole message failed in Mailable (26 Oct 2026).
        $present = fn ($address) => !($address === null || (is_string($address) && trim($address) === ''));
        $this->to = array_values(array_filter($to, $present));
        $this->mailable = $mailable;
        $this->bcc = array_values(array_filter($bcc ?: [], $present));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $mailer = app()->makeWith('user.mailer', $this->configuration);
        // Outside production, honour MAIL_REDIRECT_TO like the default mailer does
        // (AppServiceProvider): a local test must never email a real student.
        if (!$this->direct && config('mail.redirect_to') && !app()->environment('production')) {
            $mailer->alwaysTo(config('mail.redirect_to'));
        }
        $mailer->to($this->to);
        $mailer->bcc($this->bcc);
        $mailer->to($this->to)->bcc($this->bcc)->send($this->mailable);

        /* One line per message that left, so "did it send?" has an answer. */
        $subject = $this->mailable->subject ?? (method_exists($this->mailable, 'envelope') ? $this->mailable->envelope()->subject : null);
        \Illuminate\Support\Facades\Log::info('[Mail] sent', ['subject' => $subject, 'to' => $this->to, 'bcc' => $this->bcc, 'from' => $this->configuration['from_email'] ?? null]);

        
    }
}
