<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Employee;
use App\Models\EmployeeAppraisal;
use App\Models\EmployeeDocuments;
use App\Models\EmployeeEligibilites;
use App\Models\LetterHeaderFooter;
use App\Models\Option;
use Carbon\Carbon;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeAppraisalCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employeeappraisal:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Employee Appraisal Cron Job.';
  
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
     *
     * @return int
     */
    public function handle()
    {
        $expireDate = Carbon::now()->addDays(30)->format('Y-m-d');
        $overDues = EmployeeAppraisal::where('due_on', '<', date('Y-m-d'))->whereNull('completed_on')
                           ->whereHas('employee', function($q){
                                $q->where('status', 1);
                           })->orderBy('due_on', 'ASC')->get();
        $upcommings = EmployeeAppraisal::where('due_on', '>=', date('Y-m-d'))
                        ->where('due_on', '<=', $expireDate)
                        ->whereNull('completed_on')
                        ->whereHas('employee', function($q){
                            $q->where('status', 1);
                        })->orderBy('due_on', 'ASC')->get();

        
        if($overDues->count() > 0 || $upcommings->count() > 0):
            $companyReg = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_registration')->get()->first();
            $LetterHeader = LetterHeaderFooter::where('for_staff', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
            $LetterFooter = LetterHeaderFooter::where('for_staff', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get()->first();
            $PDF_title = 'Employee Appraisal Report';

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
            $mailTo = [];
            $mailTo[] = 'hr@lcc.ac.uk';

            $PDFHTML = '';
            $PDFHTML .= '<html>';
                $PDFHTML .= '<head>';
                    $PDFHTML .= '<title>'.$PDF_title.'</title>';
                    $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                    $PDFHTML .= '<style>
                                    body{font-family: "Times New Roman", Times, serif;}
                                    table{margin-left: 0px;}
                                    figure{margin: 0;}
                                    @page{margin-top: 105px;margin-left: 30px;margin-right: 30px;margin-bottom: 90px;}
                                    header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -80px;}
                                    footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px; margin-bottom: -120px;}
                                    .regInfoRow td{border-top: 1px solid gray;}
                                    .text-center{text-align: center;}
                                    .text-left{text-align: left;}
                                    .text-right{text-align: right;}

                                    .bodyContainer{font-size: 13px; line-height: normal; padding: 0 30px;}
                                    .tableTitle{font-size: 22px; font-weight: bold; color: #000; line-height: 22px; margin: 0;}
                                    .employeeInfo{line-height: normal;}
                                    .mb-30{margin-bottom: 30px;}
                                    .mb-20{margin-bottom: 20px;}
                                    .mb-15{margin-bottom: 15px;}
                                    .text-justify{text-align: justify;}
                                
                                    .table {width: 100%; text-align: left; text-indent: 0; border-color: inherit; border-collapse: collapse;}
                                    .table th {border-style: solid;border-color: #e5e7eb;border-bottom-width: 2px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;font-weight: 500;}
                                    .table td {border-style: solid;border-color: #e5e7eb; border-bottom-width: 1px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;}

                                    .table.table-bordered th, .table.table-bordered td {border-left-width: 1px;border-right-width: 1px;border-top-width: 1px;}

                                    .table.table-sm th {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}
                                    .table.table-sm td {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}
                                </style>';
                $PDFHTML .= '</head>';
                $PDFHTML .= '<body>';
                    if(isset($LetterHeader->current_file_name) && !empty($LetterHeader->current_file_name) && Storage::disk('local')->exists('public/letterheaderfooter/header/'.$LetterHeader->current_file_name)):
                        $PDFHTML .= '<header>';
                            $PDFHTML .= '<img style="width: 100%; height: auto;" src="'.url('storage/letterheaderfooter/header/'.$LetterHeader->current_file_name).'"/>';
                        $PDFHTML .= '</header>';
                    endif;

                    $PDFHTML .= '<footer>';
                        $PDFHTML .= '<table style="width: 100%; border: none; margin: 0; vertical-align: middle !important; 
                                    font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;border-spacing: 0;border-collapse: collapse;">';
                            if(isset($LetterFooter->current_file_name) && !empty($LetterFooter->current_file_name) && Storage::disk('local')->exists('public/letterheaderfooter/footer/'.$LetterFooter->current_file_name)):
                                $PDFHTML .= '<tr>';
                                    $PDFHTML .= '<td class="footerPartners" style="text-align: center; vertical-align: middle; padding-bottom: 5px;">';
                                        $PDFHTML .= '<img style=" max-width: 100%; height: auto;" src="'.url('storage/letterheaderfooter/footer/'.$LetterFooter->current_file_name).'" alt="'.$LetterFooter->name.'"/>';
                                    $PDFHTML .= '</td>';
                                $PDFHTML .= '</tr>';
                            endif;

                            if(!empty($companyReg) && isset($companyReg->value) && !empty($companyReg->value)):
                            $PDFHTML .= '<tr class="regInfoRow">';
                                $PDFHTML .= '<td class="text-center" style="padding-top: 10px;">';
                                    $PDFHTML .= $companyReg->value;
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';
                            endif;
                        $PDFHTML .= '</table>';
                    $PDFHTML .= '</footer>';

                    /*PDF BODY START*/
                    $PDFHTML .= '<div class="bodyContainer">';
                        $PDFHTML .= '<h3 class="tableTitle mb-20"><u>Appraisal Report</u></h3>';
                        $PDFHTML .= '<p class="mb-20">';
                            $PDFHTML .= 'Date: '.date('jS F Y');
                        $PDFHTML .= '</p>';
                        if(!empty($overDues) && $overDues->count() > 0):
                            $PDFHTML .= '<h3 class="tableTitle mb-15"><u>Overdue Appraisal</u></h3>';
                            $PDFHTML .= '<table class="table table-sm table-bordered mb-30">';
                                $PDFHTML .= '<thead>';
                                    $PDFHTML .= '<tr>';
                                        $PDFHTML .= '<th class="text-left">Name</th>';
                                        $PDFHTML .= '<th class="text-left">Department</th>';
                                        $PDFHTML .= '<th class="text-left">Date</th>';
                                        $PDFHTML .= '<th class="text-left">Due By</th>';
                                    $PDFHTML .= '</tr>';
                                $PDFHTML .= '</thead>';
                                $PDFHTML .= '<tbody>';
                                    foreach($overDues as $ovd):
                                        $empName = (isset($ovd->employee->title->name) ? $ovd->employee->title->name.' ' : '').$ovd->employee->full_name;
                                        $PDFHTML .= '<tr>';
                                            $PDFHTML .= '<td class="text-left;">';
                                                $PDFHTML .= '<strong style="text-transform: uppercase; font-size: 12px; line-height: normal;">'.$empName.'</strong><br/>';
                                                if(isset($ovd->employee->employment->employeeJobTitle->name) && !empty($ovd->employee->employment->employeeJobTitle->name)):
                                                    $PDFHTML .= '<small style="color: #555; line-height: normal;">'.$ovd->employee->employment->employeeJobTitle->name.'</small>';
                                                endif;
                                            $PDFHTML .= '</td>';
                                            $PDFHTML .= '<td class="text-left">'.(isset($ovd->employee->employment->department->name) ? $ovd->employee->employment->department->name : '').'</td>';
                                            $PDFHTML .= '<td class="text-left">'.date('jS M, Y', strtotime($ovd->due_on)).'</td>';
                                            $PDFHTML .= '<td class="text-left">';
                                                $date = \Carbon\Carbon::parse($ovd->due_on);
                                                $now = \Carbon\Carbon::now();

                                                $PDFHTML .= $date->diffInDays($now).' Days';
                                            $PDFHTML .= '</td>';
                                        $PDFHTML .= '</tr>';
                                    endforeach;
                                $PDFHTML .= '</tbody>';
                            $PDFHTML .= '</table>';
                        endif;

                        if(!empty($upcommings) && $upcommings->count() > 0):
                            $PDFHTML .= '<h3 class="tableTitle mb-15"><u>Upcoming Appraisal- in next 30 days</u></h3>';
                            $PDFHTML .= '<table class="table table-sm table-bordered mb-30">';
                                $PDFHTML .= '<thead>';
                                    $PDFHTML .= '<tr>';
                                        $PDFHTML .= '<th class="text-left">Name</th>';
                                        $PDFHTML .= '<th class="text-left">Department</th>';
                                        $PDFHTML .= '<th class="text-left">Date</th>';
                                        $PDFHTML .= '<th class="text-left">Due In</th>';
                                    $PDFHTML .= '</tr>';
                                $PDFHTML .= '</thead>';
                                $PDFHTML .= '<tbody>';
                                    foreach($upcommings as $ovd):
                                        $empName = (isset($ovd->employee->title->name) ? $ovd->employee->title->name.' ' : '').$ovd->employee->full_name;
                                        $PDFHTML .= '<tr>';
                                            $PDFHTML .= '<td class="text-left">';
                                                $PDFHTML .= '<strong style="text-transform: uppercase; font-size: 12px; line-height: normal;">'.$empName.'</strong><br/>';
                                                if(isset($ovd->employee->employment->employeeJobTitle->name) && !empty($ovd->employee->employment->employeeJobTitle->name)):
                                                    $PDFHTML .= '<small style="color: #555; line-height: normal;">'.$ovd->employee->employment->employeeJobTitle->name.'</small>';
                                                endif;
                                            $PDFHTML .= '</td>';
                                            $PDFHTML .= '<td class="text-left">'.(isset($ovd->employee->employment->department->name) ? $ovd->employee->employment->department->name : '').'</td>';
                                            $PDFHTML .= '<td class="text-left">'.date('jS M, Y', strtotime($ovd->due_on)).'</td>';
                                            $PDFHTML .= '<td class="text-left">';
                                                $date = \Carbon\Carbon::parse($ovd->due_on);
                                                $now = \Carbon\Carbon::now();

                                                $PDFHTML .= $date->diffInDays($now).' Days';
                                            $PDFHTML .= '</td>';
                                        $PDFHTML .= '</tr>';
                                    endforeach;
                                $PDFHTML .= '</tbody>';
                            $PDFHTML .= '</table>';
                        endif;
                    $PDFHTML .= '</div>';
                    /*PDF BODY END*/

                $PDFHTML .= '</body>';
            $PDFHTML .= '</html>';

            $MAILBODY = 'Dear Concern,<br/><br/>';
            $MAILBODY .= 'Please find the attached appraisal report.<br/><br/>';
            $MAILBODY .= 'Regards,<br/>';
            $MAILBODY .= 'London Churchill College';
            
            $fileName = time().'_Employee_Appraisal_Report.pdf';
            $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true, 'dpi' => 72])
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);
            $content = $pdf->output();
            Storage::disk('s3')->put('public/reports/'.$fileName, $content );

            $attachmentFiles = [];
            $attachmentFiles[] = [
                "pathinfo" => 'public/reports/'.$fileName,
                "nameinfo" => $fileName,
                "mimeinfo" => 'application/pdf',
                "disk" => 's3'
            ];

            UserMailerJob::dispatch($configuration, $mailTo, new CommunicationSendMail($PDF_title, $MAILBODY, $attachmentFiles));
        endif;

        return 0;
    }
}
