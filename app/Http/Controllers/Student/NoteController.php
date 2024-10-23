<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicantNoteRequest;
use App\Http\Requests\StudentNoteRequest;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentNote;
use App\Models\StudentNoteFollowedBy;
use App\Models\StudentNotesDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    public function store(StudentNoteRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $followed_up = (isset($request->followed_up) && !empty($request->followed_up) ? 'yes' : 'no');
        $follow_up_by = (isset($request->follow_up_by) && !empty($request->follow_up_by) ? $request->follow_up_by : []);

        $is_flaged = (isset($request->is_flaged) && !empty($request->is_flaged) ? $request->is_flaged : 'No');
        $student_flag_id = ($is_flaged == 'Yes' && isset($request->student_flag_id) && $request->student_flag_id > 0 ? $request->student_flag_id : 0);
        $flaged_status = ($is_flaged == 'Yes' && $student_flag_id > 0 ? 'Active' : null);
        $note = StudentNote::create([
            'student_id'=> $student_id,
            'term_declaration_id'=> (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null),
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : ''),
            'note'=> $request->content,
            'phase'=> 'Live',
            'followed_up'=> $followed_up,
            'followed_up_status'=> ($followed_up == 'yes' && !empty($follow_up_by) ? 'Pending' : ''),

            'is_flaged'=> $is_flaged,
            'student_flag_id'=> $student_flag_id,
            'flaged_status'=> $flaged_status,
            //'follow_up_start'=> ($followed_up == 'yes' && isset($request->follow_up_start) && !empty($request->follow_up_start) ? date('Y-m-d', strtotime($request->follow_up_start)) : null),
            //'follow_up_end'=> ($followed_up == 'yes' && isset($request->follow_up_end) && !empty($request->follow_up_end) ? date('Y-m-d', strtotime($request->follow_up_end)) : null),
            //'follow_up_by'=> ($followed_up == 'yes' && isset($request->follow_up_by) && !empty($request->follow_up_by) ? $request->follow_up_by : null),
            'created_by' => auth()->user()->id
        ]);
        if($note):
            if($followed_up == 'yes' && !empty($follow_up_by)):
                //$follow_up_by[] = auth()->user()->id;
                foreach($follow_up_by as $fub):
                    StudentNoteFollowedBy::create([
                        'student_note_id' => $note->id,
                        'user_id' => $fub,
                        'created_by' => auth()->user()->id,
                    ]);
                endforeach;
            endif;
            if($request->hasFile('document')):
                $document = $request->file('document');
                $documentName = time().'_'.$document->getClientOriginalName();
                $path = $document->storeAs('public/students/'.$student_id, $documentName, 's3');

                $data = [];
                $data['student_id'] = $student_id;
                $data['student_note_id'] = $note->id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $document->getClientOriginalExtension();
                $data['path'] = Storage::disk('s3')->url($path);
                $data['display_file_name'] = $documentName;
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                $studentNoteDocument = StudentNotesDocument::create($data);
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
        $term = (isset($request->term) && $request->term > 0 ? $request->term : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentNote::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('note','LIKE','%'.$queryStr.'%');
        endif;
        if($term > 0): $query->where('term_declaration_id', $term); endif;
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
                    'term' => (isset($list->term->name) && !empty($list->term->name) ? $list->term->name : ''),
                    'opening_date' => (isset($list->opening_date) && !empty($list->opening_date) ? date('jS F, Y', strtotime($list->opening_date)) : ''),
                    'note' => strip_tags($list->note),
                    'note_document_id' => (isset($list->document->id) && $list->document->id > 0 ? $list->document->id : 0),
                    'followed_up' => (isset($list->followed_up) && !empty($list->followed_up) ? $list->followed_up : 'no'),
                    'followed_up_status' => (isset($list->followed_up_status) && !empty($list->followed_up_status) ? $list->followed_up_status : ''),
                    //'follow_up_start' => (isset($list->follow_up_start) && !empty($list->follow_up_start) ? date('jS F, Y', strtotime($list->follow_up_start)) : ''),
                    //'follow_up_end' => (isset($list->follow_up_end) && !empty($list->follow_up_end) ? date('jS F, Y', strtotime($list->follow_up_end)) : ''),
                    'followed' => (isset($list->followed_tag) && !empty($list->followed_tag) ? $list->followed_tag : ''),
                    'completed_by' => (isset($list->completed->employee->full_name) && !empty($list->completed->employee->full_name) ? $list->completed->employee->full_name : ''),
                    'completed_at' => (isset($list->followup_completed_at) && !empty($list->followup_completed_at) ? date('jS F, Y', strtotime($list->followup_completed_at)) : ''),
                    'is_flaged' => (isset($list->is_flaged) && !empty($list->is_flaged) ? $list->is_flaged : 'No'),
                    'flaged_status' => (isset($list->flaged_status) && !empty($list->flaged_status) ? $list->flaged_status : ''),
                    'student_flag_id' => ($list->student_flag_id > 0 ? $list->student_flag_id : '0'),
                    'flag_name' => ($list->student_flag_id > 0 && isset($list->flag->name) && !empty($list->flag->name) ? $list->flag->name : ''),
                    'flag_color' => ($list->student_flag_id > 0 && isset($list->flag->color) && !empty($list->flag->color) ? $list->flag->color : ''),
                    'created_by'=> (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : (isset($list->user->name) && !empty($list->user->name) ? $list->user->name : '')),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at,
                    'is_ownere' => (isset($list->created_by) && $list->created_by == auth()->user()->id ? 1 : 0)
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
            if(isset($note->document->id) && $note->document->id > 0 && isset($note->document->current_file_name) && !empty($note->document->current_file_name)):
                $btns .= '<a data-id="'.$note->document->id.'" href="javascript:void(0);" class="downloadDoc btn btn-primary w-auto inline-flex"><i data-lucide="cloud-lightning" class="w-4 h-4 mr-2"></i>Download Attachment</a>';
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
        $followed_by = StudentNoteFollowedBy::where('student_note_id', $noteId)->pluck('user_id')->unique()->toArray();
        $clearer = (isset($theNote->flag->raiser) && $theNote->flag->raiser->count() > 0 ? $theNote->flag->raiser->pluck('user_id')->unique()->toArray() : []);

        $theNote = StudentNote::find($noteId);
        $student = Student::find($theNote->student_id);
        $studentApplicantId = $student->applicant_id;
        $docURL = '';
        if(isset($theNote->student_document_id) && isset($theNote->document) && Storage::disk('s3')->exists('public/students/'.$theNote->student_id.'/'.$theNote->document->current_file_name)):
            $docURL = (isset($theNote->document->current_file_name) && !empty($theNote->document->current_file_name) ? Storage::disk('s3')->url('public/students/'.$theNote->student_id.'/'.$theNote->document->current_file_name) : '');
        endif;
        $theNote['docURL'] = $docURL;
        $theNote['followed_by'] = $followed_by;
        $theNote['flag_color'] = (isset($theNote->flag->color) && !empty($theNote->flag->color) ? $theNote->flag->color : '');

        $theNote['edit_followup'] = 0;
        $theNote['edit_flag'] = 0;
        if((!empty($followed_by) && in_array(auth()->user()->id, $followed_by)) || $theNote['created_by'] == auth()->user()->id){
            $theNote['edit_followup'] = 1;
        }
        if((!empty($clearer) && in_array(auth()->user()->id, $clearer)) || $theNote['created_by'] == auth()->user()->id){
            $theNote['edit_flag'] = 1;
        }

        return response()->json(['res' => $theNote], 200);
    }

    public function update(StudentNoteRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $noteId = $request->id;
        $oleNote = StudentNote::find($noteId);
        $exist_status = $oleNote->followed_up_status;
        $exist_followup_completed_by = $oleNote->followup_completed_by;
        $followup_completed_at = $oleNote->followup_completed_at;
        $existFollowUps = (isset($oleNote->follows) && $oleNote->follows->count() > 0 ? $oleNote->follows->pluck('user_id')->unique()->toArray() : []);

        $followed_up = (isset($request->followed_up) && $request->followed_up > 0 ? 'yes' : 'no');
        $follow_up_by = (isset($request->follow_up_by) && !empty($request->follow_up_by) ? $request->follow_up_by : []);

        $is_flaged = (isset($request->is_flaged) && !empty($request->is_flaged) ? $request->is_flaged : 'No');
        $student_flag_id = ($is_flaged == 'Yes' && isset($request->student_flag_id) && $request->student_flag_id > 0 ? $request->student_flag_id : 0);
        $flaged_status = ($is_flaged == 'Yes' ? (isset($request->flaged_status) && !empty($request->flaged_status) ? $request->flaged_status : 'Active') : null);
        $note = StudentNote::where('id', $noteId)->where('student_id', $student_id)->Update([
            'student_id'=> $student_id,
            'term_declaration_id'=> (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null),
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : ''),
            'note'=> $request->content,
            'phase'=> 'Live',
            'followed_up'=> $followed_up,
            'followed_up_status'=> ($followed_up == 'yes' && !empty($request->followed_up_status) ? $request->followed_up_status : null),
            'followup_completed_by'=> ($followed_up == 'yes' && !empty($request->followed_up_status) && $request->followed_up_status == 'Completed' && $exist_status !=  $request->followed_up_status ? auth()->user()->id : $exist_followup_completed_by),
            'followup_completed_at'=> ($followed_up == 'yes' && !empty($request->followed_up_status) && $request->followed_up_status == 'Completed' && $exist_status !=  $request->followed_up_status ? date('Y-m-d H:i:s') : $followup_completed_at),

            'is_flaged'=> $is_flaged,
            'student_flag_id'=> $student_flag_id,
            'flaged_status'=> $flaged_status,
            
            'updated_by' => auth()->user()->id
        ]);

        if($followed_up == 'yes' && !empty($follow_up_by)):
            $follow_up_by[] = $oleNote->created_by;
            $deletable = array_diff($existFollowUps, $follow_up_by);
            $insertable = array_diff($follow_up_by, $existFollowUps);
            if(!empty($deletable)):
                StudentNoteFollowedBy::where('student_note_id', $noteId)->whereIn('user_id', $deletable)->forceDelete();
            endif;
            if(!empty($insertable)):
                foreach($insertable as $user):
                    StudentNoteFollowedBy::create([
                        'student_note_id' => $noteId,
                        'user_id' => $user,
                        'created_by' => auth()->user()->id,
                    ]);
                endforeach;
            endif;
        else:
            StudentNoteFollowedBy::where('student_note_id', $noteId)->forceDelete();
        endif;

        if($request->hasFile('document')):
            $noteDocument = StudentNotesDocument::where('student_id', $student_id)->where('student_note_id', $noteId)->get()->first();
            if(isset($noteDocument->id) && $noteDocument->id > 0):
                if (Storage::disk('s3')->exists('public/students/'.$student_id.'/'.$noteDocument->current_file_name)):
                    Storage::disk('s3')->delete('public/students/'.$student_id.'/'.$noteDocument->current_file_name);
                endif;

                StudentDocument::where('student_id', $student_id)->where('student_note_id', $noteId)->where('id', $noteDocument->id)->forceDelete();
            endif;

            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/students/'.$student_id, $documentName, 's3');

            $data = [];
            $data['student_id'] = $student_id;
            $data['student_note_id'] = $noteId;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = Storage::disk('s3')->url($path);
            $data['display_file_name'] = $documentName;
            $data['current_file_name'] = $documentName;
            $data['created_by'] = auth()->user()->id;
            $studentDocument = StudentNotesDocument::create($data);
        endif;
        return response()->json(['message' => 'Applicant Note successfully updated'], 200);
    }

    public function destroy(Request $request){
        $student = $request->student;
        $recordid = $request->recordid;
        $studentNote = StudentNote::find($recordid);
        StudentNote::find($recordid)->delete();

        if(isset($studentNote->document->id) && $studentNote->document->id > 0):
            StudentNotesDocument::find($studentNote->document->id)->delete();
        endif;

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function restore(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        $data = StudentNote::where('id', $recordid)->withTrashed()->restore();
        $studentNote = StudentNote::find($recordid);
        if(isset($studentNote->document->id) && $studentNote->document->id > 0):
            StudentNotesDocument::where('id', $studentNote->document->id)->withTrashed()->restore();
        endif;
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function studentNoteDocumentDownload(Request $request){ 
        $row_id = $request->row_id;

        $studentNoteDoc = StudentNotesDocument::find($row_id);
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/students/'.$studentNoteDoc->student_id.'/'.$studentNoteDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }
}
