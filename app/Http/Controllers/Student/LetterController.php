<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendLetterRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\LetterHeaderFooter;
use App\Models\LetterSet;
use App\Models\Option;
use App\Models\Signatory;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentLetter;
use Illuminate\Http\Request;

use Mail; 
use Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LetterController extends Controller
{
    public function getLetterSet(Request $request){
        $letterSetId = $request->letterSetId;
        $letterSet = LetterSet::find($letterSetId);

        return response()->json(['res' => $letterSet], 200);
    }

    public function store(SendLetterRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $pin = time();

        $issued_date = (!empty($request->issued_date) ? date('Y-m-d', strtotime($request->issued_date)) : date('Y-m-d'));
        $letter_set_id = $request->letter_set_id;
        $letterSet = LetterSet::find($letter_set_id);
        $letter_title = (isset($letterSet->letter_title) && !empty($letterSet->letter_title) ? $letterSet->letter_title : 'Letter from LCC');

        $letter_body = $request->letter_body;
        $is_email_or_attachment = (isset($request->is_email_or_attachment) && $request->is_email_or_attachment > 0 ? $request->is_email_or_attachment : 1);

        $signatory_id = $request->signatory_id;

        $comon_smtp_id = $request->comon_smtp_id;
        $commonSmtp = ComonSmtp::find($comon_smtp_id);

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
            /* Generate PDF Start */
            $companyReg = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_registration')->get()->first();
            $LetterHeader = LetterHeaderFooter::where('for_letter', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
            $LetterFooters = LetterHeaderFooter::where('for_letter', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get();
            $PDFHTML = '';
            $PDFHTML .= '<html>';
                $PDFHTML .= '<head>';
                    $PDFHTML .= '<title>'.$letter_title.'</title>';
                    $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                    $PDFHTML .= '<style>
                                    table{margin-left: 0px;}
                                    figure{margin: 0;}
                                    @page{margin-top: 95px;margin-left: 30px;margin-right: 30px;margin-bottom: 95px;}
                                    header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -70px;}
                                    footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                    .pageCounter{position: relative;}
                                    .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                    .pinRow td{border-bottom: 1px solid gray;}
                                    .text-center{text-align: center;}
                                    .text-left{text-align: left;}
                                    .text-right{text-align: right;}
                                </style>';
                $PDFHTML .= '</head>';
                $PDFHTML .= '<body>';
                    if(isset($LetterHeader->current_file_name) && !empty($LetterHeader->current_file_name)):
                        $PDFHTML .= '<header>';
                            $PDFHTML .= '<img style="width: 100%; height: auto;" src="'.Storage::disk('local')->url('public/letterheaderfooter/header/'.$LetterHeader->current_file_name).'"/>';
                        $PDFHTML .= '</header>';
                    endif;

                    $PDFHTML .= '<footer>';
                        $PDFHTML .= '<table style="width: 100%; border: none; margin: 0; vertical-align: middle !important; font-family: serif; 
                                    font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;border-spacing: 0;border-collapse: collapse;">';
                            if($LetterFooters->count() > 0):
                                $PDFHTML .= '<tr>';
                                    $PDFHTML .= '<td colspan="2" class="footerPartners" style="text-align: center; vertical-align: middle;">';
                                        $numberOfPartners = $LetterFooters->count();
                                        $pertnerWidth = ((100 - 2) - (int) $numberOfPartners) / (int) $numberOfPartners;

                                        foreach($LetterFooters as $lf):
                                            $PDFHTML .= '<img style=" width: '.$pertnerWidth.'%; height: auto; margin-left:.5%; margin-right:.5%;" src="'.Storage::disk('local')->url('public/letterheaderfooter/footer/'.$lf->current_file_name).'" alt="'.$lf->name.'"/>';
                                        endforeach;
                                    $PDFHTML .= '</td>';
                                $PDFHTML .= '</tr>';
                            endif;
                            $PDFHTML .= '<tr class="pinRow">';
                                $PDFHTML .= '<td style="padding-bottom: 3px;">';
                                    $PDFHTML .= '<span class="pageCounter text-left"></span>';
                                $PDFHTML .= '</td>';
                                $PDFHTML .= '<td class="pinNumber text-right" style="padding-bottom: 3px;">';
                                    $PDFHTML .= 'pin - '.$pin;
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';

                            if(!empty($companyReg) && isset($companyReg->value) && !empty($companyReg->value)):
                            $PDFHTML .= '<tr class="regInfoRow">';
                                $PDFHTML .= '<td colspan="2" class="text-center" style="padding-top: 3px;">';
                                    $PDFHTML .= $companyReg->value;
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';
                            endif;
                        $PDFHTML .= '</table>';
                    $PDFHTML .= '</footer>';

                    $PDFHTML .= $letter_body;
                    if($signatory_id > 0):
                        $signatory = Signatory::find($signatory_id);
                        $PDFHTML .= '<p>';
                            $PDFHTML .= '<strong>Best Regards,</strong><br/>';
                            if(isset($signatory->signature) && !empty($signatory->signature) && Storage::disk('local')->exists('public/signatories/'.$signatory->signature)):
                                $signatureImage = Storage::disk('local')->url('public/signatories/'.$signatory->signature); 
                                $PDFHTML .= '<img src="'.$signatureImage.'" style="width:150px; height: auto;" alt=""/><br/>';
                            endif;
                            $PDFHTML .= $signatory->signatory_name.'<br/>';
                            $PDFHTML .= $signatory->signatory_post.'<br/>';
                            $PDFHTML .= 'London Churchill College';
                        $PDFHTML .= '</p>';
                    endif;
                $PDFHTML .= '</body>';
            $PDFHTML .= '</html>';

            $fileName = time().'_'.$student_id.'_Letter.pdf';
            $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true, 'dpi' => 72])
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);
            $content = $pdf->output();
            Storage::disk('s3')->put('public/applicants/'.$studentApplicantId.'/'.$fileName, $content );


            $data = [];
            $data['student_id'] = $student_id;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = 'pdf';
            $data['path'] = Storage::disk('s3')->url('public/applicants/'.$studentApplicantId.'/'.$fileName);
            $data['display_file_name'] = $letter_title;
            $data['current_file_name'] = $fileName;
            $data['created_by'] = auth()->user()->id;
            $studentDocument = StudentDocument::create($data);

            if($studentDocument):
                $noteUpdate = StudentLetter::where('id', $letter->id)->update([
                    'student_document_id' => $studentDocument->id
                ]);
            endif;
            /* Generate PDF End */


            $signatoryHTML = '';
            if($signatory_id > 0):
                $signatory = Signatory::find($signatory_id);
                $signatoryHTML .= '<p>';
                    $signatoryHTML .= '<strong>Best Regards,</strong><br/>';
                    if(isset($signatory->signature) && !empty($signatory->signature) && Storage::disk('s3')->exists('public/signatories/'.$signatory->signature)):
                        $signatureImage = Storage::disk('s3')->url('public/signatories/'.$signatory->signature);
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

            $emailHTML = '';
            $emailHTML .= 'Dear '.$student->first_name.' '.$student->last_name.', <br/>';
            if($is_email_or_attachment == 2):
                $emailHTML .= '<p>Please Find the letter attached herewith. </p>';

                $attachmentFiles[] = [
                    "pathinfo" => 'public/applicants/'.$studentApplicantId.'/'.$fileName,
                    "nameinfo" => $fileName,
                    "mimeinfo" => 'application/pdf',
                    "disk" => 's3'
                ];
            else:
                $emailHTML .= $letter_body;
            endif;
            $emailHTML .= $signatoryHTML;

            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => 'no-reply@lcc.ac.uk',
                'from_name'    =>  'London Churchill College',
            ];

            UserMailerJob::dispatch($configuration, $student->users->email, new CommunicationSendMail($letter_title, $emailHTML, $attachmentFiles));

            return response()->json(['message' => 'Letter successfully generated and distributed.'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try latter.'], 422);
        endif;
    }

    public function list(Request $request){
        $student_id = (isset($request->studentId) && !empty($request->studentId) ? $request->studentId : 0);
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $queryStr = (isset($request->queryStrCML) && $request->queryStrCML != '' ? $request->queryStrCML : '');
        $status = (isset($request->statusCML) && $request->statusCML > 0 ? $request->statusCML : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = DB::table('student_letters as sl')
                        ->select('sl.*', 'ls.letter_type', 'ls.letter_title', 'sg.signatory_name', 'sg.signatory_post', 'ur.name as created_bys', 'sdc.current_file_name')
                        ->leftJoin('letter_sets as ls', 'sl.letter_set_id', '=', 'ls.id')
                        ->leftJoin('signatories as sg', 'sl.signatory_id', '=', 'sg.id')
                        ->leftJoin('users as ur', 'sl.issued_by', '=', 'ur.id')
                        ->leftJoin('student_documents as sdc', 'sl.student_document_id', '=', 'sdc.id')
                        ->where('sl.student_id', '=', $student_id);
        if(!empty($queryStr)):
            $query->where('ls.letter_type','LIKE','%'.$queryStr.'%');
            $query->orWhere('ls.letter_title','LIKE','%'.$queryStr.'%');
            $query->orWhere('sg.signatory_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('sg.signatory_post','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->whereNotNull('sl.deleted_at');
        else:
            $query->whereNull('sl.deleted_at');
        endif;
        $query->orderByRaw(implode(',', $sorts));

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->offset($offset)
               ->limit($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $docURL = '';
                if(isset($list->student_document_id) && $list->student_document_id > 0 && isset($list->current_file_name)):
                    $docURL = (!empty($list->current_file_name) && Storage::disk('s3')->exists('public/applicants/'.$studentApplicantId.'/'.$list->current_file_name) ? Storage::disk('s3')->url('public/applicants/'.$studentApplicantId.'/'.$list->current_file_name) : '');
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'letter_type' => $list->letter_type,
                    'letter_title' => $list->letter_title,
                    'signatory_name' => (isset($list->signatory_name) && !empty($list->signatory_name) ? $list->signatory_name : ''),
                    'docurl' => $docURL,
                    'created_by'=> (isset($list->created_bys) ? $list->created_bys : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function destroy(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        StudentLetter::find($recordid)->delete();

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function restore(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        StudentLetter::where('id', $recordid)->withTrashed()->restore();
        return response()->json(['message' => 'Successfully restored'], 200);
    }
}
