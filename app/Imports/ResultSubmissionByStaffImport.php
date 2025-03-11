<?php

namespace App\Imports;

use App\Models\Assessment;
use App\Models\AssessmentPlan;
use App\Models\Assign;
use App\Models\Grade;
use App\Models\Plan;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;
use App\Models\Result;
use App\Models\ResultSubmission;
use App\Models\ResultSubmissionByStaff;
use App\Models\StudentUser;
use Carbon\Carbon;

class ResultSubmissionByStaffImport implements ToModel, WithHeadingRow
{
    protected $courseMoudleBaseAssessment;
    protected $plan;
    
    public function __construct($courseMoudleBaseAssessment,Plan $plan)
    {
        //array works
        $this->courseMoudleBaseAssessment = $courseMoudleBaseAssessment;
        $this->plan = $plan;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $publish_at = isset($row['publish_date']) ? Carbon::parse($row['publish_date'])->format('Y-m-d H:i:s') : null;
        
        $assessmentPlan = AssessmentPlan::where('course_module_base_assesment_id',$this->courseMoudleBaseAssessment)->where('plan_id',$this->plan->id)->where('upload_user_type','staff')->whereNull('is_it_final')->get()->first();
        if(!$assessmentPlan){

            $assessmentPlan = AssessmentPlan::create([
                'plan_id' => $this->plan->id,
                'course_module_base_assesment_id' => $this->courseMoudleBaseAssessment,
                'upload_user_type' => 'staff',
                'created_by' => Auth::id(),
            ]);

        }
        $studentAssigns = Assign::with('student')->where('plan_id', $this->plan->id)->get()->pluck('student.id')->toArray();

        $studentUserId = StudentUser::where('email', $row["email"])->get()->first();
            if($studentUserId!=null){
                $student = Student::where('student_user_id',$studentUserId->id)->get()->first();
                if($row['grade'] === '' && $row['paper_id'] === ""){
                    $row['grade'] = 'A';
                } else if($row['paper_id'] != "" && $row['grade'] == 8){
                    $row['grade'] = 'S';
                }else if($row['paper_id'] != "" && $row['grade'] === 0){
                    $row['grade'] = 'R';
                }
                $grade = Grade::where('code',$row['grade'])->orWhere('turnitin_grade',$row['grade'])->orWhere('name',$row['grade'])->get()->first();
                
                
                return new ResultSubmissionByStaff([
                    'assessment_plan_id' => $assessmentPlan->id,
                    'published_at' => $publish_at,
                    'created_at' => Carbon::now(),    
                    'plan_id' => $this->plan->id,
                    'student_course_relation_id' => $student->crel->id,
                    'student_id' => $student->id,
                    'grade_id' => $grade->id,
                    'paper_id' => !empty($row['paper_id']) ? $row['paper_id'] : null,
                    'is_student_matched' => 1,
                    'is_it_final' => 1,
                    'module_creation_id' => $this->plan->module_creation_id,
                    'module_code' => $row['module_code'] ?? $this->plan->creations->code,
                    'upload_user_type' => 'staff',
                    'created_by' => Auth::id()
                ]);
            }
        
    }
}
