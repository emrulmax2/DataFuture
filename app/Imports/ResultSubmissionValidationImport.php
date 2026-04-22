<?php

namespace App\Imports;

use App\Models\AssessmentPlan;
use App\Models\Assign;
use App\Models\Student;
use App\Models\StudentUser;
use App\Models\Grade;
use App\Models\Plan;
use App\Models\Result;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ResultSubmissionValidationImport implements ToCollection, WithHeadingRow
{
    protected $course_moudule_based_assessment;
    protected $plan;

    protected $studentErrorFound = [];
    public $errorMessage = "Error(s) in List Found";
    public function __construct($course_moudule_based_assessment, Plan $plan)
    {
        //array works
        $this->course_moudule_based_assessment = $course_moudule_based_assessment;
        $this->plan = $plan;
    }

    public function collection(Collection $rows)
    {
        $i =0;
        $studentErrorFound = [];
        $studentAssigns = Assign::with('student')
                                            ->where('plan_id', $this->plan->id)
                                            ->get()
                                            ->pluck('student_id')
                                            ->toArray();
          //print_r( $studentAssigns);                                  
        foreach ($rows as $row) {
            // Assuming the row is an array with keys matching your database columns
            $studentUserId = StudentUser::where('email', $row['email'])->first();
           
            if($row['email']!=null) {
                if(isset($studentUserId) && !empty($studentUserId)) {
                    $studentArray = Student::where('student_user_id', $studentUserId->id)->get()
                                            ->pluck('id')
                                            ->toArray();
                    
                    // check if any of the student ids from the student array match with the student ids from the assigns for the given plan
                    $matchedStudentIds = array_values(array_intersect($studentArray, $studentAssigns));
                    $studentMatched = !empty($matchedStudentIds);
                    $matchedStudentId = $studentMatched ? $matchedStudentIds[0] : null;
                    
                    // Process the row data as needed
                    $grade = Grade::where('code',$row['grade'])->orWhere('turnitin_grade',$row['grade'])->orWhere('name',$row['grade'])->get()->first();
                    if($grade == null && $row['grade'] != null) {
                        $this->errorMessage = "The following students have invalid grade. Please correct the grade and try again.";
                        $this->studentErrorFound[$i] = $row['first_name'] . " " . $row['last_name']. " - ".$row['email']." - Invalid Grade: ".$row['grade']; 
                        $i++;
                    }
                    if ($studentMatched) {


                        $student = Student::find($matchedStudentId);
                        $assignData = Assign::where('student_id', $student->id)
                            ->where('plan_id', $this->plan->id)->get()->first();

                          
                        $assessmentPlan = AssessmentPlan::where('course_module_base_assesment_id', $this->course_moudule_based_assessment)
                            ->where('plan_id', $this->plan->id)
                            ->where('upload_user_type','staff')
                            ->orderByDesc('id')
                            ->first();
                            
                        if(isset($assessmentPlan) && !empty($assessmentPlan)):
                            
                            $foundResult = Result::where('student_id', $student->id)
                                ->where('plan_id', $this->plan->id)
                                ->where('assessment_plan_id', $assessmentPlan->id)
                                ->where(function ($query) {
                                    $query->whereBetween('grade_id', [4, 6]);
                                    $query->orWhere('grade_id', 10);
                                    
                                })->first();
                            else:
                            $foundResult = null;
                        endif;
                        
                        // Your logic here
                        if(isset($assignData) && $assignData->attendance === 0) {
                            $this->errorMessage = "The following students are inactive in this term, Please remove the students from the list and try again.";
                            $this->studentErrorFound[$i] = $row['first_name'] . " " . $row['last_name']. " - ".$row['email']; 
                            $i++;
                        }elseif(isset($foundResult->grade_id) && !empty($foundResult->grade_id)) {
                            
                            $this->errorMessage = "The following students already have published results [e.g., P, M, D] for this assessment or withold [W] result. Please remove these students from the list and try again."; 
                            $this->studentErrorFound[$i] = $row['first_name'] . " " . $row['last_name']. " - ".$row['email']; 
                            $i++;
                        }
                    } else {
                        
                        $this->studentErrorFound[$i] = $row['first_name'] . " " . $row['last_name']. " - ".$row['email']; 
                        $i++;
                    }
                }else {
                    
                    $this->studentErrorFound[$i] = $row['first_name'] . " " . $row['last_name']. " - ".$row['email']; 
                    $i++;
                }
            }
        }
        return $studentErrorFound;
    }
    public function getStudentErrorFound()
    {
        return $this->studentErrorFound;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}