<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Employee;
use App\Models\EmployeeDocuments;
use App\Models\EmployeeEligibilites;
use App\Models\LetterHeaderFooter;
use App\Models\Option;
use Carbon\Carbon;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VisaExpiryCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visaexpiry:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upcomming visa expiry cron job.';
  
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
        $expireDate = Carbon::now()->addDays(60)->format('Y-m-d');
        $visaExpry = EmployeeEligibilites::where('eligible_to_work', 'Yes')->where('employee_work_permit_type_id', 3)
                        ->whereDate('workpermit_expire', '>=', date('Y-m-d'))
                        ->whereDate('workpermit_expire', '<=', $expireDate)
                        ->whereHas('employee', function($q){
                            $q->where('status', 1);
                        })->orderBy('workpermit_expire', 'ASC')->get();
        
        if($visaExpry->count() > 0):
            $companyReg = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_registration')->get()->first();
            $LetterHeader = LetterHeaderFooter::where('for_staff', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
            $LetterFooter = LetterHeaderFooter::where('for_staff', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get()->first();
            $PDF_title = 'Urgent Visa Renewal Update Required';

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

            foreach($visaExpry as $vse):
                $employee_id = $vse->employee_id;
                $employee = Employee::find($employee_id);
                $workpermit_expire = $vse->workpermit_expire;

                $mailTo = [];
                $mailTo[] = $employee->email;
                if(isset($employee->employment->email) && !empty($employee->employment->email)):
                    $mailTo[] = $employee->employment->email;
                endif;
                $mailTo[] = 'hr@lcc.ac.uk';

                $empName = (isset($employee->title->name) ? $employee->title->name.' ' : '').$employee->full_name;
                $empNameSmaller = (isset($employee->title->name) ? $employee->title->name.' ' : '').$employee->last_name;
                $empAddress = '';
                if(isset($employee->address->address_line_1) && $employee->address->address_line_1 > 0):
                    if(isset($employee->address->address_line_1) && !empty($employee->address->address_line_1)):
                        $empAddress.= $employee->address->address_line_1.'<br/>';
                    endif;
                    if(isset($employee->address->address_line_2) && !empty($employee->address->address_line_2)):
                        $empAddress.= $employee->address->address_line_2.'<br/>';
                    endif;
                    if(isset($employee->address->city) && !empty($employee->address->city)):
                        $empAddress.= $employee->address->city.',';
                    endif;
                    if(isset($employee->address->state) && !empty($employee->address->state)):
                        $empAddress.= $employee->address->state.',';
                    endif;
                    if(isset($employee->address->post_code) && !empty($employee->address->post_code)):
                        $empAddress.= $employee->address->post_code.',<br/>';
                    endif;
                    if(isset($employee->address->country) && !empty($employee->address->country)):
                        $empAddress.= $employee->address->country;
                    endif;
                endif;

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

                                        .bodyContainer{font-size: 13px; line-height: normal; padding: 0 50px;}
                                        .employeeInfo{line-height: normal;}
                                        .mb-30{margin-bottom: 30px;}
                                        .mb-20{margin-bottom: 20px;}
                                        .mb-15{margin-bottom: 15px;}
                                        .text-justify{text-align: justify;}
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
                            $PDFHTML .= '<table style="width: 100%; border: none; margin: 0 0 20px; vertical-align: top !important; border-spacing: 0;border-collapse: collapse;">';
                                $PDFHTML .= '<tr>';
                                    $PDFHTML .= '<td style="vertical-align: top;">';
                                        $PDFHTML .= '<div class="employeeInfo">';
                                            $PDFHTML .= $empName.'<br/>';
                                            $PDFHTML .= $empAddress;
                                        $PDFHTML .= '</div>';
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td style="vertical-align: top; text-align: right;">';
                                        $PDFHTML .= '<div class="issueDate">';
                                            $PDFHTML .= 'Date : '.date('jS M, Y');
                                        $PDFHTML .= '</div>';
                                    $PDFHTML .= '</td>';
                                $PDFHTML .= '</tr>';
                            $PDFHTML .= '</table>';
                            
                            $PDFHTML .= '<div class="subject mb-20">';
                                    $PDFHTML .= 'Subject: Urgent Visa Renewal Update Required';
                            $PDFHTML .= '</div>';

                            $PDFHTML .= '<div class="letterConent">';
                                    $PDFHTML .= '<p class="mb-20">';
                                        $PDFHTML .= 'Dear '.$empName;
                                    $PDFHTML .= '</p>';
                                    $PDFHTML .= '<p class="mb-15 text-justify">';
                                        $PDFHTML .= 'Our records indicate that your Visa is set to expire on <strong>'.date('d/m/Y', strtotime($workpermit_expire)).'</strong>.';
                                    $PDFHTML .= '</p>';
                                    $PDFHTML .= '<p class="mb-15 text-justify">';
                                        $PDFHTML .= 'To comply with UK immigration regulations, we kindly request that you promptly provide us
                                                    with a copy of your renewed Visa. This step is crucial to maintaining accurate and up-to-date
                                                    employee records within our organization.';
                                    $PDFHTML .= '</p>';
                                    $PDFHTML .= '<p class="mb-15 text-justify">';
                                        $PDFHTML .= 'It is <strong>important</strong> to note that without the updated Visa information, we regret to inform you
                                                    that processing your salary payments may be impacted after the expiry date of your current
                                                    Visa. We aim to prevent any disruptions to your compensation and, therefore, it is imperative
                                                    that we receive the necessary documentation in a timely manner. ';
                                    $PDFHTML .= '</p>';
                                    $PDFHTML .= '<p class="mb-15 text-justify">';
                                        $PDFHTML .= 'Recognizing the significance of compliance with immigration regulations, we deeply
                                                    appreciate your cooperation in this matter. Your prompt attention to this request is essential,
                                                    and we are here to assist you in any way necessary to facilitate a smooth and timely renewal
                                                    process.';
                                    $PDFHTML .= '</p>';
                                    $PDFHTML .= '<p class="mb-20">';
                                        $PDFHTML .= 'Thank you for your understanding and cooperation.';
                                    $PDFHTML .= '</p>';
                                    $PDFHTML .= '<p class="mb-15">';
                                        $PDFHTML .= 'Best regards, ';
                                    $PDFHTML .= '</p>';
                                    $PDFHTML .= '<p>';
                                        $PDFHTML .= 'Human Resources Department<br/>';
                                        $PDFHTML .= 'London Churchill College<br/>';
                                        $PDFHTML .= '<a href"mailto:hr@lcc.ac.uk">hr@lcc.ac.uk</a>';
                                    $PDFHTML .= '</p>';
                            $PDFHTML .= '</div>';
                        $PDFHTML .= '</div>';
                        /*PDF BODY END*/

                    $PDFHTML .= '</body>';
                $PDFHTML .= '</html>';

                $MAILBODY = 'Dear '.$empNameSmaller.'<br/><br/>';
                $MAILBODY .= 'Please find attached a important communication from Human Resources department.<br/><br/>';
                $MAILBODY .= 'Best regards,<br/>';
                $MAILBODY .= 'Human Resources Department<br/>';
                $MAILBODY .= 'London Churchill College';


                $fileName = time().'_'.$employee_id.'_Visa_Expiry.pdf';
                $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true, 'dpi' => 72])
                    ->setPaper('a4', 'portrait')
                    ->setWarnings(false);
                $content = $pdf->output();
                Storage::disk('google')->put('public/employees/'.$employee_id.'/documents/'.$fileName, $content );

                $data = [];
                $data['employee_id'] = $employee_id;
                $data['document_setting_id'] = 6;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = 'pdf';
                $data['path'] = Storage::disk('google')->url('public/employees/'.$employee_id.'/documents/'.$fileName);
                $data['display_file_name'] = 'Visa Expiry';
                $data['current_file_name'] = $fileName;
                $data['type'] = 2;
                $data['created_by'] = 1;
                $data['created_at'] = date('Y-m-d H:i:s');
                $employeeDocuments = EmployeeDocuments::create($data);

                $attachmentFiles = [];
                $attachmentFiles[] = [
                    "pathinfo" => 'public/employees/'.$employee_id.'/documents/'.$fileName,
                    "nameinfo" => $fileName,
                    "mimeinfo" => 'application/pdf',
                    "disk" => 'google'
                ];

                UserMailerJob::dispatch($configuration, $mailTo, new CommunicationSendMail($PDF_title, $MAILBODY, $attachmentFiles));
            endforeach;
        endif;

        return 0;
    }
}
