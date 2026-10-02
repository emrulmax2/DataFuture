<?php

namespace App\Http\Controllers\HR\PolicyAssessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PolicyAssessment\PolicyRoleRequest;
use App\Models\PolicyAssignment;
use App\Models\PolicyCategory;
use App\Models\PolicyDocument;
use App\Models\PolicyRole;
use App\Services\PolicyAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Policy Assessments › Roles. A role ("Lecturer", "Admissions Officer",
 * "Finance") is a named set of ticked policies: HR picks the role when
 * assigning and its active policies are selected for them.
 *
 * A role only chooses policies at the moment of assigning. Changing its
 * ticks, switching it off or archiving it never changes assessments that are
 * already assigned. These are the feature's own roles — nothing to do with
 * App\Models\Role and the permission system.
 */
class PolicyRoleController extends Controller
{
    /** Client sort field => SQL column / count alias. Anything else is ignored. */
    const SORTABLE = [
        'id' => 'id',
        'name' => 'name',
        'sort_order' => 'sort_order',
        'is_active' => 'is_active',
        'policies_count' => 'policies_count',
        'assigned' => 'live_assignments_count',
    ];

    public function index()
    {
        $this->guard();

        $policyGroups = $this->policyGroups();
        $policyTotal = 0;
        foreach($policyGroups as $group):
            $policyTotal += count($group['policies']);
        endforeach;

        return view('pages.hr.policy-assessment.roles', [
            'title' => 'Policy Roles - London Churchill College',
            'subtitle' => 'Policy Roles',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Policy Assessments', 'href' => route('policy.assessment')],
                ['label' => 'Roles', 'href' => 'javascript:void(0);'],
            ],
            'policyGroups' => $policyGroups,
            'policyTotal' => $policyTotal,
            /* No role at all yet, archived ones included: the first-run screen. */
            'hasRoles' => PolicyRole::withTrashed()->exists(),
        ]);
    }

    public function list(Request $request)
    {
        $this->guard();

        $queryStr = (isset($request->querystr) && is_scalar($request->querystr) && trim((string) $request->querystr) !== '' ? trim((string) $request->querystr) : '');
        $status = (isset($request->status) && is_scalar($request->status) && in_array((string) $request->status, ['0', '1', '2', '3'], true) ? (int) $request->status : 3);

        $query = PolicyRole::query();
        if($queryStr !== ''):
            $query->where(function ($q) use ($queryStr) {
                $q->where('name', 'LIKE', '%'.$queryStr.'%')->orWhere('description', 'LIKE', '%'.$queryStr.'%');
            });
        endif;
        if($status == 2):
            $query->onlyTrashed();
        elseif($status == 1 || $status == 0):
            $query->where('is_active', $status);
        endif;

        $total_rows = (clone $query)->count();
        $page = (isset($request->page) && $request->page > 0 ? (int) $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? (int) $request->size : 10));
        $perpage = max(1, $perpage);
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        /* Assigned = live assignments made through the role (the policy is
           still switched on, in a category that is not archived), and how
           many members of staff those cover. */
        $query->withCount([
            'policies',
            'assignments as live_assignments_count' => function ($q) {
                $q->live();
            },
        ]);
        $query->addSelect([
            'assigned_staff_count' => PolicyAssignment::selectRaw('COUNT(DISTINCT policy_assignments.employee_id)')
                ->whereColumn('policy_assignments.policy_role_id', 'policy_roles.id')
                ->live(),
        ]);
        /* The ticked policies, for the breakdown by category (no query per row). */
        $query->with(['policies' => function ($q) {
            $q->select('policy_documents.id', 'policy_documents.policy_category_id', 'policy_documents.is_active');
        }]);
        $this->applySorters($query, $request, [['sort_order', 'ASC'], ['name', 'ASC']]);

        $Query = $query->skip($offset)->take($perpage)->get();
        $categories = PolicyCategory::withTrashed()->get(['id', 'name', 'sort_order', 'deleted_at'])->keyBy('id');

        $data = [];
        if(!empty($Query)):
            $i = $offset + 1;
            foreach($Query as $list):
                $breakdown = $this->breakdown($list, $categories);
                $policies = (int) $list->policies->count();
                $assignable = 0;
                foreach($breakdown as $part):
                    $assignable += (int) $part['assignable'];
                endforeach;

                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->name,
                    'description' => $list->description,
                    'sort_order' => (int) $list->sort_order,
                    'is_active' => ($list->is_active ? 1 : 0),
                    'policies_count' => $policies,
                    'active_policies_count' => $assignable,
                    'inactive_policies_count' => max(0, $policies - $assignable),
                    'breakdown' => $breakdown,
                    'policies_summary' => self::policiesSummary($policies, $breakdown),
                    'assigned' => (int) $list->live_assignments_count,
                    'assigned_staff' => (int) $list->assigned_staff_count,
                    'deleted_at' => $list->deleted_at,
                ];
                $i++;
            endforeach;
        endif;

        return response()->json([
            'last_page' => $last_page,
            'total_rows' => $total_rows,
            'data' => $data,
        ]);
    }

    public function store(PolicyRoleRequest $request, PolicyAssessmentService $service)
    {
        $this->guard();

        $policyIds = $this->tickedIds($request);

        $role = DB::transaction(function () use ($request, $service, $policyIds) {
            $role = PolicyRole::create([
                'name' => trim($request->name),
                'description' => (isset($request->description) && trim($request->description) !== '' ? trim($request->description) : null),
                'sort_order' => (isset($request->sort_order) && $request->sort_order !== '' ? (int) $request->sort_order : $this->nextSortOrder()),
                'is_active' => ($request->input('is_active') == 1 ? 1 : 0),
                'created_by' => auth()->user()->id,
            ]);
            $service->syncRolePolicies($role, $policyIds);

            return $role;
        });

        return response()->json(['message' => 'Role created.', 'id' => $role->id, 'policies' => count($policyIds)], 200);
    }

    public function edit(PolicyRole $role)
    {
        $this->guard();

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'sort_order' => (int) $role->sort_order,
            'is_active' => ($role->is_active ? 1 : 0),
            'policy_ids' => $this->currentIds($role),
        ]);
    }

    public function update(PolicyRoleRequest $request, PolicyRole $role, PolicyAssessmentService $service)
    {
        $this->guard();

        $role->fill([
            'name' => trim($request->name),
            'description' => (isset($request->description) && trim($request->description) !== '' ? trim($request->description) : null),
            'sort_order' => (isset($request->sort_order) && $request->sort_order !== '' ? (int) $request->sort_order : (int) $role->sort_order),
            'is_active' => ($request->input('is_active') == 1 ? 1 : 0),
        ]);

        $policyIds = $this->tickedIds($request);
        $ticksChanged = ($policyIds !== $this->currentIds($role));

        if(!$role->isDirty() && !$ticksChanged):
            return response()->json(['message' => 'No data Modified'], 304);
        endif;

        DB::transaction(function () use ($role, $service, $policyIds, $ticksChanged) {
            $role->updated_by = auth()->user()->id;
            if($role->isDirty()):
                $role->save();
            else:
                /* Only the ticks changed: still record when the role was last edited. */
                $role->touch();
            endif;

            if($ticksChanged):
                $service->syncRolePolicies($role, $policyIds);
            endif;
        });

        return response()->json(['message' => 'Data updated', 'policies' => count($policyIds)], 200);
    }

    public function destroy($id)
    {
        $this->guard();

        $role = PolicyRole::findOrFail($id);
        $role->updated_by = auth()->user()->id;
        $role->save();
        $role->delete();

        return response()->json(['message' => 'Role archived.'], 200);
    }

    public function restore($id)
    {
        $this->guard();

        $role = PolicyRole::onlyTrashed()->findOrFail($id);

        /* Names are unique among live roles, so a role cannot come back
           beside a newer one that has taken its name. */
        if(PolicyRole::where('name', $role->name)->exists()):
            return response()->json(['message' => 'Another role is already called "'.$role->name.'". Rename or archive that role first, then restore this one.'], 422);
        endif;

        $role->restore();

        return response()->json(['message' => 'Role restored.'], 200);
    }

    private function guard(): void
    {
        abort_unless(PolicyAssessmentService::canManage(), 403, 'You are not permitted to manage policy assessments.');
    }

    private function nextSortOrder(): int
    {
        return (int) PolicyRole::withTrashed()->max('sort_order') + 1;
    }

    /**
     * The tick list, loaded once with the page: every policy that is not
     * archived, grouped by category in the order HR lists them. A switched
     * off policy can be ticked (it is assigned once it is switched on), and
     * the policies of an archived category are still listed so a role's
     * existing ticks on them are shown and kept.
     *
     * [[id, name, archived, policies => [[id, title, version, is_active]]]]
     */
    private function policyGroups(): array
    {
        $categories = PolicyCategory::withTrashed()
            ->orderByRaw('deleted_at IS NOT NULL')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->orderBy('id', 'ASC')
            ->get(['id', 'name', 'deleted_at']);
        $policies = PolicyDocument::orderBy('sort_order', 'ASC')
            ->orderBy('title', 'ASC')
            ->orderBy('id', 'ASC')
            ->get(['id', 'policy_category_id', 'title', 'version', 'is_active'])
            ->groupBy('policy_category_id');

        $groups = [];
        foreach($categories as $category):
            if(!isset($policies[$category->id])):
                continue;
            endif;

            $rows = [];
            foreach($policies[$category->id] as $policy):
                $rows[] = [
                    'id' => (int) $policy->id,
                    'title' => $policy->title,
                    'version' => $policy->version,
                    'is_active' => ($policy->is_active ? 1 : 0),
                ];
            endforeach;

            $groups[] = [
                'id' => (int) $category->id,
                'name' => $category->name,
                'archived' => $category->trashed(),
                'policies' => $rows,
            ];
        endforeach;

        return $groups;
    }

    /** The policy ids sent with the form: whole numbers, no repeats, ascending. */
    private function tickedIds(Request $request): array
    {
        $ids = [];
        foreach((array) $request->input('policy_ids', []) as $id):
            if(is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0):
                $ids[(int) $id] = true;
            endif;
        endforeach;
        $ids = array_keys($ids);
        sort($ids);

        return $ids;
    }

    /** The policies ticked for the role now (archived policies left out), ascending. */
    private function currentIds(PolicyRole $role): array
    {
        $ids = array_map('intval', $role->policies()->pluck('policy_documents.id')->all());
        $ids = array_values(array_unique($ids));
        sort($ids);

        return $ids;
    }

    /**
     * A role's ticked policies counted per category, in category order:
     * [[category_id, name, label, count, assignable, archived]]. "label" is
     * the short form for the list ("Corporate" for "Corporate Policies");
     * "assignable" leaves out switched-off policies and archived categories.
     * Needs the role's policies loaded.
     */
    private function breakdown(PolicyRole $role, $categories): array
    {
        $parts = [];
        foreach($role->policies as $policy):
            $categoryId = (int) $policy->policy_category_id;
            $category = (isset($categories[$categoryId]) ? $categories[$categoryId] : null);
            if(!isset($parts[$categoryId])):
                $name = (isset($category->name) ? (string) $category->name : 'No category');
                $parts[$categoryId] = [
                    'category_id' => $categoryId,
                    'name' => $name,
                    'label' => self::shortCategoryName($name),
                    'count' => 0,
                    'assignable' => 0,
                    'archived' => (!$category || $category->trashed() ? 1 : 0),
                    'sort' => (isset($category->sort_order) ? (int) $category->sort_order : PHP_INT_MAX),
                ];
            endif;
            $parts[$categoryId]['count'] += 1;
            if($policy->is_active && $parts[$categoryId]['archived'] == 0):
                $parts[$categoryId]['assignable'] += 1;
            endif;
        endforeach;

        usort($parts, function ($a, $b) {
            return [$a['sort'], $a['name'], $a['category_id']] <=> [$b['sort'], $b['name'], $b['category_id']];
        });

        return array_map(function ($part) {
            unset($part['sort']);

            return $part;
        }, $parts);
    }

    /** "Corporate Policies" reads "Corporate" in the list; a name that is only that word is kept. */
    public static function shortCategoryName(string $name): string
    {
        $short = trim((string) preg_replace('/\s+polic(?:y|ies)\s*$/iu', '', trim($name)));

        return ($short !== '' ? $short : trim($name));
    }

    /** "12 policies · Corporate 5 · Academic 4 · Student 3" (also the export value). */
    public static function policiesSummary(int $policies, array $breakdown): string
    {
        $parts = [$policies.' '.($policies == 1 ? 'policy' : 'policies')];
        foreach($breakdown as $part):
            $parts[] = $part['label'].' '.$part['count'];
        endforeach;

        return implode(' · ', $parts);
    }

    /** Tabulator sorters, whitelisted: unknown fields are dropped, dir is asc|desc only. */
    private function applySorters($query, Request $request, array $default): void
    {
        $applied = false;
        $sorters = (isset($request->sorters) && is_array($request->sorters) ? $request->sorters : []);
        foreach($sorters as $sort):
            $field = (is_array($sort) && isset($sort['field']) && is_string($sort['field']) ? $sort['field'] : '');
            $dir = (is_array($sort) && isset($sort['dir']) && is_string($sort['dir']) && strtolower($sort['dir']) == 'desc' ? 'DESC' : 'ASC');
            if(isset(self::SORTABLE[$field])):
                $query->orderBy(self::SORTABLE[$field], $dir);
                $applied = true;
            endif;
        endforeach;

        if(!$applied):
            foreach($default as $order):
                $query->orderBy($order[0], $order[1]);
            endforeach;
        endif;
        $query->orderBy('id', 'ASC');
    }
}
