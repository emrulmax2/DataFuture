<?php

namespace App\Http\Controllers\Student;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendEmailRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ApplicantEmailsAttachment;
use App\Models\ComonSmtp;
use App\Models\EmailTemplate;
use App\Models\Employee;
use App\Models\LetterHeaderFooter;
use App\Models\Option;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentEmail;
use App\Models\StudentEmailsAttachment;
use App\Models\StudentEmailsDocument;
use App\Models\User;
use Illuminate\Http\Request;

use Mail; 
use Hash;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class EmailController extends Controller
{
    public function store(SendEmailRequest $request){
        $student_id = $request->student_id;
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
            'common_smtp_id' => $request->comon_smtp_id,
            'email_template_id' => (isset($request->email_template_id) && $request->email_template_id > 0 ? $request->email_template_id : NULL),
            'subject' => $request->subject,
            'created_by' => auth()->user()->id,
        ]);

        $commonSmtp = ComonSmtp::find($request->comon_smtp_id);
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  strtok($commonSmtp->smtp_user, '@'),
        ];

        if($studentEmail):
            $this->generateEmailPdf($studentEmail->id, $student_id, $request->subject, $request->body);

            $MAILHTML = '';
            $MAILHTML .= $request->body;

            if($request->hasFile('documents')):
                $documents = $request->file('documents');
                $docCounter = 0;
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
            return response()->json(['message' => 'Email successfully sent to Student'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
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

    public function list(Request $request){
        $student_id = (isset($request->studentId) && !empty($request->studentId) ? $request->studentId : 0);
        $queryStr = (isset($request->queryStrCME) && $request->queryStrCME != '' ? $request->queryStrCME : '');
        $status = (isset($request->statusCME) && $request->statusCME > 0 ? $request->statusCME : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentEmail::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('subject','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'subject' => $list->subject,
                    'smtp' => (isset($list->smtp->smtp_user) && !empty($list->smtp->smtp_user) ? $list->smtp->smtp_user : ''),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at,
                    'mail_pdf_file' => (isset($list->mail_pdf_file) && !empty($list->mail_pdf_file) ? $list->mail_pdf_file : ''),
                    'document_list' => (isset($list->document_list) && !empty($list->document_list) ? $list->document_list : []),
                    'can_delete' => (isset(auth()->user()->priv()['communication_delete_email']) && auth()->user()->priv()['communication_delete_email'] == 1 ? 1 : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function destroy(Request $request){
        $student_id = $request->student;
        $recordid = $request->recordid;

        StudentEmailsDocument::where('student_id', $student_id)->where('student_email_id', $recordid)->delete();
        StudentEmail::find($recordid)->delete();

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function restore(Request $request) {
        $student_id = $request->student;
        $recordid = $request->recordid;

        StudentEmail::where('id', $recordid)->withTrashed()->restore();
        StudentEmailsDocument::where('student_id', $student_id)->where('student_email_id', $recordid)->withTrashed()->restore();
        
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function getEmailTemplate(Request $request){
        $emailTemplateID = $request->emailTemplateID;
        $emailTemplate = EmailTemplate::find($emailTemplateID);

        return response()->json(['row' => $emailTemplate], 200);
    }

    public function studentEmailPdfDownload(Request $request){ 
        $row_id = $request->row_id;

        $studentEmail = StudentEmail::find($row_id);
        if(isset($studentEmail->is_bulk) && $studentEmail->is_bulk == 1):
            $tmpURL = Storage::disk('s3')->temporaryUrl('public/bulk_communications/emails/'.$studentEmail->mail_pdf_file, now()->addMinutes(5));
        else:
            $tmpURL = Storage::disk('s3')->temporaryUrl('public/students/'.$studentEmail->student_id.'/'.$studentEmail->mail_pdf_file, now()->addMinutes(5));
        endif;
        return response()->json(['res' => $tmpURL], 200);
    }

    public function studentEmailAttachmentDownload(Request $request){ 
        $row_id = $request->row_id;

        $studentEmailDoc = StudentEmailsDocument::find($row_id);
        if(isset($studentEmailDoc->is_bulk) && $studentEmailDoc->is_bulk == 1):
            $tmpURL = Storage::disk('s3')->temporaryUrl('public/bulk_communications/emails/attachments/'.$studentEmailDoc->current_file_name, now()->addMinutes(5));
        else:
            $tmpURL = Storage::disk('s3')->temporaryUrl('public/students/'.$studentEmailDoc->student_id.'/'.$studentEmailDoc->current_file_name, now()->addMinutes(5));
        endif;
        return response()->json(['res' => $tmpURL], 200);
    }
}
