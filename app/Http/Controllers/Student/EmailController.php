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
use App\Models\LetterHeaderFooter;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentEmail;
use App\Models\StudentEmailsAttachment;
use Illuminate\Http\Request;

use Mail; 
use Hash;

class EmailController extends Controller
{
    public function store(SendEmailRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;

        $studentEmail = StudentEmail::create([
            'student_id' => $student_id,
            'common_smtp_id' => $request->comon_smtp_id,
            'email_template_id' => (isset($request->email_template_id) && $request->email_template_id > 0 ? $request->email_template_id : NULL),
            'subject' => $request->subject,
            'body' => $request->body,
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
            $emailHeader = LetterHeaderFooter::where('for_email', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
            $emailFooters = LetterHeaderFooter::where('for_email', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get();

            $MAILHTML = '';
            if(isset($emailHeader->current_file_name) && !empty($emailHeader->current_file_name)):
                $MAILHTML .= '<div style="margin: 0 0 30px 0;">';
                    $MAILHTML .= '<img style="width: 100%; height: auto;" src="'.asset('storage/letterheaderfooter/header/'.$emailHeader->current_file_name).'"/>';
                $MAILHTML .= '</div>';
            endif;
            $MAILHTML .= $request->body;
            if($emailFooters->count() > 0):
                $MAILHTML .= '<div style="text-align: center; vertical-align: middle; margin: 20px 0 0 0;">';
                    $numberOfPartners = $emailFooters->count();
                    $pertnerWidth = ((100 - 2) - (int) $numberOfPartners) / (int) $numberOfPartners;

                    foreach($emailFooters as $lf):
                        $MAILHTML .= '<img style=" width: '.$pertnerWidth.'%; height: auto; margin-left:.5%; margin-right:.5%;" src="'.asset('storage/letterheaderfooter/footer/'.$lf->current_file_name).'" alt="'.$lf->name.'"/>';
                    endforeach;
                $MAILHTML .= '</div>';
            endif;

            if($request->hasFile('documents')):
                $documents = $request->file('documents');
                $docCounter = 1;
                $attachmentInfo = [];
                foreach($documents as $document):
                    $documentName = time().'_'.$document->getClientOriginalName();
                    $path = $document->storeAs('public/applicants/'.$studentApplicantId.'/', $documentName);

                    $data = [];
                    $data['student_id'] = $student_id;
                    $data['hard_copy_check'] = 0;
                    $data['doc_type'] = $document->getClientOriginalExtension();
                    $data['path'] = asset('storage/applicants/'.$studentApplicantId.'/'.$documentName);
                    $data['display_file_name'] = $documentName;
                    $data['current_file_name'] = $documentName;
                    $data['created_by'] = auth()->user()->id;
                    $studentDocument = StudentDocument::create($data);

                    if($studentDocument):
                        $noteUpdate = StudentEmailsAttachment::create([
                            'student_email_id' => $studentEmail->id,
                            'student_document_id' => $studentDocument->id,
                            'created_by' => auth()->user()->id
                        ]);

                        $attachmentInfo[$docCounter++] = [
                            "pathinfo" => 'public/applicants/'.$studentApplicantId.'/'.$documentName,
                            "nameinfo" => $document->getClientOriginalName(),
                            "mimeinfo" => $document->getMimeType()
                        ];
                        $docCounter++;
                    endif;
                endforeach;
                UserMailerJob::dispatch($configuration, $student->users->email, new CommunicationSendMail($request->subject, $MAILHTML, $attachmentInfo));
            else:
                UserMailerJob::dispatch($configuration, $student->users->email, new CommunicationSendMail($request->subject, $MAILHTML, []));
            endif;
            return response()->json(['message' => 'Email successfully sent to Student'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        endif;
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

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = StudentEmail::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('subject','LIKE','%'.$queryStr.'%');
            $query->orWhere('body','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
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
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function destroy(Request $request){
        $student_id = $request->student;
        $recordid = $request->recordid;

        $studentMailAttachments = StudentEmailsAttachment::where('student_email_id', $recordid)->get();
        if(!empty($studentMailAttachments)):
            foreach($studentMailAttachments as $attachment):
                $studentDoc = StudentDocument::find($attachment->student_document_id)->delete();
            endforeach;
        endif;
        StudentEmail::find($recordid)->delete();

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function restore(Request $request) {
        $student_id = $request->student;
        $recordid = $request->recordid;

        StudentEmail::where('id', $recordid)->withTrashed()->restore();
        $studentMailAttachments = StudentEmailsAttachment::where('student_email_id', $recordid)->get();
        if(!empty($studentMailAttachments)):
            foreach($studentMailAttachments as $attachment):
                $studentDoc = StudentDocument::where('id', $attachment->student_document_id)->withTrashed()->restore();
            endforeach;
        endif;
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function getEmailTemplate(Request $request){
        $emailTemplateID = $request->emailTemplateID;
        $emailTemplate = EmailTemplate::find($emailTemplateID);

        return response()->json(['row' => $emailTemplate], 200);
    }

    public function show(Request $request){
        $mailId = $request->recordId;
        $mail = StudentEmail::find($mailId);
        $student_id = $mail->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $heading = 'Mail Subject: <u>'.$mail->subject.'</u>';
        $html = '';
        $html .= '<div class="grid grid-cols-12 gap-4">';
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Issued Date</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div>'.(isset($mail->created_at) && !empty($mail->created_at) ? date('jS F, Y', strtotime($mail->created_at)) : '').'</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Issued By</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div>'.(isset($mail->user->name) ? $mail->user->name : 'Unknown').'</div>';
            $html .= '</div>';
            if(isset($mail->documents) && !empty($mail->documents)):
                $html .= '<div class="col-span-3">';
                    $html .= '<div class="text-slate-500 font-medium">Attachments</div>';
                $html .= '</div>';
                $html .= '<div class="col-span-9">';
                    foreach($mail->documents as $doc):
                        $html .= '<a target="_blank" class="mb-1 text-primary font-medium flex justify-start items-center" href="'.asset('storage/applicants/'.$studentApplicantId.'/'.$doc->current_file_name).'" download><i data-lucide="disc" class="w-3 h3 mr-2"></i>'.$doc->current_file_name.'</a>';
                    endforeach;
                $html .= '</div>';
            endif;
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Mail Description</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div class="mailContent">'.$mail->body.'</div>';
            $html .= '</div>';
        $html .= '</div>';

        return response()->json(['heading' => $heading, 'html' => $html], 200);
    }
}
