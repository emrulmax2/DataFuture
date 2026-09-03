<?php

namespace App\Http\Controllers\GroupLeader;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\AssessmentPlan;
use App\Models\Employee;
use App\Models\Group;
use App\Models\GroupLeader;
use App\Models\Grade;
use App\Models\GroupLeaderContact;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Result;
use App\Models\Student;
use App\Models\TermDeclaration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Group Leader dashboard.
 *
 * Two levels:
 *
 *   index()  "My groups" - every group this person leads, per term, as cards
 *   show()   one group   - Overview (KPIs, worklist, modules, staff) and
 *                          Today's classes (the live day view)
 *
 * The leader oversees rather than teaches, so the only thing this screen
 * writes is a contact log entry against a student. Everything else is read.
 *
 * Every action is behind the Group Leader "view" privilege AND the person's
 * own assignments: a group id in a request that they do not lead is a 403, not
 * a filtered-out row.
 */
class DashboardController extends Controller
{
    /**
     * Attendance feed statuses, by meaning.
     *
     * 1 Present, 2 Online, 5 Late count as attendance; 6/7/8 are excused
     * absences. The college's headline figure counts excused absence as
     * attended, which is what the personal tutor and programme dashboards
     * report, so this one matches them.
     */
    private const PRESENT_STATUSES = [1, 2, 5];
    private const EXCUSED_STATUSES = [6, 7, 8];

    /** Student statuses that leave the cohort - excluded from every count. */
    private const EXCLUDED_STUDENT_STATUSES = [22, 27, 31, 33, 14, 17, 30, 36];

    /** The thresholds the whole screen is coloured by. */
    private const AT_RISK = 75;
    private const CRITICAL = 60;
    private const ON_TRACK_SUBMISSION = 75;

    /**
     * Grades that count a module as submitted: pass, merit, distinction and
     * unclassified/compensated. Referred, fail, absent, plagiarised, withheld
     * and "submitted but not yet graded" are not an outcome, so they do not
     * count towards a student's submission figure.
     */
    private const COMPLETED_GRADES = ['P', 'M', 'D', 'U'];

    /* ------------------------------------------------------------------ *
     * Guards
     * ------------------------------------------------------------------ */

    /**
     * The dashboard tile is hidden without the "view" privilege, but a hidden
     * link is not a lock: every entry point refuses the request itself.
     */
    private function guardView(): void
    {
        abort_unless(GroupLeader::can('view'), 403, 'You are not permitted to view the Group Leader dashboard.');
    }

    /**
     * Resolves and authorises the (group, term) an ajax call asks about.
     *
     * Returns the resolved ids, or the 403 response to hand straight back —
     * every ajax entry point begins with this, so none of them can be pointed
     * at a group the caller does not lead.
     */
    private function scopeOrFail(Request $request)
    {
        $userId = auth()->user()->id;
        $termId = (int) $request->term_id;
        $groupId = (int) $request->group_id;

        $groupIds = $this->groupIdsForNode($userId, $termId, $groupId);
        if (empty($groupIds)) {
            return response()->json(['message' => 'That group is not assigned to you.'], 403);
        }

        return [
            'groupId' => $groupId,
            'termId' => $termId,
            'groupIds' => $groupIds,
            'planIds' => $this->planIds($groupIds, $termId),
        ];
    }

    /* ------------------------------------------------------------------ *
     * Level 1 — My groups
     * ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $this->guardView();

        $userId = auth()->user()->id;
        $terms = $this->leaderTerms($userId);

        // "all" is a real choice here, not a fallback: a leader carrying groups
        // in two terms wants both on one screen.
        $requested = (string) $request->query('term', '');
        $termId = $terms->pluck('id')->map(fn ($id) => (string) $id)->contains($requested)
            ? (int) $requested
            : (int) ($terms->first()->id ?? 0);
        $selected = ($requested === 'all') ? 'all' : $termId;

        return view('pages.group-leader.dashboard.index', [
            'title' => 'Group Leader Dashboard - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Group Leader', 'href' => route('gl.dashboard')],
            ],
            'leaderName' => $this->leaderName($userId),
            'terms' => $terms,
            'selected' => $selected,
            'cards' => $this->groupCards($userId, $selected === 'all' ? null : $termId),
        ]);
    }

    /** The card grid and head stats on their own, when the term changes. */
    public function getGroups(Request $request)
    {
        $this->guardView();

        $userId = auth()->user()->id;
        $termId = $request->term_id === 'all' ? null : (int) $request->term_id;

        if ($termId !== null && !$this->leadsTerm($userId, $termId)) {
            return response()->json(['message' => 'You do not lead any group in that term.'], 403);
        }

        $cards = $this->groupCards($userId, $termId);

        return response()->json([
            'cards' => view('pages.group-leader.dashboard.partials.group-cards', ['cards' => $cards])->render(),
            'stats' => view('pages.group-leader.dashboard.partials.head-stats', ['cards' => $cards])->render(),
        ], 200);
    }

    /* ------------------------------------------------------------------ *
     * Level 2 — one group
     * ------------------------------------------------------------------ */

    public function show(Request $request, $groupId)
    {
        $this->guardView();

        $userId = auth()->user()->id;
        $termId = (int) $request->query('term', 0);

        // Without a term in the URL, use the one this group is actually led in.
        if ($termId <= 0) {
            $termId = (int) GroupLeader::where('user_id', $userId)->where('group_id', $groupId)
                ->orderBy('term_declaration_id', 'DESC')->value('term_declaration_id');
        }

        $groupIds = $this->groupIdsForNode($userId, $termId, (int) $groupId);
        abort_if(empty($groupIds), 403, 'That group is not assigned to you.');

        $group = Group::with('course')->find($groupId);
        $term = TermDeclaration::with('termType')->find($termId);
        $planIds = $this->planIds($groupIds, $termId);

        $students = $this->studentRows($groupIds, $termId, $planIds);
        $modules = $this->moduleRows($groupIds, $termId);
        $lists = $this->worklists($students);

        return view('pages.group-leader.dashboard.group', [
            'title' => 'Group Leader - '.($group->name ?? '').' - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Group Leader', 'href' => route('gl.dashboard')],
            ],
            'group' => $group,
            'term' => $term,
            'termId' => $termId,
            'progress' => $this->termProgress($term),
            'kpis' => $this->kpis($students, $planIds),
            'lists' => $lists,
            'modules' => $modules,
            'staff' => $this->staffRows($modules, count($students)),
            'alerts' => $this->alerts($students, $modules),
            'glance' => $this->glance($students, $modules, $groupIds, $termId),
            'day' => $this->dayView($planIds, date('Y-m-d')),
            'today' => date('d-m-Y'),
        ]);
    }

    /** One worklist tab's rows. */
    public function getStudents(Request $request)
    {
        $this->guardView();

        $scope = $this->scopeOrFail($request);
        if (!is_array($scope)) {
            return $scope;
        }

        $students = $this->studentRows($scope['groupIds'], $scope['termId'], $scope['planIds']);
        $lists = $this->worklists($students);
        $tab = array_key_exists((string) $request->tab, $lists) ? $request->tab : 'risk';

        return response()->json([
            'htm' => view('pages.group-leader.dashboard.partials.worklist-rows', [
                'rows' => $lists[$tab],
                'groupId' => $scope['groupId'],
                'termId' => $scope['termId'],
            ])->render(),
        ], 200);
    }

    /** The live day view for one date. */
    public function getToday(Request $request)
    {
        $this->guardView();

        $scope = $this->scopeOrFail($request);
        if (!is_array($scope)) {
            return $scope;
        }

        $date = !empty($request->date) ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d');

        return response()->json([
            'htm' => view('pages.group-leader.dashboard.partials.day', [
                'day' => $this->dayView($scope['planIds'], $date),
                'group' => Group::find($scope['groupId']),
            ])->render(),
        ], 200);
    }

    /** The slide-over for one student. */
    public function getStudent(Request $request)
    {
        $this->guardView();

        $scope = $this->scopeOrFail($request);
        if (!is_array($scope)) {
            return $scope;
        }

        $students = $this->studentRows($scope['groupIds'], $scope['termId'], $scope['planIds']);
        $student = collect($students)->firstWhere('id', (int) $request->student_id);

        if (empty($student)) {
            return response()->json(['message' => 'That student is not in this group.'], 403);
        }

        return response()->json([
            'htm' => view('pages.group-leader.dashboard.partials.drawer', [
                'student' => $student,
                'groupId' => $scope['groupId'],
                'termId' => $scope['termId'],
            ])->render(),
        ], 200);
    }

    /**
     * Records a conversation with a student — the only write on this screen.
     *
     * It needs `view` like everything else: chasing attendance is what the
     * dashboard is for, not a separate power.
     */
    public function storeContact(Request $request)
    {
        $this->guardView();

        $scope = $this->scopeOrFail($request);
        if (!is_array($scope)) {
            return $scope;
        }

        $studentId = (int) $request->student_id;

        // The student has to be in this leader's group, or the log becomes a
        // way to write notes onto arbitrary student records.
        if (!Assign::whereIn('plan_id', $scope['planIds'])->where('student_id', $studentId)->exists()) {
            return response()->json(['message' => 'That student is not in this group.'], 403);
        }

        $data = $request->validate([
            'method' => 'required|string|in:'.implode(',', GroupLeaderContact::METHODS),
            'reason' => 'nullable|string|in:'.implode(',', GroupLeaderContact::REASONS),
            'note' => 'nullable|string|max:2000',
            'follow_up_date' => 'nullable|date',
        ]);

        if (empty($data['reason']) && trim((string) ($data['note'] ?? '')) === '') {
            return response()->json([
                'errors' => ['reason' => 'Give a reason or a note, otherwise there is nothing to record.'],
            ], 422);
        }

        $actor = auth()->user();

        GroupLeaderContact::create([
            'student_id' => $studentId,
            'group_id' => $scope['groupId'],
            'term_declaration_id' => $scope['termId'],
            'method' => $data['method'],
            'reason' => $data['reason'] ?? null,
            'note' => $data['note'] ?? null,
            'follow_up_date' => !empty($data['follow_up_date']) ? date('Y-m-d', strtotime($data['follow_up_date'])) : null,
            'logged_by' => $actor->id,
            'logged_by_name' => $actor->full_name,
        ]);

        // Logging contact moves the student out of "uncontacted", so the list
        // behind the drawer is stale the moment this saves.
        $students = $this->studentRows($scope['groupIds'], $scope['termId'], $scope['planIds']);
        $lists = $this->worklists($students);
        $tab = array_key_exists((string) $request->tab, $lists) ? $request->tab : 'risk';

        return response()->json([
            'message' => 'Saved to the student record.',
            'drawer' => view('pages.group-leader.dashboard.partials.drawer', [
                'student' => collect($students)->firstWhere('id', $studentId),
                'groupId' => $scope['groupId'],
                'termId' => $scope['termId'],
            ])->render(),
            'rows' => view('pages.group-leader.dashboard.partials.worklist-rows', [
                'rows' => $lists[$tab],
                'groupId' => $scope['groupId'],
                'termId' => $scope['termId'],
            ])->render(),
            'counts' => array_map('count', $lists),
        ], 200);
    }

    /* ------------------------------------------------------------------ *
     * Scope
     * ------------------------------------------------------------------ */

    private function leaderName($userId): string
    {
        $employee = Employee::with('title')->where('user_id', $userId)->first();
        $name = trim(($employee->title->name ?? '').' '.($employee->first_name ?? '').' '.($employee->last_name ?? ''));

        return $name !== '' ? $name : (User::find($userId)->full_name ?? 'Group Leader');
    }

    /** The terms this user leads a group in, most recent first. */
    private function leaderTerms($userId)
    {
        $termIds = GroupLeader::where('user_id', $userId)->pluck('term_declaration_id')->unique()->toArray();

        return empty($termIds)
            ? collect()
            : TermDeclaration::with('termType')->whereIn('id', $termIds)->orderBy('id', 'DESC')->get();
    }

    private function leadsTerm($userId, $termId): bool
    {
        return $termId > 0 && GroupLeader::where('user_id', $userId)->where('term_declaration_id', $termId)->exists();
    }

    /** Every (group, term) pair this user leads; null `$termId` means all. */
    private function leaderAssignments($userId, $termId = null)
    {
        $query = GroupLeader::where('user_id', $userId);
        if ($termId !== null) {
            $query->where('term_declaration_id', $termId);
        }

        return $query->get();
    }

    private function leaderGroupIds($userId, $termId): array
    {
        if ($termId <= 0) {
            return [];
        }

        return GroupLeader::where('user_id', $userId)->where('term_declaration_id', $termId)
            ->pluck('group_id')->unique()->values()->toArray();
    }

    /**
     * The group ids behind one node, restricted to what this user leads.
     *
     * A group node is (course + term + name) and can map to several `groups`
     * rows. Intersecting with the leader's own set is what stops a hand-edited
     * group id from reaching someone else's cohort.
     */
    private function groupIdsForNode($userId, $termId, $groupId): array
    {
        $mine = $this->leaderGroupIds($userId, $termId);
        if (empty($mine) || !in_array($groupId, $mine)) {
            return [];
        }

        $group = Group::find($groupId);
        if (empty($group)) {
            return [];
        }

        $sameName = Group::where('term_declaration_id', $termId)
            ->where('course_id', $group->course_id)
            ->where('name', $group->name)
            ->pluck('id')->unique()->toArray();

        return array_values(array_intersect($sameName, $mine));
    }

    private function planIds(array $groupIds, $termId): array
    {
        if (empty($groupIds) || $termId <= 0) {
            return [];
        }

        return Plan::whereIn('group_id', $groupIds)->where('term_declaration_id', $termId)
            ->pluck('id')->unique()->values()->toArray();
    }

    /* ------------------------------------------------------------------ *
     * Level 1 data — the cards
     * ------------------------------------------------------------------ */

    /**
     * One card per group node the leader holds.
     *
     * Same-name duplicate `groups` rows collapse into a single card, the way
     * the Class Plan tree presents them.
     */
    private function groupCards($userId, $termId = null): array
    {
        $assignments = $this->leaderAssignments($userId, $termId);
        if ($assignments->isEmpty()) {
            return [];
        }

        $groups = Group::with('course')->whereIn('id', $assignments->pluck('group_id')->unique())->get()->keyBy('id');
        $terms = TermDeclaration::with('termType')
            ->whereIn('id', $assignments->pluck('term_declaration_id')->unique())->get()->keyBy('id');

        // Fold the duplicates: one node per term + course + group name.
        $nodes = [];
        foreach ($assignments as $row) {
            $group = $groups->get($row->group_id);
            if (empty($group)) {
                continue;
            }

            $key = $row->term_declaration_id.'|'.$group->course_id.'|'.strtolower(trim((string) $group->name));
            if (!isset($nodes[$key])) {
                $nodes[$key] = [
                    'group' => $group,
                    'term' => $terms->get($row->term_declaration_id),
                    'termId' => (int) $row->term_declaration_id,
                    'groupIds' => [],
                ];
            }

            $nodes[$key]['groupIds'][] = (int) $row->group_id;
        }

        $cards = [];
        foreach ($nodes as $node) {
            $planIds = $this->planIds($node['groupIds'], $node['termId']);
            $summary = $this->attendanceSummary($planIds);
            $rates = $this->studentRates($planIds);
            $activeIds = $this->activeStudentIds($planIds);

            $below = 0;
            foreach ($activeIds as $studentId) {
                $rate = $rates[$studentId] ?? null;
                if ($rate && (float) $rate->rate < self::CRITICAL) {
                    $below++;
                }
            }

            $cards[] = [
                'id' => $node['groupIds'][0],
                'name' => $node['group']->name,
                'course' => $node['group']->course->name ?? '',
                'term' => $node['term']->name ?? '',
                'termType' => $node['term']->termType->name ?? '',
                'termId' => $node['termId'],
                'evening' => ((int) $node['group']->evening_and_weekend === 1),
                'modules' => $this->theoryModuleCount($planIds),
                'students' => count($activeIds),
                'below60' => $below,
                'attendance' => $summary['total'] > 0 ? (int) round($summary['percentage']) : null,
            ];
        }

        // The card that needs attention leads the grid.
        usort($cards, function ($a, $b) {
            return [$b['below60'], $a['attendance'] ?? 101] <=> [$a['below60'], $b['attendance'] ?? 101];
        });

        return $cards;
    }

    /* ------------------------------------------------------------------ *
     * Level 2 data — students
     * ------------------------------------------------------------------ */

    /** Actively assigned students on a set of plans, cohort leavers removed. */
    private function activeStudentIds(array $planIds): array
    {
        if (empty($planIds)) {
            return [];
        }

        $ids = Assign::whereIn('plan_id', $planIds)->where(function ($q) {
            $q->whereNull('attendance')->orWhere('attendance', 1);
        })->pluck('student_id')->unique()->values()->toArray();

        if (empty($ids)) {
            return [];
        }

        return Student::whereIn('id', $ids)->whereNotIn('status_id', self::EXCLUDED_STUDENT_STATUSES)
            ->pluck('id')->map(fn ($id) => (int) $id)->values()->toArray();
    }

    /**
     * Every row the worklist and the drawer are built from.
     *
     * Assembled once per request and filtered in memory afterwards: the four
     * worklist tabs are four views of the same set, and re-querying per tab
     * would run the same six queries four times.
     */
    private function studentRows(array $groupIds, $termId, array $planIds): array
    {
        $studentIds = $this->activeStudentIds($planIds);
        if (empty($studentIds)) {
            return [];
        }

        // `full_name` reads the title, so without this it is one query per row.
        $students = Student::with('title')->whereIn('id', $studentIds)->get()->keyBy('id');
        $rates = $this->studentRates($planIds);
        $consecutive = $this->consecutiveAbsences($planIds, $studentIds);
        $tutors = $this->personalTutors($planIds, $studentIds);
        $submissions = $this->studentSubmissions($this->theoryModulePlanIds($planIds), $studentIds);
        $contacts = $this->contactLogs($groupIds, $termId, $studentIds);

        $due = $submissions['due'];

        $rows = [];
        foreach ($studentIds as $studentId) {
            $student = $students->get($studentId);
            if (empty($student)) {
                continue;
            }

            $rate = $rates[$studentId] ?? null;
            $log = $contacts[$studentId] ?? [];
            $submitted = $submissions['byStudent'][$studentId] ?? 0;

            $rows[] = [
                'id' => $student->id,
                'name' => $student->full_name,
                'registration_no' => $student->registration_no,
                // No attendance rows yet is "no data", not 0%.
                'attendance' => $rate ? (float) $rate->rate : null,
                'marks' => $rate ? (int) $rate->total : 0,
                'submitted' => $submitted,
                'due' => $due,
                'submissionPct' => $due > 0 ? (int) round($submitted * 100 / $due) : null,
                'personalTutor' => $tutors[$studentId] ?? '',
                'consecutive' => $consecutive[$studentId] ?? 0,
                'lastContact' => $log[0]['date'] ?? null,
                'log' => $log,
            ];
        }

        // Worst attendance first; "no data" sorts last rather than as zero.
        usort($rows, function ($a, $b) {
            return ($a['attendance'] ?? 101) <=> ($b['attendance'] ?? 101);
        });

        return $rows;
    }

    /** Per-student attendance rate across a set of plans, keyed by student id. */
    private function studentRates(array $planIds): array
    {
        if (empty($planIds)) {
            return [];
        }

        $counted = implode(',', array_merge(self::PRESENT_STATUSES, self::EXCUSED_STATUSES));

        return DB::table('attendances as atn')
            ->select(
                'atn.student_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('ROUND(SUM(CASE WHEN atn.attendance_feed_status_id IN ('.$counted.') THEN 1 ELSE 0 END) * 100 / COUNT(*), 2) as rate')
            )
            ->whereNull('atn.deleted_at')
            ->whereIn('atn.plan_id', $planIds)
            ->groupBy('atn.student_id')
            ->get()
            ->keyBy('student_id')
            ->toArray();
    }

    /**
     * How many of the most recent sessions each student missed in a row.
     *
     * Three in a row is the signal a leader acts on, and it cannot be read off
     * a percentage — a student at 70% who has missed the last four weeks is a
     * different problem from one who missed four scattered days.
     */
    private function consecutiveAbsences(array $planIds, array $studentIds): array
    {
        if (empty($planIds) || empty($studentIds)) {
            return [];
        }

        $counted = array_merge(self::PRESENT_STATUSES, self::EXCUSED_STATUSES);

        $rows = DB::table('attendances')
            ->select('student_id', 'attendance_date', 'attendance_feed_status_id')
            ->whereNull('deleted_at')
            ->whereIn('plan_id', $planIds)
            ->whereIn('student_id', $studentIds)
            ->orderBy('student_id')
            ->orderBy('attendance_date', 'DESC')
            ->get();

        $streaks = [];
        $stopped = [];
        foreach ($rows as $row) {
            // Rows arrive newest first per student; the first attended mark
            // ends that student's streak and the rest are history.
            if (!empty($stopped[$row->student_id])) {
                continue;
            }

            if (in_array((int) $row->attendance_feed_status_id, $counted, true)) {
                $stopped[$row->student_id] = true;
                continue;
            }

            $streaks[$row->student_id] = ($streaks[$row->student_id] ?? 0) + 1;
        }

        return $streaks;
    }

    /**
     * Each student's personal tutor within this group.
     *
     * Tutorials carry the personal tutor, so the tutorial plan wins over its
     * parent when both name someone.
     */
    private function personalTutors(array $planIds, array $studentIds): array
    {
        if (empty($planIds) || empty($studentIds)) {
            return [];
        }

        $plans = Plan::with(['personalTutor.employee', 'tutorial.personalTutor.employee'])
            ->whereIn('id', $planIds)->get();

        $namesByPlan = [];
        foreach ($plans as $plan) {
            $name = $plan->tutorial->personalTutor->full_name ?? ($plan->personalTutor->full_name ?? null);
            if (!empty($name)) {
                $namesByPlan[$plan->id] = $name;
            }
        }

        if (empty($namesByPlan)) {
            return [];
        }

        $assigns = Assign::whereIn('plan_id', array_keys($namesByPlan))
            ->whereIn('student_id', $studentIds)->get();

        $out = [];
        foreach ($assigns as $assign) {
            if (!isset($out[$assign->student_id])) {
                $out[$assign->student_id] = $namesByPlan[$assign->plan_id];
            }
        }

        return $out;
    }

    /**
     * The Theory plans out of a set.
     *
     * A module is delivered as a Theory plan plus its tutorial and seminar
     * plans, but the assessed work hangs off Theory. Counting the others in
     * would inflate what is "due" for a student who has one piece to hand in.
     * Their rows still appear in the module table.
     *
     * Resolution matches `moduleRows()`: the plan's own class type, falling
     * back to the creation record, since `plans.class_type` is often null.
     */
    private function theoryModulePlanIds(array $planIds): array
    {
        if (empty($planIds)) {
            return [];
        }

        return Plan::with('creations')->whereIn('id', $planIds)->where('parent_id', 0)->get()
            ->filter(fn ($plan) => $this->isTheory($this->planClassType($plan)))
            ->pluck('id')->map(fn ($id) => (int) $id)->values()->toArray();
    }

    /** How many modules a set of plans represents. */
    private function theoryModuleCount(array $planIds): int
    {
        return count($this->theoryModulePlanIds($planIds));
    }

    /** `plans.class_type` is often null, so fall back to the creation record. */
    private function planClassType($plan): string
    {
        return (string) ($plan->class_type ?: ($plan->creations->class_type ?? ''));
    }

    private function isTheory(?string $type): bool
    {
        return strtolower(trim((string) $type)) === 'theory';
    }

    /**
     * Submissions expected and made.
     *
     * Expected is the group's Theory modules; a student has submitted a module
     * once they hold a completed grade on it (see COMPLETED_GRADES). Counting
     * modules rather than assessments is what makes 2/2 mean the same thing on
     * every row, and a student with no completed grade counts nothing.
     */
    private function studentSubmissions(array $planIds, array $studentIds): array
    {
        $blank = ['due' => 0, 'byStudent' => [], 'byPlan' => [], 'plansWithAssessment' => []];

        if (empty($planIds) || empty($studentIds)) {
            return $blank;
        }

        $gradeIds = Grade::whereIn('code', self::COMPLETED_GRADES)->pluck('id')->toArray();

        $results = empty($gradeIds) ? collect() : Result::whereIn('plan_id', $planIds)
            ->whereIn('student_id', $studentIds)
            ->whereIn('grade_id', $gradeIds)
            // A grade the student cannot see yet is not a submission here
            // either. The comparison also drops null and scheduled dates.
            ->where('published_at', '<', Carbon::now())
            ->get(['student_id', 'plan_id']);

        $byStudent = [];
        $byPlan = [];
        $seen = [];
        foreach ($results as $result) {
            // A module counts once however many assessments it carries, and a
            // re-mark must not inflate the figure.
            $key = $result->student_id.'|'.$result->plan_id;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $byStudent[$result->student_id] = ($byStudent[$result->student_id] ?? 0) + 1;
            $byPlan[$result->plan_id][$result->student_id] = true;
        }

        return [
            // Everyone is measured against the same denominator: the modules
            // themselves, not how many assessments happen to hang off them.
            'due' => count($planIds),
            'byStudent' => $byStudent,
            'byPlan' => array_map('count', $byPlan),
            'plansWithAssessment' => AssessmentPlan::whereIn('plan_id', $planIds)
                ->where('upload_user_type', 'staff')->where('is_it_final', 1)
                ->pluck('plan_id')->unique()->values()->toArray(),
        ];
    }

    /** The contact history for each student, newest first. */
    private function contactLogs(array $groupIds, $termId, array $studentIds): array
    {
        if (empty($groupIds) || empty($studentIds)) {
            return [];
        }

        $rows = GroupLeaderContact::whereIn('group_id', $groupIds)
            ->where('term_declaration_id', $termId)
            ->whereIn('student_id', $studentIds)
            ->orderBy('id', 'DESC')->get();

        $out = [];
        foreach ($rows as $row) {
            $out[$row->student_id][] = [
                'method' => $row->method,
                'reason' => $row->reason,
                'note' => $row->note,
                'followUp' => $row->follow_up_date ? $row->follow_up_date->format('d M Y') : null,
                'date' => $row->created_at ? $row->created_at->format('d M Y') : '',
                'by' => $row->logged_by_name,
            ];
        }

        return $out;
    }

    /* ------------------------------------------------------------------ *
     * Level 2 data — headline figures
     * ------------------------------------------------------------------ */

    /**
     * The three KPI cards.
     *
     * `completion` is the share of students on track on both counts at once —
     * the figure that says how many will finish, which neither average shows
     * on its own.
     */
    private function kpis(array $students, array $planIds): array
    {
        $summary = $this->attendanceSummary($planIds);
        $withSubs = array_values(array_filter($students, fn ($s) => $s['submissionPct'] !== null));

        $onTrack = array_filter($students, function ($s) {
            return $s['attendance'] !== null && $s['attendance'] >= 80
                && ($s['submissionPct'] === null || $s['submissionPct'] >= self::ON_TRACK_SUBMISSION);
        });

        return [
            'attendance' => $summary['total'] > 0 ? (int) round($summary['percentage']) : null,
            'attendanceTrend' => $this->attendanceTrend($planIds),
            'belowRisk' => count(array_filter($students, fn ($s) => $s['attendance'] !== null && $s['attendance'] < self::AT_RISK)),
            'submission' => count($withSubs) > 0
                ? (int) round(array_sum(array_column($withSubs, 'submissionPct')) / count($withSubs))
                : null,
            'submissionOutstanding' => count(array_filter($withSubs, fn ($s) => $s['submissionPct'] < self::ON_TRACK_SUBMISSION)),
            'completion' => count($students) > 0 ? (int) round(count($onTrack) * 100 / count($students)) : null,
        ];
    }

    /**
     * Change in attendance over the last four weeks against the four before.
     *
     * Null when either window is empty — a term two weeks old has no trend,
     * and inventing one from a single window would read as a real movement.
     */
    private function attendanceTrend(array $planIds)
    {
        if (empty($planIds)) {
            return null;
        }

        $counted = implode(',', array_merge(self::PRESENT_STATUSES, self::EXCUSED_STATUSES));

        $window = function ($from, $to) use ($planIds, $counted) {
            $row = DB::table('attendances')
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN attendance_feed_status_id IN ('.$counted.') THEN 1 ELSE 0 END) as counted')
                )
                ->whereNull('deleted_at')
                ->whereIn('plan_id', $planIds)
                ->where('attendance_date', '>=', $from)
                ->where('attendance_date', '<', $to)
                ->first();

            return ((int) ($row->total ?? 0)) > 0 ? $row->counted * 100 / $row->total : null;
        };

        $recent = $window(date('Y-m-d', strtotime('-28 days')), date('Y-m-d', strtotime('+1 day')));
        $prior = $window(date('Y-m-d', strtotime('-56 days')), date('Y-m-d', strtotime('-28 days')));

        if ($recent === null || $prior === null) {
            return null;
        }

        return (int) round($recent - $prior);
    }

    /**
     * Present / late / excused / absent split across a set of plans.
     *
     * `percentage` counts excused absence as attended, matching the figure the
     * other staff dashboards report.
     */
    private function attendanceSummary(array $planIds): array
    {
        $blank = ['total' => 0, 'present' => 0, 'late' => 0, 'excused' => 0, 'absent' => 0, 'percentage' => 0.0];

        if (empty($planIds)) {
            return $blank;
        }

        $present = implode(',', self::PRESENT_STATUSES);
        $excused = implode(',', self::EXCUSED_STATUSES);

        $row = DB::table('attendances as atn')
            ->select(
                DB::raw('COUNT(*) AS total'),
                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id IN (1,2) THEN 1 ELSE 0 END) AS present'),
                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS late'),
                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id IN ('.$excused.') THEN 1 ELSE 0 END) AS excused'),
                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id NOT IN ('.$present.','.$excused.') THEN 1 ELSE 0 END) AS absent')
            )
            ->whereNull('atn.deleted_at')
            ->whereIn('atn.plan_id', $planIds)
            ->first();

        $total = (int) ($row->total ?? 0);
        if ($total === 0) {
            return $blank;
        }

        $attended = (int) $row->present + (int) $row->late + (int) $row->excused;

        return [
            'total' => $total,
            'present' => (int) $row->present,
            'late' => (int) $row->late,
            'excused' => (int) $row->excused,
            'absent' => (int) $row->absent,
            'percentage' => round($attended * 100 / $total, 2),
        ];
    }

    /**
     * The same split as attendanceSummary(), for many plans at once.
     *
     * The module table needs a figure per plan; asking per plan was one query
     * each, so they are aggregated together and grouped in the database.
     */
    private function attendanceSummaryByPlan(array $planIds): array
    {
        if (empty($planIds)) {
            return [];
        }

        $present = implode(',', self::PRESENT_STATUSES);
        $excused = implode(',', self::EXCUSED_STATUSES);

        $rows = DB::table('attendances as atn')
            ->select(
                'atn.plan_id',
                DB::raw('COUNT(*) AS total'),
                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id IN ('.$present.','.$excused.') THEN 1 ELSE 0 END) AS attended')
            )
            ->whereNull('atn.deleted_at')
            ->whereIn('atn.plan_id', $planIds)
            ->groupBy('atn.plan_id')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $total = (int) $row->total;
            $out[$row->plan_id] = [
                'total' => $total,
                'percentage' => $total > 0 ? round($row->attended * 100 / $total, 2) : 0.0,
            ];
        }

        return $out;
    }

    /** The four worklist tabs, from one already-built student set. */
    private function worklists(array $students): array
    {
        return [
            'risk' => array_values(array_filter($students, fn ($s) => $s['attendance'] !== null && $s['attendance'] < self::AT_RISK)),
            'subs' => array_values(array_filter($students, fn ($s) => $s['submissionPct'] !== null && $s['submissionPct'] < self::ON_TRACK_SUBMISSION)),
            'uncontacted' => array_values(array_filter($students, fn ($s) => $s['attendance'] !== null && $s['attendance'] < self::AT_RISK && empty($s['log']))),
            'all' => $students,
        ];
    }

    /* ------------------------------------------------------------------ *
     * Level 2 data — modules and staff
     * ------------------------------------------------------------------ */

    /**
     * One row per class plan: who runs it, how much has been delivered, and
     * whether the record-keeping behind those numbers is complete.
     */
    private function moduleRows(array $groupIds, $termId): array
    {
        if (empty($groupIds) || $termId <= 0) {
            return [];
        }

        $plans = Plan::with([
                'creations', 'room', 'venu', 'tutorial',
                'tutor.employee', 'personalTutor.employee', 'tutorial.personalTutor.employee',
            ])
            ->whereIn('group_id', $groupIds)->where('term_declaration_id', $termId)
            ->where('parent_id', 0)
            ->orderBy('id', 'DESC')->get();

        if ($plans->isEmpty()) {
            return [];
        }

        $planIds = $plans->pluck('id')->toArray();
        $sessions = PlansDateList::whereIn('plan_id', $planIds)->get()->groupBy('plan_id');
        $attendance = $this->attendanceSummaryByPlan($planIds);
        $activeIds = $this->activeStudentIds($planIds);
        $submissions = $this->studentSubmissions($planIds, $activeIds);
        $assigned = Assign::whereIn('plan_id', $planIds)->where(function ($q) {
            $q->whereNull('attendance')->orWhere('attendance', 1);
        })->get()->groupBy('plan_id');

        $rows = [];
        foreach ($plans as $plan) {
            $planSessions = $sessions->get($plan->id, collect());
            $summary = $attendance[$plan->id] ?? ['total' => 0, 'percentage' => 0.0];
            $cohort = $assigned->get($plan->id, collect())->count();

            $hasAssessment = in_array($plan->id, $submissions['plansWithAssessment']);
            $submitted = $submissions['byPlan'][$plan->id] ?? 0;

            $rows[] = [
                'id' => $plan->id,
                'code' => $plan->id.(isset($plan->tutorial->id) ? '–'.$plan->tutorial->id : ''),
                'module' => $plan->creations->module_name ?? '',
                'type' => $this->planClassType($plan),
                'tutor' => $plan->tutor->full_name ?? '',
                'tutorialTutor' => $plan->tutorial->personalTutor->full_name ?? ($plan->personalTutor->full_name ?? ''),
                'delivered' => $planSessions->whereIn('status', ['Completed', 'Ongoing'])->count(),
                'planned' => $planSessions->count(),
                // A plan with no generated dates has not been built out yet.
                'planCreated' => $planSessions->count() > 0,
                'attendanceGaps' => $planSessions->where('status', 'Completed')->filter(function ($session) {
                    return (int) $session->feed_given !== 1;
                })->count(),
                'attendance' => $summary['total'] > 0 ? (int) round($summary['percentage']) : null,
                'submissionDue' => $hasAssessment,
                'submissionPct' => ($hasAssessment && $cohort > 0) ? (int) round($submitted * 100 / $cohort) : null,
                'students' => $cohort,
            ];
        }

        return $rows;
    }

    /**
     * Tutors and personal tutors folded out of the module rows.
     *
     * Their standing is the worst thing true of any module they hold: a missing
     * class plan outranks a missed register, which outranks low attendance,
     * because that is the order a leader can act on them.
     */
    private function staffRows(array $modules, int $studentCount): array
    {
        $staff = [];

        foreach ($modules as $module) {
            foreach ([$module['tutor'], $module['tutorialTutor']] as $name) {
                if (empty($name)) {
                    continue;
                }

                if (!isset($staff[$name])) {
                    $staff[$name] = ['name' => $name, 'modules' => 0, 'gaps' => 0, 'noPlan' => false, 'lowest' => null];
                }

                $staff[$name]['modules']++;
                $staff[$name]['gaps'] += $module['attendanceGaps'];
                $staff[$name]['noPlan'] = $staff[$name]['noPlan'] || !$module['planCreated'];

                if ($module['attendance'] !== null) {
                    $staff[$name]['lowest'] = $staff[$name]['lowest'] === null
                        ? $module['attendance']
                        : min($staff[$name]['lowest'], $module['attendance']);
                }
            }
        }

        if (empty($staff)) {
            return [];
        }

        $share = (int) round($studentCount / max(1, count($staff)));

        $rows = [];
        foreach ($staff as $person) {
            if ($person['noPlan']) {
                $status = 'behind';
                $detail = 'Class plan not created for a module';
            } elseif ($person['gaps'] > 0) {
                $status = 'behind';
                $detail = $person['gaps'].' attendance not taken';
            } elseif ($person['lowest'] !== null && $person['lowest'] < self::AT_RISK) {
                $status = 'attention';
                $detail = 'Attendance below '.self::AT_RISK.'% in a module';
            } else {
                $status = 'on-track';
                $detail = 'All attendance taken';
            }

            $rows[] = [
                'name' => $person['name'],
                'modules' => $person['modules'],
                'tutees' => $share,
                'status' => $status,
                'detail' => $detail,
            ];
        }

        usort($rows, function ($a, $b) {
            $order = ['behind' => 0, 'attention' => 1, 'on-track' => 2];

            return [$order[$a['status']], $a['name']] <=> [$order[$b['status']], $b['name']];
        });

        return $rows;
    }

    /* ------------------------------------------------------------------ *
     * Level 2 data — panels
     * ------------------------------------------------------------------ */

    /** "Needs action now" — only things that are actually outstanding. */
    private function alerts(array $students, array $modules): array
    {
        $alerts = [];

        $uncontacted = count(array_filter($students, function ($s) {
            return $s['attendance'] !== null && $s['attendance'] < self::AT_RISK && empty($s['log']);
        }));
        if ($uncontacted > 0) {
            $alerts[] = [
                'tone' => 'red',
                'title' => $uncontacted.' absence'.($uncontacted > 1 ? 's' : '').' not yet contacted',
                'detail' => 'Students below '.self::AT_RISK.'% with no note logged',
            ];
        }

        $gaps = array_sum(array_column($modules, 'attendanceGaps'));
        if ($gaps > 0) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => $gaps.' attendance not taken',
                'detail' => 'Attendance figures may be understated',
            ];
        }

        foreach (array_filter($modules, fn ($m) => !$m['planCreated']) as $module) {
            $alerts[] = [
                'tone' => 'amber',
                'title' => ($module['module'] ?: 'A module').' — plan missing',
                'detail' => ($module['tutor'] ?: 'No tutor').' · class days not generated',
            ];
        }

        $behind = array_values(array_filter($modules, function ($m) {
            return $this->isTheory($m['type'])
                && $m['submissionDue'] && $m['submissionPct'] !== null
                && $m['submissionPct'] < self::ON_TRACK_SUBMISSION;
        }));
        if (!empty($behind)) {
            $alerts[] = [
                'tone' => 'red',
                'title' => count($behind).' submission deadline'.(count($behind) > 1 ? 's' : '').' behind target',
                'detail' => implode(', ', array_map(function ($m) {
                    return preg_replace('/\s*\(.*\)/', '', $m['module']);
                }, array_slice($behind, 0, 3))),
            ];
        }

        return $alerts;
    }

    /** "Group at a glance" — the flat counts under the alerts. */
    private function glance(array $students, array $modules, array $groupIds, $termId): array
    {
        $thisWeek = GroupLeaderContact::whereIn('group_id', $groupIds)
            ->where('term_declaration_id', $termId)
            ->where('created_at', '>=', date('Y-m-d', strtotime('monday this week')))
            ->distinct()->count('student_id');

        $tutors = [];
        foreach ($modules as $module) {
            if (!empty($module['tutorialTutor'])) {
                $tutors[$module['tutorialTutor']] = true;
            }
        }

        return [
            'students' => count($students),
            'contacted' => $thisWeek,
            'modules' => count(array_filter($modules, fn ($m) => $this->isTheory($m['type']))),
            'personalTutors' => count($tutors),
            'below60' => count(array_filter($students, fn ($s) => $s['attendance'] !== null && $s['attendance'] < self::CRITICAL)),
        ];
    }

    /** Week N of M through the term, for the progress bar in the header. */
    private function termProgress($term): array
    {
        $blank = ['week' => null, 'total' => null, 'percent' => 0];

        if (empty($term)) {
            return $blank;
        }

        $start = $term->teaching_start_date ?: $term->start_date;
        $end = $term->teaching_end_date ?: $term->end_date;

        if (empty($start) || empty($end)) {
            return $blank;
        }

        $startTs = strtotime($start);
        $endTs = strtotime($end);
        if (!$startTs || !$endTs || $endTs <= $startTs) {
            return $blank;
        }

        $total = (int) ($term->total_teaching_weeks ?: max(1, ceil(($endTs - $startTs) / 604800)));
        $elapsed = (int) floor((time() - $startTs) / 604800) + 1;
        $week = max(1, min($total, $elapsed));

        return [
            'week' => $week,
            'total' => $total,
            'percent' => (int) round($week * 100 / $total),
        ];
    }

    /* ------------------------------------------------------------------ *
     * Level 2 data — the live day
     * ------------------------------------------------------------------ */

    /**
     * Every session of this group on one date, grouped by start time.
     *
     * The state is scheduled-versus-actual, which is the whole point of the
     * view: a class due at 09:00 with nobody marked in at 09:40 is what the
     * leader has to chase, and no status column in the database says so.
     */
    private function dayView(array $planIds, $date): array
    {
        $empty = ['date' => $date, 'classes' => [], 'slots' => [], 'counts' => [
            'live' => 0, 'late' => 0, 'finished' => 0, 'upcoming' => 0,
            'cancelled' => 0, 'feedMissing' => 0, 'cover' => 0, 'rooms' => 0,
        ]];

        if (empty($planIds)) {
            return $empty;
        }

        $sessions = PlansDateList::with([
                'plan.creations', 'plan.group', 'plan.course', 'plan.room', 'plan.venu',
                'plan.tutor.employee', 'proxy.employee', 'attendanceInformation',
            ])
            ->whereIn('plan_id', $planIds)
            ->where('date', $date)
            ->get()
            ->filter(fn ($s) => !empty($s->plan));

        if ($sessions->isEmpty()) {
            return $empty;
        }

        $now = time();
        $isToday = ($date === date('Y-m-d'));

        $classes = [];
        foreach ($sessions as $session) {
            $plan = $session->plan;
            $info = $session->attendanceInformation;

            $scheduled = !empty($plan->start_time) ? substr($plan->start_time, 0, 5) : '';
            $started = !empty($info->start_time) ? date('H:i', strtotime($info->start_time)) : null;
            $ended = !empty($info->end_time) ? date('H:i', strtotime($info->end_time)) : null;

            // Scheduled-versus-actual, resolved in one place.
            if ($session->status === 'Canceled') {
                $state = 'cancelled';
            } elseif ($ended || $session->status === 'Completed') {
                $state = 'finished';
            } elseif ($started) {
                $state = 'live';
            } elseif ($isToday && $scheduled !== '' && strtotime($date.' '.$scheduled) < $now) {
                $state = 'late';
            } else {
                $state = 'upcoming';
            }

            $cover = $session->proxy->full_name ?? null;

            $classes[] = [
                'id' => $session->id,
                'scheduled' => $scheduled,
                'module' => $plan->creations->module_name ?? 'Unknown module',
                'type' => $this->planClassType($plan),
                'group' => $plan->group->name ?? '',
                'course' => $plan->course->name ?? '',
                // On cover the proxy runs it and the named tutor is the one
                // being replaced — shown struck through beside them.
                'tutor' => $cover ?: ($plan->tutor->full_name ?? ''),
                'replacing' => $cover ? ($plan->tutor->full_name ?? '') : null,
                'room' => trim(($plan->venu->name ?? '').' '.($plan->room->name ?? '')) ?: 'Online',
                'start' => $started,
                'end' => $ended,
                'state' => $state,
                'feedGiven' => ((int) $session->feed_given === 1),
            ];
        }

        usort($classes, fn ($a, $b) => [$a['scheduled'], $a['module']] <=> [$b['scheduled'], $b['module']]);

        $slots = [];
        foreach ($classes as $class) {
            $slots[$class['scheduled'] ?: '—'][] = $class;
        }

        $of = fn ($state) => count(array_filter($classes, fn ($c) => $c['state'] === $state));

        return [
            'date' => $date,
            'classes' => $classes,
            'slots' => $slots,
            'counts' => [
                'live' => $of('live'),
                'late' => $of('late'),
                'finished' => $of('finished'),
                'upcoming' => $of('upcoming'),
                'cancelled' => $of('cancelled'),
                // A class that ran without a register is the one gap that
                // silently distorts every attendance figure above.
                'feedMissing' => count(array_filter($classes, function ($c) {
                    return in_array($c['state'], ['finished', 'live']) && !$c['feedGiven'];
                })),
                'cover' => count(array_filter($classes, fn ($c) => !empty($c['replacing']))),
                'rooms' => count(array_unique(array_column($classes, 'room'))),
            ],
        ];
    }
}
