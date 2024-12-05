<?php

namespace App\Http\Controllers;

use App\Exports\ResultSubmissionSampleDownload;

use App\Imports\ResultSubmissionImport;
use App\Imports\ResultSubmissionValidationImport;
use App\Imports\ResultSubmissionByStaffImport;
use App\Imports\ResultSubmissionStaffImport;
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
use App\Models\Result;
use App\Models\ResultSubmission;
use App\Models\ResultSubmissionByStaff;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;


class ResultSubmissionByStaffController extends Controller
{

    public function list(Request $request){
        
        $assessmentPlanId = (isset($request->assessmentPlanId) && !empty($request->assessmentPlanId) ? $request->assessmentPlanId : []);
        

    

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ResultSubmissionByStaff::with('grade')->orderByRaw(implode(',', $sorts))->where('assessment_plan_id', $assessmentPlanId);


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

    public function showResultSubmission(Request $request, Plan $plan)
    {
 
        $moduleCreation = ModuleCreation::find($plan->module_creation_id);
        
        $assessmentlist = $moduleCreation->module->assesments;
        
        $userData = User::find(Auth::user()->id);
        $employee = Employee::where("user_id",$userData->id)->get()->first();

        $tutor = (isset($plan->tutor_id) && $plan->tutor_id > 0 ? Employee::where('user_id', $plan->tutor_id)->get()->first() : '');
        $personalTutor = isset($plan->personal_tutor_id) && $plan->personal_tutor_id > 0 ? Employee::where('user_id', $plan->personal_tutor_id)->get()->first() : "";
        
        $planTask = PlanTask::where("plan_id",$plan->id)
                    ->where('module_creation_id',$moduleCreation->id)->get();  
        
        $studentAssign = Assign::with('student')->where('plan_id', $plan->id)->get();
        $studentListCount = $studentAssign->count();
        
        $planDates = $planDateList = PlansDateList::where("plan_id",$plan->id)->get();
        $eLearningActivites = ELearningActivitySetting::all();
        $planDateWiseContent = [];
        foreach($planDates as $classDate) {
            $content = PlanContent::where("plans_date_list_id", $classDate->id)->get();
            foreach($content as $singleContent){
                $uploads = PlanContentUpload::where("plan_content_id",$singleContent->id)->get();
    
                $planDateWiseContent[$classDate->id] = (object) [
                    "task" => $content,
                    "taskUploads" => $uploads,
                ];
            }
            
        }
        
        $allPlanTasks = [];

            foreach($planTask as $task){
                $uploads = PlanTaskUpload::with(['createdBy','updatedBy'])->where("plan_task_id",$task->id)->get();

                $allPlanTasks[$task->id] = (object) [
                    "task"=> $task,
                    "taskUploads" => $uploads
                ]; 
            }
        
        $moduleCreations = ModuleCreation::find($plan->creations->id);
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

        $resultSubmission = ResultSubmissionByStaff::with('createdBy')->where('plan_id', $plan->id)->where('upload_user_type','staff')->whereNull('is_it_final')->orderBy('created_at','DESC')->get();
        $submissionAssessment = AssessmentPlan::where('plan_id', $plan->id)->where('upload_user_type','staff')->orderBy('created_at','DESC')->get();
        $submissionAssessmentTutor = AssessmentPlan::where('plan_id', $plan->id)->where('upload_user_type','personal_tutor')->orderBy('created_at','DESC')->get();
       
        return view('pages.tutor.module.result-submission', [
            'title' => 'Attendance - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            "plan" => $plan,
            "user" => $userData,
            "employee" => $employee,
            "data" => $data,
            'planTasks' => $allPlanTasks, 
            'studentAssignArray' => $studentAssign->pluck('student_id')->toArray(),
            'studentResultSubmissionArray' => $resultSubmission->pluck('student_id')->toArray(),
            'studentAssign' => $studentAssign,
            'planDates' => $planDateWiseContent,
            'planDateList' => $planDateList,
            'eLearningActivites' => $eLearningActivites,
            'studentCount' => $studentListCount,
            'assessmentlist' => $assessmentlist, 
            'resultSubmission' => $resultSubmission,
            'submissionAssessment' => $submissionAssessment,
            'submissionAssessmentTutor' => $submissionAssessmentTutor,

            
            'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            'emailTemplates' => EmailTemplate::where('live', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'smtps' => ComonSmtp::where('is_default', 1)->get()->first(),

            'attendanceStatus' => AttendanceFeedStatus::orderBy('id', 'ASC')->get(),
            'attendance_rate' => [],
            'attendance_trend' => []
        ]);
       
        
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
            
            Excel::import(new ResultSubmissionByStaffImport($courseMoudleBaseAssessment, $plan), $document);
            
            $assessmentPlan = AssessmentPlan::where('course_module_base_assesment_id',$courseMoudleBaseAssessment)
                ->where('upload_user_type','staff')
                ->where('plan_id',$plan->id)
                ->whereNull('is_it_final')
                ->orderBy('created_at','DESC')->get()->first();

            $assessmentPlan->is_it_final = 1;
            $assessmentPlan->save();
            $submittedStudents = ResultSubmissionByStaff::where('assessment_plan_id', $assessmentPlan->id)->where('plan_id', $plan->id)->pluck('student_id')->toArray();
            $studentIds = Assign::where('plan_id', $plan->id)->where(function($q){
                $q->where('attendance', 1)->orWhereNull('attendance');
            })->pluck('student_id')->toArray();
            
            // compare and get the missing stuedents
            $missingStudents = array_diff($studentIds, $submittedStudents);
            
            $resultStudents = Result::whereIn('student_id', $missingStudents)->where('assessment_plan_id',$assessmentPlan->id)->where('plan_id', $plan->id)->pluck('student_id')->toArray();
            
            $missingStudents = array_diff($missingStudents, $resultStudents);
            
            sort($missingStudents);
            // if($resultStudents->count()>0) {
            //     foreach($resultStudents as $result) {
                    
            //         $student = Student::find($result->student_id);
            //         $resultSubmission = new ResultSubmissionByStaff();
            //         $resultSubmission->assessment_plan_id = $assessmentPlan->id;
            //         $resultSubmission->plan_id = $plan->id;
            //         $resultSubmission->student_id = $student->id;
            //         $resultSubmission->student_course_relation_id = $student->crel->id;
            //         $resultSubmission->grade_id = $result->grade->id;
            //         $resultSubmission->is_student_matched = 1;
            //         $resultSubmission->is_excel_missing = 1;
            //         $resultSubmission->is_it_final = 1;
            //         $resultSubmission->module_creation_id = $plan->module_creation_id;
            //         $resultSubmission->module_code = $plan->creations->code;
            //         $resultSubmission->upload_user_type = 'staff';
            //         $resultSubmission->created_by = Auth::id();
            //         $resultSubmission->save();
                
            //     }
            // } else 
            if(count($missingStudents) > 0) {
                // add those missing student to the result submission table
                foreach($missingStudents as $studentId) {
                    
                        $student = Student::find($studentId);
                        $resultSubmission = new ResultSubmissionByStaff();
                        $resultSubmission->assessment_plan_id = $assessmentPlan->id;
                        $resultSubmission->plan_id = $plan->id;
                        $resultSubmission->student_id = $studentId;
                        $resultSubmission->student_course_relation_id = $student->crel->id;
                        $resultSubmission->grade_id = Grade::where('code', 'A')->first()->id;
                        $resultSubmission->is_student_matched = 1;
                        $resultSubmission->is_excel_missing = 1;
                        $resultSubmission->is_it_final = 1;
                        $resultSubmission->module_creation_id = $plan->module_creation_id;
                        $resultSubmission->module_code = $plan->creations->code;
                        $resultSubmission->upload_user_type = 'staff';
                        $resultSubmission->created_by = Auth::id();
                        $resultSubmission->save();
                    
                }
            }
            return response()->json(['message' => 'Document successfully uploaded.'], 200);
        }

    }
    public function sampleDownload(Plan $plan)
    {
        return Excel::download(new ResultSubmissionSampleDownload($plan), 'sample_submission_list.xlsx');
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
            Excel::import(new ResultSubmissionByStaffImport($courseMoudleBaseAssessment, $plan), $document);
            $assessmentPlan = AssessmentPlan::where('course_module_base_assesment_id',$courseMoudleBaseAssessment)->where('plan_id',$plan->id)->orderBy('id','DESC')->get()->first();
        
            $submittedStudents = ResultSubmissionByStaff::where('assessment_plan_id', $assessmentPlan->id)->where('plan_id', $plan->id)->pluck('student_id')->toArray();

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
                    $resultSubmission = new ResultSubmissionByStaff();
                    $resultSubmission->assessment_plan_id = $assessmentPlan->id;
                    $resultSubmission->plan_id = $plan->id;
                    $resultSubmission->student_id = $studentId;
                    $resultSubmission->student_course_relation_id = $student->crel->id;
                    $resultSubmission->grade_id = Grade::where('code', 'A')->first()->id;
                    $resultSubmission->is_student_matched = 1;
                    $resultSubmission->is_excel_missing = 1;
                    $resultSubmission->module_creation_id = $plan->module_creation_id;
                    $resultSubmission->module_code = $plan->creations->code;
                    $resultSubmission->upload_user_type = 'staff';
                    $resultSubmission->created_by = Auth::id();
                    $resultSubmission->save();
                }
            }
            return response()->json(['message' => 'Document successfully uploaded.'], 200);
        }

    }


    public function finalSubmission(Request $request , Plan $plan) {
        $ids = $request->ids;
        $plan_id = $plan->id;
        $assigns= Assign::where('plan_id', $plan_id)->get()->pluck('student_id')->toArray();
         foreach($ids as $id){
            if($id!=""){
                $resultSubmission = ResultSubmissionByStaff::find($id);
                
                if(!in_array($resultSubmission->student_id, $assigns)){
                    
                    $resultSubmission->is_it_final = 0;
                } else {
                    $resultSubmission->is_it_final = 1;
                    AssessmentPlan::where('id', $resultSubmission->assessment_plan_id)->update(['is_it_final' => 1,'upload_user_type' => 'staff']);
                }
                $resultSubmission->save();
            }
        }

        return response()->json(['message' => 'Final submission successfully done.'], 200);
    }
}
