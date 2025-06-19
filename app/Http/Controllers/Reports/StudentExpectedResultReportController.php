<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Exports\CustomArrayCollectionExport;
use App\Exports\CustomExpectedResultCollectionExport;
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

class StudentExpectedResultReportController extends Controller
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
        
        return view('pages.reports.result.expected.index', [
            'title' => 'Student Result Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Student Expected Result Reports', 'href' => 'javascript:void(0);']
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
                
            }
        endforeach;

        if(!isset($attendance_semester)) {
            //validation error check
            return response()->json(['errors' => ['attendance_semester'=>'Please select a Attendance Semester'],
                'message' => 'Please select a Attendance Semester'
            ], 422);
        }
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

        $selectedTerm = isset($request->term) ? json_decode($request->term, true) : [];

        $StudentData = Student::with('crel','crel.creation')->whereIn('id',$studentIds)->get();
        $moduleList = [];
        $data = [];
        //$planList = Result::whereIn('student_id', $studentIds)->get()->pluck('plan_id')->unique()->toArray();
        //$QueryInner = Plan::with('creations','creations.module','creations.level')->whereIn('id',$planList)->orderBy('id','DESC')->get();
        $assignList = Assign::with(
            "plan",
            "plan.attenTerm",
            "plan.creations",
            "plan.creations.module",
            "plan.cCreation",
            "plan.cCreation.course",
            'student',
            'student.award',
            'student.crel',
            'student.crel.abody',
            'student.crel.creation',
            'student.crel.creation.semester')->whereIn('student_id', $studentIds)
            ->whereHas('plan', function($q) use($selectedTerm) {
                if(!empty($selectedTerm)) {
                    $q->whereIn('term_declaration_id', $selectedTerm);
                }
            })
            ->orderBy('id','DESC')->get();

            $studentDetails = [];
            $data = [];
            foreach($assignList as $assign):
                if(isset($assign->plan->term_declaration_id)) {
                  
                    $studentDetails[$assign->student->id][$assign->plan->term_declaration_id] = [
                        'registration_no' => $assign->student->registration_no,
                        'student_name' => $assign->student->full_name,
                        'status' => $assign->student->status->name,
                        'intake_semester' => isset($assign->student->crel) ? $assign->student->crel->creation->semester->name : '',
                        'course' => isset($assign->plan->cCreation) ? $assign->plan->cCreation->course->name : '',
                        'award_body_reg_no' => isset($assign->student->crel->abody->reference) ? $assign->student->crel->abody->reference : '',
                        'attendance_term' => isset($assign->plan->attenTerm->name) ? $assign->plan->attenTerm->name : '',
                        'groups' => isset($assign->plan->group->name) ? $assign->plan->group->name : '',
                        
                    ];
                    //$moduleName = $result->plan->creations->module->name . ' - ' . ($result->plan->creations->code) ?? $result->plan->creations->module->code; 
                    
                    // if(!isset($result->plan->creations)) {
                    //     dd($result);
                    // }
                    
                    if(isset($assign->id) && isset($assign->plan->creations) && ($assign->plan->class_type=="Theory")) {

                        $moduleName = $assign->plan->creations->module->name;

                        if($assign->attendance === 0) {

                            $data[$assign->student->id][$assign->plan->term_declaration_id][$moduleName] = "No";
                            $moduleList[] = $moduleName;

                        } else {

                            $data[$assign->student->id][$assign->plan->term_declaration_id][$moduleName] = "Yes";
                            $moduleList[] = $moduleName;
                        }
                    }
                    
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
        $headers[1][6] = 'Attendance Term';
        $headers[1][7] = 'Group';
        $statusIncrement = 8;
        $printed = false;
        foreach($moduleList as $module) :
            if($printed==false) {
                $headers[1][$statusIncrement++] = "Unit Numbers";
                $printed = true;
            }else {
                $headers[1][$statusIncrement++] = "";
            }
        endforeach;

        // $headers[1][$statusIncrement++] = "Completed New Units";
        // $headers[1][$statusIncrement++] = "Assessment Board Outcome";
        
        $headers[2][0] = '';
        $headers[2][1] = '';
        $headers[2][2] = '';
        $headers[2][3] = '';
        $headers[2][4] = '';
        $headers[2][5] = '';
        $headers[2][6] = '';
        $headers[2][7] = '';
        $statusIncrement = 8;

        foreach($moduleList as $module) :
            $headers[2][$statusIncrement++] = $module;
        endforeach;
        // $headers[2][$statusIncrement++] = "";
        // $headers[2][$statusIncrement++] = "";
        
        $dataCount = 1;
        foreach($data as $studentId => $value):
            foreach($selectedTerm as $term) :
                if(!isset($studentDetails[$studentId][$term])) {
                    continue;
                }
                $theCollection[$dataCount][0] = $studentDetails[$studentId][$term]['registration_no'];
                $theCollection[$dataCount][1] = $studentDetails[$studentId][$term]['student_name'];
                $theCollection[$dataCount][2] = $studentDetails[$studentId][$term]['status'];
                $theCollection[$dataCount][3] = $studentDetails[$studentId][$term]['intake_semester'];
                $theCollection[$dataCount][4] = $studentDetails[$studentId][$term]['course'];
                $theCollection[$dataCount][5] = $studentDetails[$studentId][$term]['award_body_reg_no'];
                $theCollection[$dataCount][6] = $studentDetails[$studentId][$term]['attendance_term'];
                $theCollection[$dataCount][7] = $studentDetails[$studentId][$term]['groups'];

                $statusIncrement = 8;
                $unitCount = 0;
                foreach($moduleList as $module) :
                    
                    $theCollection[$dataCount][$statusIncrement++] = isset($value[$term][$module]) ? $value[$term][$module] : "";
                    
                endforeach;
                $dataCount++;   
            endforeach; 
        endforeach;

        return Excel::download(new CustomExpectedResultCollectionExport($theCollection,$headers, $moduleList), 'board_expected_result_report.xlsx');
                
        //return Excel::download(new StudentDataReportBySelectionExport($returnData), 'student_data_report.xlsx');
    }
}
