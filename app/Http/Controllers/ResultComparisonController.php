<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResultComparisonRequest;
use App\Http\Requests\StoreResultRequest;
use App\Http\Requests\UpdateResultComparisonRequest;
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
use Google\Service\CloudAsset\Asset;
use Illuminate\Support\Facades\DB;

class ResultComparisonController extends Controller
{
    public function index(Request $request, Plan $plan,$module_assessment=null)
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
        $studentSet =    $studentAssign->pluck('student_id')->toArray();

        if(isset($module_assessment)) {
            $AssessmentPlanStaff = AssessmentPlan::find($module_assessment);
        } else {
            $AssessmentPlanStaff = AssessmentPlan::where('plan_id', $plan->id)->where('upload_user_type','staff')->orderBy('created_at','DESC')->get()->first();
        }
        
        $submissionAssessment = AssessmentPlan::where('plan_id', $plan->id)->where('upload_user_type','staff')->orderBy('created_at','DESC')->get();
        


        $AssessmentPlan = AssessmentPlan::where('plan_id', $plan->id)->where('upload_user_type','personal_tutor')->orderBy('created_at','DESC')->get()->first();
        
        $resultComparison = ResultComparison::where('plan_id', $plan->id)->where('assessment_plan_id', $AssessmentPlanStaff->id)->pluck('result_id')->toArray();


        if(isset($resultComparison)) {
            $resultIds = $resultComparison;
        } else {
            $resultIds = [];
        }
        $resultSet = [];

        foreach($studentAssign as $key => $value) {
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
            if(isset($resultIds) && count($resultIds) > 0):
                $result = Result::whereIn('id', $resultIds)->where('student_id', $value->student->id)->orderBy('created_at','DESC')->get()->first();
            else:
                $result = [];
            endif;
            if(isset($result->id)):
                $resultSet[$key]['id'] = $result->id;
            endif;  
            
            
            $resultSet[$key]['result_submission_staff_id'] = isset($resultSubmissionByStaff->id) ? $resultSubmissionByStaff->id : null;
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
            $resultSet[$key]['grade'] = '';
            $resultSet[$key]['paper_id'] =  $resultSet[$key]['staff_paper_ID'] != "" ? $resultSet[$key]['staff_paper_ID'] : '';
            
            if(($resultSet[$key]['staff_given_grade'] == $resultSet[$key]['tutor_given_grade']) && ($resultSet[$key]['staff_given_grade']!="N/A" || $resultSet[$key]['tutor_given_grade']!="N/A")) {
                if(isset($result->id)):
                    $resultSet[$key]['grade'] = $result->grade_id;
                else:
                    $resultSet[$key]['grade'] = $resultSubmissionByStaff->grade->id;
                endif;
            } else {

                if(count($resultIds) > 0):
                    if(isset($result->id)):
                        $resultSet[$key]['grade'] = $result->grade_id;
                    else:
                        $resultSet[$key]['grade'] = '';
                    endif;
                endif;

            }
            
            if(count($resultIds) > 0):
                if(isset($result->id)):
                    $resultSet[$key]['publish_at'] = (isset($result->id) && !empty($result->published_at)) ? date('d-m-Y', strtotime($result->published_at)) : '';
                    $resultSet[$key]['publish_time'] = (isset($result->id) && !empty($result->published_at)) ? date('H:i', strtotime($result->published_at)) : ''; 
                else:
                    $resultSet[$key]['publish_at'] = (isset($AssessmentPlanStaff->id) && !empty($AssessmentPlanStaff->published_at)) ? date('d-m-Y', strtotime($AssessmentPlanStaff->published_at)) : '';
                    $resultSet[$key]['publish_time'] = (isset($AssessmentPlanStaff->id) && !empty($AssessmentPlanStaff->published_at)) ? date('H:i', strtotime($AssessmentPlanStaff->published_at)) : ''; 
                endif;
            else:
                $resultSet[$key]['publish_at'] = (isset($AssessmentPlanStaff->id) && !empty($AssessmentPlanStaff->published_at)) ? date('d-m-Y', strtotime($AssessmentPlanStaff->published_at)) : '';
                $resultSet[$key]['publish_time'] = (isset($AssessmentPlanStaff->id) && !empty($AssessmentPlanStaff->published_at)) ? date('H:i', strtotime($AssessmentPlanStaff->published_at)) : ''; 
            endif;
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
            'submissionAssessment' => $submissionAssessment,
        ]);
       
        
    }


    public function store(StoreResultComparisonRequest $request) {

        
        $gradeList = $request->input('grade_id');
        $paper_id = $request->input('paper_id');
        $resultIds = array_filter($request->input('result_id'));
        $student_id = $request->input('student_id');
        $ids = $request->input('id');
        
        if(is_array($request->input('grade_id')))
        {
            
            $grade_id = $request->input('grade_id');
            $plan_id = $request->input('plan_id');
            $studentList = Assign::where('plan_id', $request->input('plan_id'))->where(function($q){
                $q->where('attendance', 1)->orWhereNull('attendance');
            })->pluck('student_id')->toArray();

            $studentListCount = count($studentList) > 0 ? count($studentList)  : 0;
            
            $assessment_plan_id = $request->input('assessment_plan_id');
            $student_id = $request->input('student_id');
            $published_at = $request->input('publish_at');
            $published_time = $request->input('publish_time');
            $created_by = Auth::user()->id;
            $insert_schedule = [];
            
            foreach($ids as $key => $value)
            {

                $index = $key;
                
                $planId = Plan::find($plan_id);
                //get the index number of selected row and pusht the data to the array
                //$index = array_search($value, $student_id);

                $assign = Assign::where('student_id', $student_id[$index])->where('plan_id', $planId->id)->get()->first();
                if($assign->attendance===1 || $assign->attendance===null) {

                    $data = array(
                            'grade_id' => $grade_id[$index],
                            'assessment_plan_id'  => $assessment_plan_id[$index],
                            'student_id'  => $student_id[$index],
                            'plan_id' =>$plan_id,
                            'paper_id'=>$paper_id[$index],
                            'module_code'=> isset($planId->creations->code) ? $planId->creations->code : $planId->creations->module->code ,
                            'term_declaration_id' => $planId->term_declaration_id,
                            'published_at'  => date("Y-m-d H:i:s",strtotime($published_at[$index]." ".$published_time[$index])),
                            'created_by'  => $created_by,
                            'created_at' => Carbon::now(),
                        );
                    $insert_schedule[] = $data; 

                }
            }
            DB::transaction(function () use ($insert_schedule, &$insertedIds) {
                foreach ($insert_schedule as $schedule) {
                    $insertedIds[] = Result::insertGetId($schedule);
                }
            });
            if(isset($insertedIds) && count($insertedIds) > 0) {
                
                $resultSet = Result::whereIn('student_id', $studentList)->where('plan_id', $plan_id)->where('assessment_plan_id', $assessment_plan_id[1])->get();
                $studentListResult = Result::whereIn('student_id', $studentList)->where('plan_id', $plan_id)->where('assessment_plan_id', $assessment_plan_id[1])->pluck('student_id')->unique()->toArray();
                //dd($resultSet);
                $publishDone =  "Yes";
                foreach($resultSet as $result) {
                    ResultComparison::updateOrCreate([
                        'plan_id' => $plan_id,
                        'assessment_plan_id' => $assessment_plan_id[1],
                        'result_id' => $result->id
                    ],
                    [
                        'plan_id' => $plan_id,
                        'assessment_plan_id' => $assessment_plan_id[1],
                        'result_id' => $result->id,
                        'student_id' => $result->student_id,
                        'created_by' => $created_by,
                        'updated_by' => $created_by,
                        'publish_done' => $publishDone,
                    ]);
                }
    
                return $insertedIds;
            }

            return $insertedIds;

        } else {
            return response()->json(['message' => 'Result could not be saved'], 302);
        }

    }

    public function update(UpdateResultComparisonRequest $request) {
            $grade_id = $request->input('grade_id');
            $plan_id = $request->input('plan_id');
            $paper_id = $request->input('paper_id');
            $student_id = $request->input('student_id');
            $published_at = $request->input('publish_at');
            $published_time = $request->input('publish_time');
            $resultIds = $request->input('result_id');
            $ids = $request->input('id');

            foreach($ids as $index => $id)
            {
                if($resultIds[$index]!=null) {
                    $ResultOldRow = Result::find($id);
                    $result = Result::find($id);
                        

                    $result->published_at = $published_at[$index]." ".$published_time[$index];
                    $result->grade_id = $grade_id[$index];
                    $result->paper_id = $paper_id[$index];

                    $changes = $result->getDirty();

                    if(!empty($changes)) {

                        //FacadesDebugbar::info($changes);
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

                } else {
                    return response()->json(['message' => 'No Previous Result Found. Add a new record',"errors"=> [
                            "grade_id.$index"=> [
                                "No Previous Result Found. Add a new record."
                            ]
                        ]
                    ], 302);
                }
            }
            
            if($result->id)
                return response()->json(['message' => 'Result successfully updated.',"data"=>['result'=>$result]], 200);
            else
                return response()->json(['message' => 'Result could not be saved'], 302);

        //}
    
    }

    public function deleteResultBulk(Request $request)
    {
        
        $resultIds = array_filter(array_unique($request->input('ids')));
        $plan_id = $request->input('plan_id');
        $assessment_plan_ids = array_filter(array_unique($request->input('assessment_plan_ids')));

        $courseModuleBaseAssesmentId = AssessmentPlan::whereIn('id',$assessment_plan_ids)->pluck('course_module_base_assesment_id')->unique()->toArray();
        
        $baseResultDelete = Result::whereIn('id', $resultIds)->delete();

        $prevResultDelete = Result::where('plan_id',$plan_id)->whereHas('assementPlan', function($query) use ($courseModuleBaseAssesmentId){
            $query->whereIn('course_module_base_assesment_id', $courseModuleBaseAssesmentId);
        })->delete();

        if($baseResultDelete||$prevResultDelete)
            return response()->json(['message' => 'Result successfully deleted.'], 200);
        else
            return response()->json(['message' => 'Result could not be deleted'], 302);
        
    }


    public function deleteResultSubmissionByStaffBulk(Request $request)
    {
        
        $SubmissionIds = array_filter(array_unique($request->input('id')));
        $query1SubmissionByStaff = ResultSubmissionByStaff::whereIn('id', $SubmissionIds)->get();
        if($query1SubmissionByStaff->isEmpty()) {
            return response()->json(['message' => 'No Result Submission found.'], 404);
        }
        foreach($query1SubmissionByStaff as $submission) {
            
            Result::where('student_id', $submission->student_id)
                ->where('assessment_plan_id', $submission->assessment_plan_id)
                ->delete();

        }

        //$query2 = ResultSubmissionByStaff::whereIn('id', $SubmissionIds)->delete();

        
 
        //if($query2)
            return response()->json(['message' => 'Result successfully deleted.'], 200);
        //else
           // return response()->json(['message' => 'Result could not be deleted'], 302);
        
    }
}
