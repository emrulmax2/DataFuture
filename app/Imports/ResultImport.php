<?php

namespace App\Imports;

use App\Models\AssessmentPlan;
use App\Models\Assign;
use App\Models\Grade;
use App\Models\Plan;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;
use App\Models\Result;

class ResultImport implements ToModel, WithHeadingRow
{
    protected $assessmentPlan;
    
    public function __construct($assessmentPlan)
    {
        //array works
        $this->assessmentPlan = $assessmentPlan;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $publish_at =  date("Y-m-d H:i:s", strtotime($row['published_date']));
        $assessmentPlan = AssessmentPlan::find($this->assessmentPlan);
        $plan = Plan::with(['course','group','creations','attenTerm'])->where('id',$assessmentPlan->plan_id)->get()->first();
        $studentAssigns = Assign::with('student')->where('plan_id', $plan->id)->get();
        $studentListCount = $studentAssigns->count();
        $student = Student::where('registration_no',$row['registration_no'])->get()->first();
        $grade = Grade::where('code',$row['grade'])->orWhere('name',$row['grade'])->get()->first();
        //$resultFound = Result::where("assessment_plan_id",$assessmentPlan->id)->where("student_id",$student->id)->get()->first();
        // if($resultFound) {
        //     $publish_at =  gmdate("Y-m-d H:i:s", strtotime($assessmentPlan->resubmission_at));
        // }
        return new Result([
            'assessment_plan_id' => $assessmentPlan->id,
            'published_at' => $publish_at,
            'plan_id' => $plan->id,
            'student_id' => $student->id,
            'grade_id' => $grade->id,
            'created_by' => Auth::id()
        ]);
    }
}
