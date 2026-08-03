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
            // Redesigned Course Management shell. Every other screen in the
            // module already opts in the same way; see layout/course-top-menu.
            'layout' => 'course-top-menu',
            'title' => 'Assign / Deassign - London Churchill College',
            'subtitle' => 'Assign / Deassign',
            'cmPageTitle' => 'Assign / Deassign',
            'cmBackUrl' => route('class.plan'),
            'cmBackLabel' => 'Back to list',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
                ['label' => 'Class Plans', 'href' => route('class.plan')],
                ['label' => 'Assign / Deassign', 'href' => 'javascript:void(0);']
            ],
            'semesters' => Semester::orderBy('id', 'DESC')->get(),
            'statuses' => $statuses,

            'theAcademicYear' => AcademicYear::find($acid),
            'theTermDeclaration' => TermDeclaration::find($tdid),
            'theCourse' => Course::find($crid),
            'theGroup' => $theGroup,
            'selectedModules' => $modules,
            'selectedModuleIds' => $planIds,
            'existingStudents' => $this->getExistingStudentsList($planIds),
            'termDeclarations' => TermDeclaration::all()->sortByDesc('id'),
            'groups' => Group::all()->sortBy('name'),
            'otherGroup' => $this->getOtherAvailableGroups($acid, $tdid, $crid, $theGroup->name)
        ]);
    }

    /* ------------------------------------------------------------------ *
     * Markup helpers
     *
     * The two transfer columns are filled by the endpoints below, so the row
     * markup lives here rather than in the blade. A row is a <li> carrying the
     * student id plus a button; selection is held as `.is-picked` on the <li>,
     * which is what the Add / Remove / Re-Assign buttons read.
     *
     * Nothing here reuses a legacy class name. `datafuture.css` is loaded
     * app-wide and still carries the old screen's rules — `.addRemoveBtns` is
     * absolutely positioned, `.assignStudentsList li` repaints every row — and
     * they outrank single-class `cm-` selectors whatever the load order. Every
     * hook on this screen is therefore a `cm-` class or a `data-cm-*`
     * attribute that the old stylesheet cannot reach.
     * ------------------------------------------------------------------ */

    private function tickGlyph(){
        return '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>';
    }

    /**
     * One student row.
     *
     * $opts: locked => cannot be moved (already there, or on another course)
     *        note   => small line under the name explaining why
     *        flag   => the student has an attendance issue on this group
     *        modules => plan ids the student is assigned to, for the "(n)" chip
     */
    private function studentRowHtml($student, array $opts = []){
        $modules = (isset($opts['modules']) && !empty($opts['modules']) ? $opts['modules'] : []);
        $locked = !empty($opts['locked']);
        $flag = !empty($opts['flag']);
        $note = (isset($opts['note']) && $opts['note'] !== '' ? $opts['note'] : '');

        $initials = mb_strtoupper(mb_substr((string) $student->first_name, 0, 1).mb_substr((string) $student->last_name, 0, 1));
        if(trim($initials) === ''):
            $initials = mb_strtoupper(mb_substr((string) $student->registration_no, 0, 2));
        endif;

        // `has-count` reserves the room the "(n)" chip is absolutely positioned
        // into, so a long name can never run under it.
        $classes = 'cm-stu'.($locked ? ' is-locked' : '').($flag ? ' is-flagged' : '').(!empty($modules) ? ' has-count' : '');

        $htm = '<li class="'.$classes.'" data-cm-student="'.$student->id.'" data-cm-reg="'.e($student->registration_no).'" data-cm-name="'.e($student->full_name).'">';
            $htm .= '<button type="button" class="cm-stu__row"'.($locked ? ' disabled' : '').($flag ? ' title="One or more of this student\'s classes has an attendance issue"' : '').'>';
                $htm .= '<span class="cm-avatar" data-cm-tone="'.((int) $student->id % 6).'">'.e($initials).'</span>';
                $htm .= '<span class="cm-stu__copy">';
                    $htm .= '<span class="cm-stu__name"><strong>'.e($student->registration_no).'</strong> &middot; '.e($student->full_name).'</span>';
                    if($note !== ''):
                        $htm .= '<span class="cm-stu__note">'.e($note).'</span>';
                    endif;
                $htm .= '</span>';
                $htm .= '<span class="cm-stu__tick" aria-hidden="true">'.$this->tickGlyph().'</span>';
            $htm .= '</button>';
            if(!empty($modules)):
                $htm .= '<button type="button" class="cm-stu__count" data-cm-modules="'.implode(',', $modules).'" title="Show the modules this student is on">('.count($modules).')</button>';
            endif;
        $htm .= '</li>';

        return $htm;
    }

    /** The status caption the potential column groups students under. */
    private function studentHeadHtml($status, $count = 0){
        $htm = '<li class="cm-stugroup" data-cm-group="'.$status->id.'">';
            $htm .= '<span>'.e($status->name).'</span>';
            $htm .= '<span data-cm-group-count>'.$count.'</span>';
        $htm .= '</li>';

        return $htm;
    }

    /** Shown instead of the list when a search would return an unusable number. */
    private function studentNoticeHtml($count){
        $htm = '<li class="cm-stunotice">';
            $htm .= '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>';
            $htm .= '<span>Too many students ('.$count.') to show &mdash; narrow the search.</span>';
        $htm .= '</li>';

        return $htm;
    }

    /** The course a student is actually on, when it is not the one being assigned. */
    private function otherCourseNote($student, $courseid){
        $stdCourseId = (isset($student->activeCR->creation->course_id) && $student->activeCR->creation->course_id > 0 ? $student->activeCR->creation->course_id : 0);
        if($stdCourseId == $courseid):
            return ['locked' => false, 'note' => ''];
        endif;

        // The note is the course name on its own, as in the design — the red
        // treatment already says "this one cannot be moved".
        $name = (isset($student->activeCR->creation->course->name) && $student->activeCR->creation->course->name != '' ? $student->activeCR->creation->course->name : 'Another course');

        return ['locked' => true, 'note' => $name];
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
            ->whereNull('plans.deleted_at')
            ->whereNull('groups.deleted_at')
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
                 ->select('s.id', 's.registration_no', 's.first_name', 's.last_name', 'c.name as c_name', 'sts.name as sts_name')
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
                    's_first_name' => $list->first_name,
                    's_last_name' => $list->last_name,
                    // `photo_url` is an accessor on the model, so it comes off
                    // the record already loaded above rather than the raw row.
                    's_photo' => $student->photo_url ?? '',
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
                $checkAttendances = Assign::whereIn('plan_id', $planIds)->where('student_id', $std->id)->where('attendance', 0)->get()->count();
                $res['htm'] .= $this->studentRowHtml($std, [
                    'modules' => $assignedTo,
                    'flag' => $checkAttendances > 0,
                ]);
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
        $courseid = (isset($request->assignToCourseId) && $request->assignToCourseId > 0 ? $request->assignToCourseId : 0);
        $students = Student::where(function($q) use ($theValue){
            $q->where('registration_no', 'LIKE', '%'.$theValue.'%')
                ->orWhere('first_name', 'LIKE', '%'.$theValue.'%')
                ->orWhere('last_name', 'LIKE', '%'.$theValue.'%');
        })->orderBy('first_name', 'ASC')->get();
        if($students->count() > 500):
            $res['count'] = $students->count();
            $res['htm'] .= $this->studentNoticeHtml($students->count());
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
                        $res['htm'] .= $this->studentHeadHtml($status, $students->count());
                        foreach($students as $std):
                            $other = $this->otherCourseNote($std, $courseid);
                            $alreadyHere = in_array($std->id, $existingStudents);
                            $res['htm'] .= $this->studentRowHtml($std, [
                                'locked' => $alreadyHere || $other['locked'],
                                'note' => $alreadyHere ? 'Already in this group' : $other['note'],
                            ]);
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
        $courseid = (isset($request->assignToCourseId) && $request->assignToCourseId > 0 ? $request->assignToCourseId : 0);
        if(!empty($student_ids)):
            $statuses = Student::whereIn('id', $student_ids)->orderBy('status_id', 'ASC')->pluck('status_id')->unique()->toArray();
            if(!empty($statuses)):
                foreach($statuses as $sts):
                    $status = Status::find($sts);
                    $students = Student::where('status_id', $sts)->whereIn('id', $student_ids)->orderBy('first_name', 'ASC')->get();
                    if(!empty($students) && $students->count() > 0):
                        $res['count'] += $students->count();
                        $res['htm'] .= $this->studentHeadHtml($status, $students->count());
                        foreach($students as $std):
                            $other = $this->otherCourseNote($std, $courseid);
                            $alreadyHere = in_array($std->id, $existingStudents);
                            $res['htm'] .= $this->studentRowHtml($std, [
                                'locked' => $alreadyHere || $other['locked'],
                                'note' => $alreadyHere ? 'Already in this group' : $other['note'],
                            ]);
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
                $theGroup = Group::where('name', trim($gr->name))->where('course_id', $courseid)->where('term_declaration_id', $termdeclarationid)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
                if(isset($theGroup->id) && $theGroup->id > 0):
                    $res[$i]['id'] = $theGroup->id;
                    $res[$i]['name'] = $theGroup->name;
                    $i++;
                endif;
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
            $res['module_html'] .= '<ul class="cm-sidemods">';
            foreach($modules as $md):
                $planIds[] = $md->id;

                $name = (isset($md->creations->module_name) && !empty($md->creations->module_name) ? $md->creations->module_name : 'Unknown Module');
                $count = (isset($md->assign) ? $md->assign->count() : 0);

                $res['modules'][$i]['id'] = $md->id;
                $res['modules'][$i]['name'] = $name;

                // `is-on` is the default; picking a single module in the search
                // form dims the rest rather than removing them, so the group's
                // whole shape stays visible.
                $res['module_html'] .= '<li class="cm-sidemod is-on" data-cm-sidemod="'.$md->id.'">';
                    // Tone by position, so a list of similarly-named modules is
                    // still scannable down the column.
                    $res['module_html'] .= '<span class="cm-sidemod__tile" data-cm-tone="'.(($i - 1) % 6).'">'.e(mb_strtoupper(mb_substr($name, 0, 1))).'</span>';
                    $res['module_html'] .= '<span class="cm-sidemod__copy">';
                        $res['module_html'] .= '<span class="cm-sidemod__name">'.e($name).'</span>';
                        $res['module_html'] .= '<span class="cm-sidemod__count">'.$count.' '.($count === 1 ? 'student' : 'students').'</span>';
                    $res['module_html'] .= '</span>';
                $res['module_html'] .= '</li>';
                $i++;
            endforeach;
            $res['module_html'] .= '</ul>';
        endif;

        if(!empty($planIds)):
            $student_ids = Assign::whereIn('plan_id', $planIds)->pluck('student_id')->unique()->toArray();
            if(!empty($student_ids) && count($student_ids) > 500):
                $res['students']['count'] = count($student_ids);
                $res['students']['htm'] .= $this->studentNoticeHtml(count($student_ids));
            elseif(!empty($student_ids) && count($student_ids) <= 500):
                $statuses = Student::whereIn('id', $student_ids)->orderBy('status_id', 'ASC')
                            ->pluck('status_id')->unique()->toArray();

                if(!empty($statuses)):
                    foreach($statuses as $sts):
                        $status = Status::find($sts);
                        $students = Student::where('status_id', $sts)->whereIn('id', $student_ids)->orderBy('first_name', 'ASC')->get();
                        if(!empty($students) && $students->count() > 0):
                            $res['students']['count'] += $students->count();
                            $res['students']['htm'] .= $this->studentHeadHtml($status, $students->count());
                            foreach($students as $std):
                                $other = $this->otherCourseNote($std, $courseid);
                                $alreadyHere = in_array($std->id, $existingStudents);
                                $assignedTo = Assign::select('plan_id')->whereIn('plan_id', $planIds)->where('student_id', $std->id)->groupBy('plan_id')->pluck('plan_id')->unique()->toArray();
                                $res['students']['htm'] .= $this->studentRowHtml($std, [
                                    'locked' => $alreadyHere || $other['locked'],
                                    'note' => $alreadyHere ? 'Already in this group' : $other['note'],
                                    'modules' => $assignedTo,
                                ]);
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
            $res['htm'] .= $this->studentNoticeHtml(count($student_ids));
        elseif(!empty($student_ids) && count($student_ids) <= 500):
            $statuses = Student::whereIn('id', $student_ids)->orderBy('status_id', 'ASC')
                        ->pluck('status_id')->unique()->toArray();

            if(!empty($statuses)):
                foreach($statuses as $sts):
                    $status = Status::find($sts);
                    $students = Student::where('status_id', $sts)->whereIn('id', $student_ids)->orderBy('first_name', 'ASC')->get();
                    if(!empty($students) && $students->count() > 0):
                        $res['count'] += $students->count();
                        $res['htm'] .= $this->studentHeadHtml($status, $students->count());
                        foreach($students as $std):
                            $other = $this->otherCourseNote($std, $courseid);
                            $alreadyHere = in_array($std->id, $existingStudents);
                            $assignedTo = Assign::select('plan_id')->whereIn('plan_id', $moduleid)->where('student_id', $std->id)->groupBy('plan_id')->pluck('plan_id')->unique()->toArray();
                            $res['htm'] .= $this->studentRowHtml($std, [
                                'locked' => $alreadyHere,
                                'note' => $alreadyHere ? 'Already in this group' : $other['note'],
                                'modules' => $assignedTo,
                            ]);
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
            if($plans->count() > 0):
                $html .= '<ul class="cm-modlist">';
                    foreach($plans as $pln):
                        $html .= '<li>';
                            $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>';
                            $html .= '<span>'.e(isset($pln->creations->module_name) ? $pln->creations->module_name : 'Unknown Module');
                            $html .= (isset($pln->class_type) && !empty($pln->class_type) ? ' <em>'.e($pln->class_type).'</em>' : '');
                            $html .= '</span>';
                        $html .= '</li>';
                    endforeach;
                $html .= '</ul>';
            endif;
        endif;

        if(empty($html)):
            $html .= '<div class="cm-finder__note"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg> No modules found for this student.</div>';
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
                $success['htm'] .= $this->studentRowHtml($theStudent, ['modules' => $assignedTo]);
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
                    // The heading is only used when the potential column has no
                    // group for this status yet, so its count is the number of
                    // students arriving with it; the browser re-counts after the
                    // rows are spliced in.
                    $res[$sts]['heading'] = $this->studentHeadHtml($status, $students->count());

                    $res[$sts]['htm'] = [];
                    foreach($students as $std):
                        $res[$sts]['htm'][$std->id] = $this->studentRowHtml($std);
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
            $NM_HTML .= '<div class="cm-checkstack">';
                foreach($newModules as $smd):
                    $NM_HTML .= $this->reassignModuleCheck($smd, 'newAssigndModuleIds', false);
                endforeach;
            $NM_HTML .= '</div>';
        else:
            $NM_HTML .= '<div class="cm-finder__note"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><path d="M12 9v4M12 17h.01"></path></svg> No modules found for '.e($theNewGroup->name).'.</div>';
        endif;

        $theOldGroup = Group::find($old_group_id);
        $sameNameOldGroupIds = Group::where('term_declaration_id', $term_declaration_id)->where('course_id', $course_id)
                            ->where('name', $theOldGroup->name)->pluck('id')->unique()->toArray();
        $OldModules = Plan::where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)->where('academic_year_id', $academic_year_id)
                   ->whereIn('group_id', $sameNameOldGroupIds)->get();

        $OM_HTML = '';
        if($OldModules->count() > 0):
            $OM_HTML .= '<div class="cm-checkstack">';
                foreach($OldModules as $smd):
                    $OM_HTML .= $this->reassignModuleCheck($smd, 'oldAssignedModuleIds', true);
                endforeach;
            $OM_HTML .= '</div>';
        else:
            $OM_HTML .= '<div class="cm-finder__note"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><path d="M12 9v4M12 17h.01"></path></svg> No modules found for '.e($theOldGroup->name).'.</div>';
        endif;

        return response()->json(['og_name' => $theOldGroup->name, 'oldModules' => $OM_HTML, 'ng_name' => $theNewGroup->name, 'newModules' => $NM_HTML], 200);
    }

    /**
     * One module checkbox inside the re-assign dialog.
     *
     * The name keeps its `[course_module_id][class_type][]` shape: the endpoint
     * pairs the old and new selections by module and class type, so a theory
     * class can only ever be swapped for another theory class.
     */
    private function reassignModuleCheck($plan, $field, $checked){
        $count = (isset($plan->assign) ? $plan->assign->count() : 0);
        $id = $field.'_'.$plan->id;
        $hook = ($field === 'oldAssignedModuleIds' ? 'data-cm-oldmod' : 'data-cm-newmod');

        $htm = '<label class="cm-check" for="'.$id.'">';
            $htm .= '<input id="'.$id.'" class="cm-check__input" '.$hook.' name="'.$field.'['.$plan->creations->course_module_id.']['.$plan->class_type.'][]" type="checkbox" value="'.$plan->id.'"'.($checked ? ' checked' : '').'>';
            $htm .= '<span class="cm-check__box">'.$this->tickGlyph().'</span>';
            $htm .= '<span class="cm-check__label">';
                $htm .= '<span class="cm-check__id">#'.$plan->id.'</span> '.e($plan->creations->module_name);
                if(isset($plan->class_type) && !empty($plan->class_type)):
                    $htm .= ' <span class="cm-check__type">'.e($plan->class_type).'</span>';
                endif;
                $htm .= ' <span class="cm-check__count">'.$count.'</span>';
            $htm .= '</span>';
        $htm .= '</label>';

        return $htm;
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
                        $exist = Assign::where('plan_id', $plan_id)->where('student_id', $student_id)->get()->count();
                        if($exist == 0):
                            $data = [];
                            $data['plan_id'] = $plan_id;
                            $data['student_id'] = $student_id;
                            $data['attendance'] = null;
                            $data['created_by'] = auth()->user()->id;

                            Assign::create($data);
                        endif;
                    endforeach;
                endforeach;
            endforeach;

            return response()->json(['message' => 'Student group successfully changed.'], 200);
        else:
            return response()->json(['message' => 'Error found. Please check module counts, Match class types, check attendance feeds. Correspondence Plan ids: '.implode(',', $error_ids)], 422);
        endif;
    }

}