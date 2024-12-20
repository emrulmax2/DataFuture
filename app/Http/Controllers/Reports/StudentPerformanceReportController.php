<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Exports\CustomArrayCollectionExport;
use App\Exports\Reports\StudentDataReportBySelectionExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Agent;
use App\Models\AgentReferralCode;
use App\Models\Assign;
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
        Plan::with('assign')->whereHas('assign', function($q) use($studentIds){
            $q->whereIn('student_id', $studentIds);
        })->get()->each(function($plan) use(&$data,$studentIds) {
            Result::whereIn('student_id',$studentIds)->wehre('plan_id',$plan->id)->get()->each(function($result) use(&$data) {
                $data[] = [
                    'term_name' => $result->term->name,
                    'term_status' => $result->term->status->name,
                    'term_student_id' => $result->term->student_id,
                    'course' => $result->course->name,
                    'intake_semester' => $result->term->semester->name,
                    'student_id' => $result->student->student_id,
                    'expected_performanance' => $result->expected_performanance,
                    'achive_performanance' => $result->achive_performanance,
                    'grand_expected' => $result->grand_expected,
                    'grand_achive' => $result->grand_achive,
                ];
            });
        });

        Result::whereIn('student_id',$studentIds)->get()->each(function($result) use(&$data) {
            $data[] = [
                'term_name' => $result->term->name,
                'term_status' => $result->term->status->name,
                'term_student_id' => $result->term->student_id,
                'course' => $result->course->name,
                'intake_semester' => $result->term->semester->name,
                'student_id' => $result->student->student_id,
                'expected_performanance' => $result->expected_performanance,
                'achive_performanance' => $result->achive_performanance,
                'grand_expected' => $result->grand_expected,
                'grand_achive' => $result->grand_achive,
            ];
        });


        $moduleList = [];
        $data = [];

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
