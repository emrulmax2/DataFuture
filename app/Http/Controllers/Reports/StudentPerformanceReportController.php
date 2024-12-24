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
ini_set('memory_limit', '256M'); // Increase memory limit
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
        
        $data = [];
        $attendances = [];
        $results = [];
        $terminfo = [];

        $assignments = Assign::with([
            'plan',
            'plan.attenTerm',
            'plan.creations',
            'plan.creations.module',
            'plan.course',
            'student',
            'student.crel',
            'student.crel.creation',
            'plan.attendances',
            'plan.attendances.plan.attenTerm',
            'plan.attendances.feed',
            'plan.results',
            'plan.results.grade',
            'plan.results.plan.attenTerm',
            'plan.results.plan.creations',
            'plan.results.plan.creations.module',
        ])->whereIn('student_id', $studentIds)->get();
        
        
        foreach ($assignments as $assign) {
            $terminfo[$assign->student_id][$assign->plan->attenTerm->name] = [
                'term_name' => $assign->plan->attenTerm->name,
                'term_status' => $assign->attendance == 1 && $assign->attendance == NULL ? '' : 'No',
                'term_student_id' => $assign->student->registration_no,
                'course' => $assign->plan->course->name,
                'intake_semester' => $assign->student->crel->creation->semester->name,
                'student_id' => $assign->student->registration_no,
            ];
            
            foreach ($assign->plan->attendances as $attendance) {
                $termName = $attendance->plan->attenTerm->name;
                if (!isset($attendances[$attendance->student_id][$termName]["total"])) {
                    $attendances[$attendance->student_id][$termName]["total"] = 0;
                }
        
                if (!isset($attendances[$attendance->student_id][$termName]["count"])) {
                    $attendances[$attendance->student_id][$termName]["count"] = 0;
                }
        
                $attendances[$attendance->student_id][$termName]["total"] += 1;
                $attendances[$attendance->student_id][$termName]["count"] += $attendance->feed->attendance_count == 1 ? 1 : 0;
        
                $averageAttendanceWithPercentage = ($attendances[$attendance->student_id][$termName]["count"] / $attendances[$attendance->student_id][$termName]["total"]) * 100;

                $attendanceCriteriaFound = AttendanceCriteria::where('range_from', '<=', round($averageAttendanceWithPercentage))
                    ->where('range_to', '>=', round($averageAttendanceWithPercentage))
                    ->first();
                
                $attendances[$attendance->student_id][$termName]["attendance_criteria"] = isset($attendanceCriteriaFound->id) ? round($attendanceCriteriaFound->point) : 0;
            }
            //dd($attendances);
            foreach ($assign->plan->results as $result) {
                
                $termName = $result->plan->attenTerm->name;
                $moduleName = $result->plan->creations->module->name;
                $results[$result->student_id][$termName][$moduleName]['grade'] = $result->grade->code;
                $academicCriteria = AcademicCriteria::where('code', $result->grade->code)->first();
                $results[$result->student_id][$termName][$moduleName]['academic_criteria'] = $academicCriteria ? $academicCriteria->point : 0;
            }
            //dd($results);
        }

        $moduleList = [];
        $data = [];
        foreach($terminfo as $studentId => $termInfo) {
            foreach($termInfo as $termName => $term) {
                $data[] = array_merge($term, [
                    'expected_performanance' => isset($attendances[$studentId][$termName]["attendance_criteria"]) ? $attendances[$studentId][$termName]["attendance_criteria"] : 0,
                    'achive_performanance' => isset($attendances[$studentId][$termName]["attendance_criteria"]) ? $attendances[$studentId][$termName]["attendance_criteria"] : 0,
                    'grand_expected' => isset($results[$studentId][$termName]) ? array_sum(array_column($results[$studentId][$termName], 'academic_criteria')) : 0,
                    'grand_achive' => isset($results[$studentId][$termName]) ? array_sum(array_column($results[$studentId][$termName], 'academic_criteria')) : 0,
                ]);
            }
        }
        
        $theCollection = [];
        $headers[1][0] = 'Term Name';
        $headers[1][1] = 'Term Status';
        $headers[1][2] = 'Terms Student ID';
        $headers[1][3] = 'Course';
        $headers[1][4] = 'Intake Semester';
        $headers[1][5] = 'Student ID';			
        $headers[1][6] = 'Expected performanance';
        $headers[1][7] = 'Achive Performanance';
        $headers[1][8] = 'Grand Expected';
        $headers[1][9] = 'Grand Achive';

        $dataCount = 1;
        foreach($data as $key => $value):
            $theCollection[$dataCount][0] = $value['term_name'];
            $theCollection[$dataCount][1] = $value['term_status'];
            $theCollection[$dataCount][2] = $value['term_student_id'];
            $theCollection[$dataCount][3] = $value['course'];
            $theCollection[$dataCount][4] = $value['intake_semester'];
            $theCollection[$dataCount][5] = $value['student_id'];
            $theCollection[$dataCount][6] = $value['expected_performanance'];
            $theCollection[$dataCount][7] = $value['achive_performanance'];
            $theCollection[$dataCount][8] = $value['grand_expected'];
            $theCollection[$dataCount][9] = $value['grand_achive'];

            $dataCount++;    
        endforeach;

        return Excel::download(new CustomArrayCollectionExport($theCollection,$headers, $moduleList), 'student_performance_report.xlsx');
                
        //return Excel::download(new StudentDataReportBySelectionExport($returnData), 'student_data_report.xlsx');
    }
}
