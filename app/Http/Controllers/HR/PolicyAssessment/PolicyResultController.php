<?php

namespace App\Http\Controllers\HR\PolicyAssessment;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PolicyAssignment;
use App\Models\PolicyAttempt;
use App\Models\PolicyBadge;
use App\Models\PolicyCategory;
use App\Models\PolicyDocument;
use App\Models\PolicyQuestionOption;
use App\Services\PolicyAssessmentService;
use App\Support\PolicyLevel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Policy Assessments › Overview & Results: headline numbers (badges
 * included), completion per category, one row per member of staff, and
 * drill-down to a single attempt exactly as it was shown (HR only — this is
 * the one place correct answers are ever sent to a browser). Also the badge
 * list the employee profile tab reads.
 *
 * The numbers are worked out in grouped SQL over live assignments on live
 * policies, so the page stays quick however many assignments there are.
 * Badges are counted when live (not revoked) on a policy that has not been
 * deleted, the same as PolicyAssessmentService::badgeCounts().
 */
class PolicyResultController extends Controller
{
    /** Failed and out of attempts (policy limit plus any extra HR allowed). */
    const SQL_LOCKED = "(pa.status = 'failed' AND pd.max_attempts IS NOT NULL AND pa.attempts_count >= pd.max_attempts + pa.extra_attempts)";

    /** Client sort field => SQL alias. Anything else is ignored. */
    const SORTABLE = [
        'assigned_count' => 'assigned_count',
        'passed_count' => 'passed_count',
        'failed_count' => 'failed_count',
        'not_started_count' => 'not_started_count',
        'overdue_count' => 'overdue_count',
        'in_progress_count' => 'in_progress_count',
        'completion_percent' => 'completion_percent',
        'last_activity_at' => 'last_activity_at',
        'badges_total' => 'badges_total',
    ];

    /** Badge list client sort fields. Anything else is ignored. */
    const BADGE_SORTABLE = ['id', 'awarded_at', 'level', 'score', 'policy_title', 'employee_name', 'deleted_at'];

    /** Badge list status filter: 1 = live, 2 = revoked, all = both. */
    const BADGE_STATUSES = ['1', '2', 'all'];

    public function index()
    {
        $this->guard();
        /* A timed test whose clock ran out is marked before anything is counted. */
        app(PolicyAssessmentService::class)->finaliseExpiredAttempts();

        $today = Carbon::today()->format('Y-m-d');

        $kpiRow = $this->assignmentBase('current')
            ->selectRaw('COUNT(DISTINCT pa.employee_id) as staff_count')
            ->selectRaw('COUNT(pa.id) as total_count')
            ->selectRaw("SUM(CASE WHEN pa.status = 'passed' THEN 1 ELSE 0 END) as passed_count")
            ->selectRaw("SUM(CASE WHEN pa.status <> 'passed' AND pa.due_date IS NOT NULL AND pa.due_date < ? THEN 1 ELSE 0 END) as overdue_count", [$today])
            ->selectRaw("SUM(CASE WHEN pa.status <> 'passed' AND oa.policy_assignment_id IS NOT NULL AND NOT ".self::SQL_LOCKED." THEN 1 ELSE 0 END) as in_progress_count")
            ->selectRaw('SUM(CASE WHEN '.self::SQL_LOCKED.' THEN 1 ELSE 0 END) as locked_count')
            ->first();

        $total = (int) (isset($kpiRow->total_count) ? $kpiRow->total_count : 0);
        $passed = (int) (isset($kpiRow->passed_count) ? $kpiRow->passed_count : 0);
        $kpis = [
            'staff' => (int) (isset($kpiRow->staff_count) ? $kpiRow->staff_count : 0),
            'total' => $total,
            'passed' => $passed,
            'passed_percent' => ($total > 0 ? (int) round($passed / $total * 100) : 0),
            'overdue' => (int) (isset($kpiRow->overdue_count) ? $kpiRow->overdue_count : 0),
            'in_progress' => (int) (isset($kpiRow->in_progress_count) ? $kpiRow->in_progress_count : 0),
            'locked' => (int) (isset($kpiRow->locked_count) ? $kpiRow->locked_count : 0),
        ];

        /* Badges held by current staff: live (not revoked), on live policies. */
        $badgeRow = $this->badgeBase('current');
        foreach(PolicyLevel::all() as $level):
            $badgeRow->selectRaw('SUM(CASE WHEN pb.level = ? THEN 1 ELSE 0 END) as '.$level.'_count', [$level]);
        endforeach;
        $badgeRow = $badgeRow->selectRaw('COUNT(pb.id) as total_count')->first();
        $badgeKpi = ['total' => (int) (isset($badgeRow->total_count) ? $badgeRow->total_count : 0), 'levels' => []];
        foreach(PolicyLevel::all() as $level):
            $column = $level.'_count';
            $badgeKpi['levels'][$level] = (int) (isset($badgeRow->$column) ? $badgeRow->$column : 0);
        endforeach;

        $categoryTotals = $this->assignmentBase('current')
            ->groupBy('pd.policy_category_id')
            ->select('pd.policy_category_id')
            ->selectRaw('COUNT(pa.id) as assigned_count')
            ->selectRaw("SUM(CASE WHEN pa.status = 'passed' THEN 1 ELSE 0 END) as passed_count")
            ->get()
            ->keyBy('policy_category_id');

        $categoryBars = [];
        $categories = PolicyCategory::withCount('policies')->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->get();
        foreach($categories as $category):
            $assigned = (isset($categoryTotals[$category->id]) ? (int) $categoryTotals[$category->id]->assigned_count : 0);
            $passedInCategory = (isset($categoryTotals[$category->id]) ? (int) $categoryTotals[$category->id]->passed_count : 0);
            $categoryBars[] = [
                'id' => $category->id,
                'name' => $category->name,
                'is_active' => (bool) $category->is_active,
                'policies' => (int) $category->policies_count,
                'assigned' => $assigned,
                'passed' => $passedInCategory,
                'percent' => ($assigned > 0 ? (int) round($passedInCategory / $assigned * 100) : 0),
            ];
        endforeach;

        return view('pages.hr.policy-assessment.index', [
            'title' => 'Policy Assessments - London Churchill College',
            'subtitle' => 'Policy Assessments',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Policy Assessments', 'href' => route('policy.assessment')],
                ['label' => 'Overview & Results', 'href' => 'javascript:void(0);'],
            ],
            'kpis' => $kpis,
            'badgeKpi' => $badgeKpi,
            'categoryBars' => $categoryBars,
            'departments' => Department::orderBy('name', 'ASC')->get(['id', 'name']),
        ]);
    }

    /** One row per member of staff with live assignments, aggregated in SQL. */
    public function list(Request $request)
    {
        $this->guard();
        app(PolicyAssessmentService::class)->finaliseExpiredAttempts();

        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? trim($request->querystr) : '');
        $department = (isset($request->department) && $request->department > 0 ? (int) $request->department : 0);
        $staff = (isset($request->staff) && $request->staff == 'all' ? 'all' : 'current');
        $today = Carbon::today()->format('Y-m-d');

        $query = $this->assignmentBase($staff)
            ->groupBy('pa.employee_id', 'e.first_name', 'e.last_name')
            ->select('pa.employee_id', 'e.first_name', 'e.last_name')
            ->selectRaw('COUNT(pa.id) as assigned_count')
            ->selectRaw("SUM(CASE WHEN pa.status = 'passed' THEN 1 ELSE 0 END) as passed_count")
            ->selectRaw("SUM(CASE WHEN pa.status = 'failed' THEN 1 ELSE 0 END) as failed_count")
            ->selectRaw('SUM(CASE WHEN '.self::SQL_LOCKED.' THEN 1 ELSE 0 END) as locked_count')
            ->selectRaw("SUM(CASE WHEN pa.status = 'pending' AND oa.policy_assignment_id IS NULL THEN 1 ELSE 0 END) as not_started_count")
            ->selectRaw("SUM(CASE WHEN pa.status <> 'passed' AND oa.policy_assignment_id IS NOT NULL AND NOT ".self::SQL_LOCKED." THEN 1 ELSE 0 END) as in_progress_count")
            ->selectRaw("SUM(CASE WHEN pa.status <> 'passed' AND pa.due_date IS NOT NULL AND pa.due_date < ? THEN 1 ELSE 0 END) as overdue_count", [$today])
            ->selectRaw("ROUND(SUM(CASE WHEN pa.status = 'passed' THEN 1 ELSE 0 END) / COUNT(pa.id) * 100) as completion_percent")
            ->selectRaw("NULLIF(MAX(GREATEST(COALESCE(pa.last_attempt_at, '1000-01-01 00:00:00'), COALESCE(pa.policy_opened_at, '1000-01-01 00:00:00'), COALESCE(pa.acknowledged_at, '1000-01-01 00:00:00'), COALESCE(oa.open_started_at, '1000-01-01 00:00:00'))), '1000-01-01 00:00:00') as last_activity_at");

        /* Badges per member of staff, pre-aggregated (one row each) so the join
           cannot multiply the assignment counts above. */
        $badgeCounts = DB::table('policy_badges as pb')
            ->join('policy_documents as bpd', function ($join) {
                $join->on('bpd.id', '=', 'pb.policy_document_id')->whereNull('bpd.deleted_at');
            })
            ->whereNull('pb.deleted_at')
            ->groupBy('pb.employee_id')
            ->select('pb.employee_id')
            ->selectRaw('COUNT(pb.id) as total_badges');
        foreach(PolicyLevel::all() as $level):
            $badgeCounts->selectRaw('SUM(CASE WHEN pb.level = ? THEN 1 ELSE 0 END) as '.$level.'_badges', [$level]);
        endforeach;
        $query->leftJoinSub($badgeCounts, 'bc', 'bc.employee_id', '=', 'pa.employee_id')
            ->selectRaw('COALESCE(MAX(bc.total_badges), 0) as badges_total');
        foreach(PolicyLevel::all() as $level):
            $query->selectRaw('COALESCE(MAX(bc.'.$level.'_badges), 0) as badges_'.$level);
        endforeach;

        if($queryStr !== ''):
            $query->whereRaw("CONCAT(e.first_name, ' ', e.last_name) LIKE ?", ['%'.$queryStr.'%']);
        endif;
        if($department > 0):
            /* The employee's current employment is the latest row, as Employee::employment() reads it. */
            $query->whereExists(function ($q) use ($department) {
                $q->select(DB::raw(1))
                    ->from('employments as em')
                    ->whereColumn('em.employee_id', 'e.id')
                    ->where('em.department_id', $department)
                    ->whereNull('em.deleted_at')
                    ->whereRaw('em.id = (SELECT MAX(em2.id) FROM employments em2 WHERE em2.employee_id = e.id AND em2.deleted_at IS NULL)');
            });
        endif;

        $total_rows = DB::query()->fromSub(clone $query, 'agg')->count();
        $page = (isset($request->page) && $request->page > 0 ? (int) $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? (int) $request->size : 10));
        $perpage = max(1, $perpage);
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $sorters = (isset($request->sorters) && is_array($request->sorters) ? $request->sorters : []);
        foreach($sorters as $sort):
            $field = (is_array($sort) && isset($sort['field']) ? (string) $sort['field'] : '');
            $dir = (is_array($sort) && isset($sort['dir']) && strtolower((string) $sort['dir']) == 'asc' ? 'asc' : 'desc');
            if($field == 'employee_name'):
                $query->orderBy('e.first_name', $dir)->orderBy('e.last_name', $dir);
            elseif(isset(self::SORTABLE[$field])):
                $query->orderBy(self::SORTABLE[$field], $dir);
            endif;
        endforeach;
        $query->orderBy('e.first_name', 'asc')->orderBy('e.last_name', 'asc')->orderBy('pa.employee_id', 'asc');

        $rows = $query->skip($offset)->take($perpage)->get();

        $employees = Employee::withTrashed()
            ->with(['employment.employeeJobTitle', 'employment.department'])
            ->whereIn('id', $rows->pluck('employee_id')->all())
            ->get()
            ->keyBy('id');

        $data = [];
        $i = $offset + 1;
        foreach($rows as $row):
            $employee = (isset($employees[$row->employee_id]) ? $employees[$row->employee_id] : null);
            $employment = (isset($employee->employment) ? $employee->employment : null);
            $data[] = [
                'id' => (int) $row->employee_id,
                'sl' => $i,
                'employee_name' => ($employee ? $employee->full_name : trim($row->first_name.' '.$row->last_name)),
                'employee_active' => (isset($employee->status) && $employee->status == 1 ? 1 : 0),
                'job_title' => (isset($employment->employeeJobTitle->name) ? $employment->employeeJobTitle->name : ''),
                'department' => (isset($employment->department->name) ? $employment->department->name : ''),
                'assigned_count' => (int) $row->assigned_count,
                'passed_count' => (int) $row->passed_count,
                'failed_count' => (int) $row->failed_count,
                'locked_count' => (int) $row->locked_count,
                'not_started_count' => (int) $row->not_started_count,
                'in_progress_count' => (int) $row->in_progress_count,
                'overdue_count' => (int) $row->overdue_count,
                'completion_percent' => (int) $row->completion_percent,
                'last_activity_at' => (!empty($row->last_activity_at) ? Carbon::parse($row->last_activity_at)->format('d M Y H:i') : ''),
                'badges_total' => (int) $row->badges_total,
                'badges_beginner' => (int) $row->badges_beginner,
                'badges_intermediate' => (int) $row->badges_intermediate,
                'badges_expert' => (int) $row->badges_expert,
                'profile_url' => route('employee.policy.assessment', $row->employee_id),
            ];
            $i++;
        endforeach;

        return response()->json(['last_page' => $last_page, 'total_rows' => $total_rows, 'data' => $data]);
    }

    /** One member of staff: headline numbers and every live assignment (with the role it came through) and its attempts. */
    public function employee(Employee $employee, PolicyAssessmentService $service)
    {
        $this->guard();
        $service->finaliseExpiredAttempts((int) $employee->id);

        $employee->load(['employment.employeeJobTitle', 'employment.department']);
        $assignments = PolicyAssignment::where('employee_id', $employee->id)
            ->whereHas('policy', function ($q) {
                $q->whereNull('policy_documents.deleted_at');
            })
            ->with([
                'openAttempt',
                /* answered_count preloaded, so the attempt rows need no query each. */
                'attempts' => function ($q) {
                    $q->withCount(['answers as answered_count' => function ($a) {
                        $a->whereNotNull('selected_option_id');
                    }]);
                },
                'badge',
                'role',
                'assignedBy:id,name',
                'policy.category' => function ($q) {
                    $q->withTrashed();
                },
            ])
            ->get()
            ->sortBy(function ($assignment) {
                $categorySort = (isset($assignment->policy->category->sort_order) ? (int) $assignment->policy->category->sort_order : 0);
                $policySort = (isset($assignment->policy->sort_order) ? (int) $assignment->policy->sort_order : 0);
                /* The same policy at several levels reads easiest first. */
                $levelSort = array_search($assignment->levelKey(), PolicyLevel::all(), true);

                return sprintf("%06d-%06d-%s\x01%d", $categorySort, $policySort, (isset($assignment->policy->title) ? strtolower($assignment->policy->title) : ''), ($levelSort === false ? 9 : $levelSort));
            })
            ->values();

        $rows = [];
        foreach($assignments as $assignment):
            $policy = $assignment->policy;
            $level = $assignment->levelKey();
            $badge = $assignment->badge;
            /* The role it was assigned through (archived roles included), as the Assignments list shows it. */
            $role = PolicyAssignmentController::roleFields($assignment);
            $attempts = [];
            foreach($assignment->attempts as $attempt):
                $attempts[] = $this->attemptSummary($attempt);
            endforeach;

            $rows[] = [
                'id' => $assignment->id,
                'policy_title' => (isset($policy->title) ? $policy->title : 'Deleted policy'),
                'category' => (isset($policy->category->name) ? $policy->category->name : ''),
                'level' => $level,
                'level_label' => PolicyLevel::label($level),
                'role_id' => $role['role_id'],
                'role_name' => $role['role_name'],
                'role_archived' => $role['role_archived'],
                'has_badge' => ($badge ? 1 : 0),
                'badge_id' => ($badge ? (int) $badge->id : null),
                'badge_awarded_at' => ($badge && $badge->awarded_at ? $badge->awarded_at->format('d M Y') : ''),
                'policy_inactive' => (isset($policy->is_active) && !$policy->is_active ? 1 : 0),
                'status' => $assignment->status,
                'display_status' => $assignment->display_status,
                'display_status_label' => $assignment->display_status_label,
                'assigned_at' => ($assignment->assigned_at ? $assignment->assigned_at->format('d M Y') : ''),
                'assigned_by' => (isset($assignment->assignedBy->name) ? $assignment->assignedBy->name : ''),
                'due_date' => ($assignment->due_date ? $assignment->due_date->format('d M Y') : ''),
                'is_overdue' => ($assignment->isOverdue() ? 1 : 0),
                'attempts_count' => (int) $assignment->attempts_count,
                'max_attempts' => $assignment->maxAttemptsAllowed(),
                'attempts_left' => $assignment->attemptsLeft(),
                'best_score' => ($assignment->best_score !== null ? (float) $assignment->best_score : null),
                'policy_opened_at' => ($assignment->policy_opened_at ? $assignment->policy_opened_at->format('d M Y H:i') : ''),
                'acknowledged_at' => ($assignment->acknowledged_at ? $assignment->acknowledged_at->format('d M Y H:i') : ''),
                'passed_at' => ($assignment->passed_at ? $assignment->passed_at->format('d M Y') : ''),
                'note' => ($assignment->note !== null ? $assignment->note : ''),
                'attempts' => $attempts,
            ];
        endforeach;

        $employment = $employee->employment;

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'job_title' => (isset($employment->employeeJobTitle->name) ? $employment->employeeJobTitle->name : ''),
                'department' => (isset($employment->department->name) ? $employment->department->name : ''),
                'profile_url' => route('employee.policy.assessment', $employee->id),
            ],
            'summary' => $service->employeeSummary($employee->id),
            'assignments' => $rows,
        ]);
    }

    /**
     * One attempt as the member of staff saw it: questions in the order shown,
     * each with its level and its options in the shuffled order, what they
     * chose (or that they did not answer) and which option was correct, plus
     * how many questions of each level the exam drew (`mix`). HR only.
     *
     * `tab_activity` lists each time the test tab or window was left, oldest
     * first (see tabActivityRows()); the totals are on `attempt` (tab_exits,
     * away_seconds, away_label), here counted from that same log.
     */
    public function attempt(PolicyAttempt $attempt, PolicyAssessmentService $service)
    {
        $this->guard();
        /* If this is a timed attempt whose clock has run out, mark it first. */
        if($service->finaliseExpiredAttempts((int) $attempt->employee_id) > 0):
            $attempt->refresh();
        endif;
        $activity = $service->tabActivity($attempt);

        $attempt->load([
            'answers',
            'employee' => function ($q) {
                $q->withTrashed();
            },
            'policy.category' => function ($q) {
                $q->withTrashed();
            },
        ]);

        /* Only needed for rows drawn before options were snapshotted. */
        $fallbackIds = [];
        foreach($attempt->answers as $answer):
            if(empty($answer->options_snapshot) && is_array($answer->option_order)):
                $fallbackIds = array_merge($fallbackIds, $answer->option_order);
            endif;
        endforeach;
        $fallbackTexts = (!empty($fallbackIds) ? PolicyQuestionOption::withTrashed()->whereIn('id', array_unique(array_map('intval', $fallbackIds)))->pluck('option_text', 'id') : collect());

        $level = (PolicyLevel::isValid($attempt->level) ? $attempt->level : PolicyLevel::BEGINNER);
        $mix = array_fill_keys(PolicyLevel::all(), 0);

        $answers = [];
        $n = 0;
        foreach($attempt->answers as $answer):
            $n++;
            /* Attempts drawn before exams mixed levels have no level per
               question; back then every question was of the exam's own level. */
            $questionLevel = (PolicyLevel::isValid($answer->question_level) ? $answer->question_level : $level);
            $mix[$questionLevel] += 1;
            $shown = [];
            if(is_array($answer->options_snapshot) && !empty($answer->options_snapshot)):
                foreach($answer->options_snapshot as $option):
                    if(isset($option['id'])):
                        $shown[] = ['id' => (int) $option['id'], 'text' => (isset($option['text']) ? (string) $option['text'] : '')];
                    endif;
                endforeach;
            elseif(is_array($answer->option_order)):
                foreach($answer->option_order as $optionId):
                    $shown[] = ['id' => (int) $optionId, 'text' => (isset($fallbackTexts[(int) $optionId]) ? (string) $fallbackTexts[(int) $optionId] : '')];
                endforeach;
            endif;

            $selectedId = ($answer->selected_option_id !== null ? (int) $answer->selected_option_id : null);
            $correctId = ($answer->correct_option_id !== null ? (int) $answer->correct_option_id : null);
            $options = [];
            foreach($shown as $option):
                $options[] = [
                    'id' => $option['id'],
                    'text' => $option['text'],
                    'selected' => ($selectedId !== null && $option['id'] === $selectedId),
                    'correct' => ($correctId !== null && $option['id'] === $correctId),
                ];
            endforeach;

            $answers[] = [
                'n' => $n,
                'question_text' => (string) $answer->question_text,
                'question_level' => $questionLevel,
                'question_level_label' => PolicyLevel::label($questionLevel),
                'options' => $options,
                'answered' => ($selectedId !== null),
                'selected_option_id' => $selectedId,
                'selected_option_text' => ($answer->selected_option_text !== null ? $answer->selected_option_text : ''),
                'correct_option_id' => $correctId,
                'correct_option_text' => ($answer->correct_option_text !== null ? $answer->correct_option_text : ''),
                'is_correct' => ($answer->is_correct === null ? null : (bool) $answer->is_correct),
            ];
        endforeach;

        $policy = $attempt->policy;

        return response()->json([
            'attempt' => $this->attemptSummary($attempt, $activity),
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'mix' => $mix,
            'mix_label' => $this->mixLabel($level, $mix),
            'tab_activity' => $this->tabActivityRows($activity),
            'employee' => [
                'id' => (int) $attempt->employee_id,
                'name' => (isset($attempt->employee->full_name) ? $attempt->employee->full_name : 'Unknown employee'),
            ],
            'policy' => [
                'id' => (int) $attempt->policy_document_id,
                'title' => (isset($policy->title) ? $policy->title : 'Deleted policy'),
                'category' => (isset($policy->category->name) ? $policy->category->name : ''),
            ],
            'answers' => $answers,
        ]);
    }

    /**
     * Badges, for the employee profile tab (and any other HR list). Params:
     * employee, policy, level, status (1 = live, the default; 2 = revoked;
     * all = both), page, size, sorters. Badges on a deleted policy are left
     * out, as they are from the counts.
     */
    public function badges(Request $request)
    {
        $this->guard();

        $employee = (isset($request->employee) && $request->employee > 0 ? (int) $request->employee : 0);
        $policy = (isset($request->policy) && $request->policy > 0 ? (int) $request->policy : 0);
        $level = (isset($request->level) && is_string($request->level) && PolicyLevel::isValid($request->level) ? $request->level : '');
        $status = (isset($request->status) && in_array((string) $request->status, self::BADGE_STATUSES, true) ? (string) $request->status : '1');

        $query = PolicyBadge::query();
        if($status == '2'):
            $query->onlyTrashed();
        elseif($status == 'all'):
            $query->withTrashed();
        endif;
        $query->whereHas('policy', function ($q) {
            $q->whereNull('policy_documents.deleted_at');
        });
        if($employee > 0):
            $query->where('policy_badges.employee_id', $employee);
        endif;
        if($policy > 0):
            $query->where('policy_badges.policy_document_id', $policy);
        endif;
        if($level !== ''):
            $query->ofLevel($level);
        endif;

        $total_rows = (clone $query)->count();
        $page = (isset($request->page) && $request->page > 0 ? (int) $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? (int) $request->size : 10));
        $perpage = max(1, $perpage);
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $sorters = (isset($request->sorters) && is_array($request->sorters) ? $request->sorters : []);
        $applied = false;
        foreach($sorters as $sort):
            $field = (is_array($sort) && isset($sort['field']) ? (string) $sort['field'] : '');
            $dir = (is_array($sort) && isset($sort['dir']) && strtolower((string) $sort['dir']) == 'asc' ? 'asc' : 'desc');
            if(!in_array($field, self::BADGE_SORTABLE, true)):
                continue;
            endif;

            if($field == 'level'):
                /* Easiest first, not alphabetical. $dir is 'asc' or 'desc' only. */
                $query->orderByRaw("FIELD(policy_badges.level, 'beginner', 'intermediate', 'expert') ".$dir);
            elseif($field == 'policy_title'):
                $query->orderBy(PolicyDocument::withTrashed()->select('title')->whereColumn('policy_documents.id', 'policy_badges.policy_document_id')->limit(1), $dir);
            elseif($field == 'employee_name'):
                $query->orderBy(Employee::withTrashed()->select('first_name')->whereColumn('employees.id', 'policy_badges.employee_id')->limit(1), $dir)
                    ->orderBy(Employee::withTrashed()->select('last_name')->whereColumn('employees.id', 'policy_badges.employee_id')->limit(1), $dir);
            else:
                $query->orderBy('policy_badges.'.$field, $dir);
            endif;
            $applied = true;
        endforeach;
        if(!$applied):
            $query->orderBy('policy_badges.awarded_at', 'desc');
        endif;
        $query->orderBy('policy_badges.id', 'desc');

        $badges = $query->with([
                'policy.category' => function ($q) {
                    $q->withTrashed();
                },
                'employee' => function ($q) {
                    $q->withTrashed();
                },
                'revokedBy:id,name',
            ])
            ->skip($offset)->take($perpage)->get();

        $data = [];
        $i = $offset + 1;
        foreach($badges as $badge):
            $badgeLevel = (PolicyLevel::isValid($badge->level) ? $badge->level : PolicyLevel::BEGINNER);
            $data[] = [
                'id' => (int) $badge->id,
                'sl' => $i,
                'employee_id' => (int) $badge->employee_id,
                'employee_name' => (isset($badge->employee->full_name) ? $badge->employee->full_name : 'Unknown employee'),
                'policy_id' => (int) $badge->policy_document_id,
                'policy_title' => (isset($badge->policy->title) ? $badge->policy->title : 'Deleted policy'),
                'category' => (isset($badge->policy->category->name) ? $badge->policy->category->name : ''),
                'level' => $badgeLevel,
                'level_label' => PolicyLevel::label($badgeLevel),
                'badge_name' => PolicyLevel::badgeName($badgeLevel),
                'score' => ($badge->score !== null ? (float) $badge->score : null),
                'awarded_at' => ($badge->awarded_at ? $badge->awarded_at->format('d M Y') : ''),
                'assignment_id' => ($badge->policy_assignment_id ? (int) $badge->policy_assignment_id : null),
                'attempt_id' => ($badge->policy_attempt_id ? (int) $badge->policy_attempt_id : null),
                'review_url' => ($badge->policy_attempt_id ? route('policy.assessment.attempt.show', $badge->policy_attempt_id) : ''),
                'revoked' => ($badge->trashed() ? 1 : 0),
                'revoked_at' => ($badge->trashed() && $badge->deleted_at ? $badge->deleted_at->format('d M Y') : ''),
                'revoked_by' => ($badge->trashed() && isset($badge->revokedBy->name) ? $badge->revokedBy->name : ''),
                'deleted_at' => $badge->deleted_at,
            ];
            $i++;
        endforeach;

        return response()->json(['last_page' => $last_page, 'total_rows' => $total_rows, 'data' => $data]);
    }

    /**
     * Live assignments on live policies for staff who are still on file
     * ('current' also requires an active employee), with their open attempt
     * (if any) joined as `oa`.
     */
    private function assignmentBase(string $staff = 'current')
    {
        $openAttempts = DB::table('policy_attempts')
            ->select('policy_assignment_id')
            ->selectRaw('MAX(started_at) as open_started_at')
            ->where('status', PolicyAttempt::STATUS_IN_PROGRESS)
            ->whereNull('deleted_at')
            ->groupBy('policy_assignment_id');

        $query = DB::table('policy_assignments as pa')
            ->join('policy_documents as pd', function ($join) {
                $join->on('pd.id', '=', 'pa.policy_document_id')->whereNull('pd.deleted_at');
            })
            ->join('employees as e', function ($join) {
                $join->on('e.id', '=', 'pa.employee_id')->whereNull('e.deleted_at');
            })
            ->leftJoinSub($openAttempts, 'oa', 'oa.policy_assignment_id', '=', 'pa.id')
            ->whereNull('pa.deleted_at');
        if($staff != 'all'):
            $query->where('e.status', 1);
        endif;

        return $query;
    }

    /**
     * Live (not revoked) badges on policies that have not been deleted, held
     * by staff still on file ('current' also requires an active employee).
     */
    private function badgeBase(string $staff = 'current')
    {
        $query = DB::table('policy_badges as pb')
            ->join('policy_documents as pd', function ($join) {
                $join->on('pd.id', '=', 'pb.policy_document_id')->whereNull('pd.deleted_at');
            })
            ->join('employees as e', function ($join) {
                $join->on('e.id', '=', 'pb.employee_id')->whereNull('e.deleted_at');
            })
            ->whereNull('pb.deleted_at');
        if($staff != 'all'):
            $query->where('e.status', 1);
        endif;

        return $query;
    }

    /**
     * One attempt for a list or the review header. Besides the result it says
     * how the attempt ran: the time limit it started with (NULL = untimed),
     * how long it took (submitted minus started), whether the clock ran out,
     * and how many questions were answered — an unanswered question is marked
     * wrong. The *_label values are ready to show; answered_label is only
     * filled when a submitted attempt left questions unanswered.
     *
     * It also says how often the test tab or window was left: tab_exits,
     * away_seconds (the total), away_label ('45s', '1m 12s', '1h 02m') and
     * tab_exits_label ('3 tab exits · 1m 12s'); both labels are '' when the
     * test was never left. A list reads the totals kept on the attempt, so
     * it needs no query per row; the review passes the log itself
     * (PolicyAssessmentService::tabActivity()) as $activity.
     */
    private function attemptSummary(PolicyAttempt $attempt, ?array $activity = null): array
    {
        $submitted = ($attempt->status == PolicyAttempt::STATUS_SUBMITTED);
        $level = (PolicyLevel::isValid($attempt->level) ? $attempt->level : PolicyLevel::BEGINNER);
        $total = (int) $attempt->total_questions;
        $timeLimit = ($attempt->isTimed() && (int) $attempt->time_limit_minutes > 0 ? (int) $attempt->time_limit_minutes : null);
        $taken = ($submitted && $attempt->started_at && $attempt->submitted_at ? max(0, (int) $attempt->started_at->diffInSeconds($attempt->submitted_at, false)) : null);
        $answered = $this->answeredCount($attempt);
        $tabExits = max(0, (int) ($activity !== null ? $activity['exits'] : $attempt->tab_exits));
        $awaySeconds = max(0, (int) ($activity !== null ? $activity['away_seconds'] : $attempt->away_seconds));

        return [
            'id' => $attempt->id,
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'attempt_no' => (int) $attempt->attempt_no,
            'status' => $attempt->status,
            'status_label' => ($submitted ? ($attempt->passed ? 'Passed' : 'Not passed') : 'In progress'),
            'started_at' => ($attempt->started_at ? $attempt->started_at->format('d M Y H:i') : ''),
            'submitted_at' => ($attempt->submitted_at ? $attempt->submitted_at->format('d M Y H:i') : ''),
            'score' => ($attempt->score !== null ? (float) $attempt->score : null),
            'correct_count' => ($attempt->correct_count !== null ? (int) $attempt->correct_count : null),
            'total_questions' => $total,
            'pass_mark' => (int) $attempt->pass_mark,
            'passed' => ($submitted ? (bool) $attempt->passed : null),
            'time_limit_minutes' => $timeLimit,
            'time_limit_label' => ($timeLimit !== null ? $timeLimit.' min' : 'Untimed'),
            'time_taken_seconds' => $taken,
            'time_taken_label' => ($taken !== null ? $this->durationLabel($taken) : ''),
            'timed_out' => (bool) $attempt->timed_out,
            'answered_count' => $answered,
            'answered_label' => ($submitted && $answered < $total ? $answered.' of '.$total.' answered' : ''),
            'tab_exits' => $tabExits,
            'away_seconds' => $awaySeconds,
            'away_label' => ($tabExits > 0 ? self::awayLabel($awaySeconds) : ''),
            'tab_exits_label' => self::tabExitsLabel($tabExits, $awaySeconds),
            'review_url' => route('policy.assessment.attempt.show', $attempt->id),
        ];
    }

    /**
     * The exits of one attempt for the review, oldest first: the service's
     * rows (at, question_no, seconds, type, type_label, open) plus `where`
     * ('Question 3', or 'Review screen' when no question was on screen) and
     * `seconds_label` ('45s', '1m 12s'). An exit that is still open — the
     * test is running and they have not come back — has no seconds yet, so
     * its seconds_label is ''.
     */
    private function tabActivityRows(array $activity): array
    {
        $rows = [];
        foreach((isset($activity['events']) && is_array($activity['events']) ? $activity['events'] : []) as $event):
            $questionNo = (isset($event['question_no']) && (int) $event['question_no'] > 0 ? (int) $event['question_no'] : null);
            $seconds = (isset($event['seconds']) ? max(0, (int) $event['seconds']) : null);
            $rows[] = [
                'at' => (isset($event['at']) ? (string) $event['at'] : ''),
                'question_no' => $questionNo,
                'where' => ($questionNo !== null ? 'Question '.$questionNo : 'Review screen'),
                'type' => (isset($event['type']) ? (string) $event['type'] : ''),
                'type_label' => (isset($event['type_label']) ? (string) $event['type_label'] : ''),
                'seconds' => $seconds,
                'seconds_label' => ($seconds !== null ? self::awayLabel($seconds) : ''),
                'open' => !empty($event['open']),
            ];
        endforeach;

        return $rows;
    }

    /** Time away from the test: '45s', '1m 12s', or '1h 02m' from an hour up. */
    public static function awayLabel(int $seconds): string
    {
        $seconds = max(0, $seconds);
        if($seconds >= 3600):
            return sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
        endif;
        if($seconds >= 60):
            return sprintf('%dm %02ds', intdiv($seconds, 60), $seconds % 60);
        endif;

        return $seconds.'s';
    }

    /** '3 tab exits · 1m 12s' / '1 tab exit · 45s'; '' when the test was never left. */
    public static function tabExitsLabel(int $exits, int $seconds): string
    {
        if($exits <= 0):
            return '';
        endif;

        return $exits.' tab '.($exits == 1 ? 'exit' : 'exits').' · '.self::awayLabel($seconds);
    }

    /** Questions answered in an attempt: preloaded as answered_count, else from loaded answers, else one query. */
    private function answeredCount(PolicyAttempt $attempt): int
    {
        if(array_key_exists('answered_count', $attempt->getAttributes())):
            return (int) $attempt->getAttributes()['answered_count'];
        endif;
        if($attempt->relationLoaded('answers')):
            return (int) $attempt->answers->whereNotNull('selected_option_id')->count();
        endif;

        return (int) $attempt->answers()->whereNotNull('selected_option_id')->count();
    }

    /** Seconds as '4:05' (minutes:seconds), or '1h 02m' from an hour up. */
    private function durationLabel(int $seconds): string
    {
        $seconds = max(0, $seconds);
        if($seconds >= 3600):
            return sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
        endif;

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    /** 'Beginner exam: 7 Beginner · 2 Intermediate · 1 Expert' — the questions an exam actually drew, by level. */
    private function mixLabel(string $examLevel, array $mix): string
    {
        $parts = [];
        foreach(PolicyLevel::all() as $questionLevel):
            $parts[] = (isset($mix[$questionLevel]) ? (int) $mix[$questionLevel] : 0).' '.PolicyLevel::label($questionLevel);
        endforeach;

        return PolicyLevel::label($examLevel).' exam: '.implode(' · ', $parts);
    }

    private function guard(): void
    {
        abort_unless(PolicyAssessmentService::canManage(), 403, 'You are not permitted to manage policy assessments.');
    }
}
