<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DocumentSettings;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentWorkPlacement;
use App\Models\StudentWorkplacementDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkplacementDocumentController extends Controller
{
    public function store(Request $request){


        $student_id = $request->student_id;
        $hard_copy_check = $request->hard_copy_check;

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/students/'.$student_id.'/workplacement', $imageName, 's3');
        $data = [];
        $data['student_id'] = $student_id;
        $data['hard_copy_check'] = ($hard_copy_check > 0 ? $hard_copy_check : 0);
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('s3')->url($path);
        $data['current_file_name'] = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : $imageName);
        $data['created_by'] = auth()->user()->id;

        $data['student_work_placement_id'] = $request->student_workplacement_id;
           
        $studentDoc = StudentWorkplacementDocument::create($data);

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    public function list(Request $request){
        $studentId = (isset($request->studentId) && !empty($request->studentId) ? $request->studentId : 0);
        $student_workplacement_id = (isset($request->rowId) && !empty($request->rowId) ? $request->rowId : 0);
        $student = Student::find($studentId);
        $studentApplicantId = $student->applicant_id;
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentWorkplacementDocument::orderByRaw(implode(',', $sorts))->where('student_id', $studentId)->where('student_work_placement_id', $student_workplacement_id);
        if(!empty($queryStr)):
            $query->where('current_file_name','LIKE','%'.$queryStr.'%');
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

    public function documentDownload(Request $request){
        $row_id = $request->row_id;

        $studentDoc = StudentWorkplacementDocument::where('id',$row_id)->withTrashed()->get()->first();
        $student_id = $studentDoc->student_id;
        //$tmpURL = Storage::disk('s3')->temporaryUrl('public/students/workplacement/documents/'.$student_id.'/'.$studentDoc->current_file_name, now()->addMinutes(5));
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/students/'.$student_id.'/workplacement/'.$studentDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }

    public function destroy(Request $request){
        $student = $request->student;
        $recordid = $request->recordid;
        $data = StudentWorkplacementDocument::find($recordid)->delete();
        return response()->json($data);
    }

    public function restore(Request $request) {
        $student = $request->student;
        $recordid = $request->recordid;
        $data = StudentWorkplacementDocument::where('id', $recordid)->withTrashed()->restore();

        response()->json($data);
    }
}
