<?php

namespace App\Http\Controllers\Student\Result;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\Grade;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\Result;
use App\Models\Status;
use App\Models\Student;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Student $student)
    {
        $grades = Grade::all();
        
            $termData = [];
            $QueryInner = DB::table('plans as plan')
                        ->select('td.id as term_id',
                            'td.name as term_name',
                            'instance_terms.start_date',
                            'instance_terms.end_date', 
                            'plan.module_creation_id as module_creation_id' , 
                            'mc.module_name',
                            'mc.code as module_code', 
                            'plan.id as plan_id',
                            'abody.name as award' )
                        ->leftJoin('instance_terms', 'instance_terms.id', 'plan.instance_term_id')
                        ->leftJoin('assigns as assign', 'plan.id', 'assign.plan_id')
                        ->leftJoin('term_declarations as td', 'td.id', 'plan.term_declaration_id')
                        ->leftJoin('module_creations as mc', 'mc.id', 'plan.module_creation_id')
                        ->leftJoin('courses', 'courses.id', 'plan.course_id')
                        ->leftJoin('awarding_bodies as abody', 'abody.id', 'courses.awarding_body_id')
                        ->where('assign.student_id', $student->id)
                        ->orderBy("td.id",'desc')
                        ->get();
            $data = [];
            foreach($QueryInner as $list):
                $moduleCreation = ModuleCreation::with('module','level')->where('id',$list->module_creation_id)->get()->first();
                $checkPrimaryResult = Result::with([
                "grade",
                "createdBy",
                "updatedBy",
                "plan",
                "plan.creations",
                "plan.course.body",
                "plan.creations.module"])->where("student_id", $student->id)
                ->whereHas('plan', function($query) use ($list) {
                    $query->where('module_creation_id', $list->module_creation_id)->where('id', $list->plan_id);
                })
                ->orderBy('id','DESC')->get();
                $resultPrimarySet = [];
                if($checkPrimaryResult->isNotEmpty()) {
                    foreach ($checkPrimaryResult as $key => $result) {
                        $data[$moduleCreation->module->name][] = $result;

                        if($result->is_primary === "Yes") {
                            $resultPrimarySet[$result->id] = "Yes";
                        }
                    }

                }
                
                //     $termName = $gradeCode = $gradeName = null;

                //     if($checkPrimaryResult->isNotEmpty()):

                //         $firstResult = $checkPrimaryResult->first();
                //         $id  = $firstResult->id;
                //         $termName  = $firstResult->plan->attenTerm->name;
                //         $gradeCode = $firstResult->grade->code;
                //         $gradeName = $firstResult->grade->name;
                        
                //         foreach ($checkPrimaryResult as $key => $result) {
                //             if ($result->is_primary === "Yes") {
                //                 $id        = $result->id;
                //                 $termName  = $result->plan->attenTerm->name;
                //                 $gradeCode = $result->grade->code;
                //                 $gradeName = $result->grade->name;
                //             }
                //         }
                //     endif;
                // if($checkPrimaryResult->isNotEmpty()) {
                //     $data[$moduleCreation->module->name] = [
                //         "id" => $id,
                //         "term"=> $termName,
                //         "module_name"=>$moduleCreation->module->name,
                //         "code"=>$moduleCreation->code,
                //         "awardingBody"=>$list->award,
                //         "grade_code" => $gradeCode,
                //         "grade_name" => $gradeName,                                                
                //         "level"=>$moduleCreation->level->name,
                //         "results" => $resultByPlanGroup[$moduleCreation->module->name],
                //     ];
                // }

                $termData[$list->term_id] = [
                    "name" => $list->term_name,
                    "start_date" => $list->start_date,
                    "end_date" => $list->end_date,
                ];
            endforeach;
        return view('pages.students.live.result.index', [
            'title' => 'Students - Results',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Results', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'dataSet' => ($data) ?? null,
            "grades" =>$grades,
            "resultPrimarySet" =>$resultPrimarySet,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function dataSet(Request $request,Student $student)
    {

            $termData = [];
            $QueryInner = DB::table('plans as plan')
                        ->select('td.id as term_id',
                            'td.name as term_name',
                            'instance_terms.start_date',
                            'instance_terms.end_date', 
                            'plan.module_creation_id as module_creation_id' , 
                            'mc.module_name',
                            'mc.code as module_code', 
                            'plan.id as plan_id',
                            'abody.name as award' )
                        ->leftJoin('instance_terms', 'instance_terms.id', 'plan.instance_term_id')
                        ->leftJoin('assigns as assign', 'plan.id', 'assign.plan_id')
                        ->leftJoin('term_declarations as td', 'td.id', 'plan.term_declaration_id')
                        ->leftJoin('module_creations as mc', 'mc.id', 'plan.module_creation_id')
                        ->leftJoin('courses', 'courses.id', 'plan.course_id')
                        ->leftJoin('awarding_bodies as abody', 'abody.id', 'courses.awarding_body_id')
                        ->where('assign.student_id', $student->id)
                        ->orderBy("td.id",'desc')
                        ->get();
            $data = [];
            foreach($QueryInner as $list):

                $resultByPlanGroup[$list->plan_id] = Result::with(["assementPlan","grade","createdBy","updatedBy"])->where("student_id", $student->id)->where("plan_id",$list->plan_id)->orderBy('id','DESC')->get()->groupBy(function($data) {
                    return $data->assessment_plan_id;
                });
                
                if(isset($resultByPlanGroup) && count($resultByPlanGroup[$list->plan_id])>0) {
                    $moduleCreation = ModuleCreation::with('module','level')->where('id',$list->module_creation_id)->get()->first();
                    $data[$list->term_id][$list->plan_id] = [
                            "term_id"=> $list->term_id,
                            "module_creation_id"=>$list->module_creation_id,
                            "module_name"=>$moduleCreation->module->name,
                            "code"=>$moduleCreation->code,
                            "awardingBody"=>$list->award,
                            "level"=>$moduleCreation->level->name,
                            "id" => $list->plan_id,
                            "results" => ($resultByPlanGroup[$list->plan_id]) ?? null
                    ];
                    
                    $termData[$list->term_id] = [
                        "name" => $list->term_name,
                        "start_date" => $list->start_date,
                        "end_date" => $list->end_date,
                    ];
                    $planDetails[$list->term_id][$list->plan_id] = Plan::with(["tutor","personalTutor"])->where('id',$list->plan_id)->get()->first();
                    

                    //total code list and total class list

                }
            endforeach;

        $termstart=0;
        $data = [];
        if( $dataSet ):
            foreach($dataSet as $termId =>$dataStartPoint):
                        $termstart++;
                foreach($dataStartPoint as $moduleDetails => $dataResult):
                    foreach($dataResult["results"] as $assessmentPlan => $resultSet):
                        if($resultSet->isNotEmpty()):
                            foreach($resultSet as $key => $result):
                                if($key==0):
                                    $i =$sl= 1;
                                    $attempt = [];
                                    foreach($resultSet as $assessmentResult):
                                        $attempt[$i]['id'] = $assessmentResult->id;
                                        $attempt[$i]['published_at'] = date('d F, Y',strtotime($assessmentResult->published_at));
                                        $attempt[$i]['grade']   = $assessmentResult->grade->code  ."-".  $assessmentResult->grade->name; 
                                        $attempt[$i]['updated_by'] = isset($assessmentResult->updatedBy) ? $assessmentResult->updatedBy->employee->full_name : $assessmentResult->createdBy->employee->full_name;
                                        $i++;  
                                    endforeach;  
                                    $data[] = [
                                        'id' => $result->id,
                                        'sl' => $sl++,
                                        'term' => $term[$termId]["name"],
                                        'module' => $dataResult['module_name']  ." - ".  $dataResult['level'],
                                        'award' => $dataResult['awardingBody'],
                                        'code' => $dataResult['code'],
                                        'published_at' => date('d F, Y',strtotime($result->published_at)),
                                        'grade_code' => (isset($result->grade->code) && !empty($result->grade->code) ? $result->grade->code : ''),
                                        'grade_name' => (isset($result->grade->name) && !empty($result->grade->name) ? $result->grade->name : ''),
                                        'total_attempt' => $resultSet->count(),
                                        'created_by'=> isset($result->updatedBy) ? $result->updatedBy->employee->full_name : $result->createdBy->employee->full_name,
                                        'created_at'=> date('d F, Y',strtotime($result->created_at)),
                                        'attempt' => $attempt,
                                    ];
                                        
                                endif;
                            endforeach;
                        endif;
                    endforeach;
                endforeach;
            endforeach;
        endif;

        return $data;
    }

}
