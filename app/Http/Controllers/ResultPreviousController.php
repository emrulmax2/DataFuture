<?php

namespace App\Http\Controllers;

use App\Models\ExamResultPrev;
use App\Models\Student;
use App\Models\StudentArchive;
use Illuminate\Http\Request;

class ResultPreviousController extends Controller
{
    public function list(Request $request){

        
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $subQuery = ExamResultPrev::select('id')
            ->where('student_id', $request->studentId)
            ->groupBy('student_id', 'course_module_id')
            ->havingRaw('MAX(created_at)');

        $query = ExamResultPrev::whereIn('id', $subQuery)
            ->orderByRaw(implode(',', $sorts))
            ->where('student_id', $request->studentId);

        if($queryStr != ''):
            $query->where(function($q) use ($queryStr){
                $q->where('grade', 'LIKE', '%'.$queryStr.'%')
                ->orWhere('status', 'LIKE', '%'.$queryStr.'%')
                ->orWhere('paperID', 'LIKE', '%'.$queryStr.'%')
                ->orWhere('module_no', 'LIKE', '%'.$queryStr.'%')
                ->orWhere('exam_date', 'LIKE', '%'.$queryStr.'%');
            });
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
            $i = 0;
            foreach($Query as $list):
                $data[$i] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'student_id' => $list->student_id,
                    'course_module_id' => $list->course_module_id,
                    'course' => $list->course->name,
                    'course_module' => $list->courseModule->name,
                    'award' => $list->course->body->name,
                    'attempt' => ExamResultPrev::where('student_id', $list->student_id)->where('course_module_id', $list->course_module_id)->count(),
                    'grade' => $list->grade,
                    'status' => $list->status,
                    'paperID' => $list->paperID,
                    'module_no' => $list->module_no,
                    'semester' => $list->semester->name,
                    'semester_id' => $list->semester_id,
                    'exam_date' => $list->exam_date,
                    'awarding_body_id' => $list->awarding_body_id,
                    'created_at' => $list->created_at,
                    'updated_at' => $list->updated_at,
                    'deleted_at' => (isset($list->deleted_at) && !empty($list->deleted_at) ? date('d-m-Y H:i:s', strtotime($list->deleted_at)) : NULL),
                    'priviliage_delete' => (isset(auth()->user()->priv()['result_delete']) && auth()->user()->priv()['result_delete'] == 1) ? true : false,
                    'priviliage_edit' => (isset(auth()->user()->priv()['result_edit']) && auth()->user()->priv()['result_edit'] == 1) ? true : false,
                ];
                if($data[$i]['priviliage_delete'] == true):
                    $data[$i]['delete_url'] = route('result-previous.destroy', $list->id);
                endif;
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return response()->json(['res' => ExamResultPrev::with('student', 'course', 'semester', 'courseModule', 'awardingBody')->find($id)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $previousExamResult = ExamResultPrev::find($id);
        $upDatExamResult = ExamResultPrev::find($id);

        $upDatExamResult->awarding_body_id = $request->awarding_body_id;
        $upDatExamResult->semester_id = $request->semester_id;
        $upDatExamResult->module_no = $request->module_no;
        $upDatExamResult->exam_date = $request->exam_date;  
        $upDatExamResult->paperID = $request->paperID;
        $upDatExamResult->course_module_id = $request->course_module_id;
        $upDatExamResult->course_id = $request->course_id;
        $upDatExamResult->student_id = $request->student_id;
        $upDatExamResult->grade = $request->grade;
        $upDatExamResult->status = $request->status;
        $upDatExamResult->updated_by = auth()->user()->id;
        $changes = $upDatExamResult->getDirty();
        $upDatExamResult->save();

        if($upDatExamResult->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $upDatExamResult->student_id;
                $data['table'] = 'exam_result_prev';
                $data['field_name'] = $field;
                $data['field_value'] = $previousExamResult->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;
            StudentArchive::create($data);
            endforeach;
        endif;


        return response()->json(['message' => 'Result updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
