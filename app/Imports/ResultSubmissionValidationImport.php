<?php

namespace App\Imports;

use App\Models\Assign;
use App\Models\Student;
use App\Models\StudentUser;
use App\Models\Grade;
use App\Models\Plan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ResultSubmissionValidationImport implements ToCollection, WithHeadingRow
{
    protected $assessmentPlan;
    protected $plan;

    protected $studentErrorFound = [];
    public $errorMessage = "Error(s) in List Found";
    public function __construct($assessmentPlan, Plan $plan)
    {
        //array works
        $this->assessmentPlan = $assessmentPlan;
        $this->plan = $plan;
    }

    public function collection(Collection $rows)
    {
        $i =0;
        $studentErrorFound = [];
        
        foreach ($rows as $row) {
            // Assuming the row is an array with keys matching your database columns
            $studentUserId = StudentUser::where('email', $row['email'])->first();
            if($row['email']!=null) {
                if(isset($studentUserId) && !empty($studentUserId)) {
                    $student = Student::where('student_user_id', $studentUserId->id)->first();
                    $grade = Grade::where('code', $row['grade'])
                                ->orWhere('name', $row['grade'])
                                ->orWhere('turnitin_grade', $row['grade'])
                                ->first();
                                
                    $studentAssigns = Assign::with('student')
                                            ->where('plan_id', $this->plan->id)
                                            ->get()
                                            ->pluck('student.id')
                                            ->toArray();

                    $studentMatched = in_array($student->id, $studentAssigns);
                    
                    // Process the row data as needed
                    
                    if ($studentMatched) {
                        $assignData = Assign::where('student_id', $student->id)
                            ->where('plan_id', $this->plan->id)->get()->first();
                        
                        if($assignData->attendance === 0) {
                            $this->errorMessage = "The below Students are inactive in this term, Please remove the students from the list and try again.";
                            $this->studentErrorFound[$i] = $row['first_name'] . " " . $row['last_name']. " - ".$row['email']; 
                            $i++;
                        }
                        // Your logic here
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