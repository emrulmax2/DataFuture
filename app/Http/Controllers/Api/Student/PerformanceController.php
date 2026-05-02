<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentAPIPerformanceResource;
use App\Models\AcademicCriteria;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceCriteria;
use App\Models\Plan;
use App\Models\Result;
use App\Models\Student;
use App\Models\TermDeclaration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PerformanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $theUser = $request->user();
        if (!$theUser) {
            return response()->json(['success' => false, 'error' => 'No authenticated user found.'], 401);
        }

        $cacheKey = 'performance_data_student_user_' . $theUser->id;
        $student = Student::where('student_user_id', $theUser->id)->first();

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found.',
            ], 404);
        }
        Cache::flush(); // Clear cache to ensure fresh data for testing

        $performanceData = Cache::remember($cacheKey, now()->addHours(1), function () use ($student) {
            return $this->buildPerformanceData($student);
        });

        return response()->json([
            'status' => 'success',
            'data' => StudentAPIPerformanceResource::make($performanceData),
        ]);
    }
    protected function buildPerformanceData(Student $student): array
    {
        $planList = Assign::where('student_id', $student->id)->get()->unique()->pluck('plan_id')->toArray();

        $planListForModules = Plan::with(['creations.module' => function ($query) {
            $query->orderBy('name', 'ASC');
        }])->whereIn('id', $planList)->get();

        $termList = $planListForModules->unique()->pluck('term_declaration_id');

        $results = Result::with('plan', 'plan.creations.module', 'grade')
            ->whereIn('plan_id', $planList)
            ->where('student_id', $student->id)
            ->where('published_at', '<', Carbon::now())
            ->orderBy('published_at', 'DESC')
            ->get();

        $attendanceList = Attendance::with('plan', 'feed')->whereHas('planDateList', function ($query) use ($planList) {
            $query->whereIn('plan_id', $planList);
        })->where('student_id', $student->id)->get();

        $termAttendanceCount = [];

        $TopAttendanceCriteria = AttendanceCriteria::orderBy('point', 'desc')->value('point');
        $academicCriteriaList = AcademicCriteria::orderBy('point', 'desc')->get();

        $TopAcademicCriteria = $academicCriteriaList->first()->point ?? 0;
        $GradeListForCount = $academicCriteriaList->pluck('code')->toArray();

        foreach ($attendanceList as $attendance) {
            if (!isset($termAttendanceCount[$attendance->plan->term_declaration_id]['present'])) {
                $termAttendanceCount[$attendance->plan->term_declaration_id]['present'] = 0;
            }
            if (!isset($termAttendanceCount[$attendance->plan->term_declaration_id]['total'])) {
                $termAttendanceCount[$attendance->plan->term_declaration_id]['total'] = 0;
            }

            $termAttendanceCount[$attendance->plan->term_declaration_id]['present'] += $attendance->feed->attendance_count == 1 ? 1 : 0;
            $termAttendanceCount[$attendance->plan->term_declaration_id]['total'] += 1;
            $termAttendanceCount[$attendance->plan->term_declaration_id]['avg'] = number_format((($termAttendanceCount[$attendance->plan->term_declaration_id]['present'] / $termAttendanceCount[$attendance->plan->term_declaration_id]['total']) * 100), 2);
        }

        $resultSets = [];
        $perTermTopSet = [];
        $perTermModuleCriteria = [];
        foreach ($planListForModules as $plan) {
            $termId = $plan->term_declaration_id;
            $moduleName = $plan->creations->module->name;
            $resultSets[$termId][$moduleName]['module'] = $moduleName;
            $resultSets[$termId][$moduleName]['grade'] = '';
            $resultSets[$termId][$moduleName]['academic_criteria'] = '';
        }

        foreach ($results as $result) {
            $gradeFound = $result->grade->code;
            if (in_array($gradeFound, $GradeListForCount, true)) {
                $academicCriteria = AcademicCriteria::where('code', $gradeFound)->first();
                $termId = $result->plan->term_declaration_id;
                $moduleName = $result->plan->creations->module->name;
                if (!isset($perTermTopSet[$termId])) {
                    $perTermTopSet[$termId] = 0;
                }
                if (!isset($perTermModuleCriteria[$termId])) {
                    $perTermModuleCriteria[$termId] = 0;
                }
                $resultSets[$termId][$moduleName]['module'] = $moduleName;
                $resultSets[$termId][$moduleName]['grade'] = $gradeFound;
                $resultSets[$termId][$moduleName]['academic_criteria'] = $academicCriteria->point ?? 0;
                $perTermModuleCriteria[$termId] += $academicCriteria->point ?? 0;

                $perTermTopSet[$termId] += $TopAcademicCriteria;
            }
        }

        $termSet = TermDeclaration::whereIn('id', $termList)->orderBy('id', 'DESC')->get();

        $communtyArray = [
            'termSet' => $termSet,
            'results' => $resultSets,
            'perTermTopSet' => $perTermTopSet,
            'TopAcademicCriteria' => $TopAcademicCriteria,
            'termAttendanceCount' => $termAttendanceCount,
            'TopAttendanceCriteria' => $TopAttendanceCriteria,
            'perTermModuleCriteria' => $perTermModuleCriteria,
        ];

        
        return $communtyArray;
    }

}
