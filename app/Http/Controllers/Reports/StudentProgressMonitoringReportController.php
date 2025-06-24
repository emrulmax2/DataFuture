<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Exports\CustomArrayCollectionExport;
use App\Exports\Reports\StudentDataReportBySelectionExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicCriteria;
use App\Models\AcademicYear;
use App\Models\Agent;
use App\Models\AgentReferralCode;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceCriteria;
use App\Models\Course;
use App\Models\CourseCreationVenue;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Group;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\Result;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\StudentProposedCourse;
use App\Models\TermDeclaration;
use Barryvdh\Debugbar\Facades\Debugbar;
use Carbon\Carbon;
use DebugBar\DebugBar as DebugBarDebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Expr\Cast\Unset_;

ini_set('memory_limit', '512M'); // Increase memory limit
ini_set('max_execution_time', '300'); // Increase execution time limit to 300 seconds

class StudentProgressMonitoringReportController extends Controller
{
    public function index(){
        $semesters = Cache::get('semesters', function () {
            $semesters = Semester::all()->sortByDesc("name");
            $semesterList = $semesters->pluck('id')->unique()->toArray();
            $semesterDataChecked = StudentProposedCourse::whereIn('semester_id',$semesterList)->pluck('semester_id')->unique()->toArray();
            $semesterData = Semester::whereIn('id', $semesterDataChecked)->get()->sortByDesc("name");
            return $semesterData;
        });

        $courses = Cache::get('courses', function () {
            return Course::all();
        });
        $statuses = Cache::get('statuses', function () {
            return Status::where('type', 'Student')->get();
        });
        
        return view('pages.reports.progress.index', [
            'title' => 'Student Progress Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => '/reports'],
                ['label' => 'Student Progress Reports', 'href' => 'javascript:void(0);']
            ],
            'semesters' => $semesters,
            'courses' => $courses,
            'allStatuses' => $statuses,
            'academicYear' => AcademicYear::all()->sortByDesc('from_date'),
            'terms' => TermDeclaration::all()->sortByDesc('id'),
            'groups' => Group::all(),
        ]);
    }


    public function totalCount(Request $request) {

        //parse_str($request->form_data, $form);
        $seratchCriteria = [];
        $groupParams = isset($request->group) && !empty($request->group) ? $request->group : [];

        $sorts = [];
        
        $Query = Student::orderBy('id','desc');
        $itemSelected = false;
        $term_declaration_ids = [];
        foreach($groupParams as $field => $value):
            $$field = (isset($value) && !empty($value) ? $value : '');

            if($$field!='') {
                $itemSelected = true;
                //$seratchCriteria[] = $field;
                

                if(isset($academic_year))
                    $seratchCriteria[$field] = AcademicYear::where('id',$academic_year)->get()->first()->name;
                if(isset($attendance_semester)) {
                    $seratchCriteria[$field] = TermDeclaration::whereIn('id',$attendance_semester)->pluck('name')->toArray();
                    $term_declaration_ids = $attendance_semester;
                }

                if(isset($course))
                    $seratchCriteria[$field] = Course::where('id',$course)->get()->first()->name;
                if(isset($group))
                    $seratchCriteria[$field] = Group::where('id',$group)->get()->first()->name;
                if(isset($intake_semester))
                    $seratchCriteria[$field] = Semester::where('id',$intake_semester)->get()->first()->name;
                if(isset($group_student_status))
                    $seratchCriteria[$field] = Status::where('id',$group_student_status)->get()->first()->name;
                if(isset($evening_weekend))
                    $seratchCriteria[$field] = ($evening_weekend==0) ? "Evening" : "Weekend";
                if(isset($certificate_claimed))
                    $seratchCriteria[$field] = ($certificate_claimed=="Yes") ? "Yes" : "No";
            }
        endforeach;
        if($itemSelected==true) {
            $studentsIds = [];
                $myRequest = new Request();

                $myRequest->setMethod('POST');

                if(isset($academic_year))
                    $myRequest->request->add(['academic_years' => $academic_year]);
                else
                    $myRequest->request->add(['academic_years' => '']);
                
                if(isset($attendance_semester))
                $myRequest->request->add(['term_declaration_ids' => $attendance_semester]);

                if(isset($course))
                    $myRequest->request->add(['courses' => $course]);
                if(isset($group))
                    $myRequest->request->add(['groups' => $group]);
                if(isset($intake_semester))
                    $myRequest->request->add(['intake_semesters' => $intake_semester]);
                if(isset($group_student_status))
                    $myRequest->request->add(['group_student_statuses' => $group_student_status]);
                if(isset($student_type))
                    $myRequest->request->add(['student_types' => $student_type]);
                if(isset($evening_weekend))
                    $myRequest->request->add(['evening_weekends' => $evening_weekend]);

                if(isset($certificate_claimed))
                    $myRequest->request->add(['certificate_claimed' => $certificate_claimed]);

                $studentsIds = $this->callTheStudentListForGroup($myRequest);
                
            if(!empty($studentsIds)): 
                $Query->whereIn('id', $studentsIds); 
            else:
                $Query->whereIn('id', [0]); 
            endif;
            
            $total_rows = $Query->count();

            $Query = $Query->get();

    
            return response()->json(['all_rows' => $total_rows, 'student_ids'=>$studentsIds,'search_criteria'=>$seratchCriteria, 'term' =>isset($term_declaration_ids) ? $term_declaration_ids : "",   "certificate_claimed"=>isset($certificate_claimed)?$certificate_claimed:null],200);

        } else {
            return response()->json(['all_rows' => 0, 'student_ids'=>[]],302);
        }

    }

    protected function callTheStudentListForGroup(Request $request) {
        

        $academic_years = $request->academic_years;
        $term_declaration_ids = $request->term_declaration_ids;
        $courses = $request->courses;
        $groups = $request->groups;
        $intake_semesters = $request->intake_semesters;
        $group_student_statuses = $request->group_student_statuses;
        $student_types = $request->student_types;
        $evening_weekends = $request->evening_weekends;
        $certificate_claimed = $request->certificate_claimed;
        $studentIds = [];

        $QueryInner = StudentCourseRelation::with('activeCreation','student.awarded');
        $QueryInner->where('active','=',1);
        
        if(!empty($evening_weekends) && ($evening_weekends==0 || $evening_weekends==1))
            $QueryInner->where('full_time',$evening_weekends);
        if(!empty($academic_years) && count($academic_years)>0)
            $QueryInner->where('academic_year_id',$academic_years);
        

            $studentIds =  $QueryInner->whereHas('activeCreation', function($q) use($intake_semesters,$courses){
                    if(!empty($intake_semesters))
                        $q->whereIn('semester_id', $intake_semesters);
                    if(!empty($courses))
                        $q->whereIn('course_id', $courses);
            })->pluck('student_id')->unique()->toArray();

            $studentsListByEveningSemesterAndCourse = $studentIds;

        if(!empty($term_declaration_ids) && count($term_declaration_ids)>0) {

            if(!empty($groups)) {
                $groups = Group::whereIn('name',$groups)->pluck('id')->unique()->toArray();
            }
            $innerQuery = Plan::whereIn('term_declaration_id', $term_declaration_ids);

                if(!empty($groups)) {
                    $innerQuery->whereIn('group_id', $groups);
                }

            $planList = $innerQuery->whereHas('course', function($q) use($courses,$academic_years){
                if(!empty($courses))
                $q->whereIn('course_id', $courses);
                if(!empty($academic_years))
                $q->whereIn('academic_year_id', $academic_years);
                

            })->pluck('id')->unique()->toArray();

            $studentsListByTerm = Assign::whereIn("plan_id",$planList)->pluck('student_id')->unique()->toArray();
            $studentIds = [];
            foreach($studentsListByEveningSemesterAndCourse as $intakeStudent):

            if(in_array($intakeStudent,$studentsListByTerm)) {
                $studentIds[] = $intakeStudent;
            }
            endforeach;
            
        }

        if(!empty($certificate_claimed) && $certificate_claimed=="Yes") {
            
            $studentIds = Student::whereIn('id',$studentIds)->whereHas('awarded', function($q) use($certificate_claimed){
                $q->where('certificate_requested',$certificate_claimed);
            })->pluck('id')->unique()->toArray();
        } else if(!empty($certificate_claimed) && $certificate_claimed=="No") {
            $studentIds = Student::whereIn('id',$studentIds)->whereHas('awarded', function($q) use($certificate_claimed){
                $q->where('certificate_requested',$certificate_claimed);
            })->pluck('id')->unique()->toArray();
        }
        //this part will use both term and intake and open
        if(!empty($student_types) && count($student_types)>0) {

            $innerQuery = Student::with('crel');
            if(!empty($studentIds)) {
                $innerQuery->whereIn('id',$studentIds);
            }
            $studentsListByStudentType = $innerQuery->whereHas('crel', function($q) use($student_types){
                $q->whereIn('type', $student_types);
            })->pluck('id')->unique()->toArray();

            $studentIds = $studentsListByStudentType;

        }
        if(!empty($group_student_statuses) && count($group_student_statuses)>0) {

                $innerQuery = Student::whereIn('status_id',$group_student_statuses);
                if(!empty($studentIds)) {
                    $innerQuery->whereIn('id',$studentIds);
                }
                $studentsListByStatus = $innerQuery->pluck('id')->unique()->toArray();

                $studentIds = $studentsListByStatus;
                
        }
            //endof the part

        sort($studentIds);

        return $studentIds;
    }


    public function excelDownload(Request $request)
    {
       
        $searchedCriteria = json_decode($request->searchedCriteria, true);
        $selectedTerm = json_decode($request->term, true);
        
        $searchedCriteria = $searchedCriteria;
        
        $theCollection = [];
        $theCollection[1][0] = 'Search Criteria';
        
        $theCollection[1][1] =  $searchedCriteria;

        $theCollection[2][0] = 'Report created date time';
        $theCollection[2][1] = Carbon::now()->format('jS M Y h:i a');
        
        $theCollection[3][0] = 'Created by';
        $theCollection[3][1] = Employee::where('user_id',auth()->user()->id)->get()->first()->full_name;

        $theCollection[4] = [];

        $headers = [];
        $theCollection[5][0] = 'LCC ID';
        $theCollection[5][1] = 'Status';
        $theCollection[5][2] = 'Intake Semester';
        $theCollection[5][3] = 'Course';
        $theCollection[5][4] = 'Attendance Semester';
        $theCollection[5][5] = 'Group';
        $theCollection[5][6] = 'Module Serial';			
        $theCollection[5][7] = 'Module';
        $theCollection[5][8] = 'Unit Value';
        $theCollection[5][9] = 'Credit Value';
        $theCollection[5][10] = 'Module Status';
        
        $theCollection[5][11] = 'Level 4 Unit Value';
        $theCollection[5][12] = 'Level 4 Credit Value';
        $theCollection[5][13] = 'Level 5 Unit Value';
        $theCollection[5][14] = 'Level 5 Credit Value';

        $theCollection[5][15] = 'Tutor';
        $theCollection[5][16] = 'Results';
        $theCollection[5][17] = 'Attempts';
        $theCollection[5][18] = 'Complete';
        $theCollection[5][19] = 'Incomplete';
        $theCollection[5][20] = 'Certificate Claimed';
        $theCollection[5][21] = 'Certificate Claimed Date';
        $theCollection[5][22] = 'Certificate Requested By';
        $theCollection[5][23] = 'Certificate Received';
        $theCollection[5][24] = 'Certificate Received Date';
        $theCollection[5][25] = 'Certificate Released';
        $theCollection[5][26] = 'Certificate Released Date';
        $theCollection[5][27] = 'Awarded Date';
        $theCollection[5][28] = 'Overall Result';

        $studentIds = explode(",",$request->studentIds);
        
        $dataSet = [];
        $termStatus = [];
        $academicCriteriaList = AcademicCriteria::orderBy('point','desc')->get();
        $GradeListForCount = $academicCriteriaList->pluck('code')->toArray();
        $dataCount = 6;
        foreach($studentIds as $studentId):
            $dataSet[$studentId]['result'] = [];
            $student = Student::with('status','activeCR.course','activeCR.propose.semester','awarded','awarded.qual','awarded.requested.employee')->where('id',$studentId)->get()->first();
            $planList = Assign::where('student_id',$studentId)->get()->unique()->pluck('plan_id')->toArray();

            $results = Result::with(['plan' => function($query) {
                $query->orderBy('term_declaration_id','DESC'); 
            }],'plan.creations.module','grade','plan.creations.module','plan.tutor.employee','plan.group','plan.attenTerm')
            ->whereIn('plan_id',$planList)
            ->where('student_id',$studentId)
            ->where('published_at','<',Carbon::now())->orderBy('published_at','DESC')->get();

            if(isset($selectedTerm) && !empty($selectedTerm)) {
                
                $term_declaration_ids = $selectedTerm;

                //dd($term_declaration_ids);
            } else {
                $term_declaration_ids = $results->pluck('plan.term_declaration_id')->unique()->toArray();
            }
            

            $resultSets = [];

            if(isset($results))
            foreach ($results as $result) {

                $gradeFound = $result->grade->code;
                $termId = $result->plan->term_declaration_id;

                $moduleStatus = $result->plan->creations->status ? $result->plan->creations->status : $result->plan->creations->module->status;
                $moduleName = $result->plan->creations->module->name;
                $unitValue = $result->plan->creations->module->unit_value;
                $creditValue = $result->plan->creations->module->credit_value;
                $termName = isset($result->plan->attenTerm) ? $result->plan->attenTerm->name : "";
                $groupName = isset($result->plan->group) ? $result->plan->group->name : "";
                $tutorEmployee = isset($result->plan->tutor->employee) ? $result->plan->tutor->employee->full_name : "";
                
                
                if(in_array($gradeFound,$GradeListForCount)) {
                    $resultSets[$termId][$moduleName]['results'] = $gradeFound;
                    
                }
                

                $resultSets[$termId][$moduleName]['attendance_term'] = $termName;
                $resultSets[$termId][$moduleName]['group'] = $groupName;
                $resultSets[$termId][$moduleName]['module'] = $moduleName;
                $resultSets[$termId][$moduleName]['unit_value'] = $unitValue;
                $resultSets[$termId][$moduleName]['credit_value'] = $creditValue;
                $resultSets[$termId][$moduleName]['module_status'] = strtoupper($moduleStatus[0]);
                $resultSets[$termId][$moduleName]['tutor'] = $tutorEmployee;

                
                if(!isset($resultSets[$termId][$moduleName]['attempts'])) {
                    $resultSets[$termId][$moduleName]['attempts'] = 1;
                } else {
                    $resultSets[$termId][$moduleName]['attempts']++;
                }
            }
           $inCompleteCount = 0;
           $CompleteCount = 0;
           $totalLevel4CreditValue = 0;
           $totalLevel5CreditValue = 0;
           $totalLevel4UnitValue = 0;
           $totalLevel5UnitValue = 0;
           $totalCreditValue = 0;
           $totalModuleCount = 0;
           foreach($term_declaration_ids as $term):
                $i =1;
                if(isset($resultSets[$term])):
                    $termBaseSingleInCompleteCount[$term] = 0;

                    $termBaseSingleCreditValueCount[$term] = 0;

                    $termBaseSingleCompleteCount[$term] = 0;

                    foreach($resultSets[$term] as $module => $result):
                        $compeleteFound = false;
                        if(!isset($result['results']) || $result['results']=="") {
                            ++$inCompleteCount;
                            ++$termBaseSingleInCompleteCount[$term];
                        }else{
                            ++$CompleteCount;
                            ++$termBaseSingleCompleteCount[$term];
                            $compeleteFound = true;
                            $totalCreditValue += $result['credit_value'];

                            if($result['unit_value'] == 4)
                                $totalLevel4CreditValue += $result['credit_value'];
                            if($result['unit_value'] == 5)
                                $totalLevel5CreditValue += $result['credit_value'];
                            
                            if($result['unit_value'] == 4)
                                $totalLevel4UnitValue += 1;
                            if($result['unit_value'] == 5)
                                $totalLevel5UnitValue += 1;
                        }

                        $totalModuleCount += 1;
                        $theCollection[$dataCount][0] = "";
                        $theCollection[$dataCount][1] = "";
                        $theCollection[$dataCount][2] = "";
                        $theCollection[$dataCount][3] = "";
                        $theCollection[$dataCount][4] = ($i>1) ? "":$result['attendance_term'];
                        $theCollection[$dataCount][5] = $result['group'];
                        $theCollection[$dataCount][6] = $i++;
                        $theCollection[$dataCount][7] = $result['module'];
                        $theCollection[$dataCount][8] = $result['unit_value'];
                        $theCollection[$dataCount][9] =  $compeleteFound ? $result['credit_value'] : "";    

                        $theCollection[$dataCount][10] =  $result['module_status'];

                        $theCollection[$dataCount][11] = '';
                        $theCollection[$dataCount][12] = '';
                        $theCollection[$dataCount][13] = '';
                        $theCollection[$dataCount][14] = '';

                        $theCollection[$dataCount][15] = $result['tutor'];
                        $theCollection[$dataCount][16] = isset($result['results']) ? $result['results'] : "";
                        $theCollection[$dataCount][17] = $result['attempts'];
                        $theCollection[$dataCount][18] = "";
                        $theCollection[$dataCount][19] = "";
                        $theCollection[$dataCount][20] = "";
                        $theCollection[$dataCount][21] = "";
                        $theCollection[$dataCount][22] = "";
                        $theCollection[$dataCount][23] = "";
                        $theCollection[$dataCount][24] = "";
                        $theCollection[$dataCount][25] = "";
                        $theCollection[$dataCount][26] = "";
                        $theCollection[$dataCount][27] = "";
                        $theCollection[$dataCount][28] = "";
                        $dataCount++;
                    endforeach;
                endif;
                if(count($term_declaration_ids)>1) {
                    $theCollection[$dataCount][0] = "";
                    $theCollection[$dataCount][1] = "";
                    $theCollection[$dataCount][2] = "";
                    $theCollection[$dataCount][3] = "";
                    $theCollection[$dataCount][4] = "";
                    $theCollection[$dataCount][5] = "";
                    $theCollection[$dataCount][6] = "";
                    $theCollection[$dataCount][7] = "";
                    $theCollection[$dataCount][8] = "";
                    $theCollection[$dataCount][9] = "";
                    $theCollection[$dataCount][10] = "";

                    
                    $theCollection[$dataCount][11] = '';
                    $theCollection[$dataCount][12] = '';
                    $theCollection[$dataCount][13] = '';
                    $theCollection[$dataCount][14] = '';

                    $theCollection[$dataCount][15] = "";
                    $theCollection[$dataCount][16] = "";
                    $theCollection[$dataCount][17] = "";
                    $theCollection[$dataCount][18] = $termBaseSingleCompleteCount[$term];
                    $theCollection[$dataCount][19] = $termBaseSingleInCompleteCount[$term];
                    $theCollection[$dataCount][20] = "";
                    $theCollection[$dataCount][21] = "";
                    $theCollection[$dataCount][22] = "";
                    $theCollection[$dataCount][23] = "";
                    $theCollection[$dataCount][24] = "";
                    $theCollection[$dataCount][25] = "";
                    $theCollection[$dataCount][26] = "";
                    $theCollection[$dataCount][27] = "";
                    $theCollection[$dataCount][28] = "";
                    $dataCount++;
                }
            endforeach;
                $theCollection[$dataCount][0] = isset($student) ?$student->registration_no : "";
                $theCollection[$dataCount][1] = isset($student->status) ? $student->status->name : "";
                $theCollection[$dataCount][2] = isset($student->activeCR->propose->semester) ? $student->activeCR->propose->semester->name : "";
                $theCollection[$dataCount][3] = isset($student->activeCR->course) ?  $student->activeCR->course->name : "";
                $theCollection[$dataCount][4] = "";
                $theCollection[$dataCount][5] = "";
                $theCollection[$dataCount][6] = "";
                $theCollection[$dataCount][7] = $totalModuleCount;
                $theCollection[$dataCount][8] = "";
                $theCollection[$dataCount][9] = $totalCreditValue;
                $theCollection[$dataCount][10] = "";

                $theCollection[$dataCount][11] = $totalLevel4UnitValue;
                $theCollection[$dataCount][12] = $totalLevel4CreditValue;
                $theCollection[$dataCount][13] = $totalLevel5UnitValue;
                $theCollection[$dataCount][14] = $totalLevel5CreditValue;

                $theCollection[$dataCount][15] = "";
                $theCollection[$dataCount][16] = "";
                $theCollection[$dataCount][17] = "";
                $theCollection[$dataCount][18] = $CompleteCount;
                $theCollection[$dataCount][19] = $inCompleteCount;
                $theCollection[$dataCount][20] = isset($student->awarded) ? $student->awarded->certificate_requested : "";
                $theCollection[$dataCount][21] = isset($student->awarded->date_of_certificate_requested) ? $student->awarded->date_of_certificate_requested : "";
                $theCollection[$dataCount][22] = isset($student->awarded->requested->employee) ? $student->awarded->requested->employee->full_name : "";
                $theCollection[$dataCount][23] = isset($student->awarded->certificate_received) ? $student->awarded->certificate_received : "";
                $theCollection[$dataCount][24] = isset($student->awarded->date_of_certificate_received) ? $student->awarded->date_of_certificate_received : "";
                $theCollection[$dataCount][25] = isset($student->awarded->certificate_released) ? $student->awarded->certificate_released : "";
                $theCollection[$dataCount][26] = isset($student->awarded->date_of_certificate_released) ? $student->awarded->date_of_certificate_released : "";
                $theCollection[$dataCount][27] = isset($student->awarded->date_of_award) ? $student->awarded->date_of_award : "";
                $theCollection[$dataCount][28] = isset($student->awarded->qual_award_result_id) ? $student->awarded->qual->name : "";
                $dataCount++;		
        endforeach;

        return Excel::download(new ArrayCollectionExport($theCollection), 'student_progress_monitor_report.xlsx');
                
        //return Excel::download(new StudentDataReportBySelectionExport($returnData), 'student_data_report.xlsx');
    }

    /**
     * View the student progress monitoring report as an View file.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function listProgressReport(Request $request)
    {
        $headers = [];
       
        $searchedCriteria = json_decode($request->searchedCriteria, true);
        $selectedTerm = json_decode($request->term, true);
        
        $searchedCriteria = $searchedCriteria;
        
        $theCollection = [];



        $studentIds = explode(",",$request->studentIds);
        
        $dataSet = [];
        $termStatus = [];
        $academicCriteriaList = AcademicCriteria::orderBy('point','desc')->get();
        $GradeListForCount = $academicCriteriaList->pluck('code')->toArray();
       

        $dataSetByStudent = [];
        $resultSetsByStudent = [];
        foreach($studentIds as $studentId):
            $dataSet[$studentId]['result'] = [];
            $student = Student::with('status','activeCR.course','activeCR.propose.semester','awarded','awarded.qual','awarded.requested.employee')->where('id',$studentId)->get()->first();
            $planList = Assign::where('student_id',$studentId)->get()->unique()->pluck('plan_id')->toArray();

            $results = Result::with(['plan' => function($query) {
                $query->orderBy('term_declaration_id','DESC'); 
            }],'plan.creations.module','grade','plan.creations.module','plan.tutor.employee','plan.group','plan.attenTerm')
            ->whereIn('plan_id',$planList)
            ->where('student_id',$studentId)
            ->where('published_at','<',Carbon::now())->orderBy('published_at','DESC')->get();

            if(isset($selectedTerm) && !empty($selectedTerm)) {
                
                $term_declaration_ids = $selectedTerm;

                //dd($term_declaration_ids);
            } else {
                $term_declaration_ids = $results->pluck('plan.term_declaration_id')->unique()->toArray();
            }
            

            $resultSets = [];

            if(isset($results))
            foreach ($results as $result) {

                $gradeFound = $result->grade->code;
                $termId = $result->plan->term_declaration_id;

                $moduleStatus = $result->plan->creations->status ? $result->plan->creations->status : $result->plan->creations->module->status;
                $moduleName = $result->plan->creations->module->name;
                $unitValue = $result->plan->creations->module->unit_value;
                $creditValue = $result->plan->creations->module->credit_value;
                $termName = isset($result->plan->attenTerm) ? $result->plan->attenTerm->name : "";
                $groupName = isset($result->plan->group) ? $result->plan->group->name : "";
                $tutorEmployee = isset($result->plan->tutor->employee) ? $result->plan->tutor->employee->full_name : "";
                
                
                if(in_array($gradeFound,$GradeListForCount)) {
                    $resultSets[$termId][$moduleName]['results'] = $gradeFound;
                    
                }
                

                $resultSets[$termId][$moduleName]['attendance_term'] = $termName;
                $resultSets[$termId][$moduleName]['group'] = $groupName;
                $resultSets[$termId][$moduleName]['module'] = $moduleName;
                $resultSets[$termId][$moduleName]['unit_value'] = $unitValue;
                $resultSets[$termId][$moduleName]['credit_value'] = $creditValue;
                $resultSets[$termId][$moduleName]['module_status'] = strtoupper($moduleStatus[0]);
                $resultSets[$termId][$moduleName]['tutor'] = $tutorEmployee;

                
                if(!isset($resultSets[$termId][$moduleName]['attempts'])) {
                    $resultSets[$termId][$moduleName]['attempts'] = 1;
                } else {
                    $resultSets[$termId][$moduleName]['attempts']++;
                }
            }
           $inCompleteCount = 0;
           $CompleteCount = 0;
           $totalLevel4CreditValue = 0;
           $totalLevel5CreditValue = 0;
           $totalLevel4UnitValue = 0;
           $totalLevel5UnitValue = 0;
           $totalCreditValue = 0;
           $totalModuleCount = 0;
           $dataCount = 0;
           foreach($term_declaration_ids as $term):
            $theCollection = [];
                $i =1;
                if(isset($resultSets[$term])):
                    $termBaseSingleInCompleteCount[$term] = 0;

                    $termBaseSingleCreditValueCount[$term] = 0;

                    $termBaseSingleCompleteCount[$term] = 0;

                    foreach($resultSets[$term] as $module => $result):
                        $compeleteFound = false;
                        if(!isset($result['results']) || $result['results']=="") {
                            ++$inCompleteCount;
                            ++$termBaseSingleInCompleteCount[$term];
                        }else{
                            ++$CompleteCount;
                            ++$termBaseSingleCompleteCount[$term];
                            $compeleteFound = true;
                            $totalCreditValue += $result['credit_value'];

                            if($result['unit_value'] == 4)
                                $totalLevel4CreditValue += $result['credit_value'];
                            if($result['unit_value'] == 5)
                                $totalLevel5CreditValue += $result['credit_value'];
                            
                            if($result['unit_value'] == 4)
                                $totalLevel4UnitValue += 1;
                            if($result['unit_value'] == 5)
                                $totalLevel5UnitValue += 1;
                        }

                        $totalModuleCount += 1;

                        $theCollection[$dataCount][0] = ($i>1) ? "":$result['attendance_term'];
                        $theCollection[$dataCount][1] = $result['group'];
                        $theCollection[$dataCount][2] = $i++;
                        $theCollection[$dataCount][3] = $result['module'];
                        $theCollection[$dataCount][4] = $result['unit_value'];
                        $theCollection[$dataCount][5] =  $compeleteFound ? $result['credit_value'] : "";    

                        $theCollection[$dataCount][6] =  $result['module_status'];

                        $theCollection[$dataCount][7] = $result['tutor'];
                        $theCollection[$dataCount][8] = isset($result['results']) ? $result['results'] : "";
                        $theCollection[$dataCount][9] = $result['attempts'];
                        $theCollection[$dataCount][10] = "";
                        $theCollection[$dataCount][11] = "";
                        
                        $dataCount++;
                    endforeach;
                endif;
                if(count($term_declaration_ids)>1) {
                    $theCollection[$dataCount][0] = "";
                    $theCollection[$dataCount][1] = "";
                    $theCollection[$dataCount][2] = "";
                    $theCollection[$dataCount][3] = "";
                    $theCollection[$dataCount][4] = "";
                    $theCollection[$dataCount][5] = "";
                    $theCollection[$dataCount][6] = "";
                    $theCollection[$dataCount][7] = "";
                    $theCollection[$dataCount][8] = "";
                    $theCollection[$dataCount][9] = "";
                    $theCollection[$dataCount][10] = $termBaseSingleCompleteCount[$term];
                    $theCollection[$dataCount][11] = $termBaseSingleInCompleteCount[$term];

                    $dataCount++;
                    
                    $dataSetByStudent[$studentId][$term] = $theCollection;
                }
                
            endforeach;
                    
                $theCollection = [];
                $theCollection['lcc_id'] = isset($student) ?$student->registration_no : "";
                $theCollection['name'] = isset($student->full_name) ? $student->full_name : "";
                $theCollection['status'] = isset($student->status) ? $student->status->name : "";
                $theCollection['intake_semester'] = isset($student->activeCR->propose->semester) ? $student->activeCR->propose->semester->name : "";
                $theCollection['course'] = isset($student->activeCR->course) ?  $student->activeCR->course->name : "";
                
                $theCollection['total_module'] = $totalModuleCount;
                $theCollection['failed_module'] = $inCompleteCount;
                $theCollection['pass_module'] = $CompleteCount;
                $theCollection['total_credit_achieved'] = $totalCreditValue;
                $theCollection['level_4_unit'] = $totalLevel4UnitValue;
                $theCollection['level_4_credit'] = $totalLevel4CreditValue;
                $theCollection['level_5_unit'] = $totalLevel5UnitValue;
                $theCollection['level_5_credit'] = $totalLevel5CreditValue;
                $theCollection['certificate_claimed'] = isset($student->awarded->certificate_requested) ? $student->awarded->certificate_requested : "";
                $theCollection['certificate_claimed_date'] = isset($student->awarded->date_of_certificate_requested) ? $student->awarded->date_of_certificate_requested : "";
                $theCollection['certificate_requested_by'] = isset($student->awarded->requested->employee) ? $student->awarded->requested->employee->full_name : "";
                $theCollection['certificate_received'] = isset($student->awarded->certificate_received) ? $student->awarded->certificate_received : "";
                $theCollection['certificate_received_date'] = isset($student->awarded->date_of_certificate_received) ? $student->awarded->date_of_certificate_received : "";
                $theCollection['certificate_released'] = isset($student->awarded->certificate_released) ? $student->awarded->certificate_released : "";
                $theCollection['certificate_released_date'] = isset($student->awarded->date_of_certificate_released) ? $student->awarded->date_of_certificate_released : "";
                $theCollection['awarded_date'] = isset($student->awarded->date_of_award) ? $student->awarded->date_of_award : "";
  
                $resultSetsByStudent[$studentId] = $theCollection;
                unset($theCollection);
                $theCollection = [];
        endforeach;
        $createdBy = Employee::where('user_id',auth()->user()->id)->get()->first()->full_name;
        $createdDateTime = Carbon::now()->format('jS M Y h:i a');

        $html = $this->getHtml($resultSetsByStudent, $dataSetByStudent);
        unset($resultSetsByStudent);
        unset($dataSetByStudent);
        return response()->json(['htm' => $html], 200);

        //return Excel::download(new StudentDataReportBySelectionExport($returnData), 'student_data_report.xlsx');
    }

    public function getHtml($results, $resultDetails){
        
        $html = '';
        $html .= '<table class="table table-bordered table-sm studentResultProgressTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th class="w-1/6">LCC ID</th>';
                    $html .= '<th>Name</th>';
                    $html .= '<th>Intake Semester</th>';
                    $html .= '<th>Total Module</th>';
                    $html .= '<th>Failed Module</th>';
                    $html .= '<th>Pass Module</th>';
                    $html .= '<th>Total Credit Achieved</th>';
                    $html .= '<th>Level 4 Unit</th>';
                    $html .= '<th>L4 Credit</th>';
                    $html .= '<th>Level 5 Unit</th>';
                    $html .= '<th>L5 Credit</th>';
                    $html .= '<th>Certificate Claimed</th>';
                    $html .= '<th>Certificate Claimed Date</th>';
                    $html .= '<th>Certificate Requested By</th>';
                    $html .= '<th>Certificate Received</th>';
                    $html .= '<th>Certificate Received Date</th>';
                    $html .= '<th>Certificate Released</th>';
                    $html .= '<th>Certificate Released Date</th>';
                    $html .= '<th>Awarded Date</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                if(isset($results) && !empty($results)):
                    foreach($results as $studentId => $theResult):
                       
                        $html .= '<tr id="studentRow-'.$studentId.'">';
                            $html .= '<td class="font-medium text-left">';
                            if(isset($resultDetails[$studentId]) && !empty($resultDetails[$studentId])):
                                $html .= '<a id="student-'.$studentId.'" data-studentid="'.$studentId.'" href="javascript:void(0);" class="studentResultRowToggle font-medium hover:text-cyan-600">';
                                    $html .= '<span class="collapseIcon">+</span>'.$theResult['lcc_id'];
                                $html .= '</a>';
                            else:
                                $html .= '<span class="text-cyan-600">'.$theResult['lcc_id']. '</span>';
                            endif;
                            $html .= '</td>';
                            $html .= '<td>'.$theResult['name'].'</td>';
                            $html .= '<td>'.$theResult['intake_semester'].'</td>';
                            $html .= '<td>'.$theResult['total_module'].'</td>';
                            $html .= '<td>'.$theResult['failed_module'].'</td>';
                            $html .= '<td>'.$theResult['pass_module'].'</td>';
                            $html .= '<td>'.$theResult['total_credit_achieved'].'</td>';
                            $html .= '<td>'.$theResult['level_4_unit'].'</td>';
                            $html .= '<td>'.$theResult['level_4_credit'].'</td>';
                            $html .= '<td>'.$theResult['level_5_unit'].'</td>';
                            $html .= '<td>'.$theResult['level_5_credit'].'</td>';
                            $html .= '<td>'.$theResult['certificate_claimed'].'</td>';
                            $html .= '<td>'.(isset($theResult['certificate_claimed_date']) ? $theResult['certificate_claimed_date'] : '').'</td>';
                            $html .= '<td>'.$theResult['certificate_requested_by'].'</td>';
                            $html .= '<td>'.$theResult['certificate_received'].'</td>';
                            $html .= '<td>'.(isset($theResult['certificate_received_date']) ? $theResult['certificate_received_date'] : '').'</td>';
                            $html .= '<td>'.$theResult['certificate_released'].'</td>';
                            $html .= '<td>'.(isset($theResult['certificate_released_date']) ? $theResult['certificate_released_date'] : '').'</td>';
                            $html .= '<td>'.(isset($theResult['awarded_date']) ? $theResult['awarded_date'] : '').'</td>';
                        $html .= '</tr>';
                        //$dataSetByStudent[$studentId][$term]
                        if(isset($resultDetails[$studentId]) && !empty($resultDetails[$studentId])):
                        foreach ($resultDetails[$studentId] as $termId => $termBasedResult) {
                            
                            $html .= '<tr  class="hidden border-0 studentRowDetails-'.$studentId.'">';
                                $html .= '<td colspan="19" class="text-center">';
                                    //implementing a new table within the row for each student
                                    $html .= '<table class="table table-bordered table-sm">';
                                    $html .= '<thead>';
                                        $html .= '<tr>';
                                            $html .= '<th class="w-1/6 text-center">Term</th>';
                                            $html .= '<th>Group</th>';
                                            $html .= '<th>Serial</th>';
                                            $html .= '<th>Module</th>';
                                            $html .= '<th>Unit value</th>';
                                            $html .= '<th>Credit value</th>';
                                            $html .= '<th>Module status</th>';
                                            $html .= '<th>Tutor</th>';
                                            $html .= '<th>Results</th>';
                                            $html .= '<th>Attempts</th>';
                                            $html .= '<th>Completed</th>';
                                            $html .= '<th>Incompleted</th>';
                                        $html .= '</tr>';
                                    $html .= '</thead>';
                                    $html .= '<tbody>';

                                    foreach ($termBasedResult as $collectionData) {
                                        
                                        $html .= '<tr>';
                                            $html .= '<td class="text-center">'.$collectionData[0].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[1].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[2].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[3].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[4].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[5].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[6].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[7].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[8].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[9].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[10].'</td>';
                                            $html .= '<td class="text-center">'.$collectionData[11].'</td>';
                                        $html .= '</tr>';
                                        
                                    }

                                    $html .= '</tbody>';
                                    $html .= '</table>';
                                $html .= '</td>';
                                $html .= '</tr>';
                        }
                        endif;
                    endforeach;
                else:
                    $html .= '<tr>';
                        $html .= '<td colspan="12" class="font-medium text-center">';
                            $html .= 'Data not found for selected semesters.';
                        $html .= '</td>';
                    $html .= '</tr>';
                endif;
            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '</tfoot>';
        $html .= '</table>';



        return $html;
    }
}
