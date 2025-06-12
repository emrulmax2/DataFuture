<?php

namespace App\Http\Controllers\Reports;

use App\Exports\StudentWorkplacementReportExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Group;
use App\Models\Plan;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\StudentProposedCourse;
use App\Models\StudentWorkPlacement;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class StudentWorkplacementReportController extends Controller
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
        
        return view('pages.reports.workplacement.index', [
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
            $initialStudentsIds = [];
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

            $initialStudentsIds = $this->callTheStudentListForGroup($myRequest);
                
            if(!empty($initialStudentsIds)): 
                $Query->whereIn('id', $initialStudentsIds); 
            else:
                $Query->whereIn('id', [0]); 
            endif;

            $Query->whereHas('workPlacements');
            
            $finalStudentIds = $Query->pluck('id')->toArray();
            $total_rows = count($finalStudentIds);

            return response()->json(['all_rows' => $total_rows, 'student_ids' => $finalStudentIds], 200);

        } else {
            return response()->json(['all_rows' => 0, 'student_ids'=>[]], 200);
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

        sort($studentIds);

        return $studentIds;
    }


 public function excelDownload(Request $request)
    {
        $studentIds = explode(",", $request->studentIds);
        $studentDataForExcel = [];
        $modulesWithConfirmedHoursGlobally = [];

        foreach ($studentIds as $studentId) {
            $student = Student::with([
                'status',
                'crel.creation.semester',
                'crel.creation.course',
                'crel.abody'
            ])->find($studentId);

            $studentRow = [
                'registration_no' => $student->registration_no,
                'student_name'    => $student->full_name,
                'status'          => $student->status->name ?? '',
                'intake_semester' => $student->crel->creation->semester->name ?? '',
                'course'          => $student->crel->creation->course->name ?? '',
                'modules'         => [],
                'total_work_hours' => StudentWorkPlacement::where('student_id', $studentId)->where('status', 'Confirmed')->sum('hours')
            ];

            $assignedModules = Assign::where('student_id', $studentId)
                                ->with('plan.creations.module')
                                ->get();

            foreach ($assignedModules as $assignment) {
                if (isset($assignment->plan->creations) && isset($assignment->plan->creations->module)) {
                    $moduleCreation = $assignment->plan->creations;
                    $moduleName = $moduleCreation->module->name;
                    
                    $confirmedHours = StudentWorkPlacement::where('student_id', $studentId)
                                        ->where('assign_module_list_id', $moduleCreation->id)
                                        ->where('status', 'Confirmed')
                                        ->sum('hours');
                    
                    $studentRow['modules'][$moduleName] = ($confirmedHours > 0 ? $confirmedHours . ' Hours' : '');

                    if ($confirmedHours > 0) {
                        if (!in_array($moduleName, $modulesWithConfirmedHoursGlobally)) {
                            $modulesWithConfirmedHoursGlobally[] = $moduleName;
                        }
                    }
                }
            }
            $studentDataForExcel[] = $studentRow;
        }

        $allModuleNames = array_values(array_unique($modulesWithConfirmedHoursGlobally));
        sort($allModuleNames); 

        $headers[1][0] = 'Student ID';
        $headers[1][1] = 'Student Name';
        $headers[1][2] = 'Status';
        $headers[1][3] = 'Intake Semester';
        $headers[1][4] = 'Course';
        
        $moduleHeaderStartColumn = 5;
        if (!empty($allModuleNames)) {
            $headers[1][$moduleHeaderStartColumn] = "Module List";
            for ($i = 1; $i < count($allModuleNames); $i++) {
                $headers[1][$moduleHeaderStartColumn + $i] = ""; 
            }
        }
        $totalHoursColumnIndex = $moduleHeaderStartColumn + (!empty($allModuleNames) ? count($allModuleNames) : 0);
        $headers[1][$totalHoursColumnIndex] = "Total Work Placement Hours";

        $headers[2] = array_fill(0, $moduleHeaderStartColumn, '');
        foreach ($allModuleNames as $moduleName) {
            $headers[2][] = $moduleName;
        }

        $headers[2][$totalHoursColumnIndex] = ""; 

        $theCollection = [];
        foreach ($studentDataForExcel as $dataRow) {
            $excelRow = [
                $dataRow['registration_no'],
                $dataRow['student_name'],
                $dataRow['status'],
                $dataRow['intake_semester'],
                $dataRow['course']
            ];

            foreach ($allModuleNames as $moduleName) {
                $excelRow[] = $dataRow['modules'][$moduleName] ?? '';
            }
            $excelRow[] = $dataRow['total_work_hours'] . ' Hours';
            $theCollection[] = $excelRow;
        }

        return Excel::download(new StudentWorkplacementReportExport($theCollection, $headers, $allModuleNames), 'student_workplacement_report.xlsx');
    }
}
