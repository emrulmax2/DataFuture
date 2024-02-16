<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseModule;
use App\Models\CourseModuleBaseAssesment;
use App\Http\Requests\CourseModuleBaseAssesmentRequest;
use App\Models\AssessmentType;
use App\Models\ResultsegmentInCoursemodules;

class CourseModuleBaseAssesmentController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $module = (isset($request->module) && $request->module > 0 ? $request->module : 0);

        $query = CourseModuleBaseAssesment::where('course_module_id', $module);
        if(!empty($queryStr)):
            $query->where('assesment_code','LIKE','%'.$queryStr.'%');
            $query->orWhere('assesment_name','LIKE','%'.$queryStr.'%');
        endif;
        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);        
        $limit = $perpage;

        $query = CourseModuleBaseAssesment::where('course_module_id', $module)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('assesment_code','LIKE','%'.$queryStr.'%');
            $query->orWhere('assesment_name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;
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
                    'is_result_segment' => $list->is_result_segment,
                    'view_in_plan' => $list->view_in_plan,
                    'course_module_id'  => $list->course_module_id,
                    'assessment_type_id'  => $list->assessment_type_id,
                    'code' => $list->assesment_code,
                    'name' => $list->assesment_name,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(CourseModuleBaseAssesmentRequest $request){
    
        $assementType = AssessmentType::find($request->assessment_type_id);
        $request->merge([
            'created_by' => auth()->user()->id,
            'assesment_code' => $assementType->code,
            'assesment_name' => $assementType->name,
        ]);
        $courseModuleAssesment = CourseModuleBaseAssesment::create($request->all());
        if(isset($request->grade)) {
            foreach($request->grade as $grade) {
                
                ResultsegmentInCoursemodules::create([
                    "grade_id" => $grade,
                    "course_module_base_assesment_id" => $courseModuleAssesment->id,
                ]);

            }
        }
        return response()->json($courseModuleAssesment);
    }

    public function edit($id){
        $data = CourseModuleBaseAssesment::with(["-"])->where('id',$id)->get()->first();

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseModuleBaseAssesmentRequest $request){
        $courseModuleID = $request->course_module_id;
        $courseModuleAssesmentID = $request->id;
        $assementType = AssessmentType::find($request->assessment_type_id);
        $courseModuleAssesments = CourseModuleBaseAssesment::where('id', $courseModuleAssesmentID)->where('course_module_id', $courseModuleID)->update([
            'assesment_code'=>  $assementType->code,
            'assesment_name'=>  $assementType->name,
            'assessment_type_id' => $request->assessment_type_id,
            'is_result_segment'=> isset($request->is_result_segment) ? $request->is_result_segment : 0,
            'view_in_plan' => isset($request->view_in_plan) ? $request->view_in_plan : 1,
            'updated_by' => auth()->user()->id
        ]);
        if(isset($request->grade)) {
            $courseModuleAssesments = CourseModuleBaseAssesment::find($courseModuleAssesmentID);
            
            $courseModuleAssesments->grades()->sync($request->grade);
            
        }

        if($courseModuleAssesments){

            return response()->json(['message' => 'Data updated'], 200);

        }else{

            return response()->json(['message' => 'omething went wrong!'], 422);
            
        }
    }

    public function destroy($id){
        $data = CourseModuleBaseAssesment::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = CourseModuleBaseAssesment::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
