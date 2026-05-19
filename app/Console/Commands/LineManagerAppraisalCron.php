<?php

namespace App\Console\Commands;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\EmployeeAppraisal;
use App\Models\EmployeeLineManager;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LineManagerAppraisalCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'linemanagerappraisal:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Appraisal Cron Job For Line Manager.';
  
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
        $subject = 'Employee Appraisal Reminder - Overdue & Upcoming Reviews';

        $expireDate = Carbon::now()->addDays(30)->format('Y-m-d');
        $lineManagers = EmployeeLineManager::orderBy('id', 'ASC')->get()->pluck('user_id')->unique()->toArray();
        if(!empty($lineManagers)):
            foreach($lineManagers as $manager_id):
                $user = User::find($manager_id);
                $employees = EmployeeLineManager::where('user_id', $manager_id)->get()->pluck('employee_id')->unique()->toArray();
                if(!empty($employees)):
                    $overDues = EmployeeAppraisal::whereIn('employee_id', $employees)->where('due_on', '<', date('Y-m-d'))->whereNull('completed_on')
                            ->whereHas('employee', function($q){
                                    $q->where('status', 1);
                            })->orderBy('due_on', 'ASC')->get();
                    $upcommings = EmployeeAppraisal::whereIn('employee_id', $employees)->where('due_on', '>=', date('Y-m-d'))
                            ->where('due_on', '<=', $expireDate)->whereNull('completed_on')
                            ->whereHas('employee', function($q){
                                $q->where('status', 1);
                            })->orderBy('due_on', 'ASC')->get();
                    if((!empty($overDues) && $overDues->count() > 0) || (!empty($upcommings) && $upcommings->count() > 0) ):
                        $content = '';
                        $content .= '<!DOCTYPE html>
                            <html>
                            <head>
                            <meta charset="UTF-8">
                            <title>Appraisal Action Required</title>
                            </head>
                            
                            <body style="margin:0; padding:0; background-color:#eef2f7; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7; padding:36px 0;">
                            <tr>
                            <td align="center">
                            
                                    <table width="680" cellpadding="0" cellspacing="0" style="width:680px; max-width:94%; background-color:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 20px 45px rgba(15,23,42,0.16);">
                            
                                    <!-- Header -->
                            <tr>
                            <td style="background:linear-gradient(135deg,#b7dff1 0%,#5fa8cf 45%,#1f5f8f 100%); padding:34px 38px; text-align:center;">
                            
                                        <img 
                                            src="https://sms.lcc.ac.uk/storage/company_logo.png"
                                            alt="London Churchill College"
                                            style="display:block; margin:0 auto 22px auto; max-width:265px; height:auto;"
                            >
                            
                                        <div style="display:inline-block; background-color:rgba(11,42,74,0.18); color:#0b2a4a; padding:7px 14px; border-radius:999px; font-size:12px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">
                                            Appraisal Reminder
                            </div>
                            
                                        <h1 style="margin:18px 0 0 0; color:#0b2a4a; font-size:25px; line-height:1.3; font-weight:700;">
                                            Employee Appraisals Require Attention
                            </h1>
                            
                                        <p style="margin:10px 0 0 0; color:#17324a; font-size:15px; line-height:1.5; text-align:center;">
                                            Please review the appraisal records that are overdue or approaching their due date.
                            </p>
                            
                                        </td>
                            </tr>
                            
                                    <!-- Status Bar -->
                            <tr>
                            <td style="background-color:#fff7ed; border-bottom:1px solid #fed7aa; padding:16px 38px;">
                            <p style="margin:0; font-size:14px; color:#9a3412; line-height:1.5; text-align:justify;">
                            <strong>Action Required:</strong> Appraisal completion needs to be coordinated with HR.
                            </p>
                            </td>
                            </tr>
                            
                                    <!-- Body -->
                            <tr>
                            <td style="padding:34px 38px 28px 38px; font-size:15px; line-height:1.7; color:#374151; text-align:justify;">
                            
                                        <p style="margin-top:0; text-align:left;">
                                            Dear <strong>'.(isset($user->employee->full_name) ? $user->employee->full_name : $user->name).'</strong>,
                            </p>
                            
                                        <p style="text-align:justify;">
                                            Please review the appraisal summary below and ensure the necessary follow-up is completed within the required timeframe.
                            </p>';
                            
                            if(!empty($overDues) && $overDues->count() > 0):
                            
                                $content .= '
                            <!-- Overdue Appraisals -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:26px 0; border-collapse:collapse; border:1px solid #fecaca; border-radius:10px; overflow:hidden;">
                            <tr>
                            <td style="background-color:#fef2f2; padding:14px 18px; font-size:15px; font-weight:700; color:#991b1b; border-bottom:1px solid #fecaca;">
                                                Overdue Appraisals
                            </td>
                            </tr>
                            <tr>
                            <td style="padding:0;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">';
                            
                                foreach($overDues as $ovd):
                            
                                    $date = Carbon::parse($ovd->due_on);
                                    $now = Carbon::now();
                                    $diif = $date->diffInDays($now).' days';
                            
                                    $empName = (isset($ovd->employee->title->name) ? $ovd->employee->title->name.' ' : '').$ovd->employee->full_name;
                            
                                    $content .= '
                            <tr>
                            <td style="padding:13px 18px; color:#374151; font-size:14px; border-bottom:1px solid #fee2e2;">
                            <strong>'.$empName.'</strong><br>
                            <span style="color:#64748b;">Due date:</span> <strong>'.date('d F, Y', strtotime($ovd->due_on)).'</strong><br>
                            <span style="color:#991b1b; font-weight:700;">'.$diif.' overdue</span>
                            </td>
                            </tr>';
                            
                                endforeach;
                            
                                $content .= '
                            </table>
                            </td>
                            </tr>
                            </table>';
                            
                            endif;
                            
                            if(!empty($upcommings) && $upcommings->count() > 0):
                            
                                $content .= '
                            <!-- Due Soon Appraisals -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:26px 0; border-collapse:collapse; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                            <tr>
                            <td style="background-color:#f8fafc; padding:14px 18px; font-size:15px; font-weight:700; color:#1f2937; border-bottom:1px solid #e5e7eb;">
                                                Due Soon Appraisals
                            </td>
                            </tr>
                            <tr>
                            <td style="padding:0;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">';
                            
                                foreach($upcommings as $ovd):
                            
                                    $empName = (isset($ovd->employee->title->name) ? $ovd->employee->title->name.' ' : '').$ovd->employee->full_name;
                            
                                    $content .= '
                            <tr>
                            <td style="padding:13px 18px; color:#374151; font-size:14px; border-bottom:1px solid #e5e7eb;">
                            <strong>'.$empName.'</strong><br>
                            <span style="color:#64748b;">Due date:</span> <strong>'.date('d F, Y', strtotime($ovd->due_on)).'</strong>
                            </td>
                            </tr>';
                            
                                endforeach;
                            
                                $content .= '
                            </table>
                            </td>
                            </tr>
                            </table>';
                            
                            endif;
                            
                            if(!empty($overDues) && $overDues->count() > 0):
                            
                                $content .= '
                            <!-- Closing Note -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:26px 0; background-color:#f8fafc; border-left:5px solid #1f5f8f; border-radius:10px; text-align:justify;">
                            <tr>
                            <td style="padding:18px 20px; color:#334155; font-size:14px; line-height:1.7; text-align:justify;">
                                                '.$overDues->count().' appraisal is already overdue. Please coordinate with HR and ensure all pending reviews are completed as soon as possible.
                            </td>
                            </tr>
                            </table>';
                            
                            else:
                            
                                $content .= '
                            <!-- Closing Note -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:26px 0; background-color:#f8fafc; border-left:5px solid #1f5f8f; border-radius:10px; text-align:justify;">
                            <tr>
                            <td style="padding:18px 20px; color:#334155; font-size:14px; line-height:1.7; text-align:justify;">
                                                Please coordinate with HR and make sure all upcoming appraisals are completed on time.
                            </td>
                            </tr>
                            </table>';
                            
                            endif;
                            
                            $content .= '
                            <p style="text-align:justify; margin-bottom:0;">
                                            Thank you for your prompt cooperation.
                            </p>
                            
                                        </td>
                            </tr>
                            
                                    <!-- Footer -->
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
                                    Automated notification from London Churchill College
                            </p>
                            
                                </td>
                            </tr>
                            </table>
                            
                            </body>
                            </html>';

                        UserMailerJob::dispatch($configuration, [$user->email, 'hr@lcc.ac.uk'], new CommunicationSendMail($subject, $content, [], false));
                    endif;
                endif;
            endforeach;
        endif;

        return 0;
    }
}
