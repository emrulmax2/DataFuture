<?php

namespace App\Http\Controllers\Programme;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelClassRequest;
use App\Http\Requests\ReAssignClassRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Assign;
use App\Models\AttendanceInformation;
use App\Models\ComonSmtp;
use App\Models\Course;
use App\Models\Employee;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\Option;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Student;
use App\Models\StudentEmail;
use App\Models\StudentSms;
use App\Models\StudentSmsContent;
use App\Models\TermDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\Avatar;
use App\Traits\SendSmsTrait;
use DateTime;
use App\Traits\GenerateEmailPdfTrait;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    use SendSmsTrait, GenerateEmailPdfTrait;

    /**
     * Palette the module paints terms with — index-aligned so a term keeps the
     * same colour in the header chips, the row badges and the donut.
     */
    public const TERM_COLOURS = [
        ['dot' => '#0E5A61', 'tint' => '#E7F0F1'],
        ['dot' => '#2A8FA8', 'tint' => '#E7F1F5'],
        ['dot' => '#8A6A24', 'tint' => '#F4EEE2'],
        ['dot' => '#5C2E7E', 'tint' => '#F0EAF6'],
        ['dot' => '#1B7F5A', 'tint' => '#E6F2EC'],
        ['dot' => '#8A3324', 'tint' => '#F6EAE7'],
    ];

    /**
     * Session-type colours. Keyed lowercase on `plans.class_type`, which in the
     * data is Theory / Tutorial / Seminar / Practical (or null). Each entry is
     * the badge's ink and its wash; white-on-ink is not used, so these only
     * need to carry against the tint.
     */
    public const CLASS_TYPE_TONES = [
        'theory' => ['ink' => '#0E5A61', 'tint' => '#E7F0F1'],
        'tutorial' => ['ink' => '#5C2E7E', 'tint' => '#F1EAF7'],
        'seminar' => ['ink' => '#1B5E9C', 'tint' => '#E8F0F8'],
        'practical' => ['ink' => '#8A3324', 'tint' => '#F8ECE8'],
    ];

    /** Fallback for a class with no type recorded. */
    public const CLASS_TYPE_FALLBACK = ['ink' => '#4C5F68', 'tint' => '#F0F3F4'];

    /** A tutor at or under this attendance rate lands in the follow-up queue. */
    public const FOLLOW_UP_THRESHOLD = 50;

    /** Rows per page in the infinite-scrolling staff absence panel. */
    public const ABSENCE_PAGE_SIZE = 12;

    public function index(){
        $theDate = Date('Y-m-d'); //'2023-11-24';
        $termDeclarationIds = TermDeclaration::whereNotNull('start_date')->whereNotNull('end_date')
                    ->whereDate('start_date', '<=', $theDate)->whereDate('end_date', '>=', $theDate)
                    ->pluck('id');

        $courseIds = Plan::whereIn('term_declaration_id', $termDeclarationIds)->pluck('course_id')->unique()->toArray();

        $day = $this->buildDayPayload($theDate);

        // The header pills, the row badges and the staff cards all key off the
        // day's own term set, so they cannot disagree with each other.
        $dayTermIds = $day['termIds'];
        $terms = TermDeclaration::whereIn('id', $dayTermIds)->get();
        $absentToday = $this->getAbsentEmployees($theDate);
        $lowTutors = $this->getLowAttendanceTutors($termDeclarationIds);

        return view('pages.programme.dashboard.index', [
            'title' => 'Programme Dashboard - London Churchill College',
            'layout' => 'programme-top-menu',
            'breadcrumbs' => [],
            'pgdCrumbCurrent' => 'Daily class information',

            'theDate' => date('d-m-Y', strtotime($theDate)),
            'theDateIso' => $theDate,
            'terms' => $terms,
            'termNames' => $terms->pluck('name')->unique()->toArray(),
            'termChips' => $this->getTermChips($dayTermIds, $theDate),
            'courses' => Course::whereIn('id', $courseIds)->orderBy('name')->get(),

            'slots' => $day['slots'],
            'stats' => $day['stats'],
            'summaryLine' => $this->buildSummaryLine($day, $terms, count($absentToday)),

            'classTutor' => $this->getClassTutorPeople($theDate, 0, 0, 0, 'tutor', $dayTermIds),
            'classPTutor' => $this->getClassTutorPeople($theDate, 0, 0, 0, 'personal', $dayTermIds),

            'absentToday' => $absentToday,
            'absentCover' => $this->getAbsenceCoverLine($absentToday, $theDate),
            'termAttendanceRates' => $this->getTermAttendanceRateFull($dayTermIds),

            'lowTutors' => $lowTutors,

            'tutors' => User::with('employee')->whereHas('employee', function($q){
                $q->where('status', 1);
            })->orderBy('name', 'ASC')->get(),
            'modules' => $this->getFilterOptions($termDeclarationIds, 'module'),
            'groups' => $this->getFilterOptions($termDeclarationIds, 'group'),

            'swapCandidates' => $this->getSwapCandidates(),
            'busyByTime' => $this->getBusyTutorsByTime($theDate),
            'absentUserIds' => $this->getAbsentUserIds($absentToday),
        ]);
    }

    /**
     * Module / group choices for the filter popovers, each with the number of
     * plans behind it so the popover can show a count like the design does.
     */
    public function getFilterOptions($term_declaration_ids, $kind = 'module'){
        $field = ($kind == 'group' ? 'group_id' : 'module_creation_id');
        $plans = Plan::whereIn('term_declaration_id', $term_declaration_ids)
            ->with($kind == 'group' ? ['group:id,name'] : ['creations:id,module_name'])
            ->get();

        $options = [];
        foreach($plans as $pln):
            $id = $pln->{$field};
            if(!$id): continue; endif;

            $name = ($kind == 'group'
                ? (isset($pln->group->name) ? $pln->group->name : null)
                : (isset($pln->creations->module_name) ? $pln->creations->module_name : null));
            if(empty($name)): continue; endif;

            if(!isset($options[$id])): $options[$id] = ['id' => $id, 'name' => $name, 'count' => 0]; endif;
            $options[$id]['count']++;
        endforeach;

        $options = array_values($options);
        usort($options, function($a, $b){ return strcasecmp($a['name'], $b['name']); });

        return $options;
    }

    /** Staff the swap dialog can offer, with the details it shows per row. */
    public function getSwapCandidates(){
        $out = [];
        $users = User::with('employee')->whereHas('employee', function($q){
            $q->where('status', 1);
        })->orderBy('name', 'ASC')->get();

        foreach($users as $usr):
            $name = (isset($usr->employee->full_name) && !empty($usr->employee->full_name) ? $usr->employee->full_name : (isset($usr->name) ? $usr->name : ''));
            if(empty($name)): continue; endif;

            $out[] = [
                'id' => $usr->id,
                'name' => $name,
                'initials' => $this->initialsOf($name),
                'photo' => (isset($usr->employee->photo_url) && !Avatar::isGenerated($usr->employee->photo_url) ? $usr->employee->photo_url : null),
                'color' => Avatar::soft($name),
                'mobile' => (isset($usr->employee->mobile) ? $usr->employee->mobile : ''),
                'role' => (isset($usr->employee->employment->employeeJobTitle->name) ? $usr->employee->employment->employeeJobTitle->name : ''),
            ];
        endforeach;

        return $out;
    }

    /** [ "09:30" => [ user_id => group name ] ] — powers the swap clash chip. */
    public function getBusyTutorsByTime($theDate){
        $busy = [];
        $planDates = PlansDateList::with('plan')->where('date', $theDate)->get();

        foreach($planDates as $pln):
            if(!isset($pln->plan->id)): continue; endif;
            $time = date('H:i', strtotime($theDate.' '.$pln->plan->start_time));
            $uid = ($pln->proxy_tutor_id > 0 ? $pln->proxy_tutor_id : ($pln->plan->tutor_id > 0 ? $pln->plan->tutor_id : $pln->plan->personal_tutor_id));
            if(!$uid): continue; endif;

            if(!isset($busy[$time])): $busy[$time] = []; endif;
            $busy[$time][$uid] = (isset($pln->plan->group->name) ? $pln->plan->group->name : 'a class');
        endforeach;

        return $busy;
    }

    /** User ids behind today's absent employees, for the swap dialog. */
    public function getAbsentUserIds($absentToday){
        if(empty($absentToday)): return []; endif;

        return User::whereHas('employee', function($q) use($absentToday){
            $q->whereIn('id', array_keys($absentToday));
        })->pluck('id')->toArray();
    }

    public function getClassInformations(Request $request){
        $planClassStatus = (isset($request->planClassStatus) && !empty($request->planClassStatus) ? $request->planClassStatus : 'All');
        $planCourseId = (isset($request->planCourseId) && $request->planCourseId > 0 ? $request->planCourseId : 0);
        $theClassDate = (isset($request->theClassDate) && !empty($request->theClassDate) ? date('Y-m-d', strtotime($request->theClassDate)) : date('Y-m-d'));
        $planModuleCreationId = (isset($request->planModuleCreationId) && $request->planModuleCreationId > 0 ? $request->planModuleCreationId : 0);
        $planGroupId = (isset($request->planGroupId) && $request->planGroupId > 0 ? $request->planGroupId : 0);

        $day = $this->buildDayPayload($theClassDate, $planCourseId, $planClassStatus, $planModuleCreationId, $planGroupId);

        // Filtering the list can narrow which terms are represented, so the
        // pills are rebuilt from the day's own term set on every round-trip.
        $dayTermIds = $this->getRelevantTermIds($theClassDate);
        $terms = TermDeclaration::whereIn('id', $dayTermIds)->get();

        $tutorPeople = $this->getClassTutorPeople($theClassDate, $planCourseId, $planModuleCreationId, $planGroupId, 'tutor', $dayTermIds);
        $ptutorPeople = $this->getClassTutorPeople($theClassDate, $planCourseId, $planModuleCreationId, $planGroupId, 'personal', $dayTermIds);
        $absentToday = $this->getAbsentEmployees($theClassDate);

        $res = [];
        $res['slots'] = view('pages.programme.dashboard.partials.slots', [
            'slots' => $day['slots'],
            'stats' => $day['stats'],
        ])->render();
        $res['stats'] = $day['stats'];
        $res['summary'] = $this->buildSummaryLine($day, $terms, count($absentToday));
        $res['termChips'] = $this->getTermChips($dayTermIds, $theClassDate);
        $res['attendance'] = view('pages.programme.dashboard.partials.attendance', [
            'termAttendanceRates' => $this->getTermAttendanceRateFull($dayTermIds, $planCourseId, $planModuleCreationId, $planGroupId),
        ])->render();
        $res['tutors'] = [
            'count' => $tutorPeople['count'],
            'html' => view('pages.programme.dashboard.partials.staff-people', [
                'people' => $tutorPeople['people'],
                'perTerm' => $tutorPeople['per_term'],
                'kind' => 'tutor',
            ])->render(),
        ];
        $res['ptutors'] = [
            'count' => $ptutorPeople['count'],
            'html' => view('pages.programme.dashboard.partials.staff-people', [
                'people' => $ptutorPeople['people'],
                'perTerm' => $ptutorPeople['per_term'],
                'kind' => 'personal',
            ])->render(),
        ];

        return response()->json(['res' => $res], 200);
    }

    /**
     * Every class scheduled on `$theDate`, grouped into the time-slot
     * accordions the design shows, plus the counters the exception tiles and
     * quick-view chips are built from.
     *
     * The status/course/module/group filters stay server-side (they narrow the
     * query); free-text search, the term chips and the quick-view chips are
     * applied in the browser over the rendered rows, which is why every row
     * carries the data-* attributes they filter on.
     */
    public function buildDayPayload($theDate = null, $course_id = 0, $planClassStatus = 'All', $moduleCreationId = 0, $groupId = 0){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');

        $query = PlansDateList::with('plan', 'attendanceInformation', 'attendances', 'proxy')
                    ->where('date', $theDate)
                    ->whereHas('plan', function($q) use($course_id, $moduleCreationId, $groupId){
                        if($course_id > 0): $q->where('course_id', $course_id); endif;
                        if($moduleCreationId > 0): $q->where('module_creation_id', $moduleCreationId); endif;
                        if($groupId > 0): $q->where('group_id', $groupId); endif;
                    });
        if($planClassStatus != 'All'):
            $query->where('status', $planClassStatus);
        endif;
        $plans = $query->get()->sortBy(function($planDates, $key) {
                    return $planDates->plan->start_time;
                });

        // Terms in play are those open on the *selected* date plus any term a
        // class that day actually belongs to. Deriving them from "today" would
        // leave a browsed date's classes with no matching term pill — and the
        // pills filter the rows, so the board would come back empty.
        $termIds = $this->getRelevantTermIds($theDate, $plans);
        $termColours = $this->getTermColourMap($termIds);
        $currentTime = date('Y-m-d H:i:s');

        $rows = [];
        foreach($plans as $pln):
            if(!isset($pln->plan->id)): continue; endif;
            $rows[] = $this->buildClassRow($pln, $theDate, $currentTime, $termColours);
        endforeach;

        $stats = [
            'total' => count($rows),
            'not_started' => 0,
            'no_attendance' => 0,
            'ongoing' => 0,
            'completed' => 0,
            'scheduled' => 0,
            'cancelled' => 0,
            'online' => 0,
            'campus' => 0,
        ];
        foreach($rows as $row):
            if($row['status'] == 'notstarted'): $stats['not_started']++; endif;
            if($row['needs_attendance']): $stats['no_attendance']++; endif;
            if($row['status'] == 'ongoing'): $stats['ongoing']++; endif;
            if($row['status'] == 'completed'): $stats['completed']++; endif;
            if($row['status'] == 'scheduled' || $row['status'] == 'shortly'): $stats['scheduled']++; endif;
            if($row['status'] == 'cancelled'): $stats['cancelled']++; endif;
            if($row['is_online']): $stats['online']++; else: $stats['campus']++; endif;
        endforeach;
        $stats['needs_action'] = $stats['not_started'] + $stats['no_attendance'];

        // Slots keep the query's ascending start-time order.
        $slots = [];
        foreach($rows as $row):
            $key = $row['time'];
            if(!isset($slots[$key])):
                $slots[$key] = ['time' => $key, 'rows' => [], 'rooms' => [], 'alerts' => 0, 'finished' => 0];
            endif;
            $slots[$key]['rows'][] = $row;
            if(!in_array($row['room'], $slots[$key]['rooms'])): $slots[$key]['rooms'][] = $row['room']; endif;
            if($row['status'] == 'notstarted'): $slots[$key]['alerts']++; endif;
            if($row['status'] == 'completed'): $slots[$key]['finished']++; endif;
        endforeach;

        return ['slots' => array_values($slots), 'stats' => $stats, 'rows' => $rows, 'termIds' => $termIds];
    }

    /**
     * Term ids relevant to a given day: those open on the date, plus the terms
     * the day's classes belong to (a class can outlive its term window).
     */
    public function getRelevantTermIds($theDate, $plans = null){
        $ids = TermDeclaration::whereNotNull('start_date')->whereNotNull('end_date')
            ->whereDate('start_date', '<=', $theDate)->whereDate('end_date', '>=', $theDate)
            ->pluck('id')->toArray();

        if($plans === null):
            $plans = PlansDateList::with('plan')->where('date', $theDate)->get();
        endif;

        foreach($plans as $pln):
            if(isset($pln->plan->term_declaration_id) && $pln->plan->term_declaration_id > 0):
                $ids[] = $pln->plan->term_declaration_id;
            endif;
        endforeach;

        return array_values(array_unique(array_filter($ids)));
    }

    /** One class row, flattened into everything the row partial paints. */
    protected function buildClassRow($pln, $theDate, $currentTime, $termColours){
        $plan = $pln->plan;

        $tutorEmployeeId = (isset($plan->tutor->employee->id) && $plan->tutor->employee->id > 0 ? $plan->tutor->employee->id : 0);
        $perTutorEmployeeId = (isset($plan->personalTutor->employee->id) && $plan->personalTutor->employee->id > 0 ? $plan->personalTutor->employee->id : 0);
        $classTutorEmployeeId = ($tutorEmployeeId > 0 ? $tutorEmployeeId : $perTutorEmployeeId);
        $tutorPresent = EmployeeAttendanceLive::where('employee_id', $classTutorEmployeeId)->where('date', $theDate)->where('attendance_type', 1)->count() > 0;

        $proxyEmployeeId = (isset($pln->proxy->employee->id) && $pln->proxy->employee->id > 0 ? $pln->proxy->employee->id : 0);
        $proxyPresent = $proxyEmployeeId > 0
            ? EmployeeAttendanceLive::where('employee_id', $proxyEmployeeId)->where('date', $theDate)->where('attendance_type', 1)->count() > 0
            : false;

        $orgStart = date('Y-m-d H:i:s', strtotime($theDate.' '.$plan->start_time));
        $orgEnd = date('Y-m-d H:i:s', strtotime($theDate.' '.$plan->end_time));

        $fed = ($pln->feed_given == 1 && $pln->attendances->count() > 0);
        $startedAt = (isset($pln->attendanceInformation->start_time) && !empty($pln->attendanceInformation->start_time) ? date('H:i', strtotime($pln->attendanceInformation->start_time)) : '');
        $finishedAt = (isset($pln->attendanceInformation->end_time) && !empty($pln->attendanceInformation->end_time) ? date('H:i', strtotime($pln->attendanceInformation->end_time)) : '');

        // Mirrors the status ladder the module has always used, with the
        // canceled flag lifted to the top so a canceled class never reads as
        // "not started" once its slot has passed.
        if($pln->status == 'Canceled'):
            $status = 'cancelled';
        elseif(date('Y-m-d', strtotime($currentTime)) < date('Y-m-d', strtotime($orgStart))):
            $status = 'scheduled';
        elseif($currentTime < $orgStart && !isset($pln->attendanceInformation->id)):
            $status = 'scheduled';
        elseif($currentTime > $orgStart && $currentTime < $orgEnd && !isset($pln->attendanceInformation->id)):
            $status = 'shortly';
        elseif(isset($pln->attendanceInformation->id)):
            $status = (!empty($finishedAt) ? 'completed' : 'ongoing');
        elseif($currentTime > $orgEnd && !isset($pln->attendanceInformation->id)):
            $status = 'notstarted';
        else:
            $status = 'scheduled';
        endif;

        $duration = '';
        if(!empty($startedAt) && !empty($finishedAt)):
            $mins = (int) round((strtotime($finishedAt) - strtotime($startedAt)) / 60);
            if($mins > 0):
                $duration = floor($mins / 60).'h'.str_pad($mins % 60, 2, '0', STR_PAD_LEFT);
            endif;
        endif;

        // E-learning task chips: green once uploads land, amber while the due
        // date is ahead, red past it — the same rule the old table used.
        $tags = [];
        if(isset($plan->tasks) && $plan->tasks->count() > 0 && $plan->class_type == 'Theory'):
            foreach($plan->tasks as $tsk):
                $tone = 'done';
                if($tsk->uploads->count() == 0):
                    if($tsk->last_date && $tsk->last_date > date('Y-m-d')):
                        $tone = 'due';
                    elseif($tsk->last_date && $tsk->last_date <= date('Y-m-d')):
                        $tone = 'late';
                    endif;
                endif;
                $tags[] = [
                    'label' => (isset($tsk->eLearn->short_code) ? $tsk->eLearn->short_code : '--'),
                    'tone' => $tone,
                    'title' => (isset($tsk->eLearn->short_code) ? $tsk->eLearn->short_code : 'Task').' — '.($tone == 'done' ? 'uploads received' : ($tone == 'due' ? 'due, nothing uploaded yet' : 'overdue, no uploads')),
                ];
            endforeach;
        endif;

        $ownerUserId = ($plan->tutor_id > 0 ? $plan->tutor_id : ($plan->personal_tutor_id > 0 ? $plan->personal_tutor_id : 0));
        $ownerIsPersonal = !($plan->tutor_id > 0) && $plan->personal_tutor_id > 0;
        $ownerName = ($plan->tutor_id > 0
            ? (isset($plan->tutor->employee->full_name) && !empty($plan->tutor->employee->full_name) ? $plan->tutor->employee->full_name : (isset($plan->tutor->name) ? $plan->tutor->name : 'LCC'))
            : (isset($plan->personalTutor->employee->full_name) && !empty($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : (isset($plan->personalTutor->name) ? $plan->personalTutor->name : '')));
        $ownerPhoto = ($plan->tutor_id > 0
            ? (isset($plan->tutor->employee->photo_url) ? $plan->tutor->employee->photo_url : null)
            : (isset($plan->personalTutor->employee->photo_url) ? $plan->personalTutor->employee->photo_url : null));
        $ownerMobile = ($plan->tutor_id > 0
            ? (isset($plan->tutor->employee->mobile) ? $plan->tutor->employee->mobile : '')
            : (isset($plan->personalTutor->employee->mobile) ? $plan->personalTutor->employee->mobile : ''));

        $isSwapped = ($pln->proxy_tutor_id > 0);
        $proxyName = (isset($pln->proxy->employee->full_name) && !empty($pln->proxy->employee->full_name) ? $pln->proxy->employee->full_name : (isset($pln->proxy->name) ? $pln->proxy->name : 'Unknown Proxy'));
        $proxyPhoto = (isset($pln->proxy->employee->photo_url) ? $pln->proxy->employee->photo_url : null);
        $proxyMobile = (isset($pln->proxy->employee->mobile) ? $pln->proxy->employee->mobile : '');

        $activeName = ($isSwapped ? $proxyName : $ownerName);
        $activePhoto = ($isSwapped ? $proxyPhoto : $ownerPhoto);
        $activePresent = ($isSwapped ? $proxyPresent : $tutorPresent);
        $activeMobile = ($isSwapped ? $proxyMobile : $ownerMobile);

        $termId = (isset($plan->term_declaration_id) ? (int) $plan->term_declaration_id : 0);
        $termMeta = (isset($termColours[$termId]) ? $termColours[$termId] : ['name' => 'Term', 'short' => 'Term', 'dot' => '#65767E', 'tint' => '#EEF2F3']);

        $room = (isset($plan->room->name) && !empty($plan->room->name) ? $plan->room->name : '--');
        $moduleName = (isset($plan->creations->module->name) && !empty($plan->creations->module->name) ? $plan->creations->module->name : 'Unknown');
        $courseName = (isset($plan->course->name) && !empty($plan->course->name) ? $plan->course->name : 'Unknown');
        $groupName = (isset($plan->group->name) && !empty($plan->group->name) ? $plan->group->name : '');
        $parentId = (isset($plan->parent_id) && $plan->parent_id > 0 ? $plan->parent_id : $pln->plan_id);

        // Action visibility copied verbatim from the previous dropdown so no
        // permission or timing rule shifts with the redesign.
        $canFeedScheduled = ($pln->status == 'Scheduled' && $pln->feed_given != 1 && $orgEnd < $currentTime);
        $canFeedCompleted = ($pln->status == 'Completed' && $ownerUserId > 0 && !$fed);
        $canViewFeed = ($pln->status == 'Completed' && $ownerUserId > 0 && $fed);
        $canFeedOngoing = ($pln->status == 'Ongoing' && $pln->feed_given != 1 && $ownerUserId > 0);
        $canSwap = ($pln->status == 'Scheduled' && ($orgStart > $currentTime || ($orgStart < $currentTime && $orgEnd > $currentTime)) && !$isSwapped && $ownerUserId > 0);
        $canCancel = ($pln->status == 'Scheduled' || $pln->status == 'Unknown');
        $canEnd = ($pln->status == 'Ongoing' && $pln->feed_given == 1 && $orgEnd < $currentTime);

        $feedUrl = null;
        if($canFeedScheduled):
            $feedUrl = route('attendance.create', $pln->id);
        elseif($canFeedCompleted || $canViewFeed || $canFeedOngoing):
            $feedUrl = route('tutor-dashboard.attendance', [$ownerUserId, $pln->id, 2]);
        endif;

        $tutorRecordUrl = null;
        if($ownerUserId > 0 && $termId > 0):
            $tutorRecordUrl = $ownerIsPersonal
                ? route('programme.dashboard.personal.tutors.details', [$termId, $ownerUserId])
                : route('programme.dashboard.tutors.details', [$termId, $ownerUserId]);
        endif;

        return [
            'id' => $pln->id,
            'plan_id' => $pln->plan_id,
            'parent_id' => $parentId,
            'module_url' => route('tutor-dashboard.plan.module.show', $parentId),

            'time' => date('H:i', strtotime($theDate.' '.$plan->start_time)),
            'end' => date('H:i', strtotime($theDate.' '.$plan->end_time)),

            'module_name' => $moduleName,
            'session_type' => (isset($plan->class_type) && !empty($plan->class_type) ? strtoupper($plan->class_type) : ''),
            'session_ink' => $this->classTypeTone($plan->class_type ?? null)['ink'],
            'session_tint' => $this->classTypeTone($plan->class_type ?? null)['tint'],
            'course' => $courseName,
            'group' => $groupName,
            'tags' => $tags,

            'term_id' => $termId,
            'term_name' => $termMeta['name'],
            'term_short' => $termMeta['short'],
            'term_color' => $termMeta['dot'],

            'tutor_name' => $activeName,
            'tutor_photo' => (Avatar::isGenerated($activePhoto) ? null : $activePhoto),
            'tutor_color' => Avatar::soft($activeName),
            'tutor_initials' => $this->initialsOf($activeName),
            'tutor_present' => $activePresent,
            'tutor_phone' => $activeMobile,
            'is_swapped' => $isSwapped,
            'former_tutor' => $ownerName,
            'former_photo' => (Avatar::isGenerated($ownerPhoto) ? null : $ownerPhoto),
            'proxy_reason' => (isset($pln->proxy_reason) ? $pln->proxy_reason : ''),

            'room' => $room,
            'is_online' => (strtolower($room) == 'online'),

            'status' => $status,
            'db_status' => $pln->status,
            'started' => $startedAt,
            'finished' => $finishedAt,
            'duration' => $duration,
            'fed' => $fed,
            'needs_attendance' => (($status == 'completed' || $status == 'ongoing') && !$fed),

            'owner_user_id' => $ownerUserId,
            'attendance_information_id' => (isset($pln->attendanceInformation->id) ? $pln->attendanceInformation->id : 0),
            'can_feed' => ($canFeedScheduled || $canFeedCompleted || $canFeedOngoing),
            'can_view_feed' => $canViewFeed,
            'feed_url' => $feedUrl,
            'can_swap' => $canSwap,
            'can_cancel' => $canCancel,
            'can_end' => $canEnd,
            'tutor_record_url' => $tutorRecordUrl,

            'search' => strtolower(implode(' ', array_filter([$moduleName, $plan->class_type, $courseName, $groupName, $activeName, $ownerName, $room, $activeMobile]))),
        ];
    }

    protected function buildSummaryLine($day, $terms, $absentCount){
        $classes = $day['stats']['total'];
        $tutorCount = 0;
        $seen = [];
        foreach($day['rows'] as $row):
            if($row['owner_user_id'] > 0 && !in_array($row['owner_user_id'], $seen)):
                $seen[] = $row['owner_user_id'];
                $tutorCount++;
            endif;
        endforeach;
        $termCount = ($terms ? $terms->count() : 0);

        return $classes.' '.($classes == 1 ? 'class' : 'classes').' across '.$termCount.' '.($termCount == 1 ? 'term' : 'terms')
            .' · '.$tutorCount.' '.($tutorCount == 1 ? 'tutor' : 'tutors').' teaching'
            .' · '.$absentCount.' staff absent';
    }

    /** Term id => display metadata, used everywhere a term is colour-coded. */
    public function getTermColourMap($term_declaration_ids = null){
        if($term_declaration_ids === null):
            $theDate = date('Y-m-d');
            $term_declaration_ids = TermDeclaration::whereNotNull('start_date')->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $theDate)->whereDate('end_date', '>=', $theDate)
                        ->pluck('id');
        endif;

        $map = [];
        $i = 0;
        foreach(TermDeclaration::whereIn('id', $term_declaration_ids)->orderBy('id', 'ASC')->get() as $term):
            $colour = self::TERM_COLOURS[$i % count(self::TERM_COLOURS)];
            $map[$term->id] = [
                'id' => $term->id,
                'name' => $term->name,
                'short' => $this->shortTermName($term->name),
                'dot' => $colour['dot'],
                'tint' => $colour['tint'],
            ];
            $i++;
        endforeach;

        return $map;
    }

    /**
     * Row badges carry a trimmed term name — the leading year is the same on
     * every open term, so it costs width without telling the reader anything.
     * "2026 June(ULaw)" becomes "June ULaw".
     */
    public function shortTermName($name){
        $short = preg_replace('/^\s*(?:19|20)\d{2}\s*/', '', (string) $name);
        $short = preg_replace('/\s*\(\s*([^)]*)\s*\)/', ' $1', $short);
        $short = trim(preg_replace('/\s+/', ' ', $short));

        return $short !== '' ? $short : (string) $name;
    }

    /** The term pills in the header sub-bar: rate plus today's class count. */
    public function getTermChips($term_declaration_ids, $theDate = null){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        $colours = $this->getTermColourMap($term_declaration_ids);
        $rates = collect($this->getTermAttendanceRateFull($term_declaration_ids))->keyBy('id');

        $todayCounts = PlansDateList::where('date', $theDate)
            ->join('plans', 'plans.id', '=', 'plans_date_lists.plan_id')
            ->whereIn('plans.term_declaration_id', $term_declaration_ids)
            ->select('plans.term_declaration_id', DB::raw('COUNT(*) as total'))
            ->groupBy('plans.term_declaration_id')
            ->pluck('total', 'term_declaration_id')
            ->toArray();

        $chips = [];
        foreach($colours as $termId => $meta):
            $chips[] = [
                'id' => $termId,
                'name' => $meta['name'],
                'dot' => $meta['dot'],
                'tint' => $meta['tint'],
                'rate' => (isset($rates[$termId]['rate']) ? number_format((float) $rates[$termId]['rate'], 1) : '0.0'),
                'today' => (isset($todayCounts[$termId]) ? (int) $todayCounts[$termId] : 0),
            ];
        endforeach;

        return $chips;
    }

    /**
     * The two "Tutors" / "Personal Tutors" cards under the class list.
     *
     * `$kind` picks which side of a plan is counted: 'tutor' walks tutor_id
     * over taught classes, 'personal' walks personal_tutor_id over tutorials —
     * the same split the tutor / personal-tutor tables use, so the counts here
     * agree with the ones on those screens.
     */
    public function getClassTutorPeople($theDate = null, $course_id = 0, $moduleCreationId = 0, $groupId = 0, $kind = 'tutor', $termDeclarationIds = null){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        $termDeclarationIds = ($termDeclarationIds !== null ? $termDeclarationIds : $this->getRelevantTermIds($theDate));

        $tutorField = ($kind == 'personal' ? 'personal_tutor_id' : 'tutor_id');

        $query = Plan::whereIn('term_declaration_id', $termDeclarationIds);
        if($course_id > 0): $query->where('course_id', $course_id); endif;
        if($moduleCreationId > 0): $query->where('module_creation_id', $moduleCreationId); endif;
        if($groupId > 0): $query->where('group_id', $groupId); endif;

        $tutorIds = (clone $query)->whereNotNull($tutorField)->where($tutorField, '>', 0)->pluck($tutorField)->unique()->values()->toArray();
        $colours = $this->getTermColourMap($termDeclarationIds);

        // One pass over the relevant plans rather than a query per tutor.
        $relevantPlans = Plan::whereIn('term_declaration_id', $termDeclarationIds)
            ->whereIn($tutorField, (!empty($tutorIds) ? $tutorIds : [0]))
            ->when($kind == 'personal', function($q){ $q->whereIn('class_type', ['Tutorial', 'Seminar']); })
            ->when($kind != 'personal', function($q){ $q->whereNotIn('class_type', ['Tutorial', 'Seminar']); })
            ->get();

        $bucket = [];
        foreach($relevantPlans as $pln):
            $uid = $pln->{$tutorField};
            if(!$uid): continue; endif;
            if(!isset($bucket[$uid])): $bucket[$uid] = ['modules' => [], 'terms' => []]; endif;
            if(!in_array($pln->module_creation_id, $bucket[$uid]['modules'])): $bucket[$uid]['modules'][] = $pln->module_creation_id; endif;
            if(!in_array($pln->term_declaration_id, $bucket[$uid]['terms'])): $bucket[$uid]['terms'][] = $pln->term_declaration_id; endif;
        endforeach;

        $people = [];
        $users = User::with('employee')->whereIn('id', array_keys($bucket))->get();
        foreach($users as $usr):
            $name = (isset($usr->employee->full_name) && !empty($usr->employee->full_name) ? $usr->employee->full_name : (isset($usr->name) ? $usr->name : 'Unknown Employee'));
            $terms = [];
            foreach($bucket[$usr->id]['terms'] as $termId):
                if(isset($colours[$termId])):
                    $terms[] = $colours[$termId];
                endif;
            endforeach;

            $people[] = [
                'id' => $usr->id,
                'name' => $name,
                'initials' => $this->initialsOf($name),
                'photo' => (isset($usr->employee->photo_url) && !Avatar::isGenerated($usr->employee->photo_url) ? $usr->employee->photo_url : null),
                'color' => Avatar::soft($name),
                'count' => count($bucket[$usr->id]['modules']),
                'terms' => $terms,
                'term_ids' => $bucket[$usr->id]['terms'],
                'url' => ($kind == 'personal'
                    ? (!empty($terms) ? route('programme.dashboard.personal.tutors.details', [$terms[0]['id'], $usr->id]) : 'javascript:void(0);')
                    : (!empty($terms) ? route('programme.dashboard.tutors.details', [$terms[0]['id'], $usr->id]) : 'javascript:void(0);')),
            ];
        endforeach;

        usort($people, function($a, $b){
            return $b['count'] <=> $a['count'] ?: strcmp($a['name'], $b['name']);
        });

        // Per-term totals for the card footer: with more than one term open a
        // single "view all" link would quietly drop the others.
        $perTerm = [];
        foreach($colours as $termId => $meta):
            $n = 0;
            foreach($people as $person):
                if(in_array($termId, $person['term_ids'])): $n++; endif;
            endforeach;

            $perTerm[] = [
                'id' => $termId,
                'name' => $meta['name'],
                'dot' => $meta['dot'],
                'count' => $n,
                'url' => ($kind == 'personal'
                    ? route('programme.dashboard.personal.tutors', $termId)
                    : route('programme.dashboard.tutors', $termId)),
            ];
        endforeach;

        return [
            'count' => count($tutorIds),
            'people' => array_slice($people, 0, 5),
            'all' => $people,
            'terms' => array_values($colours),
            'per_term' => $perTerm,
        ];
    }

    /** Badge ink + wash for a session type. */
    public function classTypeTone($type){
        $key = strtolower(trim((string) $type));

        return self::CLASS_TYPE_TONES[$key] ?? self::CLASS_TYPE_FALLBACK;
    }

    /**
     * The people to show against a plan row: its tutor, plus the personal tutor
     * from the paired tutorial when that is somebody else. Two faces on the row
     * is how the old detail table showed a split, so it is kept.
     */
    public function planPeople($plan){
        $people = [];

        $add = function($user, $role) use (&$people) {
            if(!$user): return; endif;

            $name = (isset($user->employee->full_name) && !empty($user->employee->full_name)
                ? $user->employee->full_name
                : (isset($user->name) ? $user->name : ''));
            if($name === ''): return; endif;

            foreach($people as $existing):
                if($existing['name'] === $name): return; endif;
            endforeach;

            $photo = (isset($user->employee->photo_url) ? $user->employee->photo_url : null);
            $people[] = [
                'name' => $name,
                'role' => $role,
                'photo' => (Avatar::isGenerated($photo) ? null : $photo),
                'initials' => $this->initialsOf($name),
                'color' => Avatar::soft($name),
            ];
        };

        $add($plan->tutor ?? null, 'Tutor');
        $add($plan->personalTutor ?? null, 'Personal tutor');
        $add($plan->tutorial->personalTutor ?? null, 'Personal tutor');

        return $people;
    }

    /** "AB" from "Aisha Bello" — first + last initial, titles stripped. */
    public function initialsOf($name){
        $clean = trim(preg_replace('/^(Mr|Mrs|Ms|Miss|Dr|Prof)\.?\s+/i', '', (string) $name));
        if($clean === ''): return 'LC'; endif;

        $parts = preg_split('/\s+/', $clean);
        $first = mb_substr($parts[0], 0, 1);
        $last = (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '');

        return mb_strtoupper($first.$last);
    }

    /**
     * Tutors sitting at or below the follow-up threshold across the given
     * terms. Two grouped queries (one per tutor field) rather than a rate
     * lookup per tutor, then the assigned-student counts in one more.
     */
    public function getLowAttendanceTutors($term_declaration_ids, $threshold = null){
        $threshold = ($threshold === null ? self::FOLLOW_UP_THRESHOLD : $threshold);
        if(empty($term_declaration_ids) || (is_object($term_declaration_ids) && $term_declaration_ids->isEmpty())):
            return [];
        endif;

        $rows = [];
        foreach(['tutor_id' => 'tutor', 'personal_tutor_id' => 'personal'] as $field => $kind):
            $query = DB::table('attendances as atn')
                ->select(
                    'pln.'.$field.' as user_id',
                    DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id IN (1, 2, 5, 6, 7, 8) THEN 1 ELSE 0 END) AS ATTENDED')
                )
                ->leftJoin('plans as pln', 'pln.id', '=', 'atn.plan_id')
                ->whereNull('atn.deleted_at')
                ->whereIn('pln.term_declaration_id', $term_declaration_ids)
                ->whereNotNull('pln.'.$field)
                ->where('pln.'.$field, '>', 0);

            if($kind == 'personal'):
                $query->whereIn('pln.class_type', ['Tutorial', 'Seminar']);
            else:
                $query->whereNotIn('pln.class_type', ['Tutorial', 'Seminar']);
            endif;

            foreach($query->groupBy('pln.'.$field)->get() as $rec):
                if($rec->TOTAL <= 0): continue; endif;
                $rate = round(($rec->ATTENDED / $rec->TOTAL) * 100, 2);
                if($rate > $threshold): continue; endif;

                // A tutor who is both a module tutor and a personal tutor keeps
                // the weaker of the two rates — that is the one needing chasing.
                if(isset($rows[$rec->user_id]) && $rows[$rec->user_id]['rate'] <= $rate): continue; endif;
                $rows[$rec->user_id] = ['user_id' => $rec->user_id, 'rate' => $rate, 'kind' => $kind];
            endforeach;
        endforeach;

        if(empty($rows)): return []; endif;

        $userIds = array_keys($rows);
        $planIdsByTutor = [];
        foreach(Plan::whereIn('term_declaration_id', $term_declaration_ids)
            ->where(function($q) use($userIds){
                $q->whereIn('tutor_id', $userIds)->orWhereIn('personal_tutor_id', $userIds);
            })->get(['id', 'tutor_id', 'personal_tutor_id', 'start_time', 'end_time']) as $pln):
            foreach([$pln->tutor_id, $pln->personal_tutor_id] as $uid):
                if($uid && in_array($uid, $userIds)):
                    if(!isset($planIdsByTutor[$uid])): $planIdsByTutor[$uid] = ['plans' => [], 'minutes' => 0]; endif;
                    $planIdsByTutor[$uid]['plans'][] = $pln->id;
                    $planIdsByTutor[$uid]['minutes'] += max(0, (int) round((strtotime($pln->end_time) - strtotime($pln->start_time)) / 60));
                endif;
            endforeach;
        endforeach;

        $studentCounts = [];
        foreach($planIdsByTutor as $uid => $meta):
            $studentCounts[$uid] = Assign::whereIn('plan_id', $meta['plans'])->distinct('student_id')->count('student_id');
        endforeach;

        $out = [];
        foreach(User::with('employee')->whereIn('id', $userIds)->get() as $usr):
            $name = (isset($usr->employee->full_name) && !empty($usr->employee->full_name) ? $usr->employee->full_name : (isset($usr->name) ? $usr->name : 'Unknown Employee'));
            $contracted = (isset($usr->employee->workingPattern->contracted_hour) && !empty($usr->employee->workingPattern->contracted_hour) ? $usr->employee->workingPattern->contracted_hour : '00:00');
            $contractedMins = $this->convertStringToMinute($contracted);
            $classMinutes = (isset($planIdsByTutor[$usr->id]['minutes']) ? $planIdsByTutor[$usr->id]['minutes'] : 0);

            $out[] = [
                'id' => $usr->id,
                'name' => $name,
                'initials' => $this->initialsOf($name),
                'photo' => (isset($usr->employee->photo_url) && !Avatar::isGenerated($usr->employee->photo_url) ? $usr->employee->photo_url : null),
                'color' => Avatar::soft($name),
                'role' => (isset($usr->employee->employment->employeeJobTitle->name) && !empty($usr->employee->employment->employeeJobTitle->name) ? $usr->employee->employment->employeeJobTitle->name : 'Staff'),
                'students' => (isset($studentCounts[$usr->id]) ? $studentCounts[$usr->id] : 0),
                'load' => number_format(($contractedMins > 0 ? $classMinutes / $contractedMins : 0), 2),
                'rate' => number_format($rows[$usr->id]['rate'], 2).'%',
                'rate_value' => $rows[$usr->id]['rate'],
            ];
        endforeach;

        usort($out, function($a, $b){
            return $b['students'] <=> $a['students'] ?: $a['rate_value'] <=> $b['rate_value'];
        });

        return $out;
    }

    /**
     * "N with cover arranged, M open" — cover meaning an absent member of staff
     * whose classes today have had a proxy tutor assigned.
     */
    public function getAbsenceCoverLine($absentToday, $theDate = null){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        $total = count($absentToday);
        if($total == 0):
            return 'Everyone expected on site has clocked in.';
        endif;

        $employeeIds = array_keys($absentToday);
        $coveredUserIds = User::whereHas('employee', function($q) use($employeeIds){
            $q->whereIn('id', $employeeIds);
        })->pluck('id')->toArray();

        $covered = 0;
        if(!empty($coveredUserIds)):
            $covered = PlansDateList::where('date', $theDate)
                ->whereNotNull('proxy_tutor_id')
                ->where('proxy_tutor_id', '>', 0)
                ->whereHas('plan', function($q) use($coveredUserIds){
                    $q->whereIn('tutor_id', $coveredUserIds)->orWhereIn('personal_tutor_id', $coveredUserIds);
                })
                ->join('plans', 'plans.id', '=', 'plans_date_lists.plan_id')
                ->distinct()
                ->count(DB::raw('COALESCE(plans.tutor_id, plans.personal_tutor_id)'));
        endif;

        $covered = min($covered, $total);

        return $covered.' with cover arranged, '.($total - $covered).' open';
    }

    /**
     * Infinite-scroll rows for the "Staff absence today" panel.
     *
     * Keeps the same order as the first paint (getAbsentEmployees for the day),
     * so an appended page continues the list rather than reshuffling it. The
     * optional `q` filters by name server-side — with paging in play, filtering
     * only the rows already downloaded would silently hide matches.
     */
    public function absentRows(Request $request){
        // "View all" asks for one oversized page; clamp rather than reset, or
        // that request would silently come back as a normal-sized page.
        $perPage = (int) $request->query('per_page', self::ABSENCE_PAGE_SIZE);
        if($perPage < 1): $perPage = self::ABSENCE_PAGE_SIZE; endif;
        $perPage = min($perPage, 1000);

        $page = (int) $request->query('page', 1);
        if($page < 1): $page = 1; endif;
        $offset = ($page - 1) * $perPage;

        $theDate = (!empty($request->query('date')) ? date('Y-m-d', strtotime($request->query('date'))) : date('Y-m-d'));
        $needle = trim((string) $request->query('q', ''));

        $all = $this->getAbsentEmployees($theDate);
        if($needle !== ''):
            $all = array_filter($all, function($absent) use($needle){
                return mb_stripos($absent['full_name'], $needle) !== false;
            });
        endif;

        $total = count($all);
        $slice = array_slice($all, $offset, $perPage, true);

        $html = view('pages.programme.dashboard.partials.absence-rows', [
            'absents' => $slice,
            'showEmpty' => false,
        ])->render();

        return response()->json([
            'html' => $html,
            'page' => $page,
            'total' => $total,
            'loaded' => $offset + count($slice),
            'hasMore' => ($offset + count($slice)) < $total,
        ], 200);
    }

    /** Report choices offered by the Reports dialog. */
    public function reportTypes(){
        return [
            ['key' => 'attendance', 'name' => 'Daily attendance register', 'desc' => 'Every class, tutor and fed record for the day'],
            ['key' => 'exceptions', 'name' => 'Exceptions report', 'desc' => 'Classes not started and attendance not fed'],
            ['key' => 'tutor', 'name' => 'Tutor performance', 'desc' => 'Attendance, submission and achievement by tutor'],
            ['key' => 'absence', 'name' => 'Staff absence log', 'desc' => 'Absence, expected hours and cover status'],
        ];
    }


    public function getAbsentEmployees($date = ''){
        $theDate = (empty($date) ? date('Y-m-d') : $date);
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $time = date('H:i');
        $employees = Employee::where('status', 1)->orderBy('first_name', 'ASC')->get();

        $row = 0;
        $res = [];
        foreach($employees as $employee):
            // if($row > 5): 
            //     break; 
            // endif;

            if(isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes'):
                $employee_id = $employee->id;
                $employeeLeaveDay = EmployeeLeaveDay::where('status', 'Active')
                                    ->where('leave_date', $theDate)
                                    ->whereHas('leave', function($q) use($employee_id){
                                        $q->where('employee_id', $employee_id)->where('status', 'Approved');
                                    })
                                    ->get()->first();
                $leave_status = (isset($employeeLeaveDay->id) && $employeeLeaveDay->id > 0 && isset($employeeLeaveDay->leave->status) && $employeeLeaveDay->leave->status == 'Approved' ? true : false);

                $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                                         ->where('effective_from', '<=', $theDate)
                                         ->where(function($query) use($theDate){
                                            $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                                         })->get()->first();
                $activePatternId = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
                $patternDay = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $activePatternId)->where('day', $theDayNum)->get()->first();
                $day_status = (isset($patternDay->id) && $patternDay->id > 0 ? true : false);
                if($day_status && !$leave_status):
                    $todayAttendance = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'ASC')->get();
                    if($todayAttendance->count() == 0 && $patternDay->start <= $time):
                        $res[$employee_id]['photo_url'] = $employee->photo_url;
                        $res[$employee_id]['full_name'] = $employee->full_name;
                        $res[$employee_id]['date'] =  date('jS M, Y', strtotime($theDate));
                        $res[$employee_id]['hourMinute'] =  $patternDay->total;
                        $res[$employee_id]['minute'] =  $this->convertStringToMinute($patternDay->total);

                        $row += 1;
                    endif;
                endif;
            endif;
        endforeach;

        return $res;
    }


    public function convertStringToMinute($string){
        $min = 0;
        $str = explode(':', $string);

        $min += (isset($str[0]) && $str[0] != '') ? $str[0] * 60 : 0;
        $min += (isset($str[1]) && $str[1] != '') ? $str[1] : 0;

        return $min;
    }

    function calculateHourMinute($minutes){
        $hours = (intval(trim($minutes)) / 60 >= 1) ? intval(intval(trim($minutes)) / 60) : '00';
        $mins = (intval(trim($minutes)) % 60 != 0) ? intval(trim($minutes)) % 60 : '00';
     
        $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
        $hourMins .= ':';
        $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
        
        return $hourMins;
    }


    public function calculateTutorHours($tutor, $term_declaration_id){
        $minutes = 0;
        $termIds = (is_array($term_declaration_id) || $term_declaration_id instanceof \Illuminate\Support\Collection)
            ? $term_declaration_id
            : [$term_declaration_id];
        $activePlans = Plan::where('tutor_id', $tutor)->whereIn('term_declaration_id', $termIds)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->get();
        if(!empty($activePlans)):
            foreach($activePlans as $pln):
                $startTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d').' '.$pln->start_time));
                $endTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d').' '.$pln->end_time));

                $start = new DateTime($startTime);
                $end = new DateTime($endTime);
                $diff_in_seconds = $end->getTimestamp() - $start->getTimestamp();
                $minute = floor($diff_in_seconds / 60);

                $minutes += $minute;
            endforeach;
        endif;

        return $minutes;
    }

    /**
     * Programme tutors across every open term, not just the one in the URL.
     *
     * The design shows the dashboard's term pills on this screen and summarises
     * "N staff teaching across M terms", so the list aggregates and the pills
     * narrow it in the browser. The {id} in the route stays the anchor term —
     * it is what "view all tutors" links carry and what Export XL uses — and is
     * always included even when it has already closed.
     */
    public function tutors($term_declaration_id, $course_id = 0){
        $termIds = [(int) $term_declaration_id];

        $usedCourses = Plan::whereIn('term_declaration_id', $termIds)->pluck('course_id')->unique()->toArray();

        $query = Plan::whereIn('term_declaration_id', $termIds);
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $tutorIds = $query->whereNotNull('tutor_id')->where('tutor_id', '>', 0)->pluck('tutor_id')->unique()->toArray();

        $res = [];
        $tutors = User::with('employee')->whereIn('id', $tutorIds)->orderBy('id', 'ASC')->get();
        foreach($tutors as $tut):
            $employee = Employee::with('workingPattern')->where('user_id', $tut->id)->get()->first();
            $classMinutes = $this->calculateTutorHours($tut->id, $termIds);

            $activePlans = Plan::where('tutor_id', $tut->id)->whereIn('term_declaration_id', $termIds)
                ->whereNotIn('class_type', ['Tutorial', 'Seminar'])
                ->when($course_id > 0, function($q) use($course_id){ $q->where('course_id', $course_id); })
                ->get();
            $plan_ids = $activePlans->pluck('id')->unique()->toArray();
            $assigned = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->toArray();
            $moduleCreations = $activePlans->pluck('module_creation_id')->unique()->toArray();

            $tut['no_of_module'] = count($moduleCreations);
            $tut['expected_submission'] = count($assigned);
            $res[$tut->id] = $tut;
            $res[$tut->id]['attendances'] = $this->getTermAttendanceRate($termIds, $tut->id, 1);
            $res[$tut->id]['contracted_hour'] = (isset($employee->workingPattern->contracted_hour) && !empty($employee->workingPattern->contracted_hour) ? $employee->workingPattern->contracted_hour : '00:00');
            $res[$tut->id]['class_minutes'] = $classMinutes;
            $res[$tut->id]['class_hours'] = $this->calculateHourMinute($classMinutes);
            $res[$tut->id]['initials'] = $this->initialsOf(isset($tut->employee->full_name) ? $tut->employee->full_name : (isset($tut->name) ? $tut->name : ''));
            $res[$tut->id]['term_ids'] = $activePlans->pluck('term_declaration_id')->unique()->values()->toArray();
        endforeach;

        $terms = TermDeclaration::whereIn('id', $termIds)->get();

        return view('pages.programme.dashboard.tutors', [
            'title' => 'Programme Dashboard - Welcome to London churchill college',
            'layout' => 'programme-top-menu',
            'breadcrumbs' => [],
            'pgdCrumbCurrent' => 'Programme tutors',
            'pgdTermRoute' => 'programme.dashboard.tutors',

            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'termDeclarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'terms' => $terms,
            'termNames' => $terms->pluck('name')->implode(', '),
            'tutors' => $res,
            'courses' => Course::whereIn('id', $usedCourses)->orderBy('name')->get(),
            'selected_course' => $course_id
        ]);
    }

    public function tutorsDetails($term_declaration_id, $tutorid){
        $plans = [];
        $tutorPlans = Plan::with(['tutor.employee', 'personalTutor.employee', 'tutorial.personalTutor.employee'])
            ->where('term_declaration_id', $term_declaration_id)->where('tutor_id', $tutorid)
            ->whereNotIn('class_type', ['Tutorial', 'Seminar'])->get();
        if($tutorPlans->count() > 0):
            foreach($tutorPlans as $tp):
                $plans[$tp->id] = $tp;
                $plans[$tp->id]['attendances'] = $this->getPlanAttendanceRate($tp->id);

                $assigned = Assign::where('plan_id', $tp->id)->pluck('student_id')->toArray();
                $plans[$tp->id]['expected_submission'] = (!empty($assigned) ? count($assigned) : 0);
                $plans[$tp->id]['people'] = $this->planPeople($tp);
            endforeach;
        endif;

        $tutor = User::find($tutorid);
        $classMinutes = $this->calculateTutorHours($tutorid, $term_declaration_id);

        return view('pages.programme.dashboard.tutors-details', [
            'title' => 'Programme Dashboard - London Churchill College',
            'layout' => 'programme-top-menu',
            'breadcrumbs' => [],
            'pgdCrumbMid' => 'Programme tutors',
            'pgdCrumbMidUrl' => route('programme.dashboard.tutors', $term_declaration_id),
            'pgdCrumbCurrent' => (isset($tutor->employee->full_name) ? $tutor->employee->full_name : 'Tutor'),
            'pgdTermRoute' => 'programme.dashboard.tutors.details',
            'pgdTermRouteExtra' => [$tutorid],

            'p_tutor_id' => $tutorid,
            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'termDeclarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'tutor' => $tutor,
            'plans' => $plans,
            'tutorInitials' => $this->initialsOf(isset($tutor->employee->full_name) ? $tutor->employee->full_name : (isset($tutor->name) ? $tutor->name : '')),
            'contractedHour' => (isset($tutor->employee->workingPattern->contracted_hour) && !empty($tutor->employee->workingPattern->contracted_hour) ? $tutor->employee->workingPattern->contracted_hour : '00:00'),
            'classHours' => $this->calculateHourMinute($classMinutes),
            'classMinutes' => $classMinutes,
            'termColours' => $this->getTermColourMap([$term_declaration_id]),
        ]);
    }


    /** Personal tutors across the open terms — see tutors() for the rationale. */
    public function personalTutors($term_declaration_id, $course_id = 0){
        $termIds = [(int) $term_declaration_id];

        $usedCourses = Plan::whereIn('term_declaration_id', $termIds)->pluck('course_id')->unique()->toArray();

        $query = Plan::whereIn('term_declaration_id', $termIds);
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $tutorIds = $query->whereNotNull('personal_tutor_id')->where('personal_tutor_id', '>', 0)->pluck('personal_tutor_id')->unique()->toArray();

        $res = [];
        $tutors = User::with('employee')->whereIn('id', $tutorIds)->orderBy('id', 'ASC')->get();
        foreach($tutors as $tut):
            $employee = Employee::with('workingPattern')->where('user_id', $tut->id)->get()->first();
            $activePlans = Plan::where('personal_tutor_id', $tut->id)->whereIn('term_declaration_id', $termIds)
                ->where('class_type', 'Tutorial')
                ->when($course_id > 0, function($q) use($course_id){ $q->where('course_id', $course_id); })
                ->get();
            $plan_ids = $activePlans->pluck('id')->unique()->toArray();
            $assigns = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();
            $moduleCreations = $activePlans->pluck('module_creation_id')->unique()->toArray();
            $groups = $activePlans->pluck('group_id')->unique()->toArray();

            $tut['no_of_module'] = count($moduleCreations);
            $tut['no_of_assigned'] = count($assigns);
            $tut['no_of_group'] = count($groups);
            $res[$tut->id] = $tut;
            $res[$tut->id]['attendances'] = $this->getTermAttendanceRate($termIds, $tut->id, 2);
            $res[$tut->id]['undecidedUploads'] = 0;
            $res[$tut->id]['contracted_hour'] = (isset($employee->workingPattern->contracted_hour) && !empty($employee->workingPattern->contracted_hour) ? $employee->workingPattern->contracted_hour : '00:00');
            $res[$tut->id]['outstanding_calls'] = $this->getPersonalTutorOutstandingCall($term_declaration_id, $course_id, $tut->id);
            $res[$tut->id]['initials'] = $this->initialsOf(isset($tut->employee->full_name) ? $tut->employee->full_name : (isset($tut->name) ? $tut->name : ''));
            $res[$tut->id]['term_ids'] = $activePlans->pluck('term_declaration_id')->unique()->values()->toArray();
        endforeach;

        $terms = TermDeclaration::whereIn('id', $termIds)->get();

        return view('pages.programme.dashboard.personal-tutors', [
            'title' => 'Programme Dashboard - London Churchill College',
            'layout' => 'programme-top-menu',
            'breadcrumbs' => [],
            'pgdCrumbCurrent' => 'Personal tutors',
            'pgdTermRoute' => 'programme.dashboard.personal.tutors',

            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'termDeclarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'terms' => $terms,
            'termNames' => $terms->pluck('name')->implode(', '),
            'tutors' => $res,
            'courses' => Course::whereIn('id', $usedCourses)->orderBy('name')->get(),
            'selected_course' => $course_id
        ]);
    }

    public function getPersonalTutorOutstandingCall($term_declaration_id, $course_id = 0, $user_id){

        $tutor_plans = PlansDateList::whereHas('plan', function($q) use($term_declaration_id, $course_id,$user_id){

                        $q->where('term_declaration_id', $term_declaration_id)
                            ->where('class_type', 'Tutorial')
                            ->where('tutor_id', $user_id)
                            ->orWhere('personal_tutor_id', $user_id);
                        if($course_id > 0):
                            $q->where('course_id', $course_id);
                        endif;

                    })->get();
        $date_list_ids = $tutor_plans->pluck('id')->unique()->toArray();
        $plan_ids = $tutor_plans->pluck('plan_id')->unique()->toArray();

        $assignStudents = Assign::whereIn('plan_id', $plan_ids)->where(function($q){
                    $q->whereNull('attendance')->orWhere('attendance', 1)->orWhere('attendance', '');
                })->pluck('student_id')->unique()->toArray();

        $outStandingCount = 0;
        if(!empty($assignStudents)):
            $outStandingCount += DB::table('attendances as atn')
                        ->select('atn.student_id', 'atn.attendance_date', DB::raw('count(atn.id) as no_of_rows'), DB::raw('GROUP_CONCAT(atn.id) as atn_ids'))
                        ->leftJoin('plans as pln', 'pln.id', 'atn.plan_id')
                        ->whereIn('atn.student_id', $assignStudents)
                        ->where('atn.attendance_feed_status_id', 4)
                        ->where('atn.tracking_status', 0)
                        ->whereIn('pln.id', $plan_ids)
                        ->whereIn('atn.plans_date_list_id', $date_list_ids)
                        ->groupBy('atn.student_id', 'atn.attendance_date')->orderBy('atn.attendance_date', 'DESC')->get()->count();
        endif;

        return $outStandingCount;
    }


    public function personalTutorDetails($term_declaration_id, $tutorid){
        $plans = [];//->where('class_type', 'Tutorial')
        $tutorPlans = Plan::with(['tutor.employee', 'personalTutor.employee', 'tutorial.personalTutor.employee'])
            ->where('term_declaration_id', $term_declaration_id)->where('personal_tutor_id', $tutorid)->get();
        if($tutorPlans->count() > 0):
            foreach($tutorPlans as $tp):
                $planDates = PlansDateList::where('plan_id', $tp->id)->where('class_file_upload_found', "Undecided")->where('status','Completed')
                    ->whereHas('plan', function($q) use($term_declaration_id, $tutorid){  
                            $q->where('personal_tutor_id', $tutorid);
                            $q->where('class_type', "Theory");
                            $q->where('term_declaration_id', $term_declaration_id);
                    })->get();

                $plans[$tp->id] = $tp;
                $plans[$tp->id]['attendances'] = $this->getPlanAttendanceRate($tp->id);
                $plans[$tp->id]['undecidedUploads'] = $planDates->count();
                $plans[$tp->id]['people'] = $this->planPeople($tp);
            endforeach;
        endif;

        $tutor = User::find($tutorid);
        $planIds = array_keys($plans);
        $assignedStudents = (!empty($planIds) ? Assign::whereIn('plan_id', $planIds)->distinct('student_id')->count('student_id') : 0);

        return view('pages.programme.dashboard.personal-tutors-details', [
            'title' => 'Programme Dashboard - London Churchill College',
            'layout' => 'programme-top-menu',
            'breadcrumbs' => [],
            'pgdCrumbMid' => 'Personal tutors',
            'pgdCrumbMidUrl' => route('programme.dashboard.personal.tutors', $term_declaration_id),
            'pgdCrumbCurrent' => (isset($tutor->employee->full_name) ? $tutor->employee->full_name : 'Personal tutor'),
            'pgdTermRoute' => 'programme.dashboard.personal.tutors.details',
            'pgdTermRouteExtra' => [$tutorid],

            'p_tutor_id' => $tutorid,
            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'termDeclarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'tutor' => $tutor,
            'plans' => $plans,
            'assignedStudents' => $assignedStudents,
            'tutorInitials' => $this->initialsOf(isset($tutor->employee->full_name) ? $tutor->employee->full_name : (isset($tutor->name) ? $tutor->name : '')),
            'contractedHour' => (isset($tutor->employee->workingPattern->contracted_hour) && !empty($tutor->employee->workingPattern->contracted_hour) ? $tutor->employee->workingPattern->contracted_hour : '00:00'),
            'outstandingCalls' => $this->getPersonalTutorOutstandingCall($term_declaration_id, 0, $tutorid),
            'termColours' => $this->getTermColourMap([$term_declaration_id]),
        ]);
    }

    public function getTermAttendanceRate($term_declaration_id, $tutor_id, $type = 1){
        $tutor_field = ($type == 2 ? 'personal_tutor_id' : 'tutor_id');
        $termIds = (is_array($term_declaration_id) || $term_declaration_id instanceof \Illuminate\Support\Collection)
            ? $term_declaration_id
            : [$term_declaration_id];
        /*$planDateLists = PlansDateList::whereHas('plan', function($q) use($term_declaration_id, $tutor_field, $tutor_id){
            $q->where('term_declaration_id', $term_declaration_id)->where($tutor_field, $tutor_id);
            if($tutor_field == 'personal_tutor_id'):
                $q->where('class_type', 'Tutorial');
            else:
                $q->whereNotIn('class_type', ['Tutorial', 'Seminar']);
            endif;
        })->get();
        $plan_ids = $planDateLists->pluck('plan_id')->unique()->toArray();
        $date_ids = $planDateLists->pluck('id')->unique()->toArray();*/

        $plan_ids = Plan::whereIn('term_declaration_id', $termIds)->where($tutor_field, $tutor_id)->where(function($q) use($type){
            if($type == 2):
                $q->whereIn('class_type', ['Tutorial', 'Seminar']);
            else:
                $q->whereNotIn('class_type', ['Tutorial', 'Seminar']);
            endif;
        })->pluck('id')->unique()->toArray();
        
        $student_ids = (!empty($plan_ids) ? Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray() : []);
        $query = DB::table('attendances as atn')
                    ->select(
                        DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                    )
                    
                    ->whereNull('atn.deleted_at')
                    ->whereIn('atn.plan_id', $plan_ids);
        if(!empty($student_ids)):
            $query->whereIn('atn.student_id', $student_ids);
        endif;
        $attendance = $query->get()->first();

        return $attendance;
    }

    public function getPlanAttendanceRate($plan_id){
        $planDateLists = PlansDateList::where('plan_id', $plan_id)->pluck('id')->unique()->toArray();
        $student_ids = Assign::where('plan_id', $plan_id)->pluck('student_id')->unique()->toArray();
        $query = DB::table('attendances as atn')
                    ->select(
                        DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                    )
                    
                    ->whereNull('atn.deleted_at')
                    ->whereIn('atn.plans_date_list_id', $planDateLists);
        if(!empty($student_ids)):
            $query->whereIn('atn.student_id', $student_ids);
        endif;
        $attendance = $query->get()->first();

        return $attendance;
    }

    public function cancelClass(CancelClassRequest $request){
        $plan_id = $request->plan_id;
        $plan = Plan::find($plan_id);
        $plans_date_list_id = $request->plans_date_list_id;
        $canceled_reason = $request->canceled_reason;
        $siteSettings = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->get()->first();
        $company_name = (isset($siteSettings->value) && !empty($siteSettings->value) ? $siteSettings->value : 'London Churchill College');
        $courseName = (isset($plan->course->name) ? $plan->course->name : '');
        $moduleName = (isset($plan->creations->module_name) ? $plan->creations->module_name : '');
        $groupName = (isset($plan->group->name) ? $plan->group->name : '');
        $classTime = date('h:i A', strtotime($plan->start_time)).' - '.date('h:i A', strtotime($plan->end_time));
        $tutorName = (isset($plan->tutor->employee->full_name) && !empty($plan->tutor->employee->full_name) ? $plan->tutor->employee->full_name : (isset($plan->personalTutor->employee->full_name) && !empty($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : ''));

        $notify_student = (isset($request->notify_student) && $request->notify_student > 0 ? true : false);
        $notify_tutors = (isset($request->notify_tutors) && $request->notify_tutors > 0 ? true : false);

        $data = [];
        $data['status'] = 'Canceled';
        $data['canceled_reason'] = $canceled_reason;
        $data['canceled_by'] = auth()->user()->id;
        $data['canceled_at'] = date('Y-m-d H:i:s');

        PlansDateList::where('id', $plans_date_list_id)->update($data);

        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  $company_name,
        ];
        $MAILHTML = 'This is a class cancellation notice:<br/>';
        $MAILHTML .= 'Course Name: '.$courseName.'<br/>';
        $MAILHTML .= 'Module Name: '.$moduleName.'<br/>';
        $MAILHTML .= 'Group Name: '.$groupName.'<br/>';
        $MAILHTML .= 'Time: '.$classTime.'<br/>';
        $MAILHTML .= 'Tutor Name: '.$tutorName.'<br/><br/>';
        $MAILHTML .= 'Thanks & Regards <br/>'.$company_name;

        if($notify_student):
            if(isset($plan->assign) && $plan->assign->count() > 0):
                $sms_subject = 'Class Cancellation Notice';
                foreach($plan->assign as $assign):
                    $student = Student::with('title', 'contact')->where('id', $assign->student_id)->get()->first();
                    $mobile = (isset($student->contact->mobile) && !empty($student->contact->mobile) ? $student->contact->mobile : '');
                    $emails = [];
                    if(isset($student->contact->personal_email) && !empty($student->contact->personal_email)): 
                        $emails[] = $student->contact->personal_email; 
                    endif;
                    if(isset($student->contact->institutional_email) && !empty($student->contact->institutional_email)): 
                        $emails[] = $student->contact->institutional_email; 
                    endif;

                    $sms_body = 'Dear '.$student->full_name.', this is a class cancellation notice: Course name: '.$courseName.', Module name: '.$moduleName.', Group: '.$groupName.', Time: '.$classTime.', Tutor name: '.$tutorName;
                    $studentSmsContent = StudentSmsContent::create([
                        'sms_template_id' => null,
                        'subject' => $sms_subject,
                        'sms' => $sms_body
                    ]);
                    if($studentSmsContent):
                        $studentSms = StudentSms::create([
                            'student_id' => $student->id,
                            'student_sms_content_id' => $studentSmsContent->id,
                            'phone' => $mobile,
                            'created_by' => auth()->user()->id
                        ]);

                        $sms = $this->sendSms($mobile, $sms_body, $company_name);
                    endif;
                    
                    $NEWMAILHTML = 'Dear '.$student->full_name.',<br/><br/>'.$MAILHTML;
                    $studentEmail = StudentEmail::create([
                        'student_id' => $student->id,
                        'common_smtp_id' => (isset($commonSmtp->id) && $commonSmtp->id > 0 ? $commonSmtp->id : null),
                        'email_template_id' => null,
                        'subject' => $sms_subject,
                        'created_by' => auth()->user()->id,
                    ]);
                    if($studentEmail->id):
                        $emailPdf = $this->generateEmailPdf($studentEmail->id, $student->id, $sms_subject, $NEWMAILHTML);
                        $studentEmail = StudentEmail::where('id', $studentEmail->id)->update([
                            'mail_pdf_file' => $emailPdf
                        ]);

                        UserMailerJob::dispatch($configuration, $emails, new CommunicationSendMail($sms_subject, $NEWMAILHTML, []));
                    endif;
                endforeach;
            endif;
        endif;

        if($notify_tutors):
            $SUBJECT = 'Class Cancellation Notice From '.$company_name.' Account.';
            if(isset($plan->tutor_id) && $plan->tutor_id > 0):
                $NEWMAILHTML = 'Dear '.$plan->tutor->employee->full_name.',<br/><br/>'.$MAILHTML;
                $TEMAILS = [];
                if(isset($plan->tutor->employee->email) && !empty($plan->tutor->employee->email)):
                    $TEMAILS[] = $plan->tutor->employee->email;
                endif;
                if(isset($plan->tutor->employee->employment->email) && !empty($plan->tutor->employee->employment->email)):
                    $TEMAILS[] = $plan->tutor->employee->employment->email;
                endif;

                UserMailerJob::dispatch($configuration, $TEMAILS, new CommunicationSendMail($SUBJECT, $NEWMAILHTML, []));
            endif;
            if(isset($plan->personal_tutor_id) && $plan->personal_tutor_id > 0):
                $NEWMAILHTML = 'Dear '.$plan->personalTutor->employee->full_name.',<br/><br/>'.$MAILHTML;
                $TEMAILS = [];
                if(isset($plan->personalTutor->employee->email) && !empty($plan->personalTutor->employee->email)):
                    $TEMAILS[] = $plan->personalTutor->employee->email;
                endif;
                if(isset($plan->personalTutor->employee->employment->email) && !empty($plan->personalTutor->employee->employment->email)):
                    $TEMAILS[] = $plan->personalTutor->employee->employment->email;
                endif;

                UserMailerJob::dispatch($configuration, $TEMAILS, new CommunicationSendMail($SUBJECT, $NEWMAILHTML, []));
            endif;
        endif;

        return response()->json(['message' => 'Class status updated to canceled.'], 200);
    }

    /**
     * Attendance across each open term — the numbers behind the donut.
     *
     * Returns rate, the raw present/expected counts the arcs are sized by, and
     * a seven-day delta so the sidebar can show which way a term is moving.
     */
    public function getTermAttendanceRateFull($term_declaration_ids, $course_id = 0, $moduleCreationId = 0, $groupId = 0){
        $termRates = [];
        $colours = $this->getTermColourMap($term_declaration_ids);
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $i = 0;

        foreach($term_declaration_ids as $term_declaration_id):
            $theTerm = TermDeclaration::find($term_declaration_id);
            if(!$theTerm): continue; endif;

            $planDateLists = PlansDateList::whereHas('plan', function($q) use($term_declaration_id, $course_id, $moduleCreationId, $groupId){
                $q->where('term_declaration_id', $term_declaration_id);
                if($course_id > 0): $q->where('course_id', $course_id); endif;
                if($moduleCreationId > 0): $q->where('module_creation_id', $moduleCreationId); endif;
                if($groupId > 0): $q->where('group_id', $groupId); endif;
            })->get();
            $plan_ids = $planDateLists->pluck('plan_id')->unique()->toArray();
            $date_ids = $planDateLists->pluck('id')->unique()->toArray();

            $student_ids = (!empty($plan_ids) ? Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray() : []);

            $baseQuery = function() use($date_ids, $plan_ids, $student_ids){
                $query = DB::table('attendances as atn')
                            ->select(
                                DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id IN (1, 2, 5, 6, 7, 8) THEN 1 ELSE 0 END) AS ATTENDED'),
                                DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                                DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse')
                            )
                            ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                            ->leftJoin('students as std', 'atn.student_id', 'std.id')
                            ->whereNull('atn.deleted_at')
                            ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45, 13, 15, 48, 16, 17, 18, 20, 36, 33, 47, 50])
                            ->whereIn('atn.plans_date_list_id', $date_ids);
                if(!empty($plan_ids)):
                    $query->whereIn('atn.plan_id', $plan_ids);
                endif;
                if(!empty($student_ids)):
                    $query->whereIn('atn.student_id', $student_ids);
                endif;

                return $query;
            };

            $attendances = $baseQuery()->get()->first();
            $previous = $baseQuery()->whereDate('atn.attendance_date', '<=', $weekAgo)->get()->first();

            $rate = (isset($attendances->percentage_withexcuse) && $attendances->percentage_withexcuse > 0 ? (float) $attendances->percentage_withexcuse : 0);
            $previousRate = (isset($previous->percentage_withexcuse) && $previous->percentage_withexcuse > 0 ? (float) $previous->percentage_withexcuse : 0);

            $colour = (isset($colours[$theTerm->id]) ? $colours[$theTerm->id] : self::TERM_COLOURS[$i % count(self::TERM_COLOURS)] + ['name' => $theTerm->name]);

            $termRates[$i]['id'] = $theTerm->id;
            $termRates[$i]['name'] = $theTerm->name;
            $termRates[$i]['rate'] = number_format($rate, 2);
            $termRates[$i]['rate_value'] = $rate;
            $termRates[$i]['color'] = (isset($colour['dot']) ? $colour['dot'] : '#0E5A61');
            $termRates[$i]['tint'] = (isset($colour['tint']) ? $colour['tint'] : '#E7F0F1');
            $termRates[$i]['expected'] = (isset($attendances->TOTAL) ? (int) $attendances->TOTAL : 0);
            $termRates[$i]['present'] = (isset($attendances->ATTENDED) ? (int) $attendances->ATTENDED : 0);
            $termRates[$i]['delta'] = ($previousRate > 0 ? round($rate - $previousRate, 1) : 0);
            $termRates[$i]['dates'] = (!empty($theTerm->start_date) && !empty($theTerm->end_date)
                ? date('d M', strtotime($theTerm->start_date)).' — '.date('d M Y', strtotime($theTerm->end_date))
                : '');
            $termRates[$i]['tutors'] = Plan::where('term_declaration_id', $theTerm->id)
                ->when($course_id > 0, function($q) use($course_id){ $q->where('course_id', $course_id); })
                ->when($moduleCreationId > 0, function($q) use($moduleCreationId){ $q->where('module_creation_id', $moduleCreationId); })
                ->when($groupId > 0, function($q) use($groupId){ $q->where('group_id', $groupId); })
                ->where(function($q){ $q->whereNotNull('tutor_id')->orWhereNotNull('personal_tutor_id'); })
                ->get(['tutor_id', 'personal_tutor_id'])
                ->flatMap(function($p){ return array_filter([$p->tutor_id, $p->personal_tutor_id]); })
                ->unique()->count();
            $i++;
        endforeach;

        return $termRates;
    }

    public function endClass(Request $request){
        $plan_date_list_id = $request->plan_date_list_id;
        $attendance_information_id = $request->attendance_information_id;

        $planDate = PlansDateList::with('plan')->find($plan_date_list_id);
        $endTime = (isset($planDate->plan->end_time) && !empty($planDate->plan->end_time) ? $planDate->plan->end_time : date('H:i:s'));

        $attendanceInformation = AttendanceInformation::find($attendance_information_id);
        $attendanceInformation->end_time = $endTime;
        $attendanceInformation->updated_by = Auth::user()->id;
        if($attendanceInformation->isDirty()):
            $attendanceInformation->save();
            PlansDateList::where('id', $plan_date_list_id)->update(['status' => 'Completed']);
            return response()->json(['data' => 'Class Ended' ], 200);
        else:
            return response()->json(['data' => 'error found' ], 422);
        endif;
    }

    public function reAssignClass(ReAssignClassRequest $request){
        $proxy_tutor_id = $request->proxy_tutor_id;
        $plan_id = $request->plan_id;
        $plans_date_list_id = $request->plans_date_list_id;

        $data = [];
        $data['proxy_tutor_id'] = $proxy_tutor_id;
        $data['proxy_reason'] = (isset($request->proxy_reason) && !empty($request->proxy_reason) ? $request->proxy_reason : null);
        $data['proxy_assigned_by'] = auth()->user()->id;
        $data['proxy_assigned_at'] = date('Y-m-d H:i:s');

        PlansDateList::where('id', $plans_date_list_id)->where('plan_id', $plan_id)->update($data);

        return response()->json(['message' => 'Class successfully re-assigned to new tutor.'], 200);
    }

    public function getUndecidedClass(Request $request){
        $tutor_id = $request->tutor_id;
        $term_id = $request->term_id;
        $plan_id = (isset($request->plan_id) && $request->plan_id > 0 ? $request->plan_id : 0);
        
        $html = '';
        $query = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->where('class_file_upload_found', 'Undecided')->where('status','Completed')
                    ->whereHas('plan', function($q) use($term_id, $tutor_id){
                        $q->where('personal_tutor_id', $tutor_id);
                        $q->where('class_type', "Theory");
                        $q->where('term_declaration_id', $term_id);
                    });
        if($plan_id > 0):
            $query->where('plan_id', $plan_id);
        endif;
        $planDates = $query->get()->sortBy(function($planDates, $key) {
            return date("Y-m-d H:i", strtotime($planDates->date." ".$planDates->plan->start_time));
        });

        if(!empty($planDates) && $planDates->count() > 0):
            foreach($planDates as $pln):
                $tutorEmployeeId = (isset($pln->plan->tutor->employee->id) && $pln->plan->tutor->employee->id > 0 ? $pln->plan->tutor->employee->id : 0);
                $PerTutorEmployeeId = (isset($pln->plan->personalTutor->employee->id) && $pln->plan->personalTutor->employee->id > 0 ? $pln->plan->personalTutor->employee->id : 0);
                $classTutor = ($tutorEmployeeId > 0 ? $tutorEmployeeId : ($PerTutorEmployeeId > 0 ? $PerTutorEmployeeId : 0));
                $empAttendanceLive = EmployeeAttendanceLive::where('employee_id', $classTutor)->where('date', date("Y-m-d",strtotime($pln->date)))->where('attendance_type', 1)->get();

                $proxyEmployeeId = (isset($pln->proxy->employee->id) && $pln->proxy->employee->id > 0 ? $pln->proxy->employee->id : 0);
                
                $proxyAttendanceLive = EmployeeAttendanceLive::where('employee_id', $proxyEmployeeId)->where('date', date("Y-m-d",strtotime($pln->date)))->where('attendance_type', 1)->get();

                $classStatus = 0;
                $classLabel = '';
                
                if(isset($pln->attendanceInformation->id)):
                    if($pln->feed_given == 1 && $pln->attendances->count() > 0):
                        $classLabel .= '<span class="btn-rounded btn font-medium btn-success text-white p-0 w-9 h-9 mr-1" style="flex: 0 0 36px;">A</span>';
                    endif;
                    if(!empty($pln->attendanceInformation->start_time) && empty($pln->attendanceInformation->end_time)):
                        $classLabel .= '<span class="text-success font-medium">Started '.date('h:i A', strtotime($pln->attendanceInformation->start_time)).'</span>';
                    elseif(!empty($pln->attendanceInformation->start_time) && !empty($pln->attendanceInformation->end_time)):
                        $classLabel .= '<span class="text-success font-medium">';
                            $classLabel .= 'Started '.date('h:i A', strtotime($pln->attendanceInformation->start_time)).'<br/>'; 
                            $classLabel .= 'Finished '.date('h:i A', strtotime($pln->attendanceInformation->end_time)); 
                        $classLabel .= '</span>';
                    endif;
                else:
                    $classLabel .= '<span class="text-danger font-medium">Completed But No Attendance Found</span>';
                endif;

                $html .= '<tr class="intro-x">';
                    $html .= '<td>';
                        $html .= '<div class="font-fedium">'.date('jS M, Y', strtotime($pln->date.' '.$pln->plan->start_time)).'</div>';
                        $html .= '<div class="text-slate-500">'.date('H:i ', strtotime($pln->date.' '.$pln->plan->start_time)).' - '.date('H:i ', strtotime($pln->date.' '.$pln->plan->end_time)).'</div>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<div class="flex items-center">';
                            $html .= '<div>';
                                $html .= '<a href="'.route('tutor-dashboard.plan.module.show', $pln->plan_id).'" class="font-medium whitespace-nowrap">'.(isset($pln->plan->creations->module->name) && !empty($pln->plan->creations->module->name) ? $pln->plan->creations->module->name : 'Unknown').(isset($pln->plan->class_type) && !empty($pln->plan->class_type) ? ' - '.$pln->plan->class_type : '').'</a>';
                                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'.(isset($pln->plan->course->name) && !empty($pln->plan->course->name) ? $pln->plan->course->name : 'Unknown'). ' <span class="rounded bg-primary text-white cursor-pointer font-medium inline-flex justify-center items-center w-auto ml-1 px-3 py-0.5"> '.$pln->plan->group->name .' </spane></div>';
                                if(isset($pln->plan->tasks) && $pln->plan->tasks->count() > 0):
                                    $html .= '<div class="flex flex-start pt-1">';
                                    foreach($pln->plan->tasks as $tsk):
                                        $sc_class = 'btn-success';
                                        if($tsk->uploads->count() == 0):
                                            if($tsk->last_date && $tsk->last_date > date('Y-m-d')):
                                                $sc_class = 'btn-warning';
                                            elseif($tsk->last_date && $tsk->last_date <= date('Y-m-d')):
                                                $sc_class = 'btn-danger';
                                            endif;
                                        endif;
                                        $html .= '<span class="btn btn-sm px-2 py-0.5 text-white '.$sc_class.' mr-1">'.$tsk->eLearn->short_code.'</span>';
                                    endforeach;
                                    $html .= '</div>';
                                endif;
                            $html .= '</div>';
                            
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-left">';
                        if($pln->plan->tutor_id > 0):
                            $html .= '<div class="flex justify-start items-center">';
                                $html .= '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block" style="0 0 2.5rem">';
                                    if($pln->proxy_tutor_id > 0):
                                        $html .= '<img src="'.(isset($pln->plan->proxy->employee->photo_url) ? $pln->plan->proxy->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->proxy->employee->full_name) ? $pln->plan->proxy->employee->full_name : 'LCC').'">';
                                    else:
                                        $html .= '<img src="'.(isset($pln->plan->tutor->employee->photo_url) ? $pln->plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->tutor->employee->full_name) ? $pln->plan->tutor->employee->full_name : 'LCC').'">';
                                    endif;
                                $html .= '</div>';
                                $html .= '<div class="inline-block font-medium relative text-'.($empAttendanceLive->count() > 0 ? 'success' : 'danger').'">';
                                    $html .= ($pln->proxy_tutor_id > 0 ? '<span class="line-through">' : '').(isset($pln->plan->tutor->employee->full_name) && !empty($pln->plan->tutor->employee->full_name) ? $pln->plan->tutor->employee->full_name : (isset($pln->plan->tutor->name) ? $pln->plan->tutor->name : 'LCC')).($pln->proxy_tutor_id > 0 ? '</span>' : '');
                                    if($pln->proxy_tutor_id > 0):
                                        $html .= '<br/><span class="'.($proxyAttendanceLive->count() > 0 ? 'text-success' : 'text-danger').'">'.(isset($pln->proxy->employee->full_name) && !empty($pln->proxy->employee->full_name) ? $pln->proxy->employee->full_name : 'Unknown Proxy').'</span>';
                                    endif;
                                $html .= '</div>';
                            $html .= '</div>';
                        elseif($pln->plan->personal_tutor_id > 0):
                            $html .= '<div class="flex justify-start items-center">';
                                $html .= '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block" style="0 0 2.5rem">';
                                    if($pln->proxy_tutor_id > 0):
                                        $html .= '<img src="'.(isset($pln->plan->proxy->employee->photo_url) ? $pln->plan->proxy->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->proxy->employee->full_name) ? $pln->plan->proxy->employee->full_name : 'LCC').'">';
                                    else:
                                        $html .= '<img src="'.(isset($pln->plan->personalTutor->employee->photo_url) ? $pln->plan->personalTutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->personalTutor->employee->full_name) ? $pln->plan->personalTutor->employee->full_name : 'LCC').'">';
                                    endif;
                                $html .= '</div>';
                                $html .= '<div class="inline-block font-medium relative text-'.($empAttendanceLive->count() > 0 ? 'success' : 'danger').'">';
                                    $html .= ($pln->proxy_tutor_id > 0 ? '<span class="line-through">' : '').(isset($pln->plan->personalTutor->employee->full_name) && !empty($pln->plan->personalTutor->employee->full_name) ? $pln->plan->personalTutor->employee->full_name : (isset($pln->plan->personalTutor->name) ? $pln->plan->personalTutor->name : 'LCC')).($pln->proxy_tutor_id > 0 ? '</span>' : '');
                                    if($pln->proxy_tutor_id > 0):
                                        $html .= '<br/><span class="'.($proxyAttendanceLive->count() > 0 ? 'text-success' : 'text-danger').'">'.(isset($pln->proxy->employee->full_name) && !empty($pln->proxy->employee->full_name) ? $pln->proxy->employee->full_name : 'Unknown Proxy').'</span>';
                                    endif;
                                    $html .= '</div>';
                            $html .= '</div>';
                        else:
                            $html .= '<span>N/A</span>';
                        endif;
                    $html .= '</td>';
                    $html .= '<td class="text-left">';
                        $html .= (isset($pln->plan->room->name) && !empty($pln->plan->room->name) ? $pln->plan->room->name : '');
                    $html .= '</td>';
                    $html .= '<td class="text-left">';
                        $html .= '<span class="flex justify-start items-center">';
                            $html .= $classLabel;
                        $html .= '</span>';
                    $html .= '</td>';
                    /*$html .= '<td class="text-left">';
                        $html .= '<span class="flex justify-start items-center">';
                        $html .= '<div class="mt-2 flex flex-col sm:flex-row">';
                        $html .=   '<div data-tw-merge class="flex items-center mr-2 "><input id="radio-switch-'.$pln->id.'" data-tw-merge data-id="'.$pln->id.'"  name="class_file_upload_found'.$pln->id.'" value="Yes"  type="radio" '.(isset($pln->class_file_upload_found) && !empty($pln->class_file_upload_found) && $pln->class_file_upload_found=="Yes" ? 'checked' : '').' class="class-fileupload transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type=\'radio\']]:checked:bg-primary [&[type=\'radio\']]:checked:border-primary [&[type=\'radio\']]:checked:border-opacity-10 [&[type=\'checkbox\']]:checked:bg-primary [&[type=\'checkbox\']]:checked:border-primary [&[type=\'checkbox\']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />';
                        $html .=      '<label data-tw-merge for="radio-switch-'.$pln->id.'" class="cursor-pointer ml-2">Yes</label>';
                        $html .=   '</div>';
                        $html .=   '<div data-tw-merge class="flex items-center mr-2 mt-2 sm:mt-0 "><input id="radio-switch2-'.$pln->id.'" data-tw-merge data-id="'.$pln->id.'"   name="class_file_upload_found'.$pln->id.'" value="No" type="radio" '.(isset($pln->class_file_upload_found) && !empty($pln->class_file_upload_found) && $pln->class_file_upload_found=="No" ? 'checked' : '').' class="class-fileupload transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type=\'radio\']]:checked:bg-primary [&[type=\'radio\']]:checked:border-primary [&[type=\'radio\']]:checked:border-opacity-10 [&[type=\'checkbox\']]:checked:bg-primary [&[type=\'checkbox\']]:checked:border-primary [&[type=\'checkbox\']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"   />';
                        $html .=       '<label data-tw-merge for="radio-switch2-'.$pln->id.'" class="cursor-pointer ml-2">No</label>';
                        $html .=   '</div>';
                        $html .= '</div>';
                        $html .= '</span>';
                    $html .= '</td>';
                    $html .= '<td class="text-right"></td>';*/
                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr class="intro-x">';
                $html .= '<td colspan="5">';
                    $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No calss plan found for the selected date.</div>';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function tutorsExport($term_declaration_id, $course_id = 0){
        $theTerm = TermDeclaration::find($term_declaration_id);
        $usedCourses = Plan::where('term_declaration_id', $term_declaration_id)->pluck('course_id')->unique()->toArray();
        //$tutorIds = Plan::where('term_declaration_id', $term_declaration_id)->pluck('tutor_id')->unique()->toArray();
        $query = Plan::where('term_declaration_id', $term_declaration_id);
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $tutorIds = $query->pluck('tutor_id')->unique()->toArray();

        $tutors = User::with('employee')->whereIn('id', $tutorIds)->orderBy('id', 'ASC')->get();

        $theCollection = [];
        $theCollection[1][] = 'Name';
        $theCollection[1][] = 'Work Type';
        $theCollection[1][] = 'Contracted Hour';
        $theCollection[1][] = 'Class Hour';
        $theCollection[1][] = 'Load';
        $theCollection[1][] = 'No of Module';
        $theCollection[1][] = 'Attendance Rate';
        $theCollection[1][] = 'Expected Submission';
        $theCollection[1][] = 'Submission Rage';

        $row = 2;
        if(!empty($tutors)):
            foreach($tutors as $tut):
                $employee = Employee::with('workingPattern')->where('user_id', $tut->id)->get()->first();
                $classMinutes = $this->calculateTutorHours($tut->id, $term_declaration_id);
                $contracted_hour = (isset($employee->workingPattern->contracted_hour) && !empty($employee->workingPattern->contracted_hour) ? $employee->workingPattern->contracted_hour : '00:00');

                $activePlans = Plan::where('tutor_id', $tut->id)->where('term_declaration_id', $term_declaration_id)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->get();
                $plan_ids = $activePlans->pluck('id')->unique()->toArray();
                $assigned = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->toArray();
                $moduleCreations = $activePlans->pluck('module_creation_id')->toArray();
                $groups = $activePlans->pluck('group_id')->unique()->toArray();

                $cHour = $this->convertStringToMinute($contracted_hour);
                $load = ($cHour > 0 && $classMinutes > 0 ? $classMinutes / $cHour : 0);

                $attendances = $this->getTermAttendanceRate($term_declaration_id, $tut->id, 1);
                $attendance = 0;
                $attendance += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
                $attendance += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
                $attendance += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
                $attendance += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
                $attendance += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
                $attendance += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);

                $attendanceTotal = (isset($attendances->TOTAL) && $attendances->TOTAL > 0) ? $attendances->TOTAL : 0;
                if($attendance > 0 && $attendanceTotal > 0):
                    $attendance_rate = number_format($attendance / $attendanceTotal * 100, 2);
                else:
                    $attendance_rate = '0';
                endif;

                $theCollection[$row][] = (isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee');
                $theCollection[$row][] = (isset($tut->employee->employment->employeeWorkType->name) && !empty($tut->employee->employment->employeeWorkType->name) ? $tut->employee->employment->employeeWorkType->name : '');
                $theCollection[$row][] = $contracted_hour;
                $theCollection[$row][] = $this->calculateHourMinute($classMinutes);
                $theCollection[$row][] = number_format($load, 2);
                $theCollection[$row][] = (!empty($moduleCreations) ? count($moduleCreations) : 0);
                $theCollection[$row][] = $attendance_rate;
                $theCollection[$row][] = (!empty($assigned) ? count($assigned) : 0);
                $theCollection[$row][] = '0.0';
                $row++;
            endforeach;
        endif;

        return Excel::download(new ArrayCollectionExport($theCollection), str_replace(' ', '_', $theTerm->name).'_tutors_report.xlsx');
    }

    /**
     * Follow-up queue reminder — one tutor, or every tutor in the queue when no
     * id is given ("Remind all"). Logged the same way the cancellation notice
     * is: queued through the default SMTP profile.
     */
    public function remindTutor(Request $request){
        $theDate = date('Y-m-d');
        $termDeclarationIds = TermDeclaration::whereNotNull('start_date')->whereNotNull('end_date')
                    ->whereDate('start_date', '<=', $theDate)->whereDate('end_date', '>=', $theDate)
                    ->pluck('id');

        $queue = $this->getLowAttendanceTutors($termDeclarationIds);
        $targetId = (isset($request->tutor_id) && $request->tutor_id > 0 ? (int) $request->tutor_id : 0);
        if($targetId > 0):
            $queue = array_values(array_filter($queue, function($row) use($targetId){ return $row['id'] == $targetId; }));
        endif;

        if(empty($queue)):
            return response()->json(['message' => 'No tutors to remind.', 'sent' => 0], 200);
        endif;

        $siteSettings = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->get()->first();
        $company_name = (isset($siteSettings->value) && !empty($siteSettings->value) ? $siteSettings->value : 'London Churchill College');

        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        if(!$commonSmtp):
            return response()->json(['message' => 'No default SMTP profile configured.'], 422);
        endif;

        $configuration = [
            'smtp_host' => $commonSmtp->smtp_host,
            'smtp_port' => $commonSmtp->smtp_port,
            'smtp_username' => $commonSmtp->smtp_user,
            'smtp_password' => $commonSmtp->smtp_pass,
            'smtp_encryption' => $commonSmtp->smtp_encryption,
            'from_email' => $commonSmtp->smtp_user,
            'from_name' => $company_name,
        ];

        $sent = 0;
        $subject = 'Attendance follow-up from '.$company_name;
        foreach($queue as $row):
            $usr = User::with('employee')->find($row['id']);
            if(!$usr): continue; endif;

            $emails = [];
            if(isset($usr->employee->email) && !empty($usr->employee->email)): $emails[] = $usr->employee->email; endif;
            if(isset($usr->employee->employment->email) && !empty($usr->employee->employment->email)): $emails[] = $usr->employee->employment->email; endif;
            if(empty($emails) && !empty($usr->email)): $emails[] = $usr->email; endif;
            if(empty($emails)): continue; endif;

            $body = 'Dear '.$row['name'].',<br/><br/>';
            $body .= 'Your student attendance rate for the current open terms is currently <strong>'.$row['rate'].'</strong>, which sits below the '.self::FOLLOW_UP_THRESHOLD.'% threshold.<br/>';
            $body .= 'Students affected: '.$row['students'].'<br/><br/>';
            $body .= 'Please review your registers and make sure attendance is fed for every completed class.<br/><br/>';
            $body .= 'Thanks &amp; Regards <br/>'.$company_name;

            UserMailerJob::dispatch($configuration, array_unique($emails), new CommunicationSendMail($subject, $body, []));
            $sent++;
        endforeach;

        return response()->json([
            'message' => ($sent == 1 ? 'Reminder sent to 1 tutor.' : 'Reminders sent to '.$sent.' tutors.'),
            'sent' => $sent,
        ], 200);
    }

    /**
     * Reports dialog. Four report shapes over a date range, written out through
     * the shared array exporter so CSV / XLSX / PDF all come from one payload.
     */
    public function report(Request $request){
        $report = (isset($request->report) && !empty($request->report) ? $request->report : 'attendance');
        $format = (isset($request->format) && !empty($request->format) ? strtolower($request->format) : 'xlsx');
        $range = (isset($request->range) && !empty($request->range) ? $request->range : 'today');

        $today = date('Y-m-d');
        switch($range):
            case 'week':
                $from = date('Y-m-d', strtotime('monday this week'));
                $to = date('Y-m-d', strtotime('sunday this week'));
                break;
            case 'term':
                $termStart = TermDeclaration::whereNotNull('start_date')->whereNotNull('end_date')
                    ->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)
                    ->orderBy('start_date', 'ASC')->value('start_date');
                $from = (!empty($termStart) ? date('Y-m-d', strtotime($termStart)) : $today);
                $to = $today;
                break;
            case 'custom':
                $from = (isset($request->date_from) && !empty($request->date_from) ? date('Y-m-d', strtotime($request->date_from)) : $today);
                $to = (isset($request->date_to) && !empty($request->date_to) ? date('Y-m-d', strtotime($request->date_to)) : $from);
                break;
            default:
                $from = $to = (isset($request->date_from) && !empty($request->date_from) ? date('Y-m-d', strtotime($request->date_from)) : $today);
        endswitch;

        $rows = [];
        $filename = 'programme_dashboard';

        if($report == 'tutor'):
            $termId = (isset($request->term_declaration_id) && $request->term_declaration_id > 0
                ? $request->term_declaration_id
                : TermDeclaration::whereNotNull('start_date')->whereNotNull('end_date')
                    ->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)
                    ->orderBy('id', 'DESC')->value('id'));

            if($termId):
                return $this->tutorsExport($termId, (isset($request->course_id) ? (int) $request->course_id : 0));
            endif;

            $rows[] = ['No open term found for a tutor performance report.'];
            $filename = 'tutor_performance';
        elseif($report == 'absence'):
            $filename = 'staff_absence_log';
            $rows[] = ['Date', 'Employee', 'Expected hours', 'Status'];
            $cursor = $from;
            while(strtotime($cursor) <= strtotime($to)):
                foreach($this->getAbsentEmployees($cursor) as $absent):
                    $rows[] = [
                        date('d-m-Y', strtotime($cursor)),
                        $absent['full_name'],
                        $absent['hourMinute'],
                        'Not clocked in',
                    ];
                endforeach;
                $cursor = date('Y-m-d', strtotime($cursor.' +1 day'));
            endwhile;
        else:
            $exceptionsOnly = ($report == 'exceptions');
            $filename = ($exceptionsOnly ? 'class_exceptions' : 'daily_attendance_register');
            $rows[] = ['Date', 'Time', 'Module', 'Session', 'Course', 'Group', 'Tutor', 'Room', 'Status', 'Started', 'Finished', 'Attendance fed'];

            $cursor = $from;
            while(strtotime($cursor) <= strtotime($to)):
                $day = $this->buildDayPayload($cursor, (isset($request->course_id) ? (int) $request->course_id : 0));
                foreach($day['rows'] as $row):
                    if($exceptionsOnly && !($row['status'] == 'notstarted' || $row['needs_attendance'])): continue; endif;
                    $rows[] = [
                        date('d-m-Y', strtotime($cursor)),
                        $row['time'].' - '.$row['end'],
                        $row['module_name'],
                        $row['session_type'],
                        $row['course'],
                        $row['group'],
                        $row['tutor_name'],
                        $row['room'],
                        ucfirst($row['status'] == 'notstarted' ? 'Not started' : ($row['status'] == 'shortly' ? 'Starting shortly' : $row['status'])),
                        $row['started'],
                        $row['finished'],
                        ($row['fed'] ? 'Yes' : 'No'),
                    ];
                endforeach;
                $cursor = date('Y-m-d', strtotime($cursor.' +1 day'));
            endwhile;
        endif;

        // Excel's array exporter is 1-indexed everywhere else in the app.
        $collection = [];
        $i = 1;
        foreach($rows as $row):
            $collection[$i] = $row;
            $i++;
        endforeach;

        $stamp = ($from == $to ? date('d-m-Y', strtotime($from)) : date('d-m-Y', strtotime($from)).'_to_'.date('d-m-Y', strtotime($to)));

        if($format == 'csv'):
            return Excel::download(new ArrayCollectionExport($collection), $filename.'_'.$stamp.'.csv', \Maatwebsite\Excel\Excel::CSV);
        endif;
        if($format == 'pdf'):
            return Excel::download(new ArrayCollectionExport($collection), $filename.'_'.$stamp.'.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        endif;

        return Excel::download(new ArrayCollectionExport($collection), $filename.'_'.$stamp.'.xlsx');
    }
}
