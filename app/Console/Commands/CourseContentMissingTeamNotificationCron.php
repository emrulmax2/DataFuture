<?php

namespace App\Console\Commands;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Course;
use App\Models\Option;
use App\Models\Plan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CourseContentMissingTeamNotificationCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coursecontentmissingteamnotification:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Course Content Module Document Missing Notification Send to Course Team Members.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $latest_term_declaration = Plan::orderBy('term_declaration_id', 'DESC')->get()->first();
        $term_declaration_id = $latest_term_declaration->term_declaration_id;

        $plans = Plan::where('term_declaration_id', $term_declaration_id)->where('class_type', 'Theory')->whereHas('tasks', function($q){
            $q->has('uploads', '=', 0);
        })->orderBy('id', 'ASC')->orderBy('course_id', 'ASC')->orderBy('tutor_id', 'ASC')->get();
        $plan_ids = $plans->pluck('id')->unique()->toArray();

        $res = [];
        if(!empty($plans)):
            $i = 1;
            foreach($plans as $pln):
                $empUploads = [];
                foreach($pln->tasks as $tsk):
                    if($tsk->uploads->count() == 0):
                        if($tsk->last_date && $tsk->last_date <= date('Y-m-d')):
                            $empUploads[] = $tsk->eLearn->short_code;
                        endif;
                    endif;
                endforeach;
                if(!empty($empUploads) && count($empUploads) > 0):
                    $res[$pln->course_id][$pln->tutor_id][$i]['group'] = $pln->group->name;
                    $res[$pln->course_id][$pln->tutor_id][$i]['module'] = $pln->creations->module_name;
                    $res[$pln->course_id][$pln->tutor_id][$i]['tsks'] = implode(', ', $empUploads);
                endif;
                $i++;
            endforeach;
        endif;
        if(!empty($res)):
            foreach($res as $course_id => $tutor_tasks):
                $course = Course::find($course_id);
                if(isset($course->team->email) && !empty($course->team->email)):
                    $mailTo = [];
                    $mailTo[] = $course->team->email;
                    $subject = 'Missing Course Content - Require your Attention';
                    //$fileName = $this->generateCronAttachments($course_id, $tutor_tasks);

                    /* Generate PDF Start */
                    $report_title = $course->name.' Missing Required Module Documents';
                    $PDFHTML = '';
                    $PDFHTML .= '<html>';
                        $PDFHTML .= '<head>';
                            $PDFHTML .= '<title>'.$report_title.'</title>';
                            $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                            $PDFHTML .= '<style>
                                            body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: #1e293b; padding-top: 10px;}
                                            table{margin-left: 0px; width: 100%; border-collapse: collapse;}
                                            figure{margin: 0;}
                                            @page{margin-top: 110px;margin-left: 85px !important; margin-right:85px !important; }

                                            header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -90px;}
                                            .headerTable tr td{vertical-align: top; padding: 0; line-height: 13px;}
                                            .headerTable img{height: 70px; width: auto;}
                                            .headerTable tr td.reportTitle{font-size: 16px; line-height: 16px; font-weight: bold;}

                                            footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                            .pageCounter{position: relative;}
                                            .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                            .pinRow td{border-bottom: 1px solid gray;}
                                            .text-center{text-align: center;}
                                            .text-left{text-align: left;}
                                            .text-right{text-align: right;}
                                            @media print{ .pageBreak{page-break-after: always;} }
                                            .pageBreak{page-break-after: always;}
                                            
                                            .mb-15{margin-bottom: 15px;}
                                            .mb-10{margin-bottom: 10px;}
                                            .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                            .table-sm th, .table-sm td{padding: 5px 10px;}
                                            .w-1/6{width: 16.666666%;}
                                            .w-2/6{width: 33.333333%;}
                                            .table.attenRateReportTable tr th, .table.attenRateReportTable tr td{ text-align: left;}
                                            .table.attenRateReportTable tr th a{ text-decoration: none; color: #1e293b; }
                                        </style>';
                        $PDFHTML .= '</head>';

                        $PDFHTML .= '<body>';
                            $PDFHTML .= '<header>';
                                $PDFHTML .= '<table class="headerTable">';
                                    $PDFHTML .= '<tr>';
                                        $PDFHTML .= '<td colspan="2" class="reportTitle">Missing Course Content</td>';
                                        $PDFHTML .= '<td rowspan="3" class="text-right"><img src="https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/LCC_LOGO_01_263_100.png" alt="London Churchill College"/></td>';
                                    $PDFHTML .= '</tr>';
                                    $PDFHTML .= '<tr>';
                                        $PDFHTML .= '<td>Course</td>';
                                        $PDFHTML .= '<td>'.$course->name.'</td>';
                                    $PDFHTML .= '</tr>';
                                    $PDFHTML .= '<tr>';
                                        $PDFHTML .= '<td>Cereated By</td>';
                                        $PDFHTML .= '<td>';
                                            $PDFHTML .= 'System';
                                            $PDFHTML .= '<br/>'.date('jS M, Y').' at '.date('h:i A');
                                        $PDFHTML .= '</td>';
                                    $PDFHTML .= '</tr>';
                                $PDFHTML .= '</table>';
                            $PDFHTML .= '</header>';

                            $PDFHTML .= '<table class="table table-bordered table-sm attenRateReportTable">';
                                $PDFHTML .= '<thead>';
                                    $PDFHTML .= '<tr>';
                                        $PDFHTML .= '<th>Tutor</th>';
                                        $PDFHTML .= '<th>Group</th>';
                                        $PDFHTML .= '<th>Module</th>';
                                        $PDFHTML .= '<th>Document</th>';
                                    $PDFHTML .= '</tr>';
                                $PDFHTML .= '</thead>';
                                $PDFHTML .= '<tbody>';
                                    foreach($tutor_tasks as $tutor_id => $modules):
                                        $tutor = User::with('employee')->find($tutor_id);
                                        $row = 1;
                                        foreach($modules as $module):
                                            $PDFHTML .= '<tr>';
                                                if($row == 1):
                                                    $PDFHTML .= '<td style="'.(count($modules) > 1 ? 'border-bottom: none;' : '').'">'.(isset($tutor->employee->full_name) && !empty($tutor->employee->full_name) ? $tutor->employee->full_name : $tutor->name).'</td>';
                                                else:
                                                    $PDFHTML .= '<td style="border-top: none; border-bottom: none;">&nbsp;</td>';
                                                endif;
                                                $PDFHTML .= '<td>'.$module['group'].'</td>';
                                                $PDFHTML .= '<td>'.$module['module'].'</td>';
                                                $PDFHTML .= '<td>'.$module['tsks'].'</td>';
                                            $PDFHTML .= '</tr>';
                                            $row++;
                                        endforeach;
                                    endforeach;
                                $PDFHTML .= '</tbody>';
                            $PDFHTML .= '</table>';

                        $PDFHTML .= '</body>';
                    $PDFHTML .= '</html>';

                    $fileName = $course_id.'_missing_course_content.pdf';
                    if (Storage::disk('s3')->exists('public/'.$fileName)):
                        Storage::disk('s3')->delete('public/'.$fileName);
                    endif;
                    $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
                                ->setPaper('a4', 'portrait')
                                ->setWarnings(false);
                    $content = $pdf->output();
                    Storage::disk('s3')->put('public/'.$fileName, $content);
                    /* Generate PDF END */


                    $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
                    $configuration = [
                        'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                        'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                        'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                        'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                        'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                        
                        'from_email'    => $commonSmtp->smtp_user,
                        'from_name'    =>  'London Churchill College',
                    ];

                    $attachmentFiles = [];
                    $attachmentFiles[] = [
                        "pathinfo" => 'public/'.$fileName,
                        "nameinfo" => $fileName,
                        "mimeinfo" => 'application/pdf',
                        "disk" => 's3'
                    ];

                    $MAILBODY = 'Dear Concern,<br/><br/>';
                    $MAILBODY .= '<p>I am writing to bring to your attention that there appear to be some course materials missing from the '.$course->name.'.</p>';
                    $MAILBODY .= '<p>These materials are essential and other students to fully understand the subject matter and complete the course requirements effectively.</p>';
                    $MAILBODY .= '<p>Could you kindly look into this matter and contact the relevant person , when the missing content might be made available? Your prompt attention to this would be greatly appreciated.</p>';
                    $MAILBODY .= '<p>Please find the attached list for your reference.</p>';
                    $MAILBODY .= '<p>Thank you for your time and support.</p>';
                    $MAILBODY .= 'Best regards,<br/>';
                    $MAILBODY .= 'System<br/>';
                    $MAILBODY .= 'London Churchill College';

                    UserMailerJob::dispatch($configuration, $mailTo, new CommunicationSendMail($subject, $MAILBODY, $attachmentFiles));
                endif;
            endforeach;
        endif;

        return 0;
    }
}
