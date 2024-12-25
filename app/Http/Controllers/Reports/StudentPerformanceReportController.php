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
class StudentPerformanceReportController extends Controller
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
        
        return view('pages.reports.performance.index', [
            'title' => 'Student Result Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Student Result Reports', 'href' => 'javascript:void(0);']
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

        $groupParams = isset($request->group) && !empty($request->group) ? $request->group : [];

        $sorts = [];
        
        $Query = Student::orderBy('id','desc');
        $itemSelected = false;
        foreach($groupParams as $field => $value):
            $$field = (isset($value) && !empty($value) ? $value : '');

            if($$field!='') {
                $itemSelected = true;
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

                $studentsIds = $this->callTheStudentListForGroup($myRequest);
                
            if(!empty($studentsIds)): 
                $Query->whereIn('id', $studentsIds); 
            else:
                $Query->whereIn('id', [0]); 
            endif;
            
            $total_rows = $Query->count();

            $Query = $Query->get();

    
            return response()->json(['all_rows' => $total_rows, 'student_ids'=>$studentsIds],200);

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
        
        $studentIds = [];


        $QueryInner = StudentCourseRelation::with('activeCreation');
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
             
        $studentIds = explode(",",$request->studentIds);
        $dataSet = [];
        $termStatus = [];
        foreach($studentIds as $studentId):
            $planList = Assign::where('student_id',$studentId)->get()->unique()->pluck('plan_id')->toArray();

            //$planKeySet = array_flip($planList); // Convert to array key set

            $planListForModules = Plan::with(['creations.module' => function($query) {
                $query->orderBy('name','ASC'); // Adjust the column name to the one you want to sort by
            }])->whereIn('id',$planList)->get();

            $termList = $planListForModules->unique()->pluck('term_declaration_id');


            $results = Result::with('plan','plan.creations.module','grade')->whereIn('plan_id',$planList)->where('student_id',$studentId)->where('published_at','<',Carbon::now())->orderBy('published_at','DESC')->get();

            $attendanceList = Attendance::with('plan','feed')->whereHas('planDateList', function ($query) use ($planList) {
                $query->whereIn('plan_id', $planList);
            })->where('student_id',$studentId)->get();
            $termAttendanceCount = [];

            $TopAttendanceCriteria = AttendanceCriteria::orderBy('point','desc')->get()->first()->point;
            $academicCriteriaList = AcademicCriteria::orderBy('point','desc')->get();
            
            $TopAcademicCriteria = $academicCriteriaList->first()->point;
            $GradeListForCount = $academicCriteriaList->pluck('code')->toArray();
            $termStatus = [];

            Assign::where('student_id',$studentId)->whereHas('plan', function ($query) use ($planList) {
                $query->whereIn('plan_id', $planList);
            })->each(function($assign) use (&$termStatus) {
                $termStatus[$assign->plan->term_declaration_id] = ($assign->attendance != 1 && $assign->attendance!=NULL) ? 'No' : '';
            });

            foreach($attendanceList as $attendance) {
                
                if(!isset($termAttendanceCount[$attendance->plan->term_declaration_id]['present']))
                    $termAttendanceCount[$attendance->plan->term_declaration_id]['present'] = 0;
                if(!isset($termAttendanceCount[$attendance->plan->term_declaration_id]['total']))
                    $termAttendanceCount[$attendance->plan->term_declaration_id]['total'] = 0;
                
                $termAttendanceCount[$attendance->plan->term_declaration_id]['present'] += $attendance->feed->attendance_count == 1 ? 1 : 0;
                $termAttendanceCount[$attendance->plan->term_declaration_id]['total'] +=1; 
                $termAttendanceCount[$attendance->plan->term_declaration_id]['avg'] = number_format((($termAttendanceCount[$attendance->plan->term_declaration_id]['present']/$termAttendanceCount[$attendance->plan->term_declaration_id]['total'])*100),2); 
            
            }
            $resultSets = [];
            $perTermTopSet = [];
            $perTermModuleCriteria = [];
            foreach ($planListForModules as $plan) {
                
                $termId =$plan->term_declaration_id;
                $moduleName = $plan->creations->module->name;
                $resultSets[$termId][$moduleName]['module'] = $moduleName;
                $resultSets[$termId][$moduleName]['grade'] = "";
                $resultSets[$termId][$moduleName]['academic_criteria'] = "";
            }
            if(isset($results))
            foreach ($results as $result) {
                $gradeFound = $result->grade->code;
                if(in_array($gradeFound,$GradeListForCount)) {
                    
                    $academicCriteria = AcademicCriteria::where('code', $gradeFound)->first();
                    $termId =$result->plan->term_declaration_id;
                    $moduleName = $result->plan->creations->module->name;
                    if(!isset($perTermTopSet[$termId])) {
                        $perTermTopSet[$termId] = 0;
                    }
                    if(!isset($perTermModuleCriteria[$termId])) {
                        $perTermModuleCriteria[$termId] = 0;
                    }
                    $resultSets[$termId][$moduleName]['module'] = $moduleName;
                    $resultSets[$termId][$moduleName]['grade'] = $gradeFound;
                    $resultSets[$termId][$moduleName]['academic_criteria'] = isset($academicCriteria->point) ? (float)$academicCriteria->point : 0;
                    $perTermModuleCriteria[$termId] += isset($academicCriteria->point) ? (float)$academicCriteria->point : 0;

                    $perTermTopSet[$termId] += (float)$TopAcademicCriteria;
                }
            }

            $termSet = TermDeclaration::whereIn('id',$termList)->orderBy('id','DESC')->get();
            
            $dataSet[$studentId] = [
                'student' => Student::with('status','activeCR.course','activeCR.propose.semester')->where('id',$studentId)->get()->first(),
                'termSet' => $termSet,
                'resultSets' => $resultSets,
                'termStatus' => $termStatus,
                'perTermTopSet' => $perTermTopSet,
                'perTermModuleCriteria' =>$perTermModuleCriteria,
                'termAttendanceCount' => $termAttendanceCount,
                'TopAttendanceCriteria' => $TopAttendanceCriteria,
                'TopAcademicCriteria' => $TopAcademicCriteria,
            ];
            
        endforeach;

        $theCollection = [];
        $headers = [];
        $theCollection[1][0] = 'Term Name';
        $theCollection[1][1] = 'Term Status';
        $theCollection[1][2] = 'Terms Student ID';
        $theCollection[1][3] = 'Course';
        $theCollection[1][4] = 'Intake Semester';
        $theCollection[1][5] = 'Status';
        $theCollection[1][6] = 'Student ID';			
        $theCollection[1][7] = 'Expected performanance';
        $theCollection[1][8] = 'Achive Performanance';
        $theCollection[1][9] = 'Grand Expected';
        $theCollection[1][10] = 'Grand Achive';

        $dataCount = 2;
        foreach($dataSet as $studentId => $data):
            $totalExpectedPerformance = 0;
            $totalAchivedPerformance = 0;
            foreach ($data['termSet'] as $term):
                $avgAttendance = isset($data['termAttendanceCount'][$term->id]['avg']) ? round($data['termAttendanceCount'][$term->id]['avg']) : 0;
                $attendanceCriteriaFound = AttendanceCriteria::where('range_from', '<=', $avgAttendance)
                    ->where('range_to', '>=', $avgAttendance)
                    ->first();

                if(!isset($data['perTermModuleCriteria'][$term->id])) {
                    
                    $academicAchivement = 0;
                } else {
                    $academicAchivement = $data['perTermModuleCriteria'][$term->id];
                }
                if(!isset($data['perTermTopSet'][$term->id]) || $data['perTermTopSet'][$term->id] == "") {
                    $perTermTopSetResult = 0;
                } else {
                    $perTermTopSetResult = $data['perTermTopSet'][$term->id];
                }
                $attendance_criteria = isset($attendanceCriteriaFound->id) ? $attendanceCriteriaFound->point : 0;
                $achivedPerformance = $attendance_criteria +  $academicAchivement; 
                $expectedPerformance = (float)$perTermTopSetResult  + (float)$data['TopAttendanceCriteria'];

                $theCollection[$dataCount][0] = $term->name;
                $theCollection[$dataCount][1] = $data['termStatus'][$term->id];
                $theCollection[$dataCount][2] = $data['student']->registration_no;
                $theCollection[$dataCount][3] = $data['student']->activeCR->course->name;
                $theCollection[$dataCount][4] = $data['student']->activeCR->propose->semester->name;
                $theCollection[$dataCount][5] = $data['student']->status->name;
                $theCollection[$dataCount][6] = "";
                $theCollection[$dataCount][7] = $expectedPerformance ? $expectedPerformance : "";
                $theCollection[$dataCount][8] = $achivedPerformance ? $achivedPerformance : "";

                $theCollection[$dataCount][9] = "";
                $theCollection[$dataCount][10] = "";
            
                $totalExpectedPerformance += $expectedPerformance;
                $totalAchivedPerformance += $achivedPerformance;
                
                $dataCount++;   
            endforeach;
            
            $theCollection[$dataCount][0] = "";
            $theCollection[$dataCount][1] = "";
            $theCollection[$dataCount][2] = "";
            $theCollection[$dataCount][3] = "";
            $theCollection[$dataCount][4] = "";
            $theCollection[$dataCount][5] = "";
            $theCollection[$dataCount][6] = $data['student']->registration_no;
            $theCollection[$dataCount][7] = "";
            $theCollection[$dataCount][8] = "";

            $theCollection[$dataCount][9]  = $totalExpectedPerformance;
            $theCollection[$dataCount][10] = $totalAchivedPerformance;
            $dataCount++;   
        endforeach;
        $moduleList = [];
        //dd($theCollection);
        return Excel::download(new ArrayCollectionExport($theCollection), 'student_performance_report.xlsx');
                
        //return Excel::download(new StudentDataReportBySelectionExport($returnData), 'student_data_report.xlsx');
    }
}
