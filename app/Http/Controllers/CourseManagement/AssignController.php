<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationAvailability;
use App\Models\CourseCreationInstance;
use App\Models\CourseModule;
use App\Models\Group;
use App\Models\InstanceTerm;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentCourseRelation;
use App\Models\StudentGroupChangeHistory;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignController extends Controller
{
    public function index($acid, $tdid, $crid, $grid)
    {
        $statuses = Status::where('type', 'Student')->orderBy('Name', 'ASC')->get();
        $theGroup = Group::find($grid);
        $sameNameGroupIds = Group::where('term_declaration_id', $tdid)->where('course_id', $crid)
                            ->where('name', $theGroup->name)->pluck('id')->unique()->toArray();
        $modules = Plan::where('course_id', $crid)->where('term_declaration_id', $tdid)->where('academic_year_id', $acid)
                   ->whereIn('group_id', $sameNameGroupIds)->get();
        $planIds = $modules->pluck('id')->unique()->toArray();

        return view('pages.course-management.assign.index', [
            'title' => 'Course Management - London Churchill College',
            'subtitle' => 'Assign / Deassign',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
                ['label' => 'Assign / Deassign', 'href' => 'javascript:void(0);']
            ],
            'termDeclarations' => TermDeclaration::orderBy('id', 'DESC')->get(),
            'semesters' => Semester::orderBy('id', 'DESC')->get(),
            'statuses' => $statuses,

            'theAcademicYear' => AcademicYear::find($acid),
            'theTermDeclaration' => TermDeclaration::find($tdid),
            'theCourse' => Course::find($crid),
            'theGroup' => Group::find($grid),
            'selectedModules' => $modules,
            'selectedModuleIds' => $planIds,
            'existingStudents' => $this->getExistingStudentsList($planIds),
            'termDeclarations' => TermDeclaration::all()->sortByDesc('id'),
            'groups' => Group::all()->sortBy('name'),
            'otherGroup' => $this->getOtherAvailableGroups($acid, $tdid, $crid, $theGroup->name)
        ]);
    }

    public function getOtherAvailableGroups($academicYearId, $termDeclaredId, $courseId, $existGroupName){
        $allGroups = DB::table('plans')->select('groups.name')
            ->leftJoin('groups', 'plans.group_id', '=', 'groups.id')
            ->groupBy('groups.name')
            ->where('plans.academic_year_id', '=', $academicYearId)
            ->where('plans.term_declaration_id', '=', $termDeclaredId)
            ->where('plans.course_id', '=', $courseId)
            ->where('groups.course_id', '=', $courseId)
            ->where('groups.term_declaration_id', '=', $termDeclaredId)
            ->where('groups.name', '!=', $existGroupName)
            ->orderBy('groups.name','ASC')->get();

        $groups = [];
        if($allGroups->count() > 0):
            foreach($allGroups as $group):
                $groupName = $group->name;
                $theGroup = Group::where('name', $groupName)->where('course_id', $courseId)->where('term_declaration_id', $termDeclaredId)->orderBy('id', 'DESC')->get()->first();

                $groups[$theGroup->id] = $theGroup->name;
            endforeach;
        endif;

        return $groups;
    }

    public function unsignnedList(Request $request){
        $unsignedTerm = (isset($request->unsignedTerm) && !empty($request->unsignedTerm) ? $request->unsignedTerm : 0);
        $unsignedStatuses = (isset($request->unsignedStatuses) && !empty($request->unsignedStatuses) ? $request->unsignedStatuses : []);
        $unsigned_course_id = (isset($request->unsigned_course_id) && !empty($request->unsigned_course_id) ? $request->unsigned_course_id : 0);

        //$courseCreations = CourseCreation::where('semester_id', $unsignedTerm)->pluck('id')->unique()->toArray();
        $plan_ids = Plan::where('term_declaration_id', $unsignedTerm)->pluck('id')->unique()->toArray();
        $excludedStudentids = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();
        /*$excludedStudentids = DB::table('plans as p')
                              ->select('a.student_id')
                              ->leftJoin('assigns as a', 'p.id', '=', 'a.plan_id')
                              //->where('p.term_declaration_id', $unsignedTerm)
                              ->whereIn('p.course_creation_id', $courseCreations)
                              ->groupBy('a.student_id')
                              ->orderBy('a.student_id', 'ASC')
                              ->pluck('a.student_id')->unique()->toArray();*/

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 's_id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $f = explode('_', $sort['field']);
            $sorts[] = str_replace(['s_', 'c_'], ['s.', 'c.'], $sort['field']).' '.$sort['dir'];
        endforeach;
        $query = DB::table('students as s')
                 ->select('s.id', 's.registration_no', 'c.name as c_name', 'sts.name as sts_name')
                 ->leftJoin('statuses as sts', 's.status_id', '=', 'sts.id')
                 ->leftJoin('student_course_relations as scr', 's.id', '=', 'scr.student_id')
                 ->leftJoin('course_creations as cc', 'scr.course_creation_id', '=', 'cc.id')
                 ->leftJoin('courses as c', 'cc.course_id', '=', 'c.id')
                 ->whereIn('s.status_id', $unsignedStatuses)
                 ->whereNotIn('s.id', $excludedStudentids)
                 ->where('c.id', $unsigned_course_id)
                 ->orderByRaw(implode(',', $sorts));

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
            ->take($limit)
            ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $assign = Assign::where('student_id', $list->id)->orderBy('id', 'desc')->get()->first();
                $student = Student::find($list->id);
                $data[] = [
                    's_id' => $list->id,
                    'sl' => $i,
                    's_registration_no' => $list->registration_no,
                    'c_name' => isset($list->c_name) ? $list->c_name : '',
                    'group' => (isset($assign->plan->group->name) && !empty($assign->plan->group->name) ? $assign->plan->group->name : ''),
                    'group_ev_wk' => (isset($assign->id) && $assign->id > 0 ? (isset($assign->plan->group->evening_and_weekend) && $assign->plan->group->evening_and_weekend == 1 ? 'Yes' : 'No') : ''),
                    'std_ev_wk' => (isset($student->activeCR->propose->full_time) && $student->activeCR->propose->full_time == 1 ? 'Yes' : 'No'),
                    'sts_name' => (isset($list->sts_name) ? $list->sts_name : ''),
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'total_rows' => $total_rows]);                     
    }

    public function getExistingStudentsList($planIds){
        $res = [
            'count' => 0,
            'htm' => ''
        ];
        $student_ids = Assign::whereIn('plan_id', $planIds)->pluck('student_id')->unique()->toArray();
        $students = Student::whereIn('id', $student_ids)->orderBy('first_name', 'ASC')->get();

        if(!empty($student_ids) && $students->count() > 0):
            $res['count'] = $students->count();
            foreach($students as $std):
                $assignedTo = Assign::select('plan_id')->whereIn('plan_id', $planIds)->where('student_id', $std->id)->groupBy('plan_id')->pluck('plan_id')->unique()->toArray();
                $res['htm'] .= '<li data-studentid="'.$std->id.'" data-reg="'.$std->registration_no.'" data-name="'.$std->full_name.'">';
                    $res['htm'] .= '<input type="checkbox" name="assignedStudents[]" value="'.$std->id.'" id="assignedStudents_'.$std->id.'"/>';
                    $res['htm'] .= '<label for="assignedStudents_'.$std->id.'"><i data-lucide="arrow-right-circle" class="w-4 h-4 mr-1"></i>';
                        $res['htm'] .= $std->registration_no.' - '.$std->full_name;
                    $res['htm'] .= '</label>';
                    if(!empty($assignedTo)):
                        $res['htm'] .= '&nbsp;<a data-ids="'.implode(',', $assignedTo).'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#showAllModulesModal" class="font-medium text-primary showAllModules">('.count($assignedTo).')</a>';
                    endif;
                $res['htm'] .= '</li>';
            endforeach;
        endif;

        return $res;
    }

    public function getExistingStudentListByModule(Request $request){
        $moduleids = $request->moduleids;
        $res = $this->getExistingStudentsList($moduleids);

        return response()->json(['res' => $res], 200);
    }

    public function getPotentialStudentListBySearch(Request $request){
        $res = [
            'count' => 0,
            'htm' => ''
        ];

        $existingStudents = (isset($request->existingStudents) && !empty($request->existingStudents) ? $request->existingStudents : []);
        $theValue = $request->theValue;
        $students = Student::where(function($q) use ($theValue){
            $q->where('registration_no', 'LIKE', '%'.$theValue.'%')
                ->orWhere('first_name', 'LIKE', '%'.$theValue.'%')
                ->orWhere('last_name', 'LIKE', '%'.$theValue.'%');
        })->orderBy('first_name', 'ASC')->get();
        if($students->count() > 500):
            $res['count'] = $students->count();
            $res['htm'] .= '<li class="noticeItem">';
                $res['htm'] .= '<input type="checkbox" name="potentialStudents[]" value="0" id="potentialStudents_0"/>';
                $res['htm'] .= '<label for="potentialStudents_0"><i data-lucide="alert-octagon" class="w-4 h-4 mr-1 text-danger"></i> Too many students ('.$students->count().') to show, please use the search</label>';
            $res['htm'] .= '</li>';
        else:
            $statuses = Student::where(function($q) use ($theValue){
                            $q->where('registration_no', 'LIKE', '%'.$theValue.'%')
                                ->orWhere('first_name', 'LIKE', '%'.$theValue.'%')
                                ->orWhere('last_name', 'LIKE', '%'.$theValue.'%');
                        })->orderBy('status_id', 'ASC')->pluck('status_id')->unique()->toArray();
            
            if(!empty($statuses)):
                foreach($statuses as $sts):
                    $status = Status::find($sts);
                    $students = Student::where('status_id', $sts)->where(function($q) use ($theValue){
                                    $q->where('registration_no', 'LIKE', '%'.$theValue.'%')
                                        ->orWhere('first_name', 'LIKE', '%'.$theValue.'%')
                                        ->orWhere('last_name', 'LIKE', '%'.$theValue.'%');
                                })->orderBy('first_name', 'ASC')->get();
                    if(!empty($students) && $students->count() > 0):
                        $res['count'] += $students->count();
                        $res['htm'] .= '<li class="headingItem" data-status="'.$status->id.'">';
                            $res['htm'] .= '<label><i data-lucide="list-checks" class="w-4 h-4 mr-1"></i>'.$status->name.'</label>';
                        $res['htm'] .= '</li>';
                        foreach($students as $std):
                            $res['htm'] .= '<li class="'.(in_array($std->id, $existingStudents) ? 'existThere' : '').'" data-studentid="'.$std->id.'" data-reg="'.$std->registration_no.'" data-name="'.$std->full_name.'">';
                                $res['htm'] .= '<input type="checkbox" name="potentialStudents[]" value="'.$std->id.'" id="potentialStudents_'.$std->id.'"/>';
                                $res['htm'] .= '<label for="potentialStudents_'.$std->id.'"><i data-lucide="arrow-right-circle" class="w-4 h-4 mr-1"></i>'.$std->registration_no.' - '.$std->full_name.'</label>';
                            $res['htm'] .= '</li>';
                        endforeach;
                    endif;
                endforeach;
            endif;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function getPotentialStudentListFromUnsignedList(Request $request){
        $res = [
            'count' => 0,
            'htm' => ''
        ];
        $student_ids = (isset($request->student_ids) && !empty($request->student_ids) ? $request->student_ids : []);
        $existingStudents = (isset($request->existingStudents) && !empty($request->existingStudents) ? $request->existingStudents : []);
        if(!empty($student_ids)):
            $statuses = Student::whereIn('id', $student_ids)->orderBy('status_id', 'ASC')->pluck('status_id')->unique()->toArray();
            if(!empty($statuses)):
                foreach($statuses as $sts):
                    $status = Status::find($sts);
                    $students = Student::where('status_id', $sts)->whereIn('id', $student_ids)->orderBy('first_name', 'ASC')->get();
                    if(!empty($students) && $students->count() > 0):
                        $res['count'] += $students->count();
                        $res['htm'] .= '<li class="headingItem" data-status="'.$status->id.'">';
                            $res['htm'] .= '<label><i data-lucide="list-checks" class="w-4 h-4 mr-1"></i>'.$status->name.'</label>';
                        $res['htm'] .= '</li>';
                        foreach($students as $std):
                            $res['htm'] .= '<li class="'.(in_array($std->id, $existingStudents) ? 'existThere' : '').'" data-studentid="'.$std->id.'" data-reg="'.$std->registration_no.'" data-name="'.$std->full_name.'">';
                                $res['htm'] .= '<input type="checkbox" name="potentialStudents[]" value="'.$std->id.'" id="potentialStudents_'.$std->id.'"/>';
                                $res['htm'] .= '<label for="potentialStudents_'.$std->id.'"><i data-lucide="arrow-right-circle" class="w-4 h-4 mr-1"></i>'.$std->registration_no.' - '.$std->full_name.'</label>';
                            $res['htm'] .= '</li>';
                        endforeach;
                    endif;
                endforeach;
            endif;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function getGroupList(Request $request){
        $courseid = $request->assignToCourseId;
        $termdeclarationid = $request->termDeclarationId;

        $res = [];

        $groups = DB::table('groups')->select('name')->where('course_id', $courseid)->where('term_declaration_id', $termdeclarationid)
                  ->where('active', 1)
                  ->groupBy('name')->orderBy('name', 'ASC')->get();
        if(!$groups->isEmpty()):
            $i = 1;
            foreach($groups as $gr):
                $theGroup = Group::where('name', $gr->name)->where('course_id', $courseid)->where('term_declaration_id', $termdeclarationid)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
                $res[$i]['id'] = $theGroup->id;
                $res[$i]['name'] = $theGroup->name;
                $i++;
            endforeach;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function getModuleAndStudentList(Request $request){
        $courseid = $request->assignToCourseId;
        $termdeclarationid = $request->termDeclarationId;
        $groupid = $request->assignGroupId;
        $existingStudents = (isset($request->existingStudents) && !empty($request->existingStudents) ? $request->existingStudents : []);

        $res = [
            'module_html' => '',
            'modules' => [],
            'students' => [
                'count' => 0,
                'htm' => ''
            ]
        ];

        $theGroup = Group::find($groupid);
        $sameGroupIds = Group::where('name', $theGroup->name)->where('course_id', $courseid)->where('term_declaration_id', $termdeclarationid)
                        ->pluck('id')->unique()->toArray();
        
        $planIds = [];
        $modules = Plan::where('course_id', $courseid)->where('term_declaration_id', $termdeclarationid)->whereIn('group_id', $sameGroupIds)
                   ->orderBy('module_creation_id', 'ASC')->get();
        if(!empty($modules)):
            $i = 1;
            $res['module_html'] .= '<ul>';
            foreach($modules as $md):
                $planIds[] = $md->id;

                $res['modules'][$i]['id'] = $md->id;
                $res['modules'][$i]['name'] = (isset($md->creations->module_name) && !empty($md->creations->module_name) ? $md->creations->module_name : 'Unknown');

                $res['module_html'] .= '<li  class="potential_modules_'.$md->id.' active flex items-start mb-2"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> ';
                    $res['module_html'] .= (isset($md->creations->module_name) ? $md->creations->module_name : 'Unknown Module');
                    $res['module_html'] .= (isset($md->assign) ? '&nbsp;<strong>('.$md->assign->count().')</strong>' : '&nbsp;<strong>(0)</strong>');
                $res['module_html'] .= '</li>';
                $i++;
            endforeach;
            $res['module_html'] .= '</ul>';
        endif;

        if(!empty($planIds)):
            $student_ids = Assign::whereIn('plan_id', $planIds)->pluck('student_id')->unique()->toArray();
            if(!empty($student_ids) && count($student_ids) > 500):
                $res['students']['count'] = count($student_ids);
                $res['students']['htm'] .= '<li class="noticeItem">';
                    $res['students']['htm'] .= '<input type="checkbox" name="potentialStudents[]" value="0" id="potentialStudents_0"/>';
                    $res['students']['htm'] .= '<label for="potentialStudents_0"><i data-lucide="alert-octagon" class="w-4 h-4 mr-1 text-danger"></i> Too many students ('.count($student_ids).') to show, please use the search</label>';
                $res['students']['htm'] .= '</li>';
            elseif(!empty($student_ids) && count($student_ids) <= 500):
                $statuses = Student::whereIn('id', $student_ids)->orderBy('status_id', 'ASC')
                            ->pluck('status_id')->unique()->toArray();
    
                if(!empty($statuses)):
                    foreach($statuses as $sts):
                        $status = Status::find($sts);
                        $students = Student::where('status_id', $sts)->whereIn('id', $student_ids)->orderBy('first_name', 'ASC')->get();
                        if(!empty($students) && $students->count() > 0):
                            $res['students']['count'] += $students->count();
                            $res['students']['htm'] .= '<li class="headingItem" data-status="'.$status->id.'">';
                                $res['students']['htm'] .= '<label><i data-lucide="list-checks" class="w-4 h-4 mr-1"></i>'.$status->name.'</label>';
                            $res['students']['htm'] .= '</li>';
                            foreach($students as $std):
                                $assignedTo = Assign::select('plan_id')->whereIn('plan_id', $planIds)->where('student_id', $std->id)->groupBy('plan_id')->pluck('plan_id')->unique()->toArray();
                                $res['students']['htm'] .= '<li class="'.(in_array($std->id, $existingStudents) ? 'existThere' : '').'" data-studentid="'.$std->id.'" data-reg="'.$std->registration_no.'" data-name="'.$std->full_name.'">';
                                    $res['students']['htm'] .= '<input type="checkbox" name="potentialStudents[]" value="'.$std->id.'" id="potentialStudents_'.$std->id.'"/>';
                                    $res['students']['htm'] .= '<label for="potentialStudents_'.$std->id.'"><i data-lucide="arrow-right-circle" class="w-4 h-4 mr-1"></i>'.$std->registration_no.' - '.$std->full_name.'</label>';
                                    if(!empty($assignedTo)):
                                        $res['students']['htm'] .= '&nbsp;<a data-ids="'.implode(',', $assignedTo).'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#showAllModulesModal" class="font-medium text-primary showAllModules">('.count($assignedTo).')</a>';
                                    endif;
                                $res['students']['htm'] .= '</li>';
                            endforeach;
                        endif;
                    endforeach;
                endif;
            endif;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function getStudentListByModule(Request $request){
        $courseid = $request->assignToCourseId;
        $termdeclarationid = $request->termDeclarationId;
        $groupid = $request->assignGroupId;
        $moduleid = (isset($request->assignModuleId) && !empty($request->assignModuleId) && $request->assignModuleId > 0 ? [$request->assignModuleId] : []);
        $existingStudents = (isset($request->existingStudents) && !empty($request->existingStudents) ? $request->existingStudents : []);

        $res = [
            'count' => 0,
            'htm' => ''
        ];

        if(empty($moduleid)):
            $theGroup = Group::find($groupid);
            $sameGroupIds = Group::where('name', $theGroup->name)->where('course_id', $courseid)->where('term_declaration_id', $termdeclarationid)
                            ->pluck('id')->unique()->toArray();
            
            $moduleid = Plan::where('course_id', $courseid)->where('term_declaration_id', $termdeclarationid)->whereIn('group_id', $sameGroupIds)
                        ->pluck('id')->unique()->toArray();
        endif;

        $student_ids = Assign::whereIn('plan_id', $moduleid)->pluck('student_id')->unique()->toArray();
        if(!empty($student_ids) && count($student_ids) > 500):
            $res['count'] = count($student_ids);
            $res['htm'] .= '<li class="noticeItem">';
                $res['htm'] .= '<input type="checkbox" name="potentialStudents[]" value="0" id="potentialStudents_0"/>';
                $res['htm'] .= '<label for="potentialStudents_0"><i data-lucide="alert-octagon" class="w-4 h-4 mr-1 text-danger"></i> Too many students ('.count($student_ids).') to show, please use the search</label>';
            $res['htm'] .= '</li>';
        elseif(!empty($student_ids) && count($student_ids) <= 500):
            $statuses = Student::whereIn('id', $student_ids)->orderBy('status_id', 'ASC')
                        ->pluck('status_id')->unique()->toArray();

            if(!empty($statuses)):
                foreach($statuses as $sts):
                    $status = Status::find($sts);
                    $students = Student::where('status_id', $sts)->whereIn('id', $student_ids)->orderBy('first_name', 'ASC')->get();
                    if(!empty($students) && $students->count() > 0):
                        $res['count'] += $students->count();
                        $res['htm'] .= '<li class="headingItem" data-status="'.$status->id.'">';
                            $res['htm'] .= '<label><i data-lucide="list-checks" class="w-4 h-4 mr-1"></i>'.$status->name.'</label>';
                        $res['htm'] .= '</li>';
                        foreach($students as $std):
                            $assignedTo = Assign::select('plan_id')->whereIn('plan_id', $moduleid)->where('student_id', $std->id)->groupBy('plan_id')->pluck('plan_id')->unique()->toArray();
                            $res['htm'] .= '<li class="'.(in_array($std->id, $existingStudents) ? 'existThere' : '').'" data-studentid="'.$std->id.'" data-reg="'.$std->registration_no.'" data-name="'.$std->full_name.'">';
                                $res['htm'] .= '<input type="checkbox" name="potentialStudents[]" value="'.$std->id.'" id="potentialStudents_'.$std->id.'"/>';
                                $res['htm'] .= '<label for="potentialStudents_'.$std->id.'"><i data-lucide="arrow-right-circle" class="w-4 h-4 mr-1"></i>'.$std->registration_no.' - '.$std->full_name.'</label>';
                                if(!empty($assignedTo)):
                                    $res['htm'] .= '&nbsp;<a data-ids="'.implode(',', $assignedTo).'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#showAllModulesModal" class="font-medium text-primary showAllModules">('.count($assignedTo).')</a>';
                                endif;
                            $res['htm'] .= '</li>';
                        endforeach;
                    endif;
                endforeach;
            endif;
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function getModulListHtml(Request $request){
        $ids = (isset($request->ids) && !empty($request->ids) ? explode(',', $request->ids) : []);
        $html = '';
        if(!empty($ids)):
            $plans = Plan::whereIn('id', $ids)->get();
            if(!empty($plans)):
                $html .= '<ul>';
                    foreach($plans as $pln):
                        $html .= '<li class="flex items-start mb-2"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> '.(isset($pln->creations->module_name) ? $pln->creations->module_name : 'Unknown Module').'</li>';
                    endforeach;
                $html .= '</ul>';
            endif;
        endif;

        if(empty($html)):
            $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Module not found!</div>';
        endif;

        return response()->json(['res' => $html], 200);
    }

    public function assignStudentsToPlan(Request $request){
        $term_declaration_id = $request->term_declaration;
        $plans_id = (isset($request->plans_id) && !empty($request->plans_id) ? $request->plans_id : []);
        $students_id = (isset($request->students_id) && !empty($request->students_id) ? $request->students_id : []);

        $successids = [];
        $success = [
            'ids' => [],
            'htm' => ''
        ];
        $errors = [
            'ids' => [],
            'mod_ids' => []
        ];
        if(!empty($plans_id) && !empty($students_id)):
            foreach($plans_id as $plan):
                $thePlan = Plan::find($plan);
                foreach($students_id as $student):
                    $assigned = Assign::where('plan_id', $plan)->where('student_id', $student)->get()->count();
                    $theStudent = Student::find($student);
                    if($assigned > 0):
                        $errors['ids'][] = $theStudent->id;
                        $errors['mod_ids'][$thePlan->creations->module_name][] = $theStudent->registration_no;
                    else:
                        Assign::create([
                            'plan_id' => $plan,
                            'student_id' => $student,
                            'created_by' => auth()->user()->id
                        ]);

                        StudentAttendanceTermStatus::create([
                            'student_id' => $student,
                            'term_declaration_id' => $term_declaration_id,
                            'status_id' => $theStudent->status_id,
                            'created_by' => auth()->user()->id
                        ]);
                        $successids[] = $theStudent->id;
                    endif;
                endforeach;
            endforeach;
        endif;

        if(!empty($successids)):
            $successids = array_unique($successids);
            foreach($successids as $sid):
                $assignedTo = Assign::select('plan_id')->whereIn('plan_id', $plans_id)->where('student_id', $sid)->groupBy('plan_id')->pluck('plan_id')->unique()->toArray();
                $theStudent = Student::find($sid);
                $success['ids'][] = $theStudent->id;
                $success['htm'] .= '<li data-studentid="'.$theStudent->id.'" data-reg="'.$theStudent->registration_no.'" data-name="'.$theStudent->full_name.'">';
                    $success['htm'] .= '<input type="checkbox" name="assignedStudents[]" value="'.$theStudent->id.'" id="assignedStudents_'.$theStudent->id.'"/>';
                    $success['htm'] .= '<label for="assignedStudents_'.$theStudent->id.'"><i data-lucide="arrow-right-circle" class="w-4 h-4 mr-1"></i>';
                        $success['htm'] .= $theStudent->registration_no;
                    $success['htm'] .= '</label>';
                    if(!empty($assignedTo)):
                        $success['htm'] .= '&nbsp;<a data-ids="'.implode(',', $assignedTo).'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#showAllModulesModal" class="font-medium text-primary showAllModules">('.count($assignedTo).')</a>';
                    endif;
                $success['htm'] .= '</li>';
            endforeach;
        endif;

        return response()->json(['success' => $success, 'errors' => $errors], 200);
    }

    public function deassignStudentsFromPlan(Request $request){
        $term_declaration_id = $request->term_declaration;
        $plans_id = (isset($request->plans_id) && !empty($request->plans_id) ? $request->plans_id : []);
        $students_id = (isset($request->students_id) && !empty($request->students_id) ? $request->students_id : []);

        if(!empty($plans_id) && !empty($students_id)):
            foreach($plans_id as $plan):
                $assigns = Assign::where('plan_id', $plan)->whereIn('student_id', $students_id)->forceDelete();
                $termStatus = StudentAttendanceTermStatus::where('term_declaration_id', $term_declaration_id)->whereIn('student_id', $students_id)->forceDelete();
            endforeach;
        endif;

        $res = [];
        $statuses = Student::whereIn('id', $students_id)->orderBy('status_id', 'ASC')
                            ->pluck('status_id')->unique()->toArray();
        if(!empty($statuses)):
            foreach($statuses as $sts):
                $status = Status::find($sts);
                $students = Student::where('status_id', $sts)->whereIn('id', $students_id)->orderBy('first_name', 'ASC')->get();
                if(!empty($students) && $students->count() > 0):
                    $res[$sts]['heading'] = '';
                    $res[$sts]['heading'] .= '<li class="headingItem" data-status="'.$status->id.'">';
                        $res[$sts]['heading'] .= '<label><i data-lucide="list-checks" class="w-4 h-4 mr-1"></i>'.$status->name.'</label>';
                    $res[$sts]['heading'] .= '</li>';

                    $res[$sts]['htm'] = [];
                    foreach($students as $std):
                        $res[$sts]['htm'][$std->id] = '';
                        $res[$sts]['htm'][$std->id] .= '<li data-studentid="'.$std->id.'" data-reg="'.$std->registration_no.'" data-name="'.$std->full_name.'">';
                            $res[$sts]['htm'][$std->id] .= '<input type="checkbox" name="potentialStudents[]" value="'.$std->id.'" id="potentialStudents_'.$std->id.'"/>';
                            $res[$sts]['htm'][$std->id] .= '<label for="potentialStudents_'.$std->id.'"><i data-lucide="arrow-right-circle" class="w-4 h-4 mr-1"></i>'.$std->registration_no.' - '.$std->full_name.'</label>';
                        $res[$sts]['htm'][$std->id] .= '</li>';
                    endforeach;
                endif;
            endforeach;
        endif;

        return response()->json(['res' => $res], 200);
    }


    public function getModulesForReassign(Request $request){
        $academic_year_id = $request->academic_year_id;
        $term_declaration_id = $request->term_declaration_id;
        $course_id = $request->course_id;
        $old_group_id = $request->old_group_id;
        $new_group_id = $request->new_group_id;

        $theNewGroup = Group::find($new_group_id);
        $sameNameNewGroupIds = Group::where('term_declaration_id', $term_declaration_id)->where('course_id', $course_id)
                            ->where('name', $theNewGroup->name)->pluck('id')->unique()->toArray();
        $newModules = Plan::where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)->where('academic_year_id', $academic_year_id)
                   ->whereIn('group_id', $sameNameNewGroupIds)->get();

        $NM_HTML = '';
        if($newModules->count() > 0):
            $NM_HTML .= '<div class="relative">';
                if($newModules->count() > 0):
                    foreach($newModules as $smd):
                        $NM_HTML .= '<div class="form-check items-start mb-2">';
                            $NM_HTML .= '<input id="newAssigndModuleIds_'.$smd->id.'" class="form-check-input newAssigndModuleIds" name="newAssigndModuleIds['.$smd->creations->course_module_id.']['.$smd->class_type.'][]" type="checkbox" value="'.$smd->id.'">';
                            $NM_HTML .= '<label class="form-check-label" for="newAssigndModuleIds_'.$smd->id.'">';
                                $NM_HTML .= $smd->id.' - '.$smd->creations->module_name.(isset($smd->class_type) && !empty($smd->class_type) ? ' - '.$smd->class_type.' ' : '') . (isset($smd->assign) ? ' <strong>('.$smd->assign->count().')</strong>' : ' <strong>(0)</strong>');
                            $NM_HTML .= '</label>';
                        $NM_HTML .= '</div>';
                    endforeach;
                endif;
            $NM_HTML .= '</div>';
        else:
            $NM_HTML .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Module Not found for '.$theNewGroup->name.'</div>';
        endif;

        $theOldGroup = Group::find($old_group_id);
        $sameNameOldGroupIds = Group::where('term_declaration_id', $term_declaration_id)->where('course_id', $course_id)
                            ->where('name', $theOldGroup->name)->pluck('id')->unique()->toArray();
        $OldModules = Plan::where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)->where('academic_year_id', $academic_year_id)
                   ->whereIn('group_id', $sameNameOldGroupIds)->get();

        $OM_HTML = '';
        if($OldModules->count() > 0):
            $OM_HTML .= '<div class="relative">';
                if($OldModules->count() > 0):
                    foreach($OldModules as $smd):
                        $OM_HTML .= '<div class="form-check items-start mb-2">';
                            $OM_HTML .= '<input checked id="oldAssignedModuleIds_'.$smd->id.'" class="form-check-input oldAssignedModuleIds" name="oldAssignedModuleIds['.$smd->creations->course_module_id.']['.$smd->class_type.'][]" type="checkbox" value="'.$smd->id.'">';
                            $OM_HTML .= '<label class="form-check-label" for="oldAssignedModuleIds_'.$smd->id.'">';
                                $OM_HTML .= $smd->id.' - '.$smd->creations->module_name.(isset($smd->class_type) && !empty($smd->class_type) ? ' - '.$smd->class_type.' ' : '') . (isset($smd->assign) ? ' <strong>('.$smd->assign->count().')</strong>' : ' <strong>(0)</strong>');
                            $OM_HTML .= '</label>';
                        $OM_HTML .= '</div>';
                    endforeach;
                endif;
            $OM_HTML .= '</div>';
        else:
            $OM_HTML .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Module Not found for '.$theOldGroup->name.'</div>';
        endif;

        return response()->json(['og_name' => $theOldGroup->name, 'oldModules' => $OM_HTML, 'ng_name' => $theNewGroup->name, 'newModules' => $NM_HTML], 200);
    }

    public function reAssignStudentNewGroup(Request $request){
        $new_group_id = $request->new_group_id;

        $oldAssignedPlans = (isset($request->oldAssignedModuleIds) && !empty($request->oldAssignedModuleIds) ? $request->oldAssignedModuleIds : []);
        $newAssigndPlans = (isset($request->newAssigndModuleIds) && !empty($request->newAssigndModuleIds) ? $request->newAssigndModuleIds : []);
        $student_id = $request->student_id;
        $academic_year_id = $request->academic_year_id;
        $term_declaration_id = $request->term_declaration_id;
        $course_id = $request->course_id;
        $old_group_id = $request->group_id;

        $error = 0;
        $error_ids = [];
        if(!empty($oldAssignedPlans) && count($oldAssignedPlans) > 0):
            foreach($oldAssignedPlans as $module_id => $modules):
                foreach($modules as $classType => $plan_ids):
                    $attendanceCount = Attendance::whereIn('plan_id', $plan_ids)->where('student_id', $student_id)->get()->count();
                    if($attendanceCount > 0 && (!isset($newAssigndPlans[$module_id][$classType]) || (isset($newAssigndPlans[$module_id][$classType]) && count($plan_ids) != count($newAssigndPlans[$module_id][$classType])))):
                        $error += 1;
                        foreach($plan_ids as $plan_id):
                            $error_ids[] = $plan_id;
                        endforeach;
                    endif;
                endforeach;
            endforeach;
        endif;

        if($error == 0):
            if(!empty($oldAssignedPlans) && count($oldAssignedPlans) > 0):
                foreach($oldAssignedPlans as $module_id => $modules):
                    foreach($modules as $classType => $plan_ids):
                        $i = 0;
                        foreach($plan_ids as $plan_id):
                            $newPlanId = (isset($newAssigndPlans[$module_id][$classType][$i]) && $newAssigndPlans[$module_id][$classType][$i] > 0 ? $newAssigndPlans[$module_id][$classType][$i] : 0);
                            Assign::where('student_id', $student_id)->where('plan_id', $plan_id)->forceDelete();
                            
                            $attendances = Attendance::where('plan_id', $plan_id)->where('student_id', $student_id)->get();
                            if($attendances->count() > 0):
                                foreach($attendances as $atn):
                                    $data = [];
                                    $data['plan_id'] = $newPlanId;
                                    $data['prev_plan_id'] = $atn->plan_id;
                                    Attendance::where('id', $atn->id)->update($data);
                                endforeach;
                            endif;
                            $i++;
                        endforeach;
                    endforeach;
                endforeach;
            endif;

            foreach($newAssigndPlans as $module_id => $modules):
                foreach($modules as $classType => $plan_ids):
                    foreach($plan_ids as $plan_id):
                        $data = [];
                        $data['plan_id'] = $plan_id;
                        $data['student_id'] = $student_id;
                        $data['attendance'] = null;
                        $data['created_by'] = auth()->user()->id;

                        Assign::create($data);
                    endforeach;
                endforeach;
            endforeach;

            return response()->json(['message' => 'Student group successfully changed.'], 200);
        else:
            return response()->json(['message' => 'Error found. Please check module counts, Match class types, check attendance feeds. Correspondence Plan ids: '.implode(',', $error_ids)], 422);
        endif;
    }

}
