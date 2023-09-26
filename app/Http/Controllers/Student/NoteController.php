<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicantNoteRequest;
use App\Http\Requests\StudentNoteRequest;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    public function store(StudentNoteRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $note = StudentNote::create([
            'student_id'=> $student_id,
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : ''),
            'note'=> $request->content,
            'phase'=> 'Live',
            'created_by' => auth()->user()->id
        ]);
        if($note):
            if($request->hasFile('document')):
                $document = $request->file('document');
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
                    $noteUpdate = StudentNote::where('id', $note->id)->update([
                        'student_document_id' => $studentDocument->id
                    ]);
                endif;
            endif;
            return response()->json(['message' => 'Student Note successfully created'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function list(Request $request){
        $student_id = (isset($request->studentId) && !empty($request->studentId) ? $request->studentId : 0);
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = StudentNote::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('note','LIKE','%'.$queryStr.'%');
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
                $docURL = '';
                if(isset($list->student_document_id) && isset($list->document)):
                    $docURL = (isset($list->document->current_file_name) && !empty($list->document->current_file_name) ? asset('storage/applicants/'.$studentApplicantId.'/'.$list->document->current_file_name) : '');
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'opening_date' => (isset($list->opening_date) && !empty($list->opening_date) ? date('jS F, Y', strtotime($list->opening_date)) : ''),
                    'note' => (strlen(strip_tags($list->note)) > 40 ? substr(strip_tags($list->note), 0, 40).'...' : strip_tags($list->note)),
                    'url' => $docURL,
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function show(Request $request){
        $noteId = $request->noteId;
        $note = StudentNote::find($noteId);
        $student = Student::find($note->student_id);
        $studentApplicantId = $student->applicant_id;
        $html = '';
        $btns = '';
        if(!empty($note) && !empty($note->note)):
            $html .= '<div>';
                $html .= $note->note;
            $html .= '</div>';
            if(isset($note->student_document_id) && isset($note->document)):
                $docURL = (isset($note->document->current_file_name) && !empty($note->document->current_file_name) ? asset('storage/applicants/'.$studentApplicantId.'/'.$note->document->current_file_name) : '');
                if(!empty($docURL)):
                    $btns .= '<a download href="'.$docURL.'" class="btn btn-primary w-auto inline-flex"><i data-lucide="cloud-lightning" class="w-4 h-4 mr-2"></i>Download Attachment</a>';
                endif;
            endif;
        else:
            $html .= '<div class="alert alert-danger-soft show flex items-start mb-2" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! No data foudn for this note.
                    </div>';
        endif;

        return response()->json(['message' => $html, 'btns' => $btns], 200);
    }

    public function edit(Request $request){
        $noteId = $request->noteId;
        $theNote = StudentNote::find($noteId);
        $student = Student::find($theNote->student_id);
        $studentApplicantId = $student->applicant_id;
        $docURL = '';
        if(isset($theNote->student_document_id) && isset($theNote->document)):
            $docURL = (isset($theNote->document->current_file_name) && !empty($theNote->document->current_file_name) ? asset('storage/applicants/'.$studentApplicantId.'/'.$theNote->document->current_file_name) : '');
        endif;
        $theNote['docURL'] = $docURL;

        return response()->json(['res' => $theNote], 200);
    }

    public function update(ApplicantNoteRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $noteId = $request->id;
        $oleNote = StudentNote::find($noteId);
        $studentDocumentId = (isset($oleNote->student_document_id) && $oleNote->student_document_id > 0 ? $oleNote->student_document_id : 0);

        $note = StudentNote::where('id', $noteId)->where('student_id', $student_id)->Update([
            'student_id'=> $student_id,
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : ''),
            'note'=> $request->content,
            'phase'=> 'Admission',
            'updated_by' => auth()->user()->id
        ]);
        if($request->hasFile('document')):
            if($studentDocumentId > 0 && isset($oleNote->document->current_file_name) && !empty($oleNote->document->current_file_name)):
                if (Storage::disk('local')->exists('public/applicants/'.$studentApplicantId.'/'.$oleNote->document->current_file_name)):
                    Storage::delete('public/applicants/'.$studentApplicantId.'/'.$oleNote->document->current_file_name);
                endif;

                $ad = StudentDocument::where('id', $studentDocumentId)->forceDelete();
            endif;

            $document = $request->file('document');
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
                $noteUpdate = StudentNote::where('id', $noteId)->update([
                    'student_document_id' => $studentDocument->id
                ]);
            endif;
        endif;
        return response()->json(['message' => 'Applicant Note successfully updated'], 200);
    }

    public function destroy(Request $request){
        $student = $request->student;
        $recordid = $request->recordid;
        $studentNote = StudentNote::find($recordid);
        $studentDocumentID = (isset($studentNote->student_document_id) && $studentNote->student_document_id > 0 ? $studentNote->student_document_id : 0);
        StudentNote::find($recordid)->delete();

        if($studentDocumentID > 0):
            StudentDocument::find($studentDocumentID)->delete();
        endif;

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function restore(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $data = StudentNote::where('id', $recordid)->withTrashed()->restore();
        $studentNote = StudentNote::find($recordid);
        $studentDocumentID = (isset($studentNote->student_document_id) && $studentNote->student_document_id > 0 ? $studentNote->student_document_id : 0);
        if($studentDocumentID > 0):
            StudentDocument::where('id', $studentDocumentID)->withTrashed()->restore();
        endif;
        return response()->json(['message' => 'Successfully restored'], 200);
    }
}
