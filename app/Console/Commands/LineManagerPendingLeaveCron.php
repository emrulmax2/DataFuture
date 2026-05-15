<?php

namespace App\Console\Commands;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Employee;
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
        $leaves = EmployeeLeave::with('employee', 'leaveDays', 'employee.holidayAuth', 'year')
                    ->where('created_at', '<', $submissionDate)->whereHas('leaveDays', function($q){
                        $q->where('status', 'Active')->where('supervision_status', '!=', 1);
                    })->where('status', 'Pending')->where('leave_type', 1)->orderBy('employee_id', 'ASC')->get();
        if(!empty($leaves)):
            foreach($leaves as $leave):
                $employee_id = $leave->employee_id;
                $employee_line_managers = EmployeeLineManager::where('employee_id', $employee_id)->get()->pluck('user_id')->unique()->toArray();
                $approvers = [];
                if(isset($leave->employee->holidayAuth) && $leave->employee->holidayAuth->count() > 0):
                    foreach($leave->employee->holidayAuth as $supervisor):
                        $approver = User::find($supervisor->user_id);
                        $approvers[] = (isset($approver->employee->full_name) && !empty($approver->employee->full_name) ? $approver->employee->full_name : $approver->name);
                    endforeach;
                endif;
                if(!empty($employee_line_managers)):
                    foreach($employee_line_managers as $manager):
                        $managerUser = User::with('employee')->find($manager);
                        $manager_employee_id = $managerUser->employee->id;
                        //if($manager_employee_id == 41 || $manager_employee_id == 34):
                        
                        $managerLineManagers = EmployeeLineManager::where('employee_id', $manager_employee_id)->get()->pluck('user_id')->unique()->toArray();
                        foreach($managerLineManagers as $mlm):
                            $requestedBy = (isset($leave->employee->title->name) ? $leave->employee->title->name.' ' : '').$leave->employee->full_name;
                            $theUser = User::with('employee')->find($mlm);
                            $recipientName = isset($theUser->employee->full_name) ? $theUser->employee->full_name : $theUser->name;
                            $approverList = !empty($approvers) ? implode(', ', $approvers) : 'N/A';
                            $holidayYear = (isset($leave->year) && !empty($leave->year))
                                ? date('Y', strtotime($leave->year->start_date)).'-'.date('Y', strtotime($leave->year->end_date))
                                : 'N/A';
                            $daysCount = (isset($leave->leaveDays) && $leave->leaveDays->count() > 0)
                                ? $leave->leaveDays->count().' days'
                                : '0 days';
                            $leaveDates = isset($leave->leaveDays) && $leave->leaveDays->count() > 0
                                ? $leave->leaveDays->pluck('leave_date')->unique()->toArray()
                                : [];
                            $leaveDateList = !empty($leaveDates) ? implode(', ', $leaveDates) : 'N/A';
                            $requestedOnDate = date('jS F, Y', strtotime($leave->created_at));
                            $requestedOnTime = date('H:i', strtotime($leave->created_at));

                            $content = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Request Escalation Notice</title>
</head>

<body style="margin:0; padding:0; background-color:#eef2f7; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7; padding:36px 0;">
        <tr>
            <td align="center">

                <table width="680" cellpadding="0" cellspacing="0" style="width:680px; max-width:94%; background-color:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 20px 45px rgba(15,23,42,0.16);">

                    <tr>
                        <td style="background:linear-gradient(135deg,#b7dff1 0%,#5fa8cf 45%,#1f5f8f 100%); padding:34px 38px; text-align:center;">

                            <img
                                src="https://sms.lcc.ac.uk/storage/company_logo.png"
                                alt="London Churchill College"
                                style="display:block; margin:0 auto 22px auto; max-width:265px; height:auto;"
                            >

                            <div style="display:inline-block; background-color:rgba(11,42,74,0.18); color:#0b2a4a; padding:7px 14px; border-radius:999px; font-size:12px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">
                                HR Escalation Notice
                            </div>

                            <h1 style="margin:18px 0 0 0; color:#0b2a4a; font-size:25px; line-height:1.3; font-weight:700;">
                                Pending Leave Request Requires Attention
                            </h1>

                            <p style="margin:10px 0 0 0; color:#17324a; font-size:15px; line-height:1.5; text-align:center;">
                                The approval deadline has passed and the request remains incomplete.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#fff7ed; border-bottom:1px solid #fed7aa; padding:16px 38px;">
                            <p style="margin:0; font-size:14px; color:#9a3412; line-height:1.5; text-align:justify;">
                                <strong>Action Required:</strong> Please speak with the assigned approver and request that the leave request is processed as soon as possible.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:34px 38px 28px 38px; font-size:15px; line-height:1.7; color:#374151; text-align:justify;">

                            <p style="margin-top:0; text-align:left;">Dear <strong>{$recipientName}</strong>,</p>

                            <p style="text-align:justify;">
                                This is to inform you that the leave request submitted by
                                <strong>{$requestedBy}</strong> remains pending and has not been processed within the required
                                <strong>five working days</strong> by the assigned approver(s),
                                <strong>{$approverList}</strong>.
                            </p>

                            <p style="text-align:justify;">
                                Please find the leave request details below for your reference:
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0; border:1px solid #e5e7eb; border-radius:14px; overflow:hidden; border-collapse:separate; border-spacing:0; text-align:left;">

                                <tr>
                                    <td colspan="2" style="background-color:#f8fafc; padding:16px 20px; border-bottom:1px solid #e5e7eb;">
                                        <span style="font-size:15px; font-weight:700; color:#0f172a;">
                                            Leave Request Details
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 20px; width:38%; border-bottom:1px solid #eef2f7; font-size:13px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.3px;">
                                        Holiday Year
                                    </td>
                                    <td style="padding:14px 20px; border-bottom:1px solid #eef2f7; color:#111827;">
                                        {$holidayYear}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 20px; border-bottom:1px solid #eef2f7; font-size:13px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.3px;">
                                        Number of Days
                                    </td>
                                    <td style="padding:14px 20px; border-bottom:1px solid #eef2f7; color:#111827;">
                                        {$daysCount}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 20px; border-bottom:1px solid #eef2f7; font-size:13px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.3px;">
                                        Leave Dates
                                    </td>
                                    <td style="padding:14px 20px; border-bottom:1px solid #eef2f7; color:#111827;">
                                        {$leaveDateList}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 20px; border-bottom:1px solid #eef2f7; font-size:13px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.3px;">
                                        Requested By
                                    </td>
                                    <td style="padding:14px 20px; border-bottom:1px solid #eef2f7; color:#111827;">
                                        {$requestedBy}<br>
                                        <span style="font-size:13px; color:#64748b;">{$requestedOnDate} at {$requestedOnTime}</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 20px; font-size:13px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.3px;">
                                        Assigned Approver(s)
                                    </td>
                                    <td style="padding:14px 20px; color:#111827;">
                                        {$approverList}
                                    </td>
                                </tr>

                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:26px 0; background-color:#f8fafc; border-left:5px solid #1f5f8f; border-radius:10px; text-align:justify;">
                                <tr>
                                    <td style="padding:18px 20px; color:#334155; font-size:14px; line-height:1.7; text-align:justify;">
                                        As the approval deadline has now passed, this matter has been escalated for your attention. We would be grateful if you could kindly speak with the approver and ask them to process the request as soon as possible.
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align:justify;">
                                Thank you for your prompt attention to this matter.
                            </p>

                            <p style="margin-bottom:0; text-align:left;">
                                Sincerely,<br>
                                <strong>HR Department</strong><br>
                                London Churchill College
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f8fafc; padding:26px 38px; border-top:1px solid #e5e7eb;">

                            <p style="margin:0 0 14px 0; font-size:12px; line-height:1.6; color:#64748b; text-align:justify;">
                                This e-mail and its attachments are intended for the above-named recipient only and may be confidential. If you have received this e-mail in error, you must take no action based on it, nor copy or show it to anyone. Please reply to this e-mail and notify us of the error.
                            </p>

                            <p style="margin:0 0 18px 0; font-size:12px; line-height:1.6; color:#64748b; text-align:justify;">
                                Although this e-mail and any attachments are believed to be free from viruses or other defects that may affect any computer or IT system on which they are received and opened, it is the responsibility of the recipient to ensure that they are virus-free. London Churchill College accepts no responsibility for any loss or damage arising from their use.
                            </p>

                            <p style="margin:0; font-size:13px; line-height:1.5; color:#334155; border-top:1px solid #e5e7eb; padding-top:16px; text-align:left;">
                                <strong>London Churchill College</strong><br>
                                Barclay Hall, 156B Green Street, London, E7 8JQ<br>
                                +44 (0) 207 377 1077
                            </p>

                        </td>
                    </tr>

                </table>

                <p style="margin:18px 0 0 0; font-size:12px; color:#94a3b8; text-align:center;">
                    Automated notification from London Churchill College HR Department
                </p>

            </td>
        </tr>
    </table>

</body>
</html>
HTML;

                            UserMailerJob::dispatch($configuration, [$theUser->email, 'hr@lcc.ac.uk'], new CommunicationSendMail($subject, $content, [],false));
                        endforeach;
                        //endif;
                    endforeach;
                endif;
            endforeach;
        endif;


        // $lineManagers = EmployeeLineManager::orderBy('id', 'ASC')->get()->pluck('user_id')->unique()->toArray();
        // if(!empty($lineManagers)):
        //     foreach($lineManagers as $manager_id):
        //         $user = User::find($manager_id);
        //         $employees = EmployeeLineManager::where('user_id', $manager_id)->get()->pluck('employee_id')->unique()->toArray();
        //         if(!empty($employees)):
        
        //             $leaves = EmployeeLeave::with('employee', 'leaveDays', 'employee.holidayAuth', 'year')->whereIn('employee_id', $employees)->where('created_at', '<', $submissionDate)
        //                       ->whereHas('leaveDays', function($q){
        //                             $q->where('status', 'Active')->where('supervision_status', '!=', 1);
        //                       })->where('status', 'Pending')->where('leave_type', 1)->orderBy('employee_id', 'ASC')->get();
        //             if($leaves->count() > 0):
        //                 foreach($leaves as $leave):
        //                     $empName = (isset($leave->employee->title->name) ? $leave->employee->title->name.' ' : '').$leave->employee->full_name;
        //                     $approvers = [];
        //                     if(isset($leave->employee->holidayAuth) && $leave->employee->holidayAuth->count() > 0):
        //                         foreach($leave->employee->holidayAuth as $supervisor):
        //                             $approver = User::find($supervisor->user_id);
        //                             $approvers[] = (isset($approver->employee->full_name) && !empty($approver->employee->full_name) ? $approver->employee->full_name : $approver->name);
        //                         endforeach;
        //                     endif;
        //                     $leaveDates = isset($leave->leaveDays) && $leave->leaveDays->count() > 0 ? $leave->leaveDays->pluck('leave_date')->unique()->toArray() : '';
        //                     $content = '';
        //                     $content .= '<p>Dear '.(isset($user->employee->full_name) ? $user->employee->full_name : $user->name).',</p>';
        //                     $content .= '<p>This is to inform you that the leave request submitted by <strong>'.$empName.'</strong> remains pending and has 
        //                                 not been completed within the required 5 working days by the assigned approver(s), 
        //                                 <strong>'.(!empty($approvers) ? implode(', ', $approvers) : '').'</strong>.</p>';
        //                     $content .= '<p>Please find the leave request details below for your reference:</p>';
        //                     $content .= '<p>';
        //                         $content .= '<strong>Holiday Year:</strong> '.(isset($leave->year) && !empty($leave->year) ? date('Y', strtotime($leave->year->start_date )).'-'.date('Y', strtotime($leave->year->end_date )): '').'<br/>';
        //                         $content .= '<strong>Number of Days:</strong> '.(isset($leave->leaveDays) && $leave->leaveDays->count() > 0 ? $leave->leaveDays->count().' days' : '0 days').'<br/>';
        //                         $content .= '<strong>Leave Dates:</strong> '.(!empty($leaveDates) ? implode(', ', $leaveDates) : '').'<br/>';
        //                         $content .= '<strong>Requested By:</strong> '.$empName.' on '.date('jS F, Y', strtotime($leave->created_at)).' at '.date('H:i', strtotime($leave->created_at)).'<br/>';
        //                         $content .= '<strong>Assigned Approver(s):</strong> '.(!empty($approvers) ? implode(', ', $approvers) : '');
        //                     $content .= '</p>';

        //                     $content .= '<p>As the approval deadline has now passed, this matter has been escalated for your attention. We would be grateful if you could kindly speak with the approver and ask them to process the request as soon as possible.</p>';
        //                     $content .= '<p>Thank you for your prompt attention to this matter.</p>';
        //                     $content .= '<p>Sincerely,<br/>HR Department<br/>London Churchill College</p>';

        //                     UserMailerJob::dispatch($configuration, [$user->email, 'hr@lcc.ac.uk'], new CommunicationSendMail($subject, $content, []));
        //                 endforeach;
        //             endif;
        //         endif;
        //     endforeach;
        // endif;

        return 0;
    }
}
