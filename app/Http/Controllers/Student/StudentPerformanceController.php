<?php


namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicCriteria;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceCriteria;
use App\Models\CourseModule;
use App\Models\ExamResultPrev;
use App\Models\Grade;
use App\Models\ModuleCreation;
use App\Models\ModuleLevel;
use App\Models\Plan;
use App\Models\QualAwardResult;
use App\Models\Result;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\TermDeclaration;
use App\Models\User;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Carbon\Carbon;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPerformanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Student $student)
    {

        $planList = Assign::where('student_id',$student->id)->get()->unique()->pluck('plan_id')->toArray();

        //$planKeySet = array_flip($planList); // Convert to array key set

        $planListForModules = Plan::with(['creations.module' => function($query) {
            $query->orderBy('name','ASC'); // Adjust the column name to the one you want to sort by
        }])->whereIn('id',$planList)->get();

        $termList = $planListForModules->unique()->pluck('term_declaration_id');


        $results = Result::with('plan','plan.creations.module','grade')->whereIn('plan_id',$planList)->where('student_id',$student->id)->where('published_at','<',Carbon::now())->orderBy('published_at','DESC')->get();

        $attendanceList = Attendance::with('plan','feed')->whereHas('planDateList', function ($query) use ($planList) {
            $query->whereIn('plan_id', $planList);
        })->where('student_id',$student->id)->get();
        $termAttendanceCount = [];

        $TopAttendanceCriteria = AttendanceCriteria::orderBy('point','desc')->get()->first()->point;
        $academicCriteriaList = AcademicCriteria::orderBy('point','desc')->get();
        
        $TopAcademicCriteria = $academicCriteriaList->first()->point;
        $GradeListForCount = $academicCriteriaList->pluck('code')->toArray();
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
                $resultSets[$termId][$moduleName]['academic_criteria'] = isset($academicCriteria->point) ? $academicCriteria->point : 0;
                $perTermModuleCriteria[$termId] += isset($academicCriteria->point) ? $academicCriteria->point : 0;

                $perTermTopSet[$termId] += $TopAcademicCriteria;
            }
        }
        
        
        $termSet = TermDeclaration::whereIn('id',$termList)->orderBy('id','DESC')->get();

        return view('pages.students.live.performance.index', [
            'title' => 'Students - Results',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Results', 'href' => 'javascript:void(0);'],
            ],
            'termSet' =>$termSet,
            'student' => $student,
            'results' =>$resultSets,
            'perTermTopSet' => $perTermTopSet,
            'TopAcademicCriteria' => $TopAcademicCriteria,
            'termAttendanceCount' => $termAttendanceCount,
            'TopAttendanceCriteria' => $TopAttendanceCriteria,
            'perTermModuleCriteria' => $perTermModuleCriteria,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function frontEndIndex(Student $student)
    {
       
        $planList = Assign::where('student_id',$student->id)->get()->unique()->pluck('plan_id')->toArray();

        //$planKeySet = array_flip($planList); // Convert to array key set

        $planListForModules = Plan::with(['creations.module' => function($query) {
            $query->orderBy('name','ASC'); // Adjust the column name to the one you want to sort by
        }])->whereIn('id',$planList)->get();

        $termList = $planListForModules->unique()->pluck('term_declaration_id');


        $results = Result::with('plan','plan.creations.module','grade')->whereIn('plan_id',$planList)->where('student_id',$student->id)->where('published_at','<',Carbon::now())->orderBy('published_at','DESC')->get();

        $attendanceList = Attendance::with('plan','feed')->whereHas('planDateList', function ($query) use ($planList) {
            $query->whereIn('plan_id', $planList);
        })->where('student_id',$student->id)->get();
        $termAttendanceCount = [];

        $TopAttendanceCriteria = AttendanceCriteria::orderBy('point','desc')->get()->first()->point;
        $academicCriteriaList = AcademicCriteria::orderBy('point','desc')->get();
        
        $TopAcademicCriteria = $academicCriteriaList->first()->point;
        $GradeListForCount = $academicCriteriaList->pluck('code')->toArray();
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
                $resultSets[$termId][$moduleName]['academic_criteria'] = isset($academicCriteria->point) ? $academicCriteria->point : 0;
                $perTermModuleCriteria[$termId] += isset($academicCriteria->point) ? $academicCriteria->point : 0;

                $perTermTopSet[$termId] += $TopAcademicCriteria;
            }
        }
        
        
        $termSet = TermDeclaration::whereIn('id',$termList)->orderBy('id','DESC')->get();

        return view('pages.students.frontend.performance.index', [
            'title' => 'Students - Results',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Results', 'href' => 'javascript:void(0);'],
            ],
            'termSet' =>$termSet,
            'student' => $student,
            'results' =>$resultSets,
            'perTermTopSet' => $perTermTopSet,
            'TopAcademicCriteria' => $TopAcademicCriteria,
            'termAttendanceCount' => $termAttendanceCount,
            'TopAttendanceCriteria' => $TopAttendanceCriteria,
            'perTermModuleCriteria' => $perTermModuleCriteria,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }
}
