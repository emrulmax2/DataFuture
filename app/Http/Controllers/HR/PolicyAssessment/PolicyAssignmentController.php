<?php

namespace App\Http\Controllers\HR\PolicyAssessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PolicyAssessment\PolicyAssignmentRequest;
use App\Http\Requests\PolicyAssessment\PolicyAssignmentUpdateRequest;
use App\Models\Employee;
use App\Models\PolicyAssignment;
use App\Models\PolicyAttempt;
use App\Models\PolicyBadge;
use App\Models\PolicyCategory;
use App\Models\PolicyDocument;
use App\Models\PolicyRole;
use App\Services\PolicyAssessmentService;
use App\Support\PolicyLevel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Policy Assessments › Assignments. Who has been asked to pass which policy,
 * at which level and through which role, where they have got to, and the HR
 * actions on one assignment (due date, extra attempt, archive/restore) and
 * on a badge (revoke/restore). The list endpoint is shared with the employee
 * profile's Policy Assessments tab (param `employee`).
 *
 * HR assigns by role: picking a role in the Assign modal fills the policy
 * picker with that role's policies, and what is left in the picker is what
 * is posted and assigned.
 */
class PolicyAssignmentController extends Controller
{
    /** Status filter values the list accepts — the display statuses plus archived. */
    const STATUS_FILTERS = ['pending', 'in_progress', 'failed', 'passed', 'overdue', 'locked', 'archived'];

    /** Client sort fields the list accepts. Anything else is ignored. */
    const SORTABLE = ['id', 'employee_name', 'policy_title', 'level', 'role_name', 'assigned_at', 'due_date', 'status', 'attempts_count', 'best_score', 'policy_opened_at', 'last_attempt_at'];

    /** Role filter value for assignments made without a role (policies picked by hand). */
    const ROLE_FILTER_NONE = 'none';

    /** Up to this many roles the Assign modal shows them as cards; more become a searchable list. */
    const ROLE_CARDS_MAX = 6;

    public function index(Request $request)
    {
        $this->guard();
        app(PolicyAssessmentService::class)->finaliseExpiredAttempts();

        $options = self::assignOptions();
        $preselect = (isset($request->employee) && ctype_digit((string) $request->employee) ? (int) $request->employee : 0);

        return view('pages.hr.policy-assessment.assignments', [
            'title' => 'Policy Assignments - London Churchill College',
            'subtitle' => 'Assignments',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Policy Assessments', 'href' => route('policy.assessment')],
                ['label' => 'Assignments', 'href' => 'javascript:void(0);'],
            ],
            'assignEmployees' => $options['employees'],
            'filterEmployees' => Employee::where(function ($q) {
                    $q->where('status', 1)->orWhereHas('policyAssignments');
                })
                ->orderBy('first_name', 'ASC')->orderBy('last_name', 'ASC')
                ->get(['id', 'first_name', 'last_name', 'status']),
            'roles' => $options['roles'],
            'hasRoles' => $options['hasRoles'],
            'policyGroups' => $options['policyGroups'],
            'levelMix' => $options['levelMix'],
            'levels' => PolicyLevel::labels(),
            'filterCategories' => PolicyCategory::orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->get(['id', 'name']),
            'filterRoles' => self::roleFilterOptions(),
            'allPolicies' => PolicyDocument::with('category')->orderBy('title', 'ASC')->get(['id', 'title', 'policy_category_id']),
            'autoOpenAssign' => ($request->input('assign') == 1 ? 1 : 0),
            'preselectEmployee' => $preselect,
        ]);
    }

    /**
     * What the Assign modal offers: active staff, the roles HR can pick
     * (`roles`) and active policies grouped by category, plus the question
     * pattern of each exam (`levelMix`, straight from PolicyLevel::MIX) for
     * the level cards.
     *
     * An exam is never drawn from one level alone, so each policy says, per
     * exam level, whether that exam can be drawn in full and if not what is
     * missing (`exam`, see examReadiness()); the picker flags the policies
     * whose exam at the level HR has chosen is not ready. `drawable_levels`
     * is how many active questions the bank holds at each level, and
     * `drawable` the Beginner count.
     *
     * Each role lists the policies it would add to the picker (`policy_ids`:
     * its active policies, in the order HR lists them — the same set
     * PolicyAssessmentService::assign() expands a role to) and, per exam
     * level, how many of those are not ready for that exam (`not_ready`).
     * Only switched-on roles are offered; `hasRoles` says whether any role
     * exists at all, for the empty state.
     *
     * The counts are preloaded, so none of this runs a query per policy or
     * per role. Shared with the employee profile tab.
     */
    public static function assignOptions(): array
    {
        $employees = Employee::where('status', 1)
            ->with(['employment.employeeJobTitle'])
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get();

        $policies = PolicyDocument::where('is_active', 1)
            ->whereHas('category')
            ->with('category')
            ->withDrawableCounts()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('title', 'ASC')
            ->get();

        $policyGroups = [];
        /* policy id => its exam readiness, for the roles below. */
        $examOf = [];
        foreach($policies as $policy):
            $categoryId = (int) $policy->policy_category_id;
            if(!isset($policyGroups[$categoryId])):
                $policyGroups[$categoryId] = [
                    'id' => $categoryId,
                    'name' => (isset($policy->category->name) ? $policy->category->name : 'Uncategorised'),
                    'sort_order' => (isset($policy->category->sort_order) ? (int) $policy->category->sort_order : 0),
                    'policies' => [],
                ];
            endif;
            $byLevel = $policy->drawableCountsByLevel();
            $exam = self::examReadiness($policy);
            $examOf[(int) $policy->id] = $exam;
            $policyGroups[$categoryId]['policies'][] = [
                'id' => $policy->id,
                'title' => $policy->title,
                'drawable' => (isset($byLevel[PolicyLevel::BEGINNER]) ? (int) $byLevel[PolicyLevel::BEGINNER] : 0),
                'drawable_levels' => $byLevel,
                'questions_per_attempt' => max(1, (int) $policy->questions_per_attempt),
                'time_limit_minutes' => ($policy->isTimed() ? (int) $policy->time_limit_minutes : null),
                'exam' => $exam,
            ];
        endforeach;
        uasort($policyGroups, function ($a, $b) {
            return ($a['sort_order'] <=> $b['sort_order']) ?: strcmp($a['name'], $b['name']);
        });

        $roleRows = PolicyRole::active()
            ->with(['activePolicies' => function ($q) {
                $q->select('policy_documents.id');
            }])
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        $roles = [];
        foreach($roleRows as $role):
            $policyIds = [];
            $notReady = array_fill_keys(PolicyLevel::all(), 0);
            foreach($role->activePolicies as $rolePolicy):
                $policyId = (int) $rolePolicy->id;
                /* Only what the picker offers can be added to it. */
                if(!isset($examOf[$policyId])):
                    continue;
                endif;
                $policyIds[] = $policyId;
                foreach(PolicyLevel::all() as $examLevel):
                    if(empty($examOf[$policyId][$examLevel]['ready'])):
                        $notReady[$examLevel] += 1;
                    endif;
                endforeach;
            endforeach;

            $roles[] = [
                'id' => (int) $role->id,
                'name' => (string) $role->name,
                'description' => ($role->description !== null ? trim((string) $role->description) : ''),
                'policy_ids' => $policyIds,
                'policies_count' => count($policyIds),
                'not_ready' => $notReady,
            ];
        endforeach;

        return [
            'employees' => $employees,
            'roles' => $roles,
            'hasRoles' => (!empty($roles) || PolicyRole::query()->exists()),
            'policyGroups' => array_values($policyGroups),
            'levelMix' => PolicyLevel::MIX,
        ];
    }

    /**
     * The Role filter of the assignment lists: every live role (switched on
     * or not), plus an archived role that assignments were made through,
     * marked "(archived)". [['id' => int, 'name' => string], ...]
     */
    public static function roleFilterOptions(): array
    {
        $rows = PolicyRole::withTrashed()
            ->where(function ($q) {
                $q->whereNull('policy_roles.deleted_at')
                    ->orWhereHas('assignments', function ($a) {
                        $a->withTrashed();
                    });
            })
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->orderBy('id', 'ASC')
            ->get(['id', 'name', 'deleted_at']);

        $options = [];
        foreach($rows as $role):
            $options[] = ['id' => (int) $role->id, 'name' => self::roleLabel($role)];
        endforeach;

        return $options;
    }

    /** A role's name as the lists show it: an archived role reads "<name> (archived)". */
    public static function roleLabel(?PolicyRole $role): string
    {
        if(!$role):
            return '';
        endif;

        return $role->name.($role->trashed() ? ' (archived)' : '');
    }

    /**
     * The role an assignment was made through, for a list row: `role_id`
     * (NULL when the policy was picked by hand), `role_name` ('' then) and
     * `role_archived`. Query-free when `role` was eager-loaded.
     */
    public static function roleFields(PolicyAssignment $assignment): array
    {
        $role = ($assignment->policy_role_id ? $assignment->role : null);

        return [
            'role_id' => ($assignment->policy_role_id ? (int) $assignment->policy_role_id : null),
            'role_name' => self::roleLabel($role),
            'role_archived' => ($role && $role->trashed() ? 1 : 0),
        ];
    }

    /**
     * Whether each exam on a policy can be drawn, and if not why:
     * [exam level => ['ready' => bool, 'short' => [question level => how many
     * more active questions are needed], 'why' => 'needs 1 more Expert
     * question' ('' when ready)]].
     *
     * An exam needs its share of questions from all three levels
     * (PolicyDocument::examReady()), so a Beginner exam can be held up by a
     * missing Expert question. Query-free when the policy was loaded with
     * withDrawableCounts().
     */
    public static function examReadiness(PolicyDocument $policy): array
    {
        $tiers = [];
        foreach(PolicyLevel::all() as $examLevel):
            $short = [];
            foreach($policy->examRequirements($examLevel) as $questionLevel => $row):
                if($row['short'] > 0):
                    $short[$questionLevel] = (int) $row['short'];
                endif;
            endforeach;

            $tiers[$examLevel] = [
                'ready' => $policy->examReady($examLevel),
                'short' => $short,
                'why' => self::shortfallText($short),
            ];
        endforeach;

        return $tiers;
    }

    /**
     * [question level => how many short] in words: 'needs 1 more Expert
     * question', 'needs 2 more Beginner and 1 more Expert question'. Empty
     * when nothing is short.
     */
    public static function shortfallText(array $short): string
    {
        $parts = [];
        $last = 0;
        foreach($short as $questionLevel => $count):
            if((int) $count > 0):
                $parts[] = (int) $count.' more '.PolicyLevel::label($questionLevel);
                $last = (int) $count;
            endif;
        endforeach;
        if(empty($parts)):
            return '';
        endif;

        $tail = array_pop($parts);
        $list = (!empty($parts) ? implode(', ', $parts).' and '.$tail : $tail);

        return 'needs '.$list.' '.($last == 1 ? 'question' : 'questions');
    }

    public function list(Request $request)
    {
        $this->guard();

        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? trim($request->querystr) : '');
        $employee = (isset($request->employee) && $request->employee > 0 ? (int) $request->employee : 0);
        /* A timed test whose clock ran out is marked before any status is read. */
        app(PolicyAssessmentService::class)->finaliseExpiredAttempts($employee > 0 ? $employee : null);
        $category = (isset($request->category) && $request->category > 0 ? (int) $request->category : 0);
        $policy = (isset($request->policy) && $request->policy > 0 ? (int) $request->policy : 0);
        $level = (isset($request->level) && is_string($request->level) && PolicyLevel::isValid($request->level) ? $request->level : '');
        $status = (isset($request->status) && in_array($request->status, self::STATUS_FILTERS, true) ? $request->status : '');
        /* A role id, or 'none' for assignments made without a role. Anything else is ignored. */
        $role = $request->input('role');
        $role = (is_scalar($role) ? trim((string) $role) : '');

        $query = PolicyAssignment::query();
        if($employee > 0):
            $query->where('policy_assignments.employee_id', $employee);
        endif;
        if($role === self::ROLE_FILTER_NONE):
            $query->whereNull('policy_assignments.policy_role_id');
        elseif(ctype_digit($role) && (int) $role > 0):
            $query->where('policy_assignments.policy_role_id', (int) $role);
        endif;
        if($policy > 0):
            $query->where('policy_assignments.policy_document_id', $policy);
        endif;
        if($level !== ''):
            $query->ofLevel($level);
        endif;
        if($category > 0):
            $query->whereHas('policy', function ($q) use ($category) {
                $q->where('policy_category_id', $category);
            });
        endif;
        if($queryStr !== ''):
            $query->where(function ($q) use ($queryStr) {
                $q->whereHas('employee', function ($eq) use ($queryStr) {
                    $eq->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%'.$queryStr.'%']);
                })->orWhereHas('policy', function ($pq) use ($queryStr) {
                    $pq->where('title', 'LIKE', '%'.$queryStr.'%');
                });
            });
        endif;
        $this->applyStatusFilter($query, $status);

        $total_rows = (clone $query)->count();
        $page = (isset($request->page) && $request->page > 0 ? (int) $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? (int) $request->size : 10));
        $perpage = max(1, $perpage);
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $this->applySorters($query, $request);
        /* Tab exits and time away over every attempt of the assignment, summed
           in the same query from the totals kept on each attempt (for the export). */
        $query->withSum('attempts as tab_exits_total', 'tab_exits')
            ->withSum('attempts as away_seconds_total', 'away_seconds');
        $query->with([
            'openAttempt',
            'badge',
            'role',
            'policy.category' => function ($q) {
                $q->withTrashed();
            },
            'employee' => function ($q) {
                $q->withTrashed();
            },
            'employee.employment.employeeJobTitle',
            'employee.employment.department',
            'assignedBy:id,name',
        ]);

        $Query = $query->skip($offset)->take($perpage)->get();

        $data = [];
        if(!empty($Query)):
            $i = $offset + 1;
            foreach($Query as $list):
                $data[] = $this->rowData($list, $i);
                $i++;
            endforeach;
        endif;

        return response()->json(['last_page' => $last_page, 'total_rows' => $total_rows, 'data' => $data]);
    }

    public function store(PolicyAssignmentRequest $request, PolicyAssessmentService $service)
    {
        $this->guard();

        $employeeIds = (array) ($request->input('employee_ids') ?: []);
        $policyIds = (array) ($request->input('policy_ids') ?: []);
        $categoryIds = (array) ($request->input('category_ids') ?: []);
        $roleIds = (array) ($request->input('role_ids') ?: []);
        $dueDate = (isset($request->due_date) && trim((string) $request->due_date) !== '' ? trim((string) $request->due_date) : null);
        $note = (isset($request->note) && trim((string) $request->note) !== '' ? trim((string) $request->note) : null);
        $level = (string) $request->input('level');

        /* The policy list the screen posts is what gets assigned; the roles
           say which role each new assignment came through. Roles on their
           own assign every policy they cover (see assign()). */
        $result = $service->assign($employeeIds, $policyIds, $categoryIds, $dueDate, $request->boolean('send_email'), $note, $level, $roleIds);

        if(($result['created'] + $result['updated'] + $result['skipped']) == 0):
            $nothing = (empty($policyIds) && empty($categoryIds) && !empty($roleIds)
                ? 'None of the chosen roles has an active policy to assign.'
                : 'None of the chosen categories has an active policy to assign.');

            return response()->json([
                'message' => 'There was nothing to assign.',
                'errors' => ['policy_ids' => [$nothing]],
            ], 422);
        endif;

        $roles = $this->rolesUsed($roleIds, $policyIds, $categoryIds);
        $roleNames = array_column($roles, 'name');

        return response()->json(array_merge([
            'message' => $this->assignedMessage($level, $roleNames),
            'email_requested' => $request->boolean('send_email'),
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'roles' => $roles,
            'role_names' => $roleNames,
        ], $result), 200);
    }

    /**
     * The picked roles this assign went through, in the order picked:
     * [['id' => int, 'name' => string], ...]. When the screen sent its own
     * policy list, a role none of whose policies is in that list is left out
     * (HR ticked it and then removed everything it added).
     */
    private function rolesUsed(array $roleIds, array $policyIds, array $categoryIds): array
    {
        $ids = [];
        foreach($roleIds as $roleId):
            if(is_scalar($roleId) && ctype_digit((string) $roleId) && (int) $roleId > 0 && !in_array((int) $roleId, $ids, true)):
                $ids[] = (int) $roleId;
            endif;
        endforeach;
        if(empty($ids)):
            return [];
        endif;

        $wanted = [];
        foreach($policyIds as $policyId):
            if(is_scalar($policyId) && ctype_digit((string) $policyId)):
                $wanted[(int) $policyId] = true;
            endif;
        endforeach;
        /* Roles alone, or the category route: every picked role counts. */
        $filter = (!empty($wanted) && empty($categoryIds));

        $rows = PolicyRole::active()
            ->whereIn('policy_roles.id', $ids)
            ->with(['activePolicies' => function ($q) {
                $q->select('policy_documents.id');
            }])
            ->get()
            ->keyBy('id');

        $roles = [];
        foreach($ids as $id):
            if(!isset($rows[$id])):
                continue;
            endif;
            $covers = false;
            foreach($rows[$id]->activePolicies as $rolePolicy):
                if(!$filter || isset($wanted[(int) $rolePolicy->id])):
                    $covers = true;
                    break;
                endif;
            endforeach;
            if($covers):
                $roles[] = ['id' => $id, 'name' => (string) $rows[$id]->name];
            endif;
        endforeach;

        return $roles;
    }

    /**
     * 'Policies assigned as Beginner exams.', or with the roles named:
     * 'Policies for the Lecturer role assigned as Beginner exams.',
     * 'Policies for the Lecturer and Finance roles assigned as …'.
     */
    private function assignedMessage(string $level, array $roleNames): string
    {
        $exams = 'assigned as '.PolicyLevel::label($level).' exams.';
        if(empty($roleNames)):
            return 'Policies '.$exams;
        endif;

        $last = array_pop($roleNames);
        $list = (!empty($roleNames) ? implode(', ', $roleNames).' and '.$last : $last);

        return 'Policies for the '.$list.' '.(!empty($roleNames) ? 'roles' : 'role').' '.$exams;
    }

    /** Due date and note only: the level is fixed once assigned (assign again at another level instead). */
    public function update(PolicyAssignmentUpdateRequest $request, PolicyAssignment $assignment)
    {
        $this->guard();

        $due = (isset($request->due_date) && trim((string) $request->due_date) !== '' ? Carbon::parse($request->due_date)->format('Y-m-d') : null);
        $note = (isset($request->note) && trim((string) $request->note) !== '' ? trim((string) $request->note) : null);

        $assignment->due_date = $due;
        $assignment->note = $note;
        if(!$assignment->isDirty()):
            return response()->json(['message' => 'No data modified'], 304);
        endif;

        $assignment->updated_by = auth()->user()->id;
        $assignment->save();

        return response()->json(['message' => 'Assignment updated.'], 200);
    }

    /**
     * One more attempt for a member of staff who has failed a policy that
     * limits attempts (typically because they have run out).
     */
    public function retake(PolicyAssignment $assignment, PolicyAssessmentService $service)
    {
        $this->guard();

        if($assignment->status == PolicyAssignment::STATUS_PASSED):
            return response()->json(['message' => 'This policy has already been passed.'], 422);
        endif;
        if($assignment->maxAttemptsAllowed() === null):
            return response()->json(['message' => 'This policy allows unlimited attempts, so there is nothing to allow.'], 422);
        endif;
        if($assignment->status != PolicyAssignment::STATUS_FAILED):
            return response()->json(['message' => 'A retake can only be allowed after a failed attempt.'], 422);
        endif;

        $service->grantExtraAttempt($assignment);
        $assignment->refresh();

        return response()->json([
            'message' => 'One more attempt allowed.',
            'attempts_left' => $assignment->attemptsLeft(),
            'max_attempts' => $assignment->maxAttemptsAllowed(),
        ], 200);
    }

    public function destroy($id)
    {
        $this->guard();

        $assignment = PolicyAssignment::findOrFail($id);

        return response()->json($assignment->delete());
    }

    public function restore($id)
    {
        $this->guard();

        $assignment = PolicyAssignment::onlyTrashed()->findOrFail($id);

        /* At most one live assignment per (employee, policy, level): the policy
           may have been assigned again at this level since this one was archived.
           Same locks, in the same order, as PolicyAssessmentService::assign(), so
           a restore and an assign of the same pair cannot both go live. */
        $restored = DB::transaction(function () use ($assignment) {
            PolicyDocument::where('id', $assignment->policy_document_id)->lockForUpdate()->pluck('id');
            Employee::where('id', $assignment->employee_id)->lockForUpdate()->pluck('id');

            $live = PolicyAssignment::where('employee_id', $assignment->employee_id)
                ->where('policy_document_id', $assignment->policy_document_id)
                ->where('level', $assignment->levelKey())
                ->lockForUpdate()
                ->exists();
            if($live):
                return null;
            endif;

            return $assignment->restore();
        }, 3);

        if($restored === null):
            return response()->json(['message' => 'This member of staff already has a live '.$assignment->level_label.' assignment for this policy, so this one cannot be restored.'], 422);
        endif;

        return response()->json($restored);
    }

    /** Revoke (soft-delete) a live badge. A revoked or unknown id is a 404. */
    public function destroyBadge($id, PolicyAssessmentService $service)
    {
        $this->guard();

        $badge = PolicyBadge::findOrFail($id);
        $service->revokeBadge($badge);

        return response()->json(['message' => 'Badge revoked.', 'id' => (int) $badge->id], 200);
    }

    /**
     * Give a revoked badge back. A live or unknown id is a 404; a 422 when the
     * member of staff already holds another live badge for the same policy
     * and level.
     */
    public function restoreBadge($id, PolicyAssessmentService $service)
    {
        $this->guard();

        $badge = PolicyBadge::onlyTrashed()->findOrFail($id);
        try {
            $badge = $service->restoreBadge((int) $badge->id);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $message = (isset($errors['badge'][0]) ? $errors['badge'][0] : 'This badge cannot be restored.');

            return response()->json(['message' => $message, 'errors' => $errors], 422);
        }

        return response()->json(['message' => 'Badge restored.', 'id' => (int) $badge->id], 200);
    }

    /**
     * Each value matches display_status exactly, so a filter shows the rows
     * whose pill reads that status: passed, locked, in_progress, overdue,
     * failed, pending — decided in that order.
     */
    private function applyStatusFilter($query, string $status): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $locked = function ($q) {
            $q->whereNotNull('policy_documents.max_attempts')
                ->whereRaw('policy_assignments.attempts_count >= policy_documents.max_attempts + policy_assignments.extra_attempts');
        };
        $open = function ($q) {
            $q->where('status', PolicyAttempt::STATUS_IN_PROGRESS);
        };
        $notLocked = function ($q) use ($locked) {
            $q->where('policy_assignments.status', '!=', PolicyAssignment::STATUS_FAILED)
                ->orWhereDoesntHave('policy', $locked);
        };
        $notOverdue = function ($q) use ($today) {
            $q->whereNull('policy_assignments.due_date')->orWhere('policy_assignments.due_date', '>=', $today);
        };

        if($status == 'archived'):
            $query->onlyTrashed();
        elseif($status == 'passed'):
            $query->where('policy_assignments.status', PolicyAssignment::STATUS_PASSED);
        elseif($status == 'locked'):
            $query->where('policy_assignments.status', PolicyAssignment::STATUS_FAILED)->whereHas('policy', $locked);
        elseif($status == 'in_progress'):
            $query->where('policy_assignments.status', '!=', PolicyAssignment::STATUS_PASSED)
                ->whereHas('attempts', $open)
                ->where($notLocked);
        elseif($status == 'overdue'):
            $query->where('policy_assignments.status', '!=', PolicyAssignment::STATUS_PASSED)
                ->whereNotNull('policy_assignments.due_date')
                ->where('policy_assignments.due_date', '<', $today)
                ->whereDoesntHave('attempts', $open)
                ->where($notLocked);
        elseif($status == 'failed'):
            $query->where('policy_assignments.status', PolicyAssignment::STATUS_FAILED)
                ->whereDoesntHave('policy', $locked)
                ->whereDoesntHave('attempts', $open)
                ->where($notOverdue);
        elseif($status == 'pending'):
            $query->where('policy_assignments.status', PolicyAssignment::STATUS_PENDING)
                ->whereDoesntHave('attempts', $open)
                ->where($notOverdue);
        endif;
    }

    private function applySorters($query, Request $request): void
    {
        $sorters = (isset($request->sorters) && is_array($request->sorters) ? $request->sorters : []);
        $applied = false;
        foreach($sorters as $sort):
            $field = (is_array($sort) && isset($sort['field']) ? (string) $sort['field'] : '');
            $dir = (is_array($sort) && isset($sort['dir']) && strtolower((string) $sort['dir']) == 'asc' ? 'asc' : 'desc');
            if(!in_array($field, self::SORTABLE, true)):
                continue;
            endif;

            if($field == 'employee_name'):
                $query->orderBy(Employee::withTrashed()->select('first_name')->whereColumn('employees.id', 'policy_assignments.employee_id')->limit(1), $dir)
                    ->orderBy(Employee::withTrashed()->select('last_name')->whereColumn('employees.id', 'policy_assignments.employee_id')->limit(1), $dir);
            elseif($field == 'policy_title'):
                $query->orderBy(PolicyDocument::withTrashed()->select('title')->whereColumn('policy_documents.id', 'policy_assignments.policy_document_id')->limit(1), $dir);
            elseif($field == 'level'):
                /* Easiest first, not alphabetical. $dir is 'asc' or 'desc' only. */
                $query->orderByRaw("FIELD(policy_assignments.level, 'beginner', 'intermediate', 'expert') ".$dir);
            elseif($field == 'role_name'):
                $query->orderBy(PolicyRole::withTrashed()->select('name')->whereColumn('policy_roles.id', 'policy_assignments.policy_role_id')->limit(1), $dir);
            else:
                $query->orderBy('policy_assignments.'.$field, $dir);
            endif;
            $applied = true;
        endforeach;

        if(!$applied):
            $query->orderBy('policy_assignments.assigned_at', 'desc');
        endif;
        $query->orderBy('policy_assignments.id', 'desc');
    }

    private function rowData(PolicyAssignment $list, int $sl): array
    {
        $employee = $list->employee;
        $employment = (isset($employee->employment) ? $employee->employment : null);
        $policy = $list->policy;
        $maxAllowed = $list->maxAttemptsAllowed();
        $display = $list->display_status;
        $level = $list->levelKey();
        $badge = $list->badge;
        $role = self::roleFields($list);
        /* All attempts of this assignment together; 0 and '' when the test was never left. */
        $tabExits = max(0, (int) $list->tab_exits_total);
        $awaySeconds = max(0, (int) $list->away_seconds_total);

        return [
            'id' => $list->id,
            'sl' => $sl,
            'employee_id' => (int) $list->employee_id,
            'employee_name' => (isset($employee->full_name) ? $employee->full_name : 'Unknown employee'),
            'employee_archived' => (isset($employee) && $employee->trashed() ? 1 : 0),
            'job_title' => (isset($employment->employeeJobTitle->name) ? $employment->employeeJobTitle->name : ''),
            'department' => (isset($employment->department->name) ? $employment->department->name : ''),
            'policy_id' => (int) $list->policy_document_id,
            'policy_title' => (isset($policy->title) ? $policy->title : 'Deleted policy'),
            'policy_archived' => (!$policy || $policy->trashed() ? 1 : 0),
            'policy_inactive' => (isset($policy->is_active) && !$policy->is_active ? 1 : 0),
            'category' => (isset($policy->category->name) ? $policy->category->name : ''),
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'role_id' => $role['role_id'],
            'role_name' => $role['role_name'],
            'role_archived' => $role['role_archived'],
            'has_badge' => ($badge ? 1 : 0),
            'badge_id' => ($badge ? (int) $badge->id : null),
            'badge_awarded_at' => ($badge && $badge->awarded_at ? $badge->awarded_at->format('d M Y') : ''),
            'assigned_at' => ($list->assigned_at ? $list->assigned_at->format('d M Y') : ''),
            'assigned_by' => (isset($list->assignedBy->name) ? $list->assignedBy->name : ''),
            'due_date' => ($list->due_date ? $list->due_date->format('d M Y') : ''),
            'due_date_input' => ($list->due_date ? $list->due_date->format('d-m-Y') : ''),
            'is_overdue' => ($list->isOverdue() ? 1 : 0),
            'status' => $list->status,
            'display_status' => $display,
            'display_status_label' => $list->display_status_label,
            'attempts_count' => (int) $list->attempts_count,
            'max_attempts' => $maxAllowed,
            'attempts_left' => $list->attemptsLeft(),
            'best_score' => ($list->best_score !== null ? (float) $list->best_score : null),
            'last_score' => ($list->last_score !== null ? (float) $list->last_score : null),
            'policy_opened_at' => ($list->policy_opened_at ? $list->policy_opened_at->format('d M Y H:i') : ''),
            'acknowledged_at' => ($list->acknowledged_at ? $list->acknowledged_at->format('d M Y H:i') : ''),
            'last_attempt_at' => ($list->last_attempt_at ? $list->last_attempt_at->format('d M Y H:i') : ''),
            'tab_exits' => $tabExits,
            'away_seconds' => $awaySeconds,
            'away_label' => ($tabExits > 0 ? PolicyResultController::awayLabel($awaySeconds) : ''),
            'passed_at' => ($list->passed_at ? $list->passed_at->format('d M Y') : ''),
            'note' => ($list->note !== null ? $list->note : ''),
            'can_retake' => ($list->status == PolicyAssignment::STATUS_FAILED && $maxAllowed !== null && !$list->trashed() ? 1 : 0),
            'deleted_at' => $list->deleted_at,
        ];
    }

    private function guard(): void
    {
        abort_unless(PolicyAssessmentService::canManage(), 403, 'You are not permitted to manage policy assessments.');
    }
}
