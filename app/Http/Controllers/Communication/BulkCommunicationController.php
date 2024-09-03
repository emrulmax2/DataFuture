<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendBulkEmailRequest;
use App\Http\Requests\SendBulkLetterRequest;
use App\Http\Requests\SendBulkSmsRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Assign;
use App\Models\ComonSmtp;
use App\Models\EmailTemplate;
use App\Models\LetterSet;
use App\Models\Option;
use App\Models\Signatory;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentEmail;
use App\Models\StudentEmailsDocument;
use App\Models\StudentLetter;
use App\Models\StudentLettersDocument;
use App\Models\StudentSms;
use App\Models\StudentSmsContent;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Traits\GenerateStudentLetterTrait;

class BulkCommunicationController extends Controller
{
    use GenerateStudentLetterTrait;
    
    public function index($classplans){
        return view('pages.communication.index', [
            'title' => 'Bulk Communication - London Churchill College',
            'subtitle' => 'Bulk Communication',
            'breadcrumbs' => [
                ['label' => 'Bulk Communication', 'href' => 'javascript:void(0);']
            ],
            'plans' => $classplans,
            'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            'emailTemplates' => EmailTemplate::where('live', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'letterSet' => LetterSet::where('live', 1)->where('status', 1)->orderBy('letter_title', 'ASC')->get(),
            'smtps' => ComonSmtp::orderBy('smtp_user', 'ASC')->get(),
            'signatory' => Signatory::orderBy('signatory_name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $plans = (isset($request->plans) && !empty($request->plans) ? explode('-', $request->plans) : []);
        $student_ids = (!empty($plans) ? Assign::whereIn('plan_id', $plans)->pluck('student_id')->unique()->toArray() : [0]);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Student::whereIn('id', $student_ids)->orderByRaw(implode(',', $sorts));

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 100));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $indicator = Assign::whereIn('plan_id', $plans)->where('student_id', $list->id)->where('attendance', 0)->get()->count();
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'first_name' => $list->first_name,
                    'last_name' => $list->last_name,
                    'registration_no' => $list->registration_no,
                    'status_id' => (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    'course' => (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : ''),
                    'semester' => (isset($list->activeCR->creation->semester->name) && !empty($list->activeCR->creation->semester->name) ? $list->activeCR->creation->semester->name : ''),
                    'deleted_at' => $list->deleted_at,
                    'checked' => ($indicator > 0 ? 0 : 1),
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /* Bulk Letter Start */
    public function getLetterTemplate(Request $request){
        $letterSetId = $request->letterSetId;
        $letterSet = LetterSet::find($letterSetId);

        return response()->json(['res' => $letterSet], 200);
    }

    public function sendLetter(SendBulkLetterRequest $request){
        $student_ids = (isset($request->student_ids) && !empty($request->student_ids) ? explode(',', $request->student_ids) : []);
        if(!empty($student_ids)):
            $pin = time();
            $issued_date = (!empty($request->issued_date) ? date('Y-m-d', strtotime($request->issued_date)) : date('Y-m-d'));
            $letter_set_id = $request->letter_set_id;
            $letter_body = $request->letter_body;

            $letterSet = LetterSet::find($letter_set_id);
            $letter_title = (isset($letterSet->letter_title) && !empty($letterSet->letter_title) ? $letterSet->letter_title : 'Letter from LCC');

            $is_email_or_attachment = 2;
            $signatory_id = (isset($request->signatory_id) && $request->signatory_id > 0 ? $request->signatory_id : 0);
            $signatoryHTML = '';
            if($signatory_id > 0):
                $signatory = Signatory::find($signatory_id);
                $signatoryHTML .= '<p>';
                    $signatoryHTML .= '<strong>Best Regards,</strong><br/>';
                    if(isset($signatory->signature) && !empty($signatory->signature) && Storage::disk('local')->exists('public/signatories/'.$signatory->signature)):
                        $signatureImage = url('storage/signatories/'.$signatory->signature);
                        $signatoryHTML .= '<img src="'.$signatureImage.'" style="width:150px; height: auto; margin: 10px 0 10px;" alt="'.$signatory->signatory_name.'"/><br/>';
                    endif;
                    $signatoryHTML .= $signatory->signatory_name.'<br/>';
                    $signatoryHTML .= $signatory->signatory_post.'<br/>';
                    $signatoryHTML .= 'London Churchill College';
                $signatoryHTML .= '</p>';
            else:
                $signatoryHTML .= '<p>';
                    $signatoryHTML .= '<strong>Best Regards,</strong><br/>';
                    $signatoryHTML .= 'The Academic Admin Dept.<br/>';
                    $signatoryHTML .= 'London Churchill College';
                $signatoryHTML .= '</p>';
            endif;

            $comon_smtp_id = (isset($request->comon_smtp_id) && $request->comon_smtp_id > 0 ? $request->comon_smtp_id : 0);
            $commonSmtp = ComonSmtp::find($comon_smtp_id);
            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => 'no-reply@lcc.ac.uk',
                'from_name'    =>  'London Churchill College',
            ];

            $sendLetterCount = 0;
            foreach($student_ids as $student_id):
                $student = Student::find($student_id);

                $data = [];
                $data['student_id'] = $student_id;
                $data['letter_set_id'] = $letter_set_id;
                $data['pin'] = $pin;
                $data['signatory_id'] = $signatory_id;
                $data['comon_smtp_id'] = $comon_smtp_id;
                $data['is_email_or_attachment'] = $is_email_or_attachment;
                $data['issued_by'] = auth()->user()->id;
                $data['issued_date'] = $issued_date;
                $data['created_by'] = auth()->user()->id;

                $letter = StudentLetter::create($data);

                $attachmentFiles = [];
                if($letter):
                    $generatedLetter = $this->generateLetter($student_id, $letter_title, $letter_body, $issued_date, $pin, $signatory_id);

                    $data = [];
                    $data['student_id'] = $student_id;
                    $data['student_letter_id'] = $letter->id;
                    $data['hard_copy_check'] = 0;
                    $data['doc_type'] = 'pdf';
                    $data['path'] = Storage::disk('s3')->url('public/students/'.$student_id.'/'.$generatedLetter['filename']);
                    $data['display_file_name'] = $letter_title;
                    $data['current_file_name'] = $generatedLetter['filename'];
                    $data['created_by'] = auth()->user()->id;
                    $letterDocument = StudentLettersDocument::create($data);


                    $emailHTML = '';
                    $emailHTML .= 'Dear '.$student->first_name.' '.$student->last_name.', <br/>';
                    $emailHTML .= '<p>Please Find the letter attached herewith. </p>';
                    $emailHTML .= $signatoryHTML;

                    $attachmentFiles[] = [
                        "pathinfo" => 'public/students/'.$student_id.'/'.$generatedLetter['filename'],
                        "nameinfo" => $generatedLetter['filename'],
                        "mimeinfo" => 'application/pdf',
                        "disk" => 's3'
                    ];
                    
                    $sendTo = [];
                    if(isset($student->contact->institutional_email) && !empty($student->contact->institutional_email)):
                        $sendTo[] = $student->contact->institutional_email;
                    endif;
                    if(isset($student->contact->personal_email) && !empty($student->contact->personal_email)):
                        $sendTo[] = $student->contact->personal_email;
                    endif;
                    $sendTo = (!empty($sendTo) ? $sendTo : [$student->users->email]);
        
                    UserMailerJob::dispatch($configuration, $sendTo, new CommunicationSendMail($letter_title, $emailHTML, $attachmentFiles));
                    $sendLetterCount += 1;
                endif;
            endforeach;

            return response()->json(['message' => 'Letter successfully generated and sent to <strong>'.$sendLetterCount.' out of '.count($student_ids).'</strong> students.'], 200);
        else:
            return response()->json(['message' => 'Student ids can not be empty. Please select some student first.'], 412);
        endif;
    }
    /* Bulk Letter End */

    /* Bulk Email Start */
    public function getEmailTemplate(Request $request){
        $emailTemplateID = $request->emailTemplateID;
        $emailTemplate = EmailTemplate::find($emailTemplateID);

        return response()->json(['row' => $emailTemplate], 200);
    }

    public function sendEmail(SendBulkEmailRequest $request){
        $student_ids = (isset($request->student_ids) && !empty($request->student_ids) ? explode(',', $request->student_ids) : []);
        if(!empty($student_ids)):
            $comon_smtp_id = $request->comon_smtp_id;
            $email_template_id = (isset($request->email_template_id) && $request->email_template_id > 0 ? $request->email_template_id : NULL);
            $subject = $request->subject;
            $body = $request->body;

            $commonSmtp = ComonSmtp::find($comon_smtp_id);
            $configuration = [
                'smtp_host'    => $commonSmtp->smtp_host,
                'smtp_port'    => $commonSmtp->smtp_port,
                'smtp_username'  => $commonSmtp->smtp_user,
                'smtp_password'  => $commonSmtp->smtp_pass,
                'smtp_encryption'  => $commonSmtp->smtp_encryption,
                
                'from_email'    => $commonSmtp->smtp_user,
                'from_name'    =>  strtok($commonSmtp->smtp_user, '@'),
            ];

            $sendMailCount = 0;
            foreach($student_ids as $student_id):
                $student = Student::find($student_id);
                $sendTo = [];
                if(isset($student->contact->institutional_email) && !empty($student->contact->institutional_email)):
                    $sendTo[] = $student->contact->institutional_email;
                endif;
                if(isset($student->contact->personal_email) && !empty($student->contact->personal_email)):
                    $sendTo[] = $student->contact->personal_email;
                endif;
                $sendTo = (!empty($sendTo) ? $sendTo : [$student->users->email]);

                $studentEmail = StudentEmail::create([
                    'student_id' => $student_id,
                    'common_smtp_id' => $comon_smtp_id,
                    'email_template_id' => $email_template_id,
                    'subject' => $subject,
                    'created_by' => auth()->user()->id,
                ]);
                if($studentEmail):
                    $this->generateEmailPdf($studentEmail->id, $student_id, $subject, $request->body);

                    $MAILHTML = '';
                    $MAILHTML .= $body;

                    if($request->hasFile('documents')):
                        $documents = $request->file('documents');
                        $docCounter = 1;
                        $attachmentInfo = [];
                        foreach($documents as $document):
                            $documentName = time().'_'.$document->getClientOriginalName();
                            $path = $document->storeAs('public/students/'.$student_id, $documentName, 's3');

                            $data = [];
                            $data['student_id'] = $student_id;
                            $data['student_email_id'] = $studentEmail->id;
                            $data['hard_copy_check'] = 0;
                            $data['doc_type'] = $document->getClientOriginalExtension();
                            $data['path'] = Storage::disk('s3')->url($path);
                            $data['display_file_name'] = $documentName;
                            $data['current_file_name'] = $documentName;
                            $data['created_by'] = auth()->user()->id;
                            $studentEmailDocument = StudentEmailsDocument::create($data);

                            if($studentEmailDocument):
                                $attachmentInfo[$docCounter++] = [
                                    "pathinfo" => $path,
                                    "nameinfo" => $document->getClientOriginalName(),
                                    "mimeinfo" => $document->getMimeType(),
                                    'disk'     => 's3'      
                                ];
                                $docCounter++;
                            endif;
                        endforeach;
                        UserMailerJob::dispatch($configuration, $sendTo, new CommunicationSendMail($request->subject, $MAILHTML, $attachmentInfo));
                    else:
                        UserMailerJob::dispatch($configuration, $sendTo, new CommunicationSendMail($request->subject, $MAILHTML, []));
                    endif;
                    $sendMailCount += 1;
                endif;
            endforeach;
            return response()->json(['message' => 'Email successfully sent to <strong>'.$sendMailCount.' out of '.count($student_ids).'</strong> students.'], 200);
        else:
            return response()->json(['message' => 'Student ids can not be empty. Please select some student first.'], 412);
        endif;
    }

    public function generateEmailPdf($student_email_id, $student_id, $subject, $body){
        $user = User::where('id', auth()->user()->id)->get()->first();

        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$subject.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: rgb(30, 41, 59);}
                                table{margin-left: 0px; width: 100%;}
                                figure{margin: 0;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                                .vtop{vertical-align: top;}
                                .mailContentTable tr th, .mailContentTable tr td{ padding: 0 0 10px 0; vertical-align: top;}
                            </style>';
            $PDFHTML .= '</head>';
            $PDFHTML .= '<body>';
                $PDFHTML .= '<table class="mailContentTable">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th style="width: 150px;" class="text-left">Issued Date</th>';
                            $PDFHTML .= '<td>'.date('d-m-Y').'</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th style="width: 150px;" class="text-left">Issued BY</th>';
                            $PDFHTML .= '<td>'.(isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name).'</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th style="width: 150px;" class="text-left">Email Body</th>';
                            $PDFHTML .= '<td>'.$body.'</td>';
                        $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';
                
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = $student_email_id.'_'.$student_id.'.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        $content = $pdf->output();
        Storage::disk('s3')->put('public/students/'.$student_id.'/'.$fileName, $content );

        $studentEmail = StudentEmail::where('id', $student_email_id)->update([
            'mail_pdf_file' => $fileName
        ]);
        return $studentEmail;
    }
    /* Bulk Email Start */


    /* Bulk SMS Start */
    public function getSmsTemplate(Request $request){
        $smsTemplateId = $request->smsTemplateId;
        $smsTemplate = SmsTemplate::where('id', $smsTemplateId)->get()->first();

        return response()->json(['row' => $smsTemplate], 200);
    }

    public function sendSms(SendBulkSmsRequest $request){
        $student_ids = (isset($request->student_ids) && !empty($request->student_ids) ? explode(',', $request->student_ids) : []);
        $smsTemplateID = (isset($request->sms_template_id) && $request->sms_template_id > 0 ? $request->sms_template_id : NULL);
        $subject = $request->subject;
        $sms = $request->sms;

        $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
        $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
        $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();

        if(!empty($student_ids)):
            $studentSmsContent = StudentSmsContent::create([
                'sms_template_id' => $smsTemplateID,
                'subject' => $subject,
                'sms' => $sms,
            ]);
            if($studentSmsContent):
                $smsSentCount = 0;
                foreach($student_ids as $student_id):
                    $studentContact = StudentContact::where('student_id', trim($student_id))->get()->first();
                    $studentSms = StudentSms::create([
                        'student_id' => trim($student_id),
                        'student_sms_content_id' => $studentSmsContent->id,
                        'phone' => $studentContact->mobile,
                        'created_by' => auth()->user()->id,
                    ]);
                    if(isset($studentContact->mobile) && !empty($studentContact->mobile)):
                        if($active_api == 1 && !empty($textlocal_api)):
                            $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                                'apikey' => $textlocal_api, 
                                'message' => $request->sms, 
                                'sender' => 'London Churchill College', 
                                'numbers' => $studentContact->mobile
                            ]);
                            $smsSentCount += 1;
                        elseif($active_api == 2 && !empty($smseagle_api)):
                            $response = Http::withHeaders([
                                    'access-token' => $smseagle_api,
                                    'Content-Type' => 'application/json',
                                ])->withoutVerifying()->withOptions([
                                    "verify" => false
                                ])->post('https://79.171.153.104/api/v2/messages/sms', [
                                    'to' => [$studentContact->mobile],
                                    'text' => $request->sms
                                ]);
                            $smsSentCount += 1;
                        endif;
                    endif;
                endforeach;

                return response()->json(['message' => 'SMS successfully sent to <strong>'.$smsSentCount.' out of '.count($student_ids).'</strong> students.'], 200);
            else:
                return response()->json(['message' => 'Something went wrong. SMS content can not inserted.'], 412);
            endif;
        else:
            return response()->json(['message' => 'Student ids can not be empty. Please select some student first.'], 412);
        endif;
    }
    /* Bulk SMS End */
}
