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
ini_set('memory_limit', '512M'); // Increase memory limit
ini_set('max_execution_time', '300'); // Increase execution time limit to 300 seconds

class StudentProgressMonitoringReportController extends Controller
{
    public function index(){
        $semesters = Cache::get('semesters', function () {
            $semesters = Semester::all()->sortByDesc("name");
            $semesterData = [];
            foreach ($semesters as $semester):
                $studentProposedCourse = StudentProposedCourse::where('semester_id',$semester->id)->get()->first();
                if(isset($studentProposedCourse->id))
                    $semesterData[] = $semester;
            endforeach;
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
        $theCollection[5][8] = 'Tutor';
        $theCollection[5][9] = 'Results';
        $theCollection[5][10] = 'Attempts';
        $theCollection[5][11] = 'Complete';
        $theCollection[5][12] = 'Incomplete';
        $theCollection[5][13] = 'Certificate Claimed';
             
        $studentIds = explode(",",$request->studentIds);
        
        $dataSet = [];
        $termStatus = [];
        $academicCriteriaList = AcademicCriteria::orderBy('point','desc')->get();
        $GradeListForCount = $academicCriteriaList->pluck('code')->toArray();
        $dataCount = 6;
        foreach($studentIds as $studentId):
            $dataSet[$studentId]['result'] = [];
            $student = Student::with('status','activeCR.course','activeCR.propose.semester','awarded')->where('id',$studentId)->get()->first();
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
                $moduleName = $result->plan->creations->module->name;
                $termName = isset($result->plan->attenTerm) ? $result->plan->attenTerm->name : "";
                $groupName = isset($result->plan->group) ? $result->plan->group->name : "";
                $tutorEmployee = isset($result->plan->tutor->employee) ? $result->plan->tutor->employee->full_name : "";
                
                
                if(in_array($gradeFound,$GradeListForCount)) {
                    $resultSets[$termId][$moduleName]['results'] = $gradeFound;
                    
                }
                

                $resultSets[$termId][$moduleName]['attendance_term'] = $termName;
                $resultSets[$termId][$moduleName]['group'] = $groupName;
                $resultSets[$termId][$moduleName]['module'] = $moduleName;
                $resultSets[$termId][$moduleName]['tutor'] = $tutorEmployee;

                
                if(!isset($resultSets[$termId][$moduleName]['attempts'])) {
                    $resultSets[$termId][$moduleName]['attempts'] = 1;
                } else {
                    $resultSets[$termId][$moduleName]['attempts']++;
                }
            }
           $inCompleteCount = 0;
           $CompleteCount = 0;
           foreach($term_declaration_ids as $term):
                $i =1;
                if(isset($resultSets[$term]))
                foreach($resultSets[$term] as $module => $result):
                    
                    if(!isset($result['results']) || $result['results']=="") {
                        ++$inCompleteCount;
                    }else{
                        ++$CompleteCount;
                    }
                    $theCollection[$dataCount][0] = "";
                    $theCollection[$dataCount][1] = "";
                    $theCollection[$dataCount][2] = "";
                    $theCollection[$dataCount][3] = "";
                    $theCollection[$dataCount][4] = ($i>1) ? "":$result['attendance_term'];
                    $theCollection[$dataCount][5] = $result['group'];
                    $theCollection[$dataCount][6] = $i++;
                    $theCollection[$dataCount][7] = $result['module'];
                    $theCollection[$dataCount][8] = $result['tutor'];
                    $theCollection[$dataCount][9] = isset($result['results']) ? $result['results'] : "";
                    $theCollection[$dataCount][10] = $result['attempts'];
                    $theCollection[$dataCount][11] = "";
                    $theCollection[$dataCount][12] = "";
                    $theCollection[$dataCount][13] = "";
                    $dataCount++;
                endforeach;
            endforeach;
                $theCollection[$dataCount][0] = isset($student) ?$student->registration_no : "";
                $theCollection[$dataCount][1] = isset($student->status) ? $student->status->name : "";
                $theCollection[$dataCount][2] = isset($student->activeCR->propose->semester) ? $student->activeCR->propose->semester->name : "";
                $theCollection[$dataCount][3] = isset($student->activeCR->course) ?  $student->activeCR->course->name : "";
                $theCollection[$dataCount][4] = "";
                $theCollection[$dataCount][5] = "";
                $theCollection[$dataCount][6] = "";
                $theCollection[$dataCount][7] = "";
                $theCollection[$dataCount][8] = "";
                $theCollection[$dataCount][9] = "";
                $theCollection[$dataCount][10] = "";
                $theCollection[$dataCount][11] = $CompleteCount;
                $theCollection[$dataCount][12] = $inCompleteCount;
                $theCollection[$dataCount][13] = isset($student->awarded) ? $student->awarded->certificate_requested : "";
                $dataCount++;		
        endforeach;

        return Excel::download(new ArrayCollectionExport($theCollection), 'student_progress_monitor_report.xlsx');
                
        //return Excel::download(new StudentDataReportBySelectionExport($returnData), 'student_data_report.xlsx');
    }
}
