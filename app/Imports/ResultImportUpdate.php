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

class ResultImportUpdate implements ToModel, WithHeadingRow
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
        $resultFound = Result::with('grade')
                        ->where("assessment_plan_id",$assessmentPlan->id)
                        ->where("student_id",$student->id)
                        ->latest()
                        ->get()
                        ->first();
        if($resultFound) {
             if($grade->id !=$resultFound->grade->id) {
                $resultData = Result::find($resultFound->id);
                $resultData->grade_id=$grade->id;
                $resultData->updated_by=Auth::id();
                $resultData->published_at=$publish_at;
                $resultData->save();

                return $resultData;
             }
         
        }
    }
}
