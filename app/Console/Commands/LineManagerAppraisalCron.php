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
                        $content .= '<p>Dear '.(isset($user->employee->full_name) ? $user->employee->full_name : $user->name).',</p>';
                        $content .= '<p>I am writing to bring to your urgent attention the employee appraisals that require immediate action. Please review the details below and coordinate with HR to ensure all appraisals are completed within the required timeframe.</p>';
                        $content .= '<hr>';
                        if(!empty($overDues) && $overDues->count() > 0):
                            $content .= '<h3 style="color: red;"><u>Overdue Appraisal</u></h3>';
                            $content .= '<ul>';
                                foreach($overDues as $ovd):
                                    $date = Carbon::parse($ovd->due_on);
                                    $now = Carbon::now();
                                    $diif = $date->diffInDays($now).' days';

                                    $empName = (isset($ovd->employee->title->name) ? $ovd->employee->title->name.' ' : '').$ovd->employee->full_name;
                                    $content .= '<li><strong>'.$empName.'</strong> - Due date <strong>'.date('d F, Y', strtotime($ovd->due_on)).'</strong> - <strong>'.$diif.' Overdue</strong></li>';
                                endforeach;
                            $content .= '</ul>';

                            // $content .= '<table style="width: 100%; border: 1px solid #DDD;">';
                            //     $content .= '<thead>';
                            //         $content .= '<tr>';
                            //             $content .= '<th style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">Name</th>';
                            //             $content .= '<th style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">Department</th>';
                            //             $content .= '<th style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">Date</th>';
                            //             $content .= '<th style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">Due By</th>';
                            //         $content .= '</tr>';
                            //     $content .= '</thead>';
                            //     $content .= '<tbody>';
                            //         foreach($overDues as $ovd):
                            //             $empName = (isset($ovd->employee->title->name) ? $ovd->employee->title->name.' ' : '').$ovd->employee->full_name;
                            //             $content .= '<tr>';
                            //                 $content .= '<td style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">';
                            //                     $content .= '<strong style="text-transform: uppercase; font-size: 12px; line-height: normal;">'.$empName.'</strong><br/>';
                            //                     if(isset($ovd->employee->employment->employeeJobTitle->name) && !empty($ovd->employee->employment->employeeJobTitle->name)):
                            //                         $content .= '<small style="color: #555; line-height: normal;">'.$ovd->employee->employment->employeeJobTitle->name.'</small>';
                            //                     endif;
                            //                 $content .= '</td>';
                            //                 $content .= '<td style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">'.(isset($ovd->employee->employment->department->name) ? $ovd->employee->employment->department->name : '').'</td>';
                            //                 $content .= '<td style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">'.date('jS M, Y', strtotime($ovd->due_on)).'</td>';
                            //                 $content .= '<td style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">';
                            //                     $date = Carbon::parse($ovd->due_on);
                            //                     $now = Carbon::now();

                            //                     $content .= $date->diffInDays($now).' Days';
                            //                 $content .= '</td>';
                            //             $content .= '</tr>';
                            //         endforeach;
                            //     $content .= '</tbody>';
                            // $content .= '</table>';
                        endif;

                        if(!empty($upcommings) && $upcommings->count() > 0):
                            $content .= '<h3><u>Due Soon Appraisals</u></h3>';

                            $content .= '<ul>';
                            foreach($upcommings as $ovd):
                                $empName = (isset($ovd->employee->title->name) ? $ovd->employee->title->name.' ' : '').$ovd->employee->full_name;
                                $content .= '<li><strong>'.$empName.'</strong> - Due date <strong>'.date('d F, Y', strtotime($ovd->due_on)).'</strong></li>';
                            endforeach;
                            $content .= '</ul>';


                            // $content .= '<table style="width: 100%; border: 1px solid #DDD;">';
                            //     $content .= '<thead>';
                            //         $content .= '<tr>';
                            //             $content .= '<th style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">Name</th>';
                            //             $content .= '<th style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">Department</th>';
                            //             $content .= '<th style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">Date</th>';
                            //             $content .= '<th style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">Due In</th>';
                            //         $content .= '</tr>';
                            //     $content .= '</thead>';
                            //     $content .= '<tbody>';
                            //         foreach($upcommings as $ovd):
                            //             $empName = (isset($ovd->employee->title->name) ? $ovd->employee->title->name.' ' : '').$ovd->employee->full_name;
                            //             $content .= '<tr>';
                            //                 $content .= '<td style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">';
                            //                     $content .= '<strong style="text-transform: uppercase; font-size: 12px; line-height: normal;">'.$empName.'</strong><br/>';
                            //                     if(isset($ovd->employee->employment->employeeJobTitle->name) && !empty($ovd->employee->employment->employeeJobTitle->name)):
                            //                         $content .= '<small style="color: #555; line-height: normal;">'.$ovd->employee->employment->employeeJobTitle->name.'</small>';
                            //                     endif;
                            //                 $content .= '</td>';
                            //                 $content .= '<td style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">'.(isset($ovd->employee->employment->department->name) ? $ovd->employee->employment->department->name : '').'</td>';
                            //                 $content .= '<td style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">'.date('jS M, Y', strtotime($ovd->due_on)).'</td>';
                            //                 $content .= '<td style="text-align: left; padding: 4px 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">';
                            //                     $date = Carbon::parse($ovd->due_on);
                            //                     $now = Carbon::now();

                            //                     $content .= $date->diffInDays($now).' Days';
                            //                 $content .= '</td>';
                            //             $content .= '</tr>';
                            //         endforeach;
                            //     $content .= '</tbody>';
                            // $content .= '</table>';
                        endif;
                        if(!empty($overDues) && $overDues->count() > 0):
                            $content .= '<p>As '.$overDues->count().' appraisal is already overdue and the remaining appraisals are due soon, I would appreciate your urgent attention to this matter. Please communicate with HR and ensure all pending reviews are completed on time. Thank you for your prompt cooperation.</p>';
                        else:
                            $content .= '<p>Please coordinate with HR and make sure all upcoming appraisals are completed on time.  Thank you for your prompt cooperation.</p>';
                        endif;
                        $content .= '<p>Kind regards,<br/>HR Department<br/>London Churchill College</p>';

                        UserMailerJob::dispatch($configuration, [$user->email, 'hr@lcc.ac.uk'], new CommunicationSendMail($subject, $content, []));
                    endif;
                endif;
            endforeach;
        endif;

        return 0;
    }
}
