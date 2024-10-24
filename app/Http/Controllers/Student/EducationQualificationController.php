<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentQualificationRequest;
use App\Models\StudentArchive;
use App\Models\StudentOtherDetail;
use App\Models\StudentQualification;
use Illuminate\Http\Request;

class EducationQualificationController extends Controller
{
    public function updateStudentQualificationStatus(Request $request){
        $student_id = $request->student_id;
        $student_other_detail_id = $request->student_other_detail_id;
        $otherDetailOld = StudentOtherDetail::where('student_id', $student_id)->where('id', $student_other_detail_id)->first();

        $student = StudentOtherDetail::find($student_other_detail_id);
        $student->fill([
            'is_education_qualification' => (isset($request->is_education_qualification) && $request->is_education_qualification > 0 ? $request->is_education_qualification : 0)
        ]);
        $changes = $student->getDirty();
        $student->save();

        if($student->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'student_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['message' => 'Student qualification status successfully updated.'], 200);
    }


    public function list(Request $request){
        $student_id = (isset($request->student_id) && $request->student_id > 0 ? $request->student_id : '0');
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentQualification::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('highest_academic','LIKE','%'.$queryStr.'%');
            $query->orWhere('subjects','LIKE','%'.$queryStr.'%');
            $query->orWhere('result','LIKE','%'.$queryStr.'%');
            $query->orWhere('awarding_body','LIKE','%'.$queryStr.'%');
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

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'awarding_body' => $list->awarding_body,
                    'highest_academic' => $list->highest_academic,
                    'subjects' => $list->subjects,
                    'result' => $list->result,
                    'degree_award_date' => $list->degree_award_date,
                    'highest_qualification_on_entry_id' => ($list->highest_qualification_on_entries) ?$list->highest_qualification_on_entries->name : null,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(StudentQualificationRequest $request){
        
        $data = StudentQualification::create([
            'student_id'=> $request->student_id,
            'highest_academic'=> $request->highest_academic,
            'awarding_body'=> $request->awarding_body,
            'subjects'=> $request->subjects,
            'result'=> $request->result,
            'highest_qualification_on_entry_id'=> $request->highest_qualification_on_entry_id,
            'hesa_qualification_subject_id'=> $request->hesa_qualification_subject_id,
            'qualification_type_identifier_id'=> $request->qualification_type_identifier_id,
            'previous_provider_id'=> $request->previous_provider_id,
            'hesa_exam_sitting_venue_id'=> ($request->hesa_exam_sitting_venue_id) ?? null,
            'degree_award_date'=> date('Y-m-d', strtotime($request->degree_award_date)),
            'created_by' => auth()->user()->id
        ]);

        return response()->json($data);
    }

    public function edit($id){
        $data = StudentQualification::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(StudentQualificationRequest $request){      
        $data = StudentQualification::where('id', $request->id)->update([
            'student_id' => $request->student_id,
            'highest_academic' => $request->highest_academic,
            'awarding_body' => $request->awarding_body,
            'subjects' => $request->subjects,
            'result' => $request->result,
            'highest_qualification_on_entry_id'=> (isset($request->highest_qualification_on_entry_id) && !empty($request->highest_qualification_on_entry_id)) ? $request->highest_qualification_on_entry_id : null,
            'hesa_qualification_subject_id'=> (isset($request->hesa_qualification_subject_id) && !empty($request->hesa_qualification_subject_id)) ? $request->hesa_qualification_subject_id : null,
            'qualification_type_identifier_id'=> (isset($request->qualification_type_identifier_id) && !empty($request->qualification_type_identifier_id)) ? $request->qualification_type_identifier_id : null,
            'previous_provider_id'=> (isset($request->previous_provider_id) && !empty($request->previous_provider_id)) ? $request->previous_provider_id : null,
            'hesa_exam_sitting_venue_id'=> (isset($request->hesa_exam_sitting_venue_id) && !empty($request->hesa_exam_sitting_venue_id)) ? $request->hesa_exam_sitting_venue_id : null,
            'degree_award_date'=> (isset($request->degree_award_date) && !empty($request->degree_award_date)) ? date('Y-m-d', strtotime($request->degree_award_date)) : null,
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = StudentQualification::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = StudentQualification::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
