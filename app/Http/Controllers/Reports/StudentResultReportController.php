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

class StudentResultReportController extends Controller
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
        
        return view('pages.reports.result.index', [
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

        //$StudentData = Student::with('crel','crel.creation')->whereIn('id',$studentIds)->get();
        $moduleList = [];
        $data = [];
        //$planList = Result::whereIn('student_id', $studentIds)->get()->pluck('plan_id')->unique()->toArray();
        //$QueryInner = Plan::with('creations','creations.module','creations.level')->whereIn('id',$planList)->orderBy('id','DESC')->get();
        $resultList = Result::with("grade",
            "plan",
            "plan.creations",
            "plan.creations.module",
            "plan.cCreation",
            "plan.cCreation.course",
            'student',
            'student.assign',
            'student.award',
            'student.crel',
            'student.crel.abody',
            'student.crel.creation',
            'student.crel.creation.semester')->whereIn('student_id', $studentIds)->orderBy('published_at','ASC')->get();
            $studentDetails = [];
            $data = [];
            foreach($resultList as $result):
                $iGoupCount = 0;
                $highestTermId = 0;
                $groupName = [];
                foreach($result->student->assign as $assign):
                    if(isset($assign->plan->group->name)) {
                        //check if $assign->plan->group->name already exists in array for same term declaration
                        if(isset($groupName[$assign->plan->term_declaration_id])) {
                            if(!in_array($assign->plan->group->name, $groupName[$assign->plan->term_declaration_id])) {
                                $groupName[$assign->plan->term_declaration_id][$iGoupCount] = $assign->plan->group->name;
                            }
                        } else {
                            $groupName[$assign->plan->term_declaration_id][$iGoupCount] = $assign->plan->group->name;
                        }
                        $highestTermId = ($highestTermId < $assign->plan->term_declaration_id) ? $assign->plan->term_declaration_id : $highestTermId;
                        
                        $iGoupCount++;
                    }
                endforeach;
                $correctGroupNames = $groupName[$highestTermId];
                $studentDetails[$result->student->id] = [
                    'registration_no' => $result->student->registration_no,
                    'student_name' => $result->student->full_name,
                    'status' => $result->student->status->name,
                    'intake_semester' => isset($result->student->crel) ? $result->student->crel->creation->semester->name : '',
                    'course' => isset($result->plan->cCreation) ? $result->plan->cCreation->course->name : '',
                    'award_body_reg_no' => isset($result->student->crel->abody->reference) ? $result->student->crel->abody->reference : '',
                    'groups' => (isset($result->plan->group->name)) ? implode(", ", $correctGroupNames) : "",
                ];
                //$moduleName = $result->plan->creations->module->name . ' - ' . ($result->plan->creations->code) ?? $result->plan->creations->module->code; 
                
                // if(!isset($result->plan->creations)) {
                //     dd($result);
                // }
                
                if(isset($result->id) && isset($result->plan->creations)) {
                        $moduleName = $result->plan->creations->module->name; 
                        $data[$result->student->id][$moduleName] = $result->grade->code;
                        $moduleList[] = $moduleName;
                }
            endforeach;
            $moduleList = array_unique($moduleList);
            sort($moduleList);
        $theCollection = [];
        $headers[1][0] = 'Stuent ID';
        $headers[1][1] = 'Student Name';
        $headers[1][2] = 'Status';
        $headers[1][3] = 'Intake Semester';
        $headers[1][4] = 'Course';
        $headers[1][5] = 'Awarding Body Ref';
        $headers[1][6] = 'Group';
        $statusIncrement = 7;
        $printed = false;
        foreach($moduleList as $module) :
            if($printed==false) {
                $headers[1][$statusIncrement++] = "Unit Numbers";
                $printed = true;
            }else {
                $headers[1][$statusIncrement++] = "";
            }
        endforeach;

        $headers[1][$statusIncrement++] = "Completed New Units";
        $headers[1][$statusIncrement++] = "Assessment Board Outcome";

        $headers[2][0] = '';
        $headers[2][1] = '';
        $headers[2][2] = '';
        $headers[2][3] = '';
        $headers[2][4] = '';
        $headers[2][5] = '';
        $headers[2][6] = '';
        $statusIncrement = 7;

        foreach($moduleList as $module) :
            $headers[2][$statusIncrement++] = $module;
        endforeach;
        $headers[2][$statusIncrement++] = "";
        $headers[2][$statusIncrement++] = "";


        
        $dataCount = 1;
        foreach($data as $key => $value):
            $theCollection[$dataCount][0] = $studentDetails[$key]['registration_no'];
            $theCollection[$dataCount][1] = $studentDetails[$key]['student_name'];
            $theCollection[$dataCount][2] = $studentDetails[$key]['status'];
            $theCollection[$dataCount][3] = $studentDetails[$key]['intake_semester'];
            $theCollection[$dataCount][4] = $studentDetails[$key]['course'];
            $theCollection[$dataCount][5] = $studentDetails[$key]['award_body_reg_no'];
            $theCollection[$dataCount][6] = $studentDetails[$key]['groups'];

            $statusIncrement = 7;
            $unitCount = 0;
            foreach($moduleList as $module) :
                if(isset($value[$module]) && ($value[$module]=='P' || $value[$module]=='M' || $value[$module]=='D')) {
                    $unitCount+=1;
                }
                $theCollection[$dataCount][$statusIncrement++] = isset($value[$module]) ? $value[$module] : '';
            endforeach;
            $theCollection[$dataCount][$statusIncrement++] = $unitCount;
            $theCollection[$dataCount][$statusIncrement++] = '';
            $dataCount++;    
        endforeach;

        return Excel::download(new CustomArrayCollectionExport($theCollection,$headers, $moduleList), 'board_result_report.xlsx');
                
        //return Excel::download(new StudentDataReportBySelectionExport($returnData), 'student_data_report.xlsx');
    }
}
