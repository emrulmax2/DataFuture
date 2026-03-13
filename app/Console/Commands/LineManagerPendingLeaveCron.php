<?php

namespace App\Console\Commands;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLineManager;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LineManagerPendingLeaveCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'linemanagerpendingleave:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending leave notifications to the line manager if leave submitted befor 5 working days.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


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
            
            'from_email'    => 'hr@lcc.ac.uk',
            'from_name'    =>  'London Churchill College',
        ];
        $subject = 'Reminder: Staff Leave Requests Pending Approval';

        $submissionDate = Carbon::now()->subWeekdays(5)->format('Y-m-d');
        $lineManagers = EmployeeLineManager::orderBy('id', 'ASC')->get()->pluck('user_id')->unique()->toArray();
        if(!empty($lineManagers)):
            foreach($lineManagers as $manager_id):
                $user = User::find($manager_id);
                $employees = EmployeeLineManager::where('user_id', $manager_id)->get()->pluck('employee_id')->unique()->toArray();
                if(!empty($employees)):
        
                    $leaves = EmployeeLeave::with('employee', 'leaveDays', 'employee.holidayAuth', 'year')->whereIn('employee_id', $employees)->where('created_at', '<', $submissionDate)
                              ->whereHas('leaveDays', function($q){
                                    $q->where('status', 'Active')->where('supervision_status', '!=', 1);
                              })->where('status', 'Pending')->where('leave_type', 1)->orderBy('employee_id', 'ASC')->get();
                    if($leaves->count() > 0):
                        foreach($leaves as $leave):
                            $empName = (isset($leave->employee->title->name) ? $leave->employee->title->name.' ' : '').$leave->employee->full_name;
                            $approvers = [];
                            if(isset($leave->employee->holidayAuth) && $leave->employee->holidayAuth->count() > 0):
                                foreach($leave->employee->holidayAuth as $supervisor):
                                    $approver = User::find($supervisor->user_id);
                                    $approvers[] = (isset($approver->employee->full_name) && !empty($approver->employee->full_name) ? $approver->employee->full_name : $approver->name);
                                endforeach;
                            endif;
                            $leaveDates = isset($leave->leaveDays) && $leave->leaveDays->count() > 0 ? $leave->leaveDays->pluck('leave_date')->unique()->toArray() : '';
                            $content = '';
                            $content .= '<p>Dear '.(isset($user->employee->full_name) ? $user->employee->full_name : $user->name).',</p>';
                            $content .= '<p>This is to inform you that the leave request submitted by <strong>'.$empName.'</strong> remains pending and has 
                                        not been completed within the required 5 working days by the assigned approver(s), 
                                        <strong>'.(!empty($approvers) ? implode(', ', $approvers) : '').'</strong>.</p>';
                            $content .= '<p>Please find the leave request details below for your reference:</p>';
                            $content .= '<p>';
                                $content .= '<strong>Holiday Year:</strong> '.(isset($leave->year) && !empty($leave->year) ? date('Y', strtotime($leave->year->start_date )).'-'.date('Y', strtotime($leave->year->end_date )): '').'<br/>';
                                $content .= '<strong>Number of Days:</strong> '.(isset($leave->leaveDays) && $leave->leaveDays->count() > 0 ? $leave->leaveDays->count().' days' : '0 days').'<br/>';
                                $content .= '<strong>Leave Dates:</strong> '.(!empty($leaveDates) ? implode(', ', $leaveDates) : '').'<br/>';
                                $content .= '<strong>Requested By:</strong> '.$empName.' on '.date('jS F, Y', strtotime($leave->created_at)).' at '.date('H:i', strtotime($leave->created_at)).'<br/>';
                                $content .= '<strong>Assigned Approver(s):</strong> '.(!empty($approvers) ? implode(', ', $approvers) : '');
                            $content .= '</p>';

                            $content .= '<p>As the approval deadline has now passed, this matter has been escalated for your attention. We would be grateful if you could kindly speak with the approver and ask them to process the request as soon as possible.</p>';
                            $content .= '<p>Thank you for your prompt attention to this matter.</p>';
                            $content .= '<p>Sincerely,<br/>HR Department<br/>London Churchill College</p>';

                            UserMailerJob::dispatch($configuration, [$user->email, 'hr@lcc.ac.uk'], new CommunicationSendMail($subject, $content, []));
                        endforeach;
                    endif;
                endif;
            endforeach;
        endif;

        return 0;
    }
}
