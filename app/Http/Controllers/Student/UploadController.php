<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DocumentSettings;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $studentApplicantId = $student->applicant_id;
        $document_setting_id = $request->document_setting_id;
        $documentSetting = DocumentSettings::find($document_setting_id);
        $document_settings_name = (isset($documentSetting->name) && !empty($documentSetting->name) ? $documentSetting->name : '');
        $hard_copy_check = $request->hard_copy_check;

        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : '');
        $display_file_name .= ($display_file_name != '' ? ($document_settings_name != '' ? ' - '.$document_settings_name : '') : $document_settings_name);

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/students/'.$student_id, $imageName, 's3');
        $data = [];
        $data['student_id'] = $student_id;
        $data['document_setting_id'] = ($document_setting_id > 0 ? $document_setting_id : 0);
        $data['hard_copy_check'] = ($hard_copy_check > 0 ? $hard_copy_check : 0);
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('s3')->url($path);
        $data['display_file_name'] = $display_file_name; //(isset($documentSetting->name) && !empty($documentSetting->name) ? $documentSetting->name : $imageName);
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $studentDoc = StudentDocument::create($data);

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    public function list(Request $request){
        $studentId = (isset($request->studentId) && !empty($request->studentId) ? $request->studentId : 0);
        $student = Student::find($studentId);
        $studentApplicantId = $student->applicant_id;
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentDocument::orderByRaw(implode(',', $sorts))->where('student_id', $studentId);
        if(!empty($queryStr)):
            $query->where('display_file_name','LIKE','%'.$queryStr.'%');
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
                $url = '';
                if(isset($list->current_file_name) && !empty($list->current_file_name) && Storage::disk('s3')->exists('public/students/'.$list->student_id.'/'.$list->current_file_name)):
                    $disk = Storage::disk('s3');
                    $url = $disk->url('public/students/'.$list->student_id.'/'.$list->current_file_name);
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'display_file_name' => (!empty($list->display_file_name) ? $list->display_file_name : 'Unknown'),
                    'hard_copy_check' => $list->hard_copy_check,
                    'doc_type' => strtoupper($list->doc_type),
                    'current_file_name'=> $list->current_file_name,
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
        $student = $request->student;
        $recordid = $request->recordid;
        $data = StudentDocument::find($recordid)->delete();
        return response()->json($data);
    }

    public function restore(Request $request) {
        $student = $request->student;
        $recordid = $request->recordid;
        $data = StudentDocument::where('id', $recordid)->withTrashed()->restore();

        response()->json($data);
    }
}
