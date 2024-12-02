<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResultComparisonRequest;
use App\Http\Requests\StoreResultRequest;
use App\Http\Requests\UpdateResultRequest;
use App\Models\Assessment;
use App\Models\AssessmentPlan;
use App\Models\Assign;
use App\Models\ELearningActivitySetting;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlanContent;
use App\Models\PlanContentUpload;
use App\Models\PlansDateList;
use App\Models\PlanTask;
use App\Models\PlanTaskUpload;
use App\Models\Result;
use App\Models\ResultComparison;
use App\Models\ResultSubmission;
use App\Models\ResultSubmissionByStaff;
use App\Models\StudentArchive;
use App\Models\TermDeclaration;
use App\Models\TermPublishDate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facades\Debugbar as FacadesDebugbar;
use Illuminate\Support\Facades\DB;

class ResultComparisonController extends Controller
{
    public function index(Request $request, Plan $plan)
    {
 
        $moduleCreation = ModuleCreation::find($plan->module_creation_id);
        
        $assessmentlist = $moduleCreation->module->assesments;
        
        $userData = User::find(Auth::user()->id);
        $employee = Employee::where("user_id",$userData->id)->get()->first();

        $tutor = (isset($plan->tutor_id) && $plan->tutor_id > 0 ? Employee::where('user_id', $plan->tutor_id)->get()->first() : '');
        $personalTutor = isset($plan->personal_tutor_id) && $plan->personal_tutor_id > 0 ? Employee::where('user_id', $plan->personal_tutor_id)->get()->first() : "";
        
        
        $studentAssign = Assign::with('student')->where('plan_id', $plan->id)->get();
        $studentListCount = $studentAssign->count();
        
        $eLearningActivites = ELearningActivitySetting::all();
        
        
        
            $moduleCreations = ModuleCreation::find($plan->creations->id);

            $term_publish_date = TermDeclaration::where('id', $plan->term_declaration_id)->get()->first();
            $data = (object) [
                'id' => $plan->id,
                'term_name' => $moduleCreations->term->name,
                'course' => $plan->course->name,
                'classType' => $plan->creations->class_type,
                'module' => $plan->creations->module_name,
                'group'=> $plan->group->name,           
                'room'=> $plan->room->name,           
                'venue'=> $plan->venu->name,           
                'tutor'=> ($tutor->full_name) ?? null,           
                'personalTutor'=> ($personalTutor->full_name) ?? null,           
            ];
        
        $AssessmentPlanStaff = AssessmentPlan::where('plan_id', $plan->id)->where('upload_user_type','staff')->orderBy('created_at','DESC')->get()->first();
        
        
        $AssessmentPlan = AssessmentPlan::where('plan_id', $plan->id)->where('upload_user_type','personal_tutor')->orderBy('created_at','DESC')->get()->first();
        
        $resultComparison = ResultComparison::where('plan_id', $plan->id)->where('assessment_plan_id', $AssessmentPlanStaff->id)->get()->first();

        if(isset($resultComparison->id)) {
            $resultIds = json_decode($resultComparison->result_Ids);
        } else {
            $resultIds = [];
        }
        $resultSet = [];

        foreach($studentAssign as $key => $value){

            $resultSubmissionByStaff = ResultSubmissionByStaff::with('createdBy')
                            ->where('plan_id', $plan->id)
                            ->where('student_id', $value->student->id)
                            ->where('is_it_final',1)
                            ->whereHas('assessmentPlan', function($query) use ($AssessmentPlanStaff){
                                $query->where('id', $AssessmentPlanStaff->id);
                            })
                            ->orderBy('created_at','DESC')
                            ->get()->first();
            $resultSubmissionByTutor = ResultSubmission::with('createdBy')
            ->where('plan_id', $plan->id)
            ->where('student_id', $value->student->id)
            ->where('is_it_final',1)
            ->whereHas('assessmentPlan', function($query) use ($AssessmentPlan){
                $query->where('id', $AssessmentPlan->id);
            })
            ->orderBy('created_at','DESC')
            ->get()->first();
            if(count($resultIds) > 0):
                $result = Result::whereIn('id', $resultIds)->where('student_id', $value->student->id)->orderBy('created_at','DESC')->get()->first();
            else:
                $result = [];
            endif;
            if(isset($result->id)):
                $resultSet[$key]['id'] = $result->id;
            endif;  
            $resultSet[$key]['full_name'] = $value->student->full_name;
            $resultSet[$key]['student_id'] = $value->student->id;
            $resultSet[$key]['registration_no'] = $value->student->registration_no;
            $resultSet[$key]['status'] = $value->student->status->name;
            $resultSet[$key]['assement'] = ($AssessmentPlanStaff->course_module_base_assesment_id == $AssessmentPlan->course_module_base_assesment_id) ? $AssessmentPlan->courseModuleBase->assesment_name." - ".$AssessmentPlan->courseModuleBase->assesment_code : 'Assesment Plan Not Matched';
            $resultSet[$key]['assessment_plan_id'] = ($AssessmentPlanStaff->course_module_base_assesment_id == $AssessmentPlan->course_module_base_assesment_id) ? $AssessmentPlanStaff->id : '';
            $resultSet[$key]['staff_given_grade'] = isset($resultSubmissionByStaff->grade->name) ? $resultSubmissionByStaff->grade->code. "-" .$resultSubmissionByStaff->grade->name : 'N/A';
            $resultSet[$key]['staff_paper_ID'] = isset($resultSubmissionByStaff->paper_id) ? $resultSubmissionByStaff->paper_id : '';
            $resultSet[$key]['tutor_given_grade'] = isset($resultSubmissionByTutor->grade->name) ? $resultSubmissionByTutor->grade->code. "-" .$resultSubmissionByTutor->grade->name : 'N/A';
            $resultSet[$key]['tutor_given_paper_ID'] = isset($resultSubmissionByStaff->paper_id) ? $resultSubmissionByStaff->paper_id : '';
            $resultSet[$key]['attendance'] = $value->attendance;
            $resultSet[$key]['grade_matched'] = ($resultSet[$key]['staff_given_grade'] == $resultSet[$key]['tutor_given_grade']) ? "Matched" : "Not Matched";
            $resultSet[$key]['grade'] = ($resultSet[$key]['staff_given_grade'] == $resultSet[$key]['tutor_given_grade']) && ($resultSet[$key]['staff_given_grade']!="N/A" || $resultSet[$key]['tutor_given_grade']!="N/A") ? $resultSubmissionByStaff->grade->id : "";
            $resultSet[$key]['publish_at'] = (isset($AssessmentPlanStaff->id) && !empty($AssessmentPlanStaff->published_at)) ? date('d-m-Y', strtotime($AssessmentPlanStaff->published_at)) : '';
            $resultSet[$key]['publish_time'] = (isset($AssessmentPlanStaff->id) && !empty($AssessmentPlanStaff->published_at)) ? date('H:i', strtotime($AssessmentPlanStaff->published_at)) : '';
        }
        return view('pages.tutor.module.result-comparison', [
            'title' => 'Attendance - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            "plan" => $plan,
            "user" => $userData,
            "employee" => $employee,
            "data" => $data,
            'studentAssign' => $studentAssign,
            'eLearningActivites' => $eLearningActivites,
            'studentCount' => $studentListCount,
            'assessmentlist' => $assessmentlist, 
            'resultSet'=>$resultSet,
            'term_publish_date' => $term_publish_date,
            'AssessmentPlan' => $AssessmentPlanStaff,
            'grades' => Grade::all(),
            'resultIds' => $resultIds,

        ]);
       
        
    }


    public function store(StoreResultComparisonRequest $request) {

        
        $gradeList = $request->input('grade_id');
        foreach($gradeList as $grade) {
            if($grade==null) {
                return response()->json(['message' => 'Grade field required',"errors"=>["grade_id[]"=>"This field is required."]], 422);
            }
        }
        if(is_array($request->input('grade_id')))
        {

            $grade_id = $request->input('grade_id');
            $plan_id = $request->input('plan_id');
            $assessment_plan_id = $request->input('assessment_plan_id');
            $student_id = $request->input('student_id');
            $published_at = $request->input('publish_at');
            $published_time = $request->input('publish_time');
            $created_by = Auth::user()->id;

            for($count = 0; $count < count($grade_id); $count++)
            {
                $data = array(
                        'grade_id' => $grade_id[$count],
                        'assessment_plan_id'  => $assessment_plan_id[$count],
                        'student_id'  => $student_id[$count],
                        'plan_id' =>$plan_id,
                        'published_at'  => date("Y-m-d H:i:s",strtotime($published_at[$count]." ".$published_time[$count])),
                        'created_by'  => $created_by,
                    );

                $insert_schedule[] = $data; 
            }
            DB::transaction(function () use ($insert_schedule, &$insertedIds) {
                foreach ($insert_schedule as $schedule) {
                    $insertedIds[] = Result::insertGetId($schedule);
                }
            });
            ResultComparison::updateOrCreate([
                'plan_id' => $plan_id,
                'assessment_plan_id' => $assessment_plan_id[0]
            ],
            [
                'plan_id' => $plan_id,
                'assessment_plan_id' => $assessment_plan_id[0],
                'result_Ids' => json_encode($insertedIds),
                'created_by' => $created_by,
                'updated_by' => $created_by,
            ]);

            return $insertedIds;

        } else {
            $result = new Result();
            $result->fill($request->all());
            $result->save();

            if($result->id)
                return response()->json(['message' => 'Result successfully created.',"data"=>['result'=>$result]], 200);
            else
                return response()->json(['message' => 'Result could not be saved'], 302);
        }
    }

    public function update(UpdateResultRequest $request) {
            $grade_id = $request->input('grade_id');
            $plan_id = $request->input('plan_id');
            $term_declaration_id = $request->input('term_declaration_id');
            $student_id = $request->input('student_id');
            $published_at = $request->input('published_at');
            $created_at = $request->input('created_at');
            $id = $request->input('id');

            for($count = 0; $count < count($grade_id); $count++)
            {
                


                if($id[$count] == null) {

                    $data = [
                        'grade_id' => $grade_id[$count],
                        'term_declaration_id'  => $term_declaration_id[$count],
                        'student_id'  => $student_id[$count],
                        'plan_id' =>$plan_id[$count],
                        'published_at'  => date("Y-m-d H:i:s",strtotime($published_at[$count])),
                        'created_at'  => ($created_at[$count]!=null) ? date("Y-m-d H:i:s",strtotime($created_at[$count])) : date("Y-m-d H:i:s"),
                        'created_by'  => auth()->user()->id,

                    ];
                    $resultCreate = Result::create($data);
                   
                } else {
                    //(isset($result->createdBy->employee->full_name) ? $result->createdBy->employee->full_name: $result->createdBy->name) 
                    
                    

                    $ResultOldRow = Result::find($id[$count]);
                    $result = Result::find($id[$count]);
                    
                    // Normalize the datetime values
                    $publishedAt = Carbon::parse($published_at[$count])->format('Y-m-d H:i:s');
                    $currentPublishedAt = $result->published_at ? Carbon::parse($result->published_at)->format('Y-m-d H:i:s'): null;

                    // Assign the normalized value to the model
                    if ($currentPublishedAt != $publishedAt) {
                        $result->published_at = $publishedAt;
                    }
                    // Normalize the datetime values for created_at
                    $createdAt = Carbon::parse($created_at[$count])->format('Y-m-d H:i:s');
                    $currentCreatedAt = $result->created_at ? Carbon::parse($result->created_at)->format('Y-m-d H:i:s') : null;

                    // Assign the normalized value to the model if they are different
                    if ($currentCreatedAt != $createdAt) {
                        $result->created_at = $createdAt;
                    }
                    
                    $result->grade_id = $grade_id[$count];
                    $result->term_declaration_id  = $term_declaration_id[$count];
                    $result->student_id  = $student_id[$count];
                    $result->plan_id = $plan_id[$count];

                    $changes = $result->getDirty();

                    if(!empty($changes)) {

                        FacadesDebugbar::info($changes);
                        $result->updated_by = auth()->user()->id;
                        
                        $result->save();

                        if($result->wasChanged() && !empty($changes)):
                            foreach($changes as $field => $value):
                                $data = [];
                                $data['student_id'] = $result->student_id;
                                $data['table'] = 'results';
                                $data['field_name'] = $field;
                                $data['field_value'] = $ResultOldRow->$field;
                                $data['field_new_value'] = $value;
                                $data['created_by'] = auth()->user()->id;
                                StudentArchive::create($data);
                            endforeach;
                        endif;
                    }

                    
                }
                
            }
            
            if($result->id)
                return response()->json(['message' => 'Result successfully updated.',"data"=>['result'=>$result]], 200);
            else if($resultCreate->id)
                return response()->json(['message' => 'Result successfully created.',"data"=>['result'=>$resultCreate]], 200);
            else
                return response()->json(['message' => 'Result could not be saved'], 302);

        //}
    
    }
}
