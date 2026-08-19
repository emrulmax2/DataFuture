<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignGroupLeaderRequest;
use App\Http\Requests\PlanAssignParticipantRequest;
use App\Http\Requests\PlansUpdateRequest;
use App\Http\Requests\StoreTutorialPlanRequest;
use App\Http\Requests\SyncTutorialRequest;
use App\Models\AcademicYear;
use App\Models\AssessmentPlan;
use App\Models\Assign;
use App\Models\BankHoliday;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\Group;
use App\Models\GroupLeader;
use App\Models\GroupLeaderLog;
use App\Models\InstanceTerm;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlanParticipant;
use App\Models\PlansDateList;
use App\Models\Result;
use App\Models\ResultComparison;
use App\Models\Room;
use App\Models\Student;
use App\Models\TermDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanTreeController extends Controller
{
    public function index()
    {
        $academicYears = DB::table('plans')
                ->select('academic_year_id')
                ->groupBy('academic_year_id')
                ->whereNull('plans.deleted_at')
                ->distinct()
                ->get();
        $yearPush = [];
        foreach($academicYears as $year):
            $yearPush[] = $year->academic_year_id;
        endforeach;       
        $staff = User::with('employee')->where('active', 1)->get()
            ->sortBy('full_name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return view('pages.course-management.plan.tree.index', [
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Plans - London Churchill College',
            'subtitle' => 'Class Plan - Tree View',
            'cmPageTitle' => 'Class Plan — Tree View',
            'cmBackUrl' => route('class.plan'),
            'cmBackLabel' => 'Back to Class Plans',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
                ['label' => 'Class Plans', 'href' => route('class.plan')],
                ['label' => 'Tree', 'href' => 'javascript:void(0);']
            ],
            'acyers' => AcademicYear::orderBy('from_date', 'DESC')->whereIn("id",$yearPush)->get(),
            'courses' => Course::all(),
            'terms' => InstanceTerm::all(),
            'room' => Room::all(),
            'group' => Group::all(),
            // Staff are shown by their employee name, not their login name, so
            // the employee comes with them (without it every option in every
            // dropdown lazy-loads one) and the sort follows what is displayed.
            'tutor' => $staff,
            'ptutor' => $staff,
            'users' => $staff,
        ]);
    }


    public function getAttenDanceSemester(Request $request){
        $academicYear = $request->academicyear;
        $years = AcademicYear::find($academicYear);
        $Query = DB::table('plans')
                ->select('term_declaration_id as id')
                ->groupBy('term_declaration_id')
                ->where('academic_year_id', $academicYear)
                ->whereNull('plans.deleted_at')
                ->distinct()
                ->get();

        $html = '';
        if(!empty($Query) && count($Query) > 0):
            $html .= '<ul class="cm-tree__child">';
            foreach($Query as $list):
                $TermDeclaration = TermDeclaration::find($list->id);
                $visibility = $this->getTermVisibility($academicYear, $list->id);

                $html .= '<li class="cm-tree__item">';
                    $html .= '<div class="cm-tree__line">';
                    $html .= '<button type="button" data-yearid="'.$academicYear.'" data-attendanceSemester="'.$list->id.'" class="cm-tree__row theTerm" data-cm-level="1">';
                        $html .= '<span class="cm-tree__mark" data-cm-mark>+</span>';
                        $html .= '<span class="cm-tree__label">'.e($TermDeclaration->name).'</span>';
                        $html .= '<span class="cm-tree__spin" data-cm-tree-spin hidden></span>';
                    $html .= '</button>';
                    $html .= $this->treeToolsHtml($academicYear, $list->id, '', '', $visibility, false);
                    $html .= '</div>';
                $html .= '</li>';
            endforeach;
            $html .= '</ul>';
        else:
            $html .= $this->treeEmptyHtml('No terms found for this year.');
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function getCourses(Request $request){
        $academicYearId = $request->academicYearId;
        $attendanceSemester = $request->attendanceSemester;
        
        $query = DB::table('courses')
                ->select('courses.id as id' , 'courses.name as name')
                ->leftJoin('plans', 'plans.course_id', '=', 'courses.id')
                ->where('plans.academic_year_id', '=', $academicYearId)
                ->where('plans.term_declaration_id', '=', $attendanceSemester);
        $Query = $query->distinct()->get();

        $html = '';
        if(!$Query->isEmpty()):
            $html .= '<ul class="cm-tree__child">';
            foreach($Query as $list):
                $visibility = $this->getCourseVisibility($academicYearId, $attendanceSemester, $list->id);

                $html .= '<li class="cm-tree__item">';
                    $html .= '<div class="cm-tree__line">';
                    $html .= '<button type="button" data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$attendanceSemester.'" data-courseid="'.$list->id.'" class="cm-tree__row theCourse" data-cm-level="2">';
                        $html .= '<span class="cm-tree__mark" data-cm-mark>+</span>';
                        $html .= '<span class="cm-tree__label">'.e($list->name).'</span>';
                        $html .= '<span class="cm-tree__spin" data-cm-tree-spin hidden></span>';
                    $html .= '</button>';
                    $html .= $this->treeToolsHtml($academicYearId, $attendanceSemester, $list->id, '', $visibility, false);
                    $html .= '</div>';
                $html .= '</li>';
            endforeach;
            $html .= '</ul>';
        else:
            $html .= $this->treeEmptyHtml('No courses found for this term.');
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function getGroups(Request $request){
        $courseId = $request->courseId;
        $termDeclaredId = $request->attendanceSemester;
        $academicYearId = $request->academicYearId;
        $course = Course::find($courseId);

        $query = DB::table('plans')->select('groups.name')
            ->leftJoin('groups', 'plans.group_id', '=', 'groups.id')
            ->groupBy('groups.name')
            ->whereNull('plans.deleted_at')
            ->whereNull('groups.deleted_at')
            ->where('plans.academic_year_id', '=', $academicYearId)
            ->where('plans.term_declaration_id', '=', $termDeclaredId)
            ->where('plans.course_id', '=', $courseId)
            ->where('groups.course_id', '=', $courseId)
            ->where('groups.term_declaration_id', '=', $termDeclaredId)
            ->orderBy('groups.name','ASC')->get();

        $html = '';
        if(!$query->isEmpty()):
            $html .= '<ul class="cm-tree__child" data-total-group="'.count($query).'">';
                foreach($query as $list):
                    $theGroup = Group::where('name', $list->name)->where('course_id', $courseId)
                        ->where('term_declaration_id', $termDeclaredId)->orderBy('id', 'DESC')->get()->first();
                    if (empty($theGroup)) {
                        continue;
                    }
                    $visibility = $this->getGroupVisibility($academicYearId, $termDeclaredId, $courseId, $theGroup->id);
                    $evening = ((int) $theGroup->evening_and_weekend === 1);

                    $html .= '<li class="cm-tree__item">';
                    $html .= '<div class="cm-tree__line cm-tree__line--leaf">';
                        $html .= '<button type="button" data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$termDeclaredId.'" data-courseid="'.$courseId.'" data-groupid="'.$theGroup->id.'" class="cm-tree__row cm-tree__row--leaf theGroup" data-cm-level="3">';
                            $html .= '<span class="cm-tree__mark cm-tree__mark--leaf" data-cm-mark>';
                                $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>';
                            $html .= '</span>';
                            $html .= '<span class="cm-tree__label">'.e($theGroup->name).'</span>';
                            // Evening groups run outside the normal week, which
                            // changes who can be assigned to them.
                            $html .= '<span class="cm-tree__eve '.($evening ? 'is-eve' : '').'" title="'.($evening ? 'Evening &amp; weekend' : 'Weekdays').'">';
                                $html .= ($evening
                                    ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a5 5 0 0 0-10 0M12 2v7M4.2 10.2l1.4 1.4M1 18h2M21 18h2M18.4 11.6l1.4-1.4M23 22H1M16 5l-4 4-4-4"></path></svg>'
                                    : '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"></path></svg>');
                            $html .= '</span>';
                            $html .= '<span class="cm-tree__spin" data-cm-tree-spin hidden></span>';
                        $html .= '</button>';
                        $html .= $this->treeToolsHtml($academicYearId, $termDeclaredId, $courseId, $theGroup->id, $visibility, true);
                    $html .= '</div>';
                    $html .= '</li>';
                endforeach;
            $html .= '</ul>';
        else:
            $html .= $this->treeEmptyHtml('No groups found for this course.');
        endif;

        return response()->json(['htm' => $html], 200);
    }

    /** The empty state a tree level falls back to. */
    private function treeEmptyHtml($message)
    {
        return '<ul class="cm-tree__child"><li class="cm-tree__empty">'.e($message).'</li></ul>';
    }

    /**
     * The visibility toggle every level carries, plus the settings menu that
     * only groups get (Assign Manager / Audit User).
     *
     * `visibility_1` / `visibility_0` stay on the button: the class is what
     * `updateVisibility` and the client both read to know the current state.
     */
    private function treeToolsHtml($year, $term, $course, $group, $visibility, $withMenu)
    {
        $on = ((int) $visibility === 1);
        $attrs = 'data-yearid="'.$year.'" data-attendanceSemester="'.$term.'" data-courseid="'.$course.'" data-groupid="'.$group.'"';

        $html = '<span class="cm-tree__tools">';
            $html .= '<button type="button" '.$attrs.' data-visibility="'.($on ? 0 : 1).'" title="'.($on ? 'Visible to students' : 'Hidden from students').'" class="cm-tree__tool visibilityBtn visibility_'.((int) $visibility).'">';
                $html .= ($on
                    ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
                    : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.24A9.1 9.1 0 0 1 12 4c7 0 10 8 10 8a18 18 0 0 1-2.16 3.19M6.61 6.61A18 18 0 0 0 2 12s3 8 10 8a9 9 0 0 0 5.39-1.61M2 2l20 20M14.12 14.12a3 3 0 1 1-4.24-4.24"></path></svg>');
            $html .= '</button>';

            if ($withMenu) {
                $html .= '<span class="dropdown">';
                    $html .= '<button type="button" class="dropdown-toggle cm-tree__tool" aria-expanded="false" data-tw-toggle="dropdown" title="Group settings">';
                        $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>';
                    $html .= '</button>';
                    $html .= '<span class="dropdown-menu cm-treemenu">';
                        $html .= '<ul class="dropdown-content">';
                            $html .= '<li><a href="javascript:void(0);" '.$attrs.' class="dropdown-item assignManager"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M19 8v6M22 11h-6"></path></svg>Assign Manager</a></li>';
                            $html .= '<li><a href="javascript:void(0);" '.$attrs.' class="dropdown-item assignCoOrdinator"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><path d="M16 11l2 2 4-4"></path></svg>Audit User</a></li>';
                        $html .= '</ul>';
                    $html .= '</span>';
                $html .= '</span>';
            }
        $html .= '</span>';

        return $html;
    }

    public function getModule(Request $request) {
        $courseId = $request->courseId;
        //$termId = $request->termId;
        $termDeclaredData = $request->attendancesemester;
        $academicYearId = $request->academicYearId;
        $groupId = $request->groupId;
        
        //$term = InstanceTerm::find($termId);
        $course = Course::find($courseId);
        $group = Group::find($groupId);
        $sameNameGroupIds = Group::where('term_declaration_id', $termDeclaredData)->where('course_id', $courseId)
                            ->where('name', $group->name)->pluck('id')->unique()->toArray();

        $termDeclaraion = TermDeclaration::find($termDeclaredData);
        //$termsModuleCreations = ModuleCreation::where('instance_term_id', $termId)->pluck('id')->unique()->toArray();
        $plans = Plan::where('course_id', $courseId)->where('term_declaration_id', $termDeclaredData)->where('academic_year_id', $academicYearId)
                        ->whereIn('group_id', $sameNameGroupIds)->get();
        
        $meta = [
            ['Term', ($termDeclaraion->name ?? '').' · '.($termDeclaraion->termType->name ?? ''), 'calendar'],
            ['Course', $course->name ?? '', 'book'],
            ['Group', $group->name ?? '', 'users'],
            ['Evening & Weekend', ((int) $group->evening_and_weekend === 1 ? 'Yes' : 'No'), 'alert'],
        ];

        $icons = [
            'calendar' => '<path d="M8 2v4M16 2v4M3 10h18"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect>',
            'book' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>',
            'alert' => '<circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path>',
        ];

        $html = '<div class="cm-card cm-treepanel">';
            $html .= '<div class="cm-treepanel__head">';
                $html .= '<div class="cm-treepanel__meta">';
                    foreach ($meta as $m):
                        $html .= '<div class="cm-meta">';
                            $html .= '<span class="cm-meta__icon" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">'.$icons[$m[2]].'</svg></span>';
                            $html .= '<div style="min-width:0;">';
                                $html .= '<div class="cm-meta__label">'.e($m[0]).'</div>';
                                $html .= '<div class="cm-meta__value">'.(trim($m[1], ' ·') !== '' ? e($m[1]) : '—').'</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    endforeach;

                    // Group leader is the one tile you can act on, so it brings
                    // its own assign / deassign / history controls with it.
                    $html .= $this->groupLeaderTileHtml($academicYearId, $termDeclaredData, $courseId, $group->id);
                $html .= '</div>';

                if($plans->count() > 0):
                    $html .= '<div class="cm-treepanel__actions">';
                        // Both appear only once rows are ticked, as in the design.
                        $html .= '<button type="button" id="generateDaysBtn" class="cm-btn cm-btn--go" hidden>';
                            $html .= '<svg style="display:none;" class="cm-spinner" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
                            $html .= '<svg data-cm-btn-icon width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect></svg>';
                            $html .= 'Generate Days';
                        $html .= '</button>';
                        $html .= '<button type="button" id="bulkCommunication" class="cm-btn cm-btn--bulk" hidden>';
                            $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path></svg>';
                            $html .= 'Bulk Communication';
                        $html .= '</button>';
                        $html .= '<a href="'.route('assign', [$academicYearId, $termDeclaredData, $courseId, $group->id]).'" id="assignStudent" class="cm-btn cm-btn--pill">';
                            $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M19 8v6M22 11h-6"></path></svg>';
                            $html .= 'Assign / Deassign Students';
                        $html .= '</a>';
                    $html .= '</div>';
                endif;
            $html .= '</div>';

            if($plans->count() > 0):
                $html .= '<div class="cm-tabulator-wrap">';
                    $html .= '<div id="classPlanTreeListTable" class="cm-tabulator" data-course="'.$courseId.'" data-attendanceSemester="'.$termDeclaredData.'" data-group="'.(!empty($sameNameGroupIds) ? implode(',', $sameNameGroupIds) : '0').'" data-year="'.$academicYearId.'"></div>';
                $html .= '</div>';
            else:
                $html .= '<div class="cm-finder__note" style="margin:0 26px 22px;">';
                    $html .= '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>';
                    $html .= 'No class plans found for this combination.';
                $html .= '</div>';
            endif;
        $html .= '</div>';

        return response()->json(['htm' => $html], 200);
    }

    public function list(Request $request){
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : 0);
        $group = (isset($request->group) && !empty($request->group) ? explode(',', $request->group) : [0]);
        $year = (isset($request->year) && !empty($request->year) ? $request->year : 0);
        $termDeclarion = (isset($request->attendanceSemester) && !empty($request->attendanceSemester) ? $request->attendanceSemester : 0);


        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Plan::orderByRaw(implode(',', $sorts))->where('parent_id', 0)->where('course_id', $courses)
                ->where('academic_year_id', $year)->where('term_declaration_id', $termDeclarion)
                ->whereIn('group_id', $group);

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        // 1, not '' — an empty string reaches Tabulator as NaN and breaks the pager.
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $day = '';
                if($list->sat == 1){
                    $day = 'Sat';
                }elseif($list->sun == 1){
                    $day = 'Sun';
                }elseif($list->mon == 1){
                    $day = 'Mon';
                }elseif($list->tue == 1){
                    $day = 'Tue';
                }elseif($list->wed == 1){
                    $day = 'Wed';
                }elseif($list->thu == 1){
                    $day = 'Thu';
                }elseif($list->fri == 1){
                    $day = 'Fri';
                }
                $iActiveStudentCount = 0;
                $studentDataSet = [];
                $assignStudentListForPlans = Assign::where('plan_id',$list->id)->get();
                foreach($assignStudentListForPlans as $assign):

                        if($assign->attendance!==0) {
                            $studentDataSet[] = $assign->student_id;
                            $iActiveStudentCount++;
                        }
                endforeach;

                $tutorialSet = [];
                if(isset($list->tutorial) && $list->tutorial->id > 0):
                    $tutorialSet['id'] = $list->tutorial->id;
                    $tutorialSet['parent_id'] = $list->tutorial->parent_id;
                    $tutorialSet['day'] = $list->tutorial->plan_day;
                    $tutorialSet['dates'] = (isset($list->tutorial->dates) && $list->tutorial->dates->count() > 0 ? $list->tutorial->dates->count() : 0);
                    $tutorialSet['time'] = (!empty($list->tutorial->start_time) ? date('H:i', strtotime($list->tutorial->start_time)) : '').' - '.(!empty($list->tutorial->end_time) ? date('H:i', strtotime($list->tutorial->end_time)) : '');
                    $tutorialSet['day_match'] = (isset($list->tutorial->generated_day_match) && $list->tutorial->generated_day_match ? 1 : 0);
                endif;

                $assesmentPlanByStaffAssesment = AssessmentPlan::where('plan_id', $list->id)->where('upload_user_type','staff')->where('is_it_final',1)->orderBy('created_at','DESC')->get()->first();
                $getAllAssessmentPlan = AssessmentPlan::where('plan_id', $list->id)->where('upload_user_type','staff')->where('is_it_final',1)->orderBy('created_at','DESC')->pluck('id')->toArray();
                $assesmentPlanByTutorAssesment = AssessmentPlan::where('plan_id', $list->id)->where('upload_user_type','personal_tutor')->where('is_it_final',1)->orderBy('created_at','DESC')->get()->first();
                $resultData = [];
                if(isset($assesmentPlanByStaffAssesment->id)) {
                    
                    $resultDataStudent = Result::whereIn('student_id',$studentDataSet)
                    ->whereIn('assessment_plan_id', $getAllAssessmentPlan)->where('plan_id',$list->id)->pluck('student_id')->unique()->toArray();
                    
                    $studentIds = Assign::where('plan_id', $list->id)->where(function($q){
                        $q->where('attendance', 1)->orWhereNull('attendance');
                    })->pluck('student_id')->toArray();
                   // Get the missing student IDs
                   $missingStudentIds = array_diff($studentIds,$resultDataStudent);
                   
                    // Do something with the missing student IDs
                    $SubmissionDone = count($missingStudentIds) <= 0 ? "Yes" : "No";
                    
                } else {

                    $SubmissionDone = "No";
                }

                if((isset(auth()->user()->priv()['result_management_staff']) && auth()->user()->priv()['result_management_staff'] == 1)) {
                        $submissionAvailable = isset($assesmentPlanByStaffAssesment->course_module_base_assesment_id) && isset($assesmentPlanByTutorAssesment->course_module_base_assesment_id) && $assesmentPlanByStaffAssesment->course_module_base_assesment_id == $assesmentPlanByTutorAssesment->course_module_base_assesment_id ? 1 : 0;
                        $uploadAssesment= 1;
                } else {
                    $submissionAvailable = 0;
                    $uploadAssesment= 0;
                }
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'parent_id' => $list->parent_id,
                    'course_id' => $list->course_id ,
                    'module_creation_id'=> $list->module_creation_id,
                    'module'=> isset($list->creations->module_name) ? $list->creations->module_name : '',
                    'room'=> (isset($list->venu->name) ? $list->venu->name : '').' - '.(isset($list->room->name) ? $list->room->name : ''),
                    'time'=> (!empty($list->start_time) ? date('H:i', strtotime($list->start_time)) : '').' - '.(!empty($list->end_time) ? date('H:i', strtotime($list->end_time)) : ''),
                    'module_enrollment_key'=> $list->module_enrollment_key,
                    'submission_date'=> $list->submission_date,
                    'tutor'=> (isset($list->tutor->name) ? $list->tutor->name : ''),
                    'personalTutor'=> (isset($list->tutorial->personalTutor->name) && !empty($list->tutorial->personalTutor->name) ? $list->tutorial->personalTutor->name : (isset($list->class_type) && ($list->class_type == 'Tutorial' || $list->class_type == 'Seminar' || $list->class_type == 'Practical') && isset($list->personalTutor->name) && !empty($list->personalTutor->name) ? $list->personalTutor->name : '')),
                    'virtual_room'=> $list->virtual_room,
                    'group'=> (isset($list->group->name) ? $list->group->name : ''),
                    'day'=> $day,
                    'day_match' => (isset($list->generated_day_match) && $list->generated_day_match ? 1 : 0),
                    'deleted_at' => $list->deleted_at,
                    'dates' => $list->dates->count() > 0 ? $list->dates->count() : 0,
                    'assigned_count' => $assignStudentListForPlans->count(),
                    'on_of_student' => $iActiveStudentCount.'/'.$assignStudentListForPlans->count(),
                    'class_type' => (isset($list->class_type) && !empty($list->class_type) ? $list->class_type : (isset($list->creations->class_type) && !empty($list->creations->class_type) ? $list->creations->class_type : '')),
                    'tutorial' => (!empty($tutorialSet) ? $tutorialSet : 0),
                    'child_id' => (isset($list->tutorial->id) && $list->tutorial->id > 0 ? $list->tutorial->id : 0),
                    'submissionAvailable' => $submissionAvailable,
                    'uploadAssesment' => $uploadAssesment,
                    'submissionDone' => isset($SubmissionDone) ? $SubmissionDone : "No",
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'total' => $total_rows, 'data' => $data]);
    }

    public function edit($id){
        $plan = Plan::where('id', $id)->first();
        $start_time = (!empty($plan->start_time) ? substr($plan->start_time, 0, 5) : '');
        $end_time = (!empty($plan->end_time) ? substr($plan->end_time, 0, 5) : '');
        $moduleCreations = ModuleCreation::where('instance_term_id', $plan->instance_term_id)->orderBy('module_name', 'ASC')->get();
        $modules = '<option value="">Please Select</option>';
        if(!empty($moduleCreations)):
            foreach($moduleCreations as $mods):
                $modules .= '<option '.($plan->module_creation_id == $mods->id ? 'selected' : '').' value="'.$mods->id.'">'.$mods->module_name.'</option>';
            endforeach;
        endif;

        $data = [];
        $data['term'] = (isset($plan->attenTerm->name) && !empty($plan->attenTerm->name) ? $plan->attenTerm->name : '---');
        $data['course'] = (isset($plan->course->name) ? $plan->course->name : '---');
        $data['group'] = (isset($plan->group->name) ? $plan->group->name : '---');
        $data['module'] = $plan->creations->module_name;
        $data['venue_id'] = $plan->venue_id;
        $data['rooms_id'] = $plan->rooms_id;
        $data['group_id'] = $plan->group_id;
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        //$data['module_enrollment_key'] = $plan->module_enrollment_key;
        $data['submission_date'] = $plan->submission_date;
        $data['tutor_id'] = $plan->tutor_id;
        $data['personal_tutor_id'] = $plan->personal_tutor_id;
        $data['virtual_room'] = $plan->virtual_room;
        $data['note'] = $plan->note;
        $data['class_type'] = (isset($plan->class_type) && !empty($plan->class_type) ? $plan->class_type : $plan->creations->class_type);
        $data['sat'] = $plan->sat;
        $data['sun'] = $plan->sun;
        $data['mon'] = $plan->mon;
        $data['tue'] = $plan->tue;
        $data['wed'] = $plan->wed;
        $data['thu'] = $plan->thu;
        $data['fri'] = $plan->fri;
        $data['modules'] = $modules;

        return response()->json(['plan' => $data], 200);
    }

    public function update(PlansUpdateRequest $request){
        $planID = $request->id;
        $classDay = $request->class_day;
        $start_time = !empty($request->start_time) ? $request->start_time.':00' : '';
        $end_time = !empty($request->end_time) ? $request->end_time.':00' : '';
        $submission_date = !empty($request->submission_date) ? date('Y-m-d', strtotime($request->submission_date)) : '';
        $room = ($request->rooms_id > 0 ? Room::find($request->rooms_id) : []);
        $day = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        $data = [];
        $data['venue_id'] = (isset($room->venue->id) ? $room->venue->id : null);
        $data['rooms_id'] = (isset($room->id) ? $room->id : null);
        //$data['group_id'] = $request->group_id;
        $data['module_creation_id'] = $request->module_creation_id;
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        foreach($day as $d):
            $data[$d] = ($d == $classDay ? 1 : 0);
        endforeach;
        $data['tutor_id'] = (isset($request->tutor_id) ? $request->tutor_id : null);
        $data['personal_tutor_id'] = (isset($request->personal_tutor_id) ? $request->personal_tutor_id : null);
        //$data['module_enrollment_key'] = (isset($request->module_enrollment_key) ? $request->module_enrollment_key : null);
        $data['virtual_room'] = (isset($request->virtual_room) ? $request->virtual_room : null);
        $data['note'] = (isset($request->note) ? $request->note : null);
        $data['submission_date'] = (isset($request->submission_date) && !empty($request->submission_date) ? date('Y-m-d', strtotime($request->submission_date)) : null);
        $data['updated_by'] = auth()->user()->id;
        $data['class_type'] = (isset($request->class_type) ? $request->class_type : null);

        $plan = Plan::where('id', $planID)->update($data);
        if($plan):
            return response()->json(['msg' => 'Successfully updated!'], 200);
        else:
            return response()->json(['msg' => 'Error Found'], 422);
        endif;
    }

    public function destroy($id){
        $plan = Plan::find($id)->delete();
        return response()->json($plan);
    }

    public function restore($id) {
        $data = Plan::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function getAssignDetails(Request $request){
        $type = $request->type;

        $yearid = $request->yearid;
        $ACYear = AcademicYear::find($yearid);

        $termid = $request->termid;
        $term = TermDeclaration::find($termid);

        $courseid = $request->courseid;
        $course = Course::find($courseid);

        $groupid = $request->groupid;
        $group = Group::find($groupid);
        $sameNameGroupIds = Group::where('term_declaration_id', $termid)->where('course_id', $courseid)
                            ->where('name', $group->name)->pluck('id')->unique()->toArray();

        $title = '';
        $title .= '<u>'.$ACYear->name.'</u> > ';
        $title .= '<u>'.$term->name.'</u> > ';
        $title .= '<u>'.$course->name.'</u>';
        $title .= (isset($group->name) && !empty($group->name) ? ' > <u>'.$group->name.'</u>' : '');

        $planIds = Plan::orderBy('id', 'ASC')->where('course_id', $courseid)->where('academic_year_id', $yearid)
                ->where('term_declaration_id', $termid)
                ->whereIn('group_id', $sameNameGroupIds)
                ->pluck('id')->unique()->toArray();

        $userIds = [];
        if(!empty($planIds)):
            $userIds = PlanParticipant::whereIn('plan_id', $planIds)->where('type', $type)->pluck('user_id')->unique()->toArray();
        endif;

        $title .= ' > Assign <u>'.($type == 'Auditor' ? 'Audit User' : 'Manager').'</u>';
        return response()->json(['plans' => $planIds, 'participants' => $userIds, 'title' => $title], 200);
    }

    public function assignParticipants(PlanAssignParticipantRequest $request){
        $assigned_user_ids = $request->assigned_user_ids;
        $plan_ids = !empty($request->plan_ids) ? explode(',', $request->plan_ids) : [];
        $type = (isset($request->type) && !empty($request->type) ? $request->type : 'Manager');

        if(!empty($plan_ids) && !empty($assigned_user_ids)):
            foreach($plan_ids as $pid):
                $deleteParticipants = PlanParticipant::where('plan_id', $pid)->where('type', $type)->forceDelete();

                foreach($assigned_user_ids as $uid):
                    $data = [];
                    $data['plan_id'] = $pid;
                    $data['user_id'] = $uid;
                    $data['type'] = $type;
                    $data['created_by'] = auth()->user()->id;

                    PlanParticipant::create($data);
                endforeach;
            endforeach;
            return response()->json(['message' => 'Participants successfully assigned.'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        endif;
    }

    /**
     * The group ids the tree treats as one node.
     *
     * A group node is (course + term + name); the same name can exist as more
     * than one `groups` row, and the plan list already unions them, so leader
     * assignments have to cover the whole set too.
     */
    private function sameNameGroupIds($courseid, $termid, $groupid)
    {
        $group = Group::find($groupid);
        if (empty($group)) {
            return [];
        }

        return Group::where('term_declaration_id', $termid)
            ->where('course_id', $courseid)
            ->where('name', $group->name)
            ->pluck('id')->unique()->toArray();
    }

    /**
     * Refuses an action the signed-in user lacks the privilege for.
     *
     * The tile already hides the control, but hiding is not enforcing: these
     * endpoints are reachable directly, so each one re-checks its own key.
     * Returns the failure rather than throwing so the caller can answer with
     * the JSON shape its dialog expects.
     */
    private function groupLeaderDenied(string $action)
    {
        return response()->json([
            'message' => 'You are not permitted to '.$action.' group leaders.',
        ], 403);
    }

    /** Null when the action is allowed, otherwise the 403 to return. */
    private function groupLeaderDenial(string $action)
    {
        return GroupLeader::can($action) ? null : $this->groupLeaderDenied($action);
    }

    /**
     * Who currently leads the group behind a tree node, plus the trail the
     * modal shows as its eyebrow.
     */
    public function getGroupLeaderDetails(Request $request)
    {
        // Opening the picker is the first half of assigning or editing.
        if (!GroupLeader::can('add') && !GroupLeader::can('edit')) {
            return $this->groupLeaderDenied('assign');
        }

        $yearid = $request->yearid;
        $termid = $request->termid;
        $courseid = $request->courseid;
        $groupid = $request->groupid;

        $ACYear = AcademicYear::find($yearid);
        $term = TermDeclaration::find($termid);
        $course = Course::find($courseid);
        $group = Group::find($groupid);

        $title = '';
        $title .= '<u>'.($ACYear->name ?? '').'</u> > ';
        $title .= '<u>'.($term->name ?? '').'</u> > ';
        $title .= '<u>'.($course->name ?? '').'</u>';
        $title .= (isset($group->name) && !empty($group->name) ? ' > <u>'.$group->name.'</u>' : '');

        $groupIds = $this->sameNameGroupIds($courseid, $termid, $groupid);
        $leaders = !empty($groupIds)
            ? GroupLeader::whereIn('group_id', $groupIds)->where('term_declaration_id', $termid)
                ->pluck('user_id')->unique()->values()->toArray()
            : [];

        return response()->json(['leaders' => $leaders, 'title' => $title], 200);
    }

    /**
     * Replaces the group's leaders with whoever the modal sent.
     *
     * Written once per same-name group id so the dashboard can pivot straight
     * off `group_id` without re-deriving the name set. Old rows are removed
     * outright rather than soft-deleted; the audit trail lives in
     * `group_leader_logs`, which is what the history dialog reads.
     */
    public function assignGroupLeader(AssignGroupLeaderRequest $request)
    {
        $yearid = (int) $request->yearid;
        $termid = (int) $request->termid;
        $courseid = (int) $request->courseid;
        $groupid = (int) $request->groupid;
        $userIds = array_values(array_unique(array_map('intval', (array) $request->group_leader_ids)));

        $groupIds = $this->sameNameGroupIds($courseid, $termid, $groupid);
        $group = Group::find($groupid);
        if (empty($groupIds) || empty($group)) {
            return response()->json(['message' => 'The group could not be found.'], 422);
        }

        // Only what actually changed is logged, so re-saving an unchanged
        // selection does not fill the history with noise.
        $before = GroupLeader::whereIn('group_id', $groupIds)->where('term_declaration_id', $termid)
            ->pluck('user_id')->unique()->map(function ($id) { return (int) $id; })->toArray();

        $added = array_values(array_diff($userIds, $before));
        $removed = array_values(array_diff($before, $userIds));

        // Each half of the change carries its own key, and touching a group
        // that already has leaders is an edit whichever way it moves. Checking
        // the diff rather than the request means a save that changes nothing
        // never trips a privilege the user does not need.
        if (!empty($before) && (!empty($added) || !empty($removed))) {
            if ($denied = $this->groupLeaderDenial('edit')) {
                return $denied;
            }
        }
        if (!empty($added) && ($denied = $this->groupLeaderDenial('add'))) {
            return $denied;
        }
        if (!empty($removed) && ($denied = $this->groupLeaderDenial('delete'))) {
            return $denied;
        }

        DB::transaction(function () use ($groupIds, $termid, $yearid, $courseid, $userIds, $added, $removed, $group) {
            GroupLeader::whereIn('group_id', $groupIds)->where('term_declaration_id', $termid)->forceDelete();

            foreach ($groupIds as $gid) {
                foreach ($userIds as $uid) {
                    GroupLeader::create([
                        'user_id' => $uid,
                        'academic_year_id' => $yearid,
                        'term_declaration_id' => $termid,
                        'course_id' => $courseid,
                        'group_id' => $gid,
                        'created_by' => auth()->user()->id,
                    ]);
                }
            }

            $scope = [
                'academic_year_id' => $yearid,
                'term_declaration_id' => $termid,
                'course_id' => $courseid,
            ];

            foreach (User::whereIn('id', $added)->get() as $user) {
                GroupLeaderLog::record('Assigned', $user, $group, $scope);
            }
            foreach (User::whereIn('id', $removed)->get() as $user) {
                GroupLeaderLog::record('Deassigned', $user, $group, $scope);
            }
        });

        return response()->json([
            'message' => $this->assignmentMessage(count($added), count($removed)),
            'leaders' => $this->groupLeaderTileHtml($yearid, $termid, $courseid, $groupid),
        ], 200);
    }

    /** Says what the save actually did, rather than a flat "saved". */
    private function assignmentMessage(int $added, int $removed): string
    {
        if ($added === 0 && $removed === 0) {
            return 'No changes were made.';
        }

        $parts = [];
        if ($added > 0) {
            $parts[] = $added.' '.($added === 1 ? 'leader' : 'leaders').' assigned';
        }
        if ($removed > 0) {
            $parts[] = $removed.' '.($removed === 1 ? 'leader' : 'leaders').' deassigned';
        }

        return ucfirst(implode(' and ', $parts)).'.';
    }

    /** Removes one leader from a group without touching the others. */
    public function deassignGroupLeader(Request $request)
    {
        if ($denied = $this->groupLeaderDenial('delete')) {
            return $denied;
        }

        $yearid = (int) $request->yearid;
        $termid = (int) $request->termid;
        $courseid = (int) $request->courseid;
        $groupid = (int) $request->groupid;
        $userid = (int) $request->userid;

        $groupIds = $this->sameNameGroupIds($courseid, $termid, $groupid);
        $group = Group::find($groupid);
        $user = User::find($userid);

        if (empty($groupIds) || empty($group) || empty($user)) {
            return response()->json(['message' => 'That group leader could not be found.'], 422);
        }

        $removed = GroupLeader::whereIn('group_id', $groupIds)->where('term_declaration_id', $termid)
            ->where('user_id', $userid)->forceDelete();

        if ($removed) {
            GroupLeaderLog::record('Deassigned', $user, $group, [
                'academic_year_id' => $yearid,
                'term_declaration_id' => $termid,
                'course_id' => $courseid,
            ]);
        }

        return response()->json([
            'message' => $user->full_name.' is no longer a group leader for '.$group->name.'.',
            'leaders' => $this->groupLeaderTileHtml($yearid, $termid, $courseid, $groupid),
        ], 200);
    }

    /** The assignment history behind the log dialog, newest first. */
    public function getGroupLeaderLogs(Request $request)
    {
        if ($denied = $this->groupLeaderDenial('view')) {
            return $denied;
        }

        $termid = (int) $request->termid;
        $courseid = (int) $request->courseid;
        $groupid = (int) $request->groupid;

        $groupIds = $this->sameNameGroupIds($courseid, $termid, $groupid);
        $group = Group::find($groupid);

        $logs = !empty($groupIds)
            ? GroupLeaderLog::whereIn('group_id', $groupIds)->where('term_declaration_id', $termid)
                ->orderBy('id', 'DESC')->get()
            : collect();

        $html = '';
        if ($logs->count() > 0) {
            $html .= '<ul class="cm-timeline">';
            foreach ($logs as $log) {
                $on = ($log->action === 'Assigned');

                $html .= '<li class="cm-timeline__item '.($on ? 'is-on' : 'is-off').'">';
                    $html .= '<span class="cm-timeline__dot" aria-hidden="true">';
                        $html .= ($on
                            ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>'
                            : '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>');
                    $html .= '</span>';
                    $html .= '<div class="cm-timeline__body">';
                        $html .= '<div class="cm-timeline__title">'.e($log->user_name ?: ($log->user->full_name ?? 'Unknown user')).'</div>';
                        $html .= '<div class="cm-timeline__meta">';
                            $html .= '<span class="cm-timeline__tag">'.e($log->action).'</span>';
                            $html .= '<span>'.e($log->created_at ? $log->created_at->format('d M Y, H:i') : '').'</span>';
                            if (!empty($log->performed_by_name)) {
                                $html .= '<span>by '.e($log->performed_by_name).'</span>';
                            }
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<div class="cm-finder__note" style="margin:0;">';
                $html .= '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>';
                $html .= 'No group leader has been assigned or deassigned for this group yet.';
            $html .= '</div>';
        }

        return response()->json([
            'htm' => $html,
            'title' => 'Group Leader History',
            'subtitle' => ($group->name ?? ''),
        ], 200);
    }

    /**
     * The Group Leader tile in the plan panel header.
     *
     * It carries its own controls rather than living in the tree's settings
     * menu, so it is rebuilt and swapped in place after every assign or
     * deassign — which is why it is a method and not inline markup.
     */
    private function groupLeaderTileHtml($year, $term, $course, $group)
    {
        $groupIds = $this->sameNameGroupIds($course, $term, $group);
        $leaders = !empty($groupIds)
            ? GroupLeader::with('user.employee')->whereIn('group_id', $groupIds)
                ->where('term_declaration_id', $term)->get()
                ->pluck('user')->filter()->unique('id')->values()
            : collect();

        $attrs = 'data-yearid="'.$year.'" data-attendanceSemester="'.$term.'" data-courseid="'.$course.'" data-groupid="'.$group.'"';

        // Which controls appear is a privilege question, and the endpoints
        // behind them re-check the same keys — this only decides what is worth
        // showing. Changing an existing set is an edit, so a group that already
        // has leaders needs `edit` rather than `add` to open the modal.
        $canAssign = $leaders->isEmpty()
            ? GroupLeader::can('add')
            : (GroupLeader::can('add') || GroupLeader::can('edit'));
        $canDelete = GroupLeader::can('delete');
        $canViewLog = GroupLeader::can('view');

        $html = '<div class="cm-meta cm-meta--leader" data-cm-leader-tile '.$attrs.'>';
            $html .= '<span class="cm-meta__icon" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M19 2l1.4 2.9 3.1.5-2.3 2.2.6 3.1L19 9.2l-2.8 1.5.6-3.1-2.3-2.2 3.1-.5z"></path></svg></span>';
            $html .= '<div style="min-width:0;">';
                $html .= '<div class="cm-meta__label cm-meta__label--tools">';
                    $html .= '<span>Group Leader</span>';
                    if ($canAssign || $canViewLog) {
                        $html .= '<span class="cm-metatools">';
                            if ($canAssign) {
                                $html .= '<button type="button" '.$attrs.' class="cm-metatool assignGroupLeader" title="'.($leaders->isEmpty() ? 'Assign group leader' : 'Change group leaders').'">';
                                    $html .= '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M19 8v6M22 11h-6"></path></svg>';
                                $html .= '</button>';
                            }
                            if ($canViewLog) {
                                $html .= '<button type="button" '.$attrs.' class="cm-metatool groupLeaderLog" title="Assignment history">';
                                    $html .= '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"></path><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"></path><path d="M12 7v5l4 2"></path></svg>';
                                $html .= '</button>';
                            }
                        $html .= '</span>';
                    }
                $html .= '</div>';

                $html .= '<div class="cm-meta__value">';
                    if ($leaders->count() > 0) {
                        $html .= '<span class="cm-leaders">';
                        foreach ($leaders as $leader) {
                            $html .= '<span class="cm-leader">';
                                $html .= '<span class="cm-leader__name">'.e($leader->full_name).'</span>';
                                if ($canDelete) {
                                    $html .= '<button type="button" '.$attrs.' data-userid="'.$leader->id.'" data-username="'.e($leader->full_name).'" class="cm-leader__x deassignGroupLeader" title="Deassign '.e($leader->full_name).'">';
                                        $html .= '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>';
                                    $html .= '</button>';
                                }
                            $html .= '</span>';
                        }
                        $html .= '</span>';
                    } else {
                        $html .= '<span class="cm-leaders__empty">Not assigned</span>';
                    }
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function getTermVisibility($academicYear, $termDeclarationId){
        $query = DB::table('courses')
                ->select('courses.id as id')
                ->leftJoin('plans', 'plans.course_id', '=', 'courses.id')
                ->where('plans.academic_year_id', '=', $academicYear)
                ->where('plans.term_declaration_id', '=', $termDeclarationId)
                ->distinct()->get();
        $courseid = [];
        if(!empty($query)):
            foreach($query as $q):
                $courseid[] = $q->id;
            endforeach;
        endif;

        $query = Plan::orderBy('id', 'ASC')->where('academic_year_id', $academicYear)->where('term_declaration_id', $termDeclarationId);
        if(!empty($courseid)):
            $query->whereIn('course_id', $courseid);
        endif;
        $Query = $query->where('visibility', 1)->get();

        return ($Query->count() > 0 ? 1 : 0);
    }

    public function getCourseVisibility($academicYear, $termDeclarationId, $courseid){
        $query = Plan::orderBy('id', 'ASC')->where('academic_year_id', $academicYear)->where('term_declaration_id', $termDeclarationId)
                ->where('course_id', $courseid)->where('visibility', 1)->get();

        return ($query->count() > 0 ? 1 : 0);
    }

    public function getGroupVisibility($academicYear, $termDeclaredId, $courseid, $groupid){
        $group_ids = [];
        if($groupid && $groupid > 0):
            $group = Group::find($groupid);
            $group_ids = Group::where('term_declaration_id', $termDeclaredId)->where('course_id', $courseid)
                        ->where('name', $group->name)->pluck('id')->unique()->toArray();
        endif;


        $query = Plan::orderBy('id', 'ASC')->where('academic_year_id', $academicYear)->where('term_declaration_id', $termDeclaredId);
        if($courseid && $courseid > 0): $query->where('course_id', $courseid); endif;
        if(!empty($group_ids)): $query->whereIn('group_id', $group_ids); endif;
        $query->where('visibility', 1)->get();

        return ($query->count() > 0 ? 1 : 0);
    }

    public function updateVisibility(Request $request){
        $yearid = $request->yearid;
        $attendancesemester = $request->attendancesemester;
        $courseid = $request->courseid;
        $groupid = $request->groupid;
        $visibility = $request->visibility;

        $courseids = [];
        if(!$courseid || empty($courseid)):
            $query = DB::table('courses')->select('courses.id as id')
                ->leftJoin('plans', 'plans.course_id', '=', 'courses.id')
                ->where('plans.academic_year_id', '=', $yearid)
                ->where('plans.term_declaration_id', '=', $attendancesemester)
                ->distinct()->get();
            if(!empty($query)):
                foreach($query as $q):
                    $courseid[] = $q->id;
                endforeach;
            endif;
        else:
            $courseids[] = (int) $courseid;
        endif;
        if(!$groupid || empty($groupid)):
            $query = Group::where('term_declaration_id', $attendancesemester);
            if(!empty($courseids)): $query->whereIn('course_id', $courseids); endif;
            $groupids = $query->pluck('id')->unique()->toArray();
        else:
            $group = Group::find($groupid);
            $groupids = Group::where('term_declaration_id', $attendancesemester)->whereIn('course_id', $courseids)
                                ->where('name', $group->name)->pluck('id')->unique()->toArray();
        endif;

        
        $query = Plan::orderBy('id', 'ASC')->where('academic_year_id', $yearid)->where('term_declaration_id', $attendancesemester);
        if(!empty($courseids)): $query->whereIn('course_id', $courseids); endif;
        if(!empty($courseids)): $query->whereIn('group_id', $groupids); endif;
        $planIds = $query->pluck('id')->unique()->toArray();

        if(!empty($planIds)):
            foreach($planIds as $pid):
                $plan = Plan::find($pid);

                $data = [];
                $data['visibility'] = $visibility;
                $data['updated_by'] = auth()->user()->id;

                Plan::where('id', $pid)->update($data);
            endforeach;
            $message = 'Plans visibility successfully updated.';
            $suc = 1;
        else:
            $message = 'Plans not found under selected criteria.';
            $suc = 2;
        endif;

        return response()->json(['message' => $message, 'suc' => $suc, 'visibility' => ($visibility == 1 ? 0 : 1)], 200);
    }

    public function assignedList(Request $request){
        $plan_id = (isset($request->plan_id) && !empty($request->plan_id) ? $request->plan_id : 0);
        $student_ids = Assign::where('plan_id', $plan_id)->pluck('student_id')->unique()->toArray();
        $student_ids = (!empty($student_ids) ? $student_ids : [0]);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Student::orderByRaw(implode(',', $sorts))->whereIn('id', $student_ids);

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        // 1, not '' — an empty string reaches Tabulator as NaN and breaks the pager.
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'full_time' => (isset($list->activeCR->propose->full_time) && $list->activeCR->propose->full_time > 0) ? $list->activeCR->propose->full_time : 0, 
                    'registration_no' => (!empty($list->registration_no) ? $list->registration_no : $list->application_no),
                    'first_name' => $list->first_name,
                    'last_name' => $list->last_name,
                    'course'=> (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : ''),
                    'semester'=> (isset($list->activeCR->creation->semester->name) && !empty($list->activeCR->creation->semester->name) ? $list->activeCR->creation->semester->name : ''),
                    'status_id'=> (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    'url' => route('student.show', $list->id),
                    'photo_url' => $list->photo_url,
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'total' => $total_rows, 'data' => $data]);
    }

    public function getTheories(Request $request){
        $plan_id = $request->plan_id;
        $plan = Plan::find($plan_id);
        $theGroup = Group::find($plan->group_id);
        $sameNameGroupIds = Group::where('term_declaration_id', $plan->term_declaration_id)->where('course_id', $plan->course_id)
                            ->where('name', $theGroup->name)->pluck('id')->unique()->toArray();
        $modules = Plan::where('course_id', $plan->course_id)->where('term_declaration_id', $plan->term_declaration_id)->where('academic_year_id', $plan->academic_year_id)
                   ->whereIn('group_id', $sameNameGroupIds)->where('class_type', 'Theory')->get();

        $html = '<option value="">Please Select</option>';
        if($modules->count()):
            foreach($modules as $mod):
                $html .= '<option '.($plan->module_creation_id == $mod->module_creation_id ? 'Selected' : '').' value="'.$mod->id.'">'.$mod->id.' - '.$mod->creations->module_name.'</option>';
            endforeach;
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function syncTutorial(SyncTutorialRequest $request){
        $tutorial_id = $request->id;
        $theory_id = $request->sync_plan_id;

        Plan::where('id', $tutorial_id)->update(['parent_id' => $theory_id]);
        return response()->json(['msg' => 'Successfully synced'], 200);
    }

    public function getTutorial(Request $request){
        $theory_id = (isset($request->theory_id) && $request->theory_id > 0 ? $request->theory_id : 0);
        $tutorial_id = (isset($request->tutorial_id) && $request->tutorial_id > 0 ? $request->tutorial_id : 0);

        $tutorial = Plan::find($tutorial_id);
        $start_time = (isset($tutorial->start_time) && !empty($tutorial->start_time) ? substr($tutorial->start_time, 0, 5) : '');
        $end_time = (isset($tutorial->end_time) && !empty($tutorial->end_time) ? substr($tutorial->end_time, 0, 5) : '');

        $data = [];
        if($theory_id > 0):
            $theory = Plan::find($theory_id);
            $data['term'] = (isset($theory->attenTerm->name) && !empty($theory->attenTerm->name) ? $theory->attenTerm->name : '---');
            $data['course'] = (isset($theory->course->name) ? $theory->course->name : '---');
            $data['group'] = (isset($theory->group->name) ? $theory->group->name : '---');
            $data['module'] = (isset($theory->creations->module_name) && !empty($theory->creations->module_name) ? $theory->creations->module_name : '');
            $data['venue'] = (isset($theory->venu->name) && !empty($theory->venu->name) ? $theory->venu->name : '---');
            $data['group_id'] = $theory->group_id;
            $data['venue_id'] = $theory->venue_id;
            $data['pt_id'] = $theory->personal_tutor_id;
        else:
            $data['term'] = (isset($tutorial->attenTerm->name) && !empty($tutorial->attenTerm->name) ? $tutorial->attenTerm->name : '---');
            $data['course'] = (isset($tutorial->course->name) ? $tutorial->course->name : '---');
            $data['group'] = (isset($tutorial->group->name) ? $tutorial->group->name : '---');
            $data['module'] = (isset($tutorial->creations->module_name) && !empty($tutorial->creations->module_name) ? $tutorial->creations->module_name : '');
            $data['venue'] = (isset($tutorial->venu->name) && !empty($tutorial->venu->name) ? $tutorial->venu->name : '---');
            $data['group_id'] = $tutorial->group_id;
            $data['venue_id'] = $tutorial->venue_id;
            $data['pt_id'] = $tutorial->personal_tutor_id;
        endif;

        $data['rooms_id'] = (isset($tutorial->rooms_id) && $tutorial->rooms_id > 0 ? $tutorial->rooms_id : '');
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['personal_tutor_id'] = (isset($tutorial->personal_tutor_id) && $tutorial->personal_tutor_id > 0 ? $tutorial->personal_tutor_id : '');
        $data['virtual_room'] = (isset($tutorial->virtual_room) && !empty($tutorial->virtual_room) ? $tutorial->virtual_room : '');
        $data['note'] = (isset($tutorial->note) && !empty($tutorial->note) ? $tutorial->note : '');
        $data['sat'] = (isset($tutorial->sat) && $tutorial->sat > 0 ? $tutorial->sat : 0);
        $data['sun'] = (isset($tutorial->sun) && $tutorial->sun > 0 ? $tutorial->sun : 0);
        $data['mon'] = (isset($tutorial->mon) && $tutorial->mon > 0 ? $tutorial->mon : 0);
        $data['tue'] = (isset($tutorial->tue) && $tutorial->tue > 0 ? $tutorial->tue : 0);
        $data['wed'] = (isset($tutorial->wed) && $tutorial->wed > 0 ? $tutorial->wed : 0);
        $data['thu'] = (isset($tutorial->thu) && $tutorial->thu > 0 ? $tutorial->thu : 0);
        $data['fri'] = (isset($tutorial->fri) && $tutorial->fri > 0 ? $tutorial->fri : 0);

        return response()->json(['plan' => $data], 200);
    }

    public function storeTutorial(StoreTutorialPlanRequest $request){
        $theory_id = (isset($request->theory_id) && $request->theory_id > 0 ? $request->theory_id : 0);
        $tutorial_id = (isset($request->tutorial_id) && $request->tutorial_id > 0 ? $request->tutorial_id : 0);
        $theory = Plan::find($theory_id);

        $classDay = $request->class_day;
        $start_time = !empty($request->start_time) ? $request->start_time.':00' : '';
        $end_time = !empty($request->end_time) ? $request->end_time.':00' : '';
        $room = ($request->rooms_id > 0 ? Room::find($request->rooms_id) : []);
        $day = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        $data = [];
        $data['parent_id'] = $theory_id;
        $data['venue_id'] = (isset($room->venue->id) ? $room->venue->id : null);
        $data['rooms_id'] = (isset($room->id) ? $room->id : null);
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        foreach($day as $d):
            $data[$d] = ($d == $classDay ? 1 : 0);
        endforeach;
        $data['personal_tutor_id'] = (isset($request->personal_tutor_id) ? $request->personal_tutor_id : null);
        $data['virtual_room'] = (isset($request->virtual_room) ? $request->virtual_room : null);
        $data['note'] = (isset($request->note) ? $request->note : null);
        $data['class_type'] = (isset($request->class_type) ? $request->class_type : 'Tutorial');

        if($tutorial_id > 0):
            $data['updated_by'] = auth()->user()->id;
            Plan::where('id', $tutorial_id)->update($data);

            return response()->json(['msg' => 'Tutorial plan data successfully updated.'], 200);
        elseif($theory_id > 0 && $tutorial_id == 0):
            $data['term_declaration_id'] = $theory->term_declaration_id;
            $data['academic_year_id'] = $theory->academic_year_id;
            $data['course_creation_id'] = $theory->course_creation_id;
            $data['instance_term_id'] = $theory->instance_term_id;
            $data['course_id'] = $theory->course_id;
            $data['module_creation_id'] = $theory->module_creation_id;
            $data['group_id'] = $theory->group_id;
            $data['created_by'] = auth()->user()->id;

            $tutorialPlan = Plan::create($data);
            if($tutorialPlan->id):
                $thePlan = Plan::find($tutorialPlan->id);

                /* Generate Days */
                $term = $thePlan->creations->term;
                $courseCreationInstance = CourseCreationInstance::find($term->course_creation_instance_id);
                $academic_year_id = $courseCreationInstance->academic_year_id;
                $bankHolidays = BankHoliday::where('academic_year_id', $academic_year_id)->get();

                $submission_date = (isset($thePlan->submission_date) ? $thePlan->submission_date : '');
                $teaching_start_date = $start = (isset($term->teaching_start_date) && !empty($term->teaching_start_date) ? date('Y-m-d', strtotime($term->teaching_start_date)) : '');
                $teaching_end_date = $end = (isset($term->teaching_end_date) && !empty($term->teaching_end_date) ? date('Y-m-d', strtotime($term->teaching_end_date)) : '');
                $revision_start_date = (isset($term->revision_start_date) && !empty($term->revision_start_date) ? date('Y-m-d', strtotime($term->revision_start_date)) : '');
                $revision_end_date = (isset($term->revision_end_date) && !empty($term->revision_end_date) ? date('Y-m-d', strtotime($term->revision_end_date)) : '');

                $term_start_date = (isset($term->start_date) && !empty($term->start_date) ? date('Y-m-d', strtotime($term->start_date)) : $teaching_start_date);
                $term_end_date = (isset($term->end_date) && !empty($term->end_date) ? date('Y-m-d', strtotime($term->end_date)) : $teaching_end_date);
                
                if($term_start_date != '' && $term_end_date != ''):
                    $start = $term_start_date;
                    $end = $term_end_date;

                    while(strtotime($start) <= strtotime($end)):
                        $dayName = strtolower(date('D', strtotime($start)));
                        $bankHolidays = BankHoliday::where('academic_year_id', $academic_year_id)->where('start_date', '>=', $start)->where('end_date', '<=', $start)->get();
                        if(isset($thePlan->$dayName) && $thePlan->$dayName == 1 && $bankHolidays->count() == 0):
                            $name = '';
                            if($start == $submission_date):
                                $name = 'Submission';
                            elseif($start >= $revision_start_date && $start <= $revision_end_date):
                                $name = 'Revision';
                            else:
                                $name = 'Teaching';
                            endif;
                            $data = [];
                            $data['plan_id'] = $thePlan->id;
                            $data['name'] = $name;
                            $data['date'] = $start;
                            $data['status'] = 'Scheduled';
                            $data['created_by'] = auth()->user()->id;

                            $plandateList = PlansDateList::create($data);
                        endif;
                        $start = date("Y-m-d", strtotime("+1 day", strtotime($start)));
                    endwhile;
                endif;
                /* Generate Days */

                /* Copy Assigns */
                $theoryAssigns = Assign::where('plan_id', $theory_id)->orderBy('id', 'ASC')->get();
                if($theoryAssigns->count() > 0):
                    foreach($theoryAssigns as $ta):
                        $data = [];
                        $data['plan_id'] = $thePlan->id;
                        $data['student_id'] = $ta->student_id;
                        $data['attendance'] = $ta->attendance;
                        $data['created_by'] = auth()->user()->id;

                        Assign::create($data);
                    endforeach;
                endif;
                /* Copy Assigns */
                return response()->json(['msg' => 'Tutorial plan successfully created.'], 200);
            else:
                return response()->json(['msg' => 'Can not create Tutorial plan with given information. Please try again later.'], 304);
            endif;
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 304);
        endif;
        
    }
}
