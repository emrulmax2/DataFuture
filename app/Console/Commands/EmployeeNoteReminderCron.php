<?php

namespace App\Console\Commands;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\EmployeeNotes;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EmployeeNoteReminderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employeenotereminder:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Employee Note Reminder Cron.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
            'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
            'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
            'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
            'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
            
            'from_email'    => 'no-reply@lcc.ac.uk',
            'from_name'    =>  'London Churchill College',
        ];
        $subject = 'HR Note Reminder';

        $notes = EmployeeNotes::where('reminder', 1)->whereBetween('reminder_date', [
                    Carbon::today(),
                    Carbon::today()->addDays(7)
                ])->orderBy('reminder_date', 'ASC')->get();
        if($notes->count() > 0):
            foreach($notes as $note):
                $content = '<p>Dear HR Staff</p>';
                $content .= '<p>This is a reminder to review a note and take the necessary action. If it has already been completed, please close the reminder note.</p>';
                
                $content .= '<p>';
                    $content .= '<strong>Note:</strong><br/>'.$note->note;
                    $content .= '<strong>Employee:</strong> '.(isset($note->employee->full_name) && !empty($note->employee->full_name) ? $note->employee->full_name : 'N/A').'<br/>';
                    $content .= '<strong>Reminder set by:</strong> '.(isset($note->user->employee->full_name) && !empty($note->user->employee->full_name) ? $note->user->employee->full_name : $note->user->name).(!empty($note->created_at) ? ' on '.date('jS F, Y \a\t h:i A', strtotime($note->created_at)) : '');
                $content .= '<p/>';
                $content .= 'Thank you.';

                UserMailerJob::dispatch($configuration, ['hr@lcc.ac.uk'], new CommunicationSendMail($subject, $content, []));
            endforeach;
        endif;

        return 0;
    }
}
