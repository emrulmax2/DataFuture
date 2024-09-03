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
use App\Models\StudentLettersDocument;
use Illuminate\Http\Request;

use Mail; 
use Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\GenerateStudentLetterTrait;

use App\Exports\ArrayCollectionExport;
use Maatwebsite\Excel\Facades\Excel;

class LetterController extends Controller
{
    use GenerateStudentLetterTrait;

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
        $is_email_or_attachment = 2;

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
            /* Generate PDF End */


            $signatoryHTML = '';
            if($signatory_id > 0):
                $signatory = Signatory::find($signatory_id);
                $signatoryHTML .= '<p>';
                    $signatoryHTML .= '<strong>Best Regards,</strong><br/>';
                    /*if(isset($signatory->signature) && !empty($signatory->signature) && Storage::disk('local')->exists('public/signatories/'.$signatory->signature)):
                        $signatureImage = url('storage/signatories/'.$signatory->signature);
                        $signatoryHTML .= '<img src="'.$signatureImage.'" style="width:150px; height: auto; margin: 10px 0 10px;" alt="'.$signatory->signatory_name.'"/><br/>';
                    endif;*/
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
            $emailHTML .= '<p>Please Find the letter attached herewith. </p>';
            $emailHTML .= $signatoryHTML;

            $attachmentFiles[] = [
                "pathinfo" => 'public/students/'.$student_id.'/'.$generatedLetter['filename'],
                "nameinfo" => $generatedLetter['filename'],
                "mimeinfo" => 'application/pdf',
                "disk" => 's3'
            ];

            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => 'no-reply@lcc.ac.uk',
                'from_name'    =>  'London Churchill College',
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
                        ->select('sl.*', 'ls.letter_type', 'ls.letter_title', 'sg.signatory_name', 'sg.signatory_post', 'ur.name as created_bys', 'sld.id as letter_doc_id', 'sld.current_file_name')
                        ->leftJoin('letter_sets as ls', 'sl.letter_set_id', '=', 'ls.id')
                        ->leftJoin('signatories as sg', 'sl.signatory_id', '=', 'sg.id')
                        ->leftJoin('users as ur', 'sl.issued_by', '=', 'ur.id')
                        ->leftJoin('student_letters_documents as sld', 'sl.id', '=', 'sld.student_letter_id')
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
                if(isset($list->current_file_name) && !empty($list->current_file_name)):
                    $docURL = (!empty($list->current_file_name) && Storage::disk('s3')->exists('public/students/'.$student_id.'/'.$list->current_file_name) ? Storage::disk('s3')->url('public/students/'.$student_id.'/'.$list->current_file_name) : '');
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'letter_type' => $list->letter_type,
                    'letter_title' => $list->letter_title,
                    'signatory_name' => (isset($list->signatory_name) && !empty($list->signatory_name) ? $list->signatory_name : ''),
                    'letter_doc_id' => (isset($list->letter_doc_id) && $list->letter_doc_id > 0 ? $list->letter_doc_id : 0),
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

        StudentLettersDocument::where('student_letter_id', $recordid)->delete();
        StudentLetter::find($recordid)->delete();

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function restore(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        StudentLetter::where('id', $recordid)->withTrashed()->restore();
        StudentLettersDocument::where('student_letter_id', $recordid)->withTrashed()->restore();
        
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function studentLetterDownload(Request $request){ 
        $row_id = $request->row_id;

        $studentLetterDoc = StudentLettersDocument::find($row_id);
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/students/'.$studentLetterDoc->student_id.'/'.$studentLetterDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }

    public function studentExportLetterTags(){
        $letterSet = LetterSet::orderBy('id', 'ASC')->get();
        $letterTags = [];
        if(!empty($letterSet)):
            foreach($letterSet as $ls):
                $tags = $this->perseLetterData($ls->description);
                $letterTags = ($tags ? array_merge($letterTags, $tags) : $letterTags);
            endforeach;
        endif;

        $theCollection = [];
        $theCollection[1][0] = 'Old Tag';
        $theCollection[1][1] = 'New Tag';
        $theCollection[1][2] = 'Status';

        $row = 2;
        if(!empty($letterTags)):
            foreach($letterTags as $tag):
                $theCollection[$row][0] = '[DATA '.(isset($tag[0]) && !empty($tag[0]) ? $tag[0] : '').']'.(isset($tag[1]) && !empty($tag[1]) ? $tag[1] : '').'[/DATA]';
                $theCollection[$row][1] = '';
                $theCollection[$row][2] = '';
                $row++;
            endforeach;
        endif;

        return Excel::download(new ArrayCollectionExport($theCollection), 'Student_Letters_Tags.xlsx');
    }

    public function perseLetterData($content){
        preg_match_all('/\[DATA=(.*?)\](.*?)\[\/DATA\]/', $content, $matches, PREG_SET_ORDER);

        $lists = [];
        $i = 0;
        foreach ($matches as $val):

            if (!isset($lists[$i])):
                $lists[$i] = array();
                $lists[$i] = array_merge($lists[$i], array($val[1], $val[2]));
            else:
                $lists[$i] = array_merge($lists[$i], array($val[1], $val[2]));
            endif;
            $i++;
        endforeach;

        return (!empty($lists) && count($lists) ? $lists : false);
    }
}
