<?php

namespace App\Http\Controllers;

use App\Exports\ResultSubmissionSampleDownload;
use App\Imports\ResultImport;
use App\Imports\ResultSubmissionImport;
use App\Imports\ResultSubmissionValidationImport;
use App\Models\AssessmentPlan;
use App\Models\Assign;
use App\Models\AttendanceFeedStatus;
use App\Models\ComonSmtp;
use App\Models\ELearningActivitySetting;
use App\Models\EmailTemplate;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlanContent;
use App\Models\PlanContentUpload;
use App\Models\PlansDateList;
use App\Models\PlanTask;
use App\Models\PlanTaskUpload;
use App\Models\ResultSubmission;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\User;
use Barryvdh\Debugbar\Twig\Extension\Debug;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ResultSubmissionController extends Controller
{

    public function list(Request $request){
        
        $assessmentPlanId = (isset($request->assessmentPlanId) && !empty($request->assessmentPlanId) ? $request->assessmentPlanId : []);
        

    

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ResultSubmission::with('grade')->orderByRaw(implode(',', $sorts))->where('assessment_plan_id', $assessmentPlanId);


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
            $i = $offset+1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'student_id' => $list->student_id,
                    'student_photo' => (isset($list->student->photo_url) && !empty($list->student->photo_url) ? $list->student->photo_url : asset('build/assets/images/user_avatar.png')),
                    'first_name' => (isset($list->student->first_name) && !empty($list->student->first_name) ? $list->student->first_name : ''),
                    'last_name' => (isset($list->student->last_name) && !empty($list->student->last_name) ? $list->student->last_name : ''),
                    'registration_no' => (isset($list->student->registration_no) && !empty($list->student->registration_no) ? $list->student->registration_no : ''),
                    'module_code' => ucfirst($list->module_code),
                    'paper_id' => $list->paper_id,
                    'grade' => ucfirst($list->grade->code)."-".ucfirst($list->grade->name),
                    'publish_at' => $list->published_at,
                    'created_at'=> $list->created_at,
                    'created_by'=> isset($list->createdBy->id) ?$list->createdBy->employee->full_name : $list->createdBy->name,
                ];
                $i++;
            endforeach;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function upload(Request $request , Plan $plan)
    {
        $courseMoudleBaseAssessment = $request->assessment_plan_id;
        $document = $request->file('file');
        
        // read the uploaded file and store it in the database
        $import = new ResultSubmissionValidationImport($courseMoudleBaseAssessment, $plan);
        Excel::import($import, $document);
        $unmatchedStudents = $import->getStudentErrorFound();
        $errorMessage = $import->getErrorMessage();
        
        if (count($unmatchedStudents) > 0) {
            return response()->json(['message' => $errorMessage, 'errors' => $unmatchedStudents], 400);
        } else {

            Excel::import(new ResultSubmissionImport($courseMoudleBaseAssessment, $plan), $document);
            $assessmentPlan = AssessmentPlan::where('course_module_base_assesment_id',$courseMoudleBaseAssessment)->where('plan_id',$plan->id)->orderBy('id','DESC')->get()->first();
            $assessmentPlan->is_it_final = 1;
            $assessmentPlan->save();
            $submittedStudents = ResultSubmission::where('assessment_plan_id', $assessmentPlan->id)->where('plan_id', $plan->id)->pluck('student_id')->toArray();

            $studentIds = Assign::where('plan_id', $plan->id)->where(function($q){
                $q->where('attendance', 1)->orWhereNull('attendance');
            })->pluck('student_id')->toArray();

            
            // compare and get the missing stuedents
            $missingStudents = array_diff($studentIds, $submittedStudents);
            //Debugbar::info($missingStudents);
            sort($missingStudents);
            if(count($missingStudents) > 0){
                // add those missing student to the result submission table
                foreach($missingStudents as $studentId){
                    $student = Student::find($studentId);
                    $resultSubmission = new ResultSubmission();
                    $resultSubmission->assessment_plan_id = $assessmentPlan->id;
                    $resultSubmission->plan_id = $plan->id;
                    $resultSubmission->student_id = $studentId;
                    $resultSubmission->student_course_relation_id = $student->crel->id;
                    $resultSubmission->grade_id = Grade::where('code', 'A')->first()->id;
                    $resultSubmission->is_student_matched = 1;
                    $resultSubmission->is_it_final = 1;
                    $resultSubmission->module_creation_id = $plan->module_creation_id;
                    $resultSubmission->module_code = $plan->creations->code;
                    $resultSubmission->upload_user_type = 'personal_tutor';
                    $resultSubmission->created_by = Auth::id();
                    $resultSubmission->save();
                }
            }
            return response()->json(['message' => 'Document successfully uploaded.'], 200);
        }

    }
    public function uploadAssemsentSubmission(Request $request , Plan $plan)
    {
        $assessmentPlan = AssessmentPlan::find($request->assessment_plan_id);
        $courseMoudleBaseAssessment = $assessmentPlan->course_module_base_assesment_id ;
        $document = $request->file('file');
        
        // read the uploaded file and store it in the database
        $import = new ResultSubmissionValidationImport($courseMoudleBaseAssessment, $plan);
        Excel::import($import, $document);
        $unmatchedStudents = $import->getStudentErrorFound();
        $errorMessage = $import->getErrorMessage();
        
        if (count($unmatchedStudents) > 0) {
            return response()->json(['message' => $errorMessage, 'errors' => $unmatchedStudents], 400);
        } else {

            Excel::import(new ResultSubmissionImport($courseMoudleBaseAssessment, $plan), $document);
            $assessmentPlan = AssessmentPlan::where('course_module_base_assesment_id',$courseMoudleBaseAssessment)->where('plan_id',$plan->id)->orderBy('id','DESC')->get()->first();
        
            $submittedStudents = ResultSubmission::where('assessment_plan_id', $assessmentPlan->id)->where('plan_id', $plan->id)->pluck('student_id')->toArray();

            $studentIds = Assign::where('plan_id', $plan->id)->where(function($q){
                $q->where('attendance', 1)->orWhereNull('attendance');
            })->pluck('student_id')->toArray();

            
            // compare and get the missing stuedents
            $missingStudents = array_diff($studentIds, $submittedStudents);
            //Debugbar::info($missingStudents);
            sort($missingStudents);
            if(count($missingStudents) > 0){
                // add those missing student to the result submission table
                foreach($missingStudents as $studentId){

                    $assigns = Assign::where('student_id', $studentId)->where('plan_id', $plan->id)->get()->first();
                    if($assigns->attendance ===1 || $assigns->attendance === null) {
                        
                        $student = Student::find($studentId);
                        $resultSubmission = new ResultSubmission();
                        $resultSubmission->assessment_plan_id = $assessmentPlan->id;
                        $resultSubmission->plan_id = $plan->id;
                        $resultSubmission->student_id = $studentId;
                        $resultSubmission->student_course_relation_id = $student->crel->id;
                        $resultSubmission->grade_id = Grade::where('code', 'A')->first()->id;
                        $resultSubmission->is_student_matched = 1;
                        $resultSubmission->is_it_final = 1;
                        $resultSubmission->module_creation_id = $plan->module_creation_id;
                        $resultSubmission->module_code = $plan->creations->code;
                        $resultSubmission->upload_user_type = 'personal_tutor';
                        $resultSubmission->created_by = Auth::id();
                        $resultSubmission->save();
                    }
                }
            }
            return response()->json(['message' => 'Document successfully uploaded.'], 200);
        }

    }

    public function sampleDownload(Plan $plan)
    {
        return Excel::download(new ResultSubmissionSampleDownload($plan), 'sample_submission_list.xlsx');
    }


    public function finalSubmission(Request $request , Plan $plan) {
        $ids = $request->ids;
        $plan_id = $plan->id;
        $assigns= Assign::where('plan_id', $plan_id)->get()->pluck('student_id')->toArray();
         foreach($ids as $id){
            if($id!=""){
                $resultSubmission = ResultSubmission::find($id);
                
                if(!in_array($resultSubmission->student_id, $assigns)){
                    
                    $resultSubmission->is_it_final = 0;
                } else {
                    $resultSubmission->is_it_final = 1;
                    AssessmentPlan::where('id', $resultSubmission->assessment_plan_id)->update(['is_it_final' => 1]);
                }
                $resultSubmission->save();
            }
        }

        return response()->json(['message' => 'Final submission successfully done.'], 200);
    }
}
