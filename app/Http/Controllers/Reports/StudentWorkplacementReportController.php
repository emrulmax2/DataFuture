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
use App\Models\WorkplacementDetails;
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
        $hasUnassignedModuleHoursData = false;
        $allModulesGlobally = [];
        $allWorkplacementDetailsGlobally = [];

        foreach ($studentIds as $studentId) {
            $student = Student::with(['status','crel.creation.semester','crel.creation.course',])->find($studentId);

            $studentRow = [
                'registration_no' => $student->registration_no,
                'student_name'    => $student->full_name,
                'status'          => $student->status->name ?? 'N/A',
                'intake_semester' => $student->crel->creation->semester->name ?? '',
                'course'          => $student->crel->creation->course->name ?? '',
                'modules'         => [],
                'unassigned_module_hours' => '',
                'workplacement_level_learning_hours_data' => [],
                'total_overall_wpd_required_hours_for_student' => 0,
                'total_confirmed_work_hours' => StudentWorkPlacement::where('student_id', $studentId)->where('status', 'Confirmed')->sum('hours')
            ];

            $assignedModules = Assign::where('student_id', $studentId)->with('plan.creations.module')->get();

            foreach ($assignedModules as $assignment) {
                if (isset($assignment->plan->creations) && isset($assignment->plan->creations->module)) {
                    $moduleCreation = $assignment->plan->creations;
                    $moduleName = $moduleCreation->module->name ?? 'Unknown Module';

                    $confirmedHours = StudentWorkPlacement::where('student_id', $studentId)
                                        ->where('assign_module_list_id', $moduleCreation->id)
                                        ->where('status', 'Confirmed')
                                        ->sum('hours');

                    $studentRow['modules'][$moduleName] = ($confirmedHours > 0 ? $confirmedHours . ' Hours' : '');

                    if (!in_array($moduleName, $allModulesGlobally)) {
                        $allModulesGlobally[] = $moduleName;
                    }
                }
            }

            $unassignedHoursSum = StudentWorkPlacement::where('student_id', $studentId)
                                    ->whereNull('assign_module_list_id')
                                    ->where('status', 'Confirmed')
                                    ->sum('hours');
            if ($unassignedHoursSum > 0) {
                $studentRow['unassigned_module_hours'] = $unassignedHoursSum . ' Hours';
                $hasUnassignedModuleHoursData = true;
            }

            $allStudentWorkPlacements = StudentWorkPlacement::where('student_id', $studentId)
                ->with(['workplacementDetails.level_hours.learning_hours', 'level_hours.learning_hours', 'learning_hours'])
                ->get();

            $distinctWorkplacementDetailsForStudent = $allStudentWorkPlacements->map(function($wp) {
                return $wp->workplacementDetails;
            })->filter()->unique('id');

            foreach ($distinctWorkplacementDetailsForStudent as $wpDetail) {
                if (!$wpDetail) continue;
                $wpdId = $wpDetail->id;
                $wpdName = $wpDetail->name ?? 'Unknown Workplacement Detail';

                if (!isset($allWorkplacementDetailsGlobally[$wpdId])) {
                    $allWorkplacementDetailsGlobally[$wpdId] = [
                        'name' => $wpdName,
                        'required_hours' => (float) $wpDetail->hours,
                        'level_hours' => []
                    ];

                    $levelHoursForWpd = $wpDetail->level_hours()->orderBy('name')->get();
                    foreach ($levelHoursForWpd as $lh) {
                        $allWorkplacementDetailsGlobally[$wpdId]['level_hours'][$lh->id] = [
                            'name' => $lh->name ?? 'Unknown Level Hour',
                            'required_hours' => (float) $lh->hours,
                            'learning_hours' => []
                        ];
                        $learningHoursForLh = $lh->learning_hours()->orderBy('name')->get();
                        foreach ($learningHoursForLh as $learnH) {
                            $allWorkplacementDetailsGlobally[$wpdId]['level_hours'][$lh->id]['learning_hours'][$learnH->id] = [
                                'name' => $learnH->name ?? 'Unknown Learning Hour',
                                'required_hours' => (float) $learnH->hours
                            ];
                        }
                    }
                }

                $studentRow['total_overall_wpd_required_hours_for_student'] += (float) $wpDetail->hours;
                $studentRow['workplacement_level_learning_hours_data'][$wpdId]['main'] = [
                    'required' => ((float) $wpDetail->hours > 0 ? (float) $wpDetail->hours . ' Hours' : ''),
                    'completed' => ''
                ];
                $studentRow['workplacement_level_learning_hours_data'][$wpdId]['level_hours_details'] = [];

                $levelHoursForWpdStudent = $wpDetail->level_hours()->orderBy('name')->get();
                foreach ($levelHoursForWpdStudent as $lh_student) {
                    $studentRow['workplacement_level_learning_hours_data'][$wpdId]['level_hours_details'][$lh_student->id]['main'] = [
                        'required' => ((float) $lh_student->hours > 0 ? (float) $lh_student->hours . ' Hours' : ''),
                        'completed' => ''
                    ];
                    $studentRow['workplacement_level_learning_hours_data'][$wpdId]['level_hours_details'][$lh_student->id]['learning_hours_details'] = [];
                    $learningHoursForLhStudent = $lh_student->learning_hours()->orderBy('name')->get();
                    foreach ($learningHoursForLhStudent as $learnH_student) {
                        $studentRow['workplacement_level_learning_hours_data'][$wpdId]['level_hours_details'][$lh_student->id]['learning_hours_details'][$learnH_student->id] = [
                            'required' => ((float) $learnH_student->hours > 0 ? (float) $learnH_student->hours . ' Hours' : ''),
                            'completed' => ''
                        ];
                    }
                }
            }

            $studentConfirmedWorkPlacements = $allStudentWorkPlacements->where('status', 'Confirmed');
            $studentWpdCompleted = [];
            $studentLevelCompleted = [];
            $studentLearningCompleted = [];

            foreach ($studentConfirmedWorkPlacements as $wp) {
                $hours = (float) $wp->hours;
                $wpdId_comp = $wp->workplacement_details_id;
                $lhId_comp = $wp->level_hours_id;
                $learnHId_comp = $wp->learning_hours_id;

                if ($wpdId_comp) {
                    $studentWpdCompleted[$wpdId_comp] = ($studentWpdCompleted[$wpdId_comp] ?? 0) + $hours;
                    if ($lhId_comp) {
                        $studentLevelCompleted[$wpdId_comp][$lhId_comp] = ($studentLevelCompleted[$wpdId_comp][$lhId_comp] ?? 0) + $hours;
                        if ($learnHId_comp) {
                            $studentLearningCompleted[$wpdId_comp][$lhId_comp][$learnHId_comp] = ($studentLearningCompleted[$wpdId_comp][$lhId_comp][$learnHId_comp] ?? 0) + $hours;
                        }
                    }
                }
            }
            
            foreach ($studentWpdCompleted as $wpdId_agg => $completedHours) {
                if (isset($studentRow['workplacement_level_learning_hours_data'][$wpdId_agg]['main'])) {
                    $studentRow['workplacement_level_learning_hours_data'][$wpdId_agg]['main']['completed'] = $completedHours > 0 ? $completedHours . ' Hours' : '';
                }
            }
            foreach ($studentLevelCompleted as $wpdId_agg_l => $levels) {
                foreach ($levels as $lhId_agg_l => $completedHours_l) {
                    if (isset($studentRow['workplacement_level_learning_hours_data'][$wpdId_agg_l]['level_hours_details'][$lhId_agg_l]['main'])) {
                        $studentRow['workplacement_level_learning_hours_data'][$wpdId_agg_l]['level_hours_details'][$lhId_agg_l]['main']['completed'] = $completedHours_l > 0 ? $completedHours_l . ' Hours' : '';
                    }
                }
            }
            foreach ($studentLearningCompleted as $wpdId_agg_ln => $levels_ln) {
                foreach ($levels_ln as $lhId_agg_ln => $learnings_ln) {
                    foreach ($learnings_ln as $learnHId_agg_ln => $completedHours_ln) {
                        if (isset($studentRow['workplacement_level_learning_hours_data'][$wpdId_agg_ln]['level_hours_details'][$lhId_agg_ln]['learning_hours_details'][$learnHId_agg_ln])) {
                            $studentRow['workplacement_level_learning_hours_data'][$wpdId_agg_ln]['level_hours_details'][$lhId_agg_ln]['learning_hours_details'][$learnHId_agg_ln]['completed'] = $completedHours_ln > 0 ? $completedHours_ln . ' Hours' : '';
                        }
                    }
                }
            }

            $studentDataForExcel[] = $studentRow;
        }

        $allModuleNamesSorted = array_values(array_unique($allModulesGlobally));
        sort($allModuleNamesSorted);

        uasort($allWorkplacementDetailsGlobally, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $excelHeaders = [[], []];
        $columnMergeData = ['row1' => []];

        $staticHeaderTitles = ['Student ID', 'Student Name', 'Status', 'Intake Semester', 'Course'];
        for ($i = 0; $i < count($staticHeaderTitles); $i++) {
            $excelHeaders[0][$i] = $staticHeaderTitles[$i];
            $excelHeaders[1][$i] = '';
        }
        $currentAbsoluteColumn = count($staticHeaderTitles);

        foreach ($allWorkplacementDetailsGlobally as $wpdId => $wpdDetails) {
            $wpdName = $wpdDetails['name'];
            $wpdStartColForRow1Merge = $currentAbsoluteColumn;

            if (!empty($wpdDetails['level_hours'])) {
                foreach ($wpdDetails['level_hours'] as $lhId => $lhDetails) {
                    $lhName = $lhDetails['name'];

                    $excelHeaders[0][$currentAbsoluteColumn] = $wpdName;
                    $excelHeaders[1][$currentAbsoluteColumn] = $lhName . " - Required";
                    $currentAbsoluteColumn++;
                    $excelHeaders[0][$currentAbsoluteColumn] = $wpdName;
                    $excelHeaders[1][$currentAbsoluteColumn] = $lhName . " - Completed";
                    $currentAbsoluteColumn++;

                    if (!empty($lhDetails['learning_hours'])) {
                        foreach ($lhDetails['learning_hours'] as $learnhId => $learnhDetails) {
                            $learnhName = $learnhDetails['name'];

                            $excelHeaders[0][$currentAbsoluteColumn] = $wpdName;
                            $excelHeaders[1][$currentAbsoluteColumn] = $lhName . " - " . $learnhName . " - Required";
                            $currentAbsoluteColumn++;
                            $excelHeaders[0][$currentAbsoluteColumn] = $wpdName;
                            $excelHeaders[1][$currentAbsoluteColumn] = $lhName . " - " . $learnhName . " - Completed";
                            $currentAbsoluteColumn++;
                        }
                    }
                }
            }
            $wpdTotalSpanForRow1 = $currentAbsoluteColumn - $wpdStartColForRow1Merge;
            if ($wpdTotalSpanForRow1 > 0) {
                $columnMergeData['row1'][] = ['span' => $wpdTotalSpanForRow1, 'start_col_abs' => $wpdStartColForRow1Merge];
            }
        }

        $moduleSectionStartCol = $currentAbsoluteColumn;
        $moduleHeaderAdded = false;
        $moduleRow1HeaderName = "Module List";

        if ($hasUnassignedModuleHoursData) {
            $excelHeaders[0][$currentAbsoluteColumn] = $moduleRow1HeaderName;
            $excelHeaders[1][$currentAbsoluteColumn] = "";
            $currentAbsoluteColumn++;
            $moduleHeaderAdded = true;
        }

        if (!empty($allModuleNamesSorted)) {
            foreach ($allModuleNamesSorted as $moduleName) {
                $excelHeaders[0][$currentAbsoluteColumn] = $moduleRow1HeaderName;
                $excelHeaders[1][$currentAbsoluteColumn] = $moduleName;
                $currentAbsoluteColumn++;
                $moduleHeaderAdded = true;
            }
        }
        if ($moduleHeaderAdded) {
            $moduleSectionSpan = $currentAbsoluteColumn - $moduleSectionStartCol;
            if ($moduleSectionSpan > 0) {
                $columnMergeData['row1'][] = ['span' => $moduleSectionSpan, 'start_col_abs' => $moduleSectionStartCol];
            }
        }

        $excelHeaders[0][$currentAbsoluteColumn] = "Total Hours Required";
        $excelHeaders[1][$currentAbsoluteColumn] = "";                     
        $currentAbsoluteColumn++;

        $excelHeaders[0][$currentAbsoluteColumn] = "Total Hours Completed";
        $excelHeaders[1][$currentAbsoluteColumn] = "";                     
        $currentAbsoluteColumn++;
        
        $maxColumnCount = 0;
        foreach($excelHeaders as $headerRowInstance) {
            $maxColumnCount = max($maxColumnCount, count($headerRowInstance));
        }

        for($i=0; $i<2; $i++){
            for ($k = 0; $k < $maxColumnCount; $k++) {
                if (!isset($excelHeaders[$i][$k])) $excelHeaders[$i][$k] = '';
            }
        }

        $theCollection = [];
        foreach ($studentDataForExcel as $dataRow) {
            $excelRow = [
                $dataRow['registration_no'],
                $dataRow['student_name'],
                $dataRow['status'],
                $dataRow['intake_semester'],
                $dataRow['course']
            ];

            foreach ($allWorkplacementDetailsGlobally as $wpdId => $wpdDetails) {

                if (!empty($wpdDetails['level_hours'])) {
                    foreach ($wpdDetails['level_hours'] as $lhId => $lhDetails) {
                        $excelRow[] = $dataRow['workplacement_level_learning_hours_data'][$wpdId]['level_hours_details'][$lhId]['main']['required'] ?? '';
                        $excelRow[] = $dataRow['workplacement_level_learning_hours_data'][$wpdId]['level_hours_details'][$lhId]['main']['completed'] ?? '';

                        if (!empty($lhDetails['learning_hours'])) {
                            foreach ($lhDetails['learning_hours'] as $learnhId => $learnhDetails) {
                                $excelRow[] = $dataRow['workplacement_level_learning_hours_data'][$wpdId]['level_hours_details'][$lhId]['learning_hours_details'][$learnhId]['required'] ?? '';
                                $excelRow[] = $dataRow['workplacement_level_learning_hours_data'][$wpdId]['level_hours_details'][$lhId]['learning_hours_details'][$learnhId]['completed'] ?? '';
                            }
                        }
                    }
                }
            }

            if ($hasUnassignedModuleHoursData) {
                $excelRow[] = $dataRow['unassigned_module_hours'] ?? '';
            }
            if (!empty($allModuleNamesSorted)) {
                foreach ($allModuleNamesSorted as $moduleName) {
                    $excelRow[] = $dataRow['modules'][$moduleName] ?? '';
                }
            }
            
            $excelRow[] = $dataRow['total_overall_wpd_required_hours_for_student'] > 0 ? $dataRow['total_overall_wpd_required_hours_for_student'] . ' Hours' : '';
            $excelRow[] = $dataRow['total_confirmed_work_hours'] > 0 ? $dataRow['total_confirmed_work_hours'] . ' Hours' : '0 Hours';
            $theCollection[] = $excelRow;
        }

        return Excel::download(new StudentWorkplacementReportExport($theCollection, $excelHeaders, $allModuleNamesSorted, [], $columnMergeData), 'student_workplacement_report.xlsx');
    }

}
