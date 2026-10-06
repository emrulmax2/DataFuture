<?php

namespace App\Http\Controllers\HR\PolicyAssessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PolicyAssessment\PolicyCategoryRequest;
use App\Models\PolicyCategory;
use App\Services\PolicyAssessmentService;
use Illuminate\Http\Request;

/**
 * Policy Assessments › Categories. The groups policies are filed under
 * (Corporate / Academic / Student Policies). Archiving a category hides its
 * policies' tests from staff until it is restored.
 */
class PolicyCategoryController extends Controller
{
    /** Client sort field => SQL column. Anything else is ignored. */
    const SORTABLE = [
        'id' => 'id',
        'name' => 'name',
        'sort_order' => 'sort_order',
        'policies_count' => 'policies_count',
        'is_active' => 'is_active',
    ];

    public function index()
    {
        $this->guard();

        return view('pages.hr.policy-assessment.categories', [
            'title' => 'Policy Categories - London Churchill College',
            'subtitle' => 'Policy Categories',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Policy Assessments', 'href' => route('policy.assessment')],
                ['label' => 'Categories', 'href' => 'javascript:void(0);'],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $this->guard();

        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? trim($request->querystr) : '');
        $status = (isset($request->status) && $request->status > 0 ? (int) $request->status : 1);

        $query = PolicyCategory::query();
        if($queryStr !== ''):
            $query->where(function ($q) use ($queryStr) {
                $q->where('name', 'LIKE', '%'.$queryStr.'%')->orWhere('description', 'LIKE', '%'.$queryStr.'%');
            });
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = (clone $query)->count();
        $page = (isset($request->page) && $request->page > 0 ? (int) $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? (int) $request->size : 10));
        $perpage = max(1, $perpage);
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query->withCount([
            'policies',
            'policies as active_policies_count' => function ($q) {
                $q->where('is_active', 1);
            },
        ]);
        $this->applySorters($query, $request, [['sort_order', 'ASC'], ['name', 'ASC']]);

        $Query = $query->skip($offset)->take($perpage)->get();

        $data = [];
        if(!empty($Query)):
            $i = $offset + 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->name,
                    'description' => $list->description,
                    'sort_order' => (int) $list->sort_order,
                    'is_active' => ($list->is_active ? 1 : 0),
                    'policies_count' => (int) $list->policies_count,
                    'active_policies_count' => (int) $list->active_policies_count,
                    'deleted_at' => $list->deleted_at,
                ];
                $i++;
            endforeach;
        endif;

        return response()->json(['last_page' => $last_page, 'total_rows' => $total_rows, 'data' => $data]);
    }

    public function store(PolicyCategoryRequest $request)
    {
        $this->guard();

        $category = PolicyCategory::create([
            'name' => trim($request->name),
            'description' => (isset($request->description) && trim($request->description) !== '' ? trim($request->description) : null),
            'sort_order' => (isset($request->sort_order) && $request->sort_order !== '' ? (int) $request->sort_order : $this->nextSortOrder()),
            'is_active' => ($request->input('is_active') == 1 ? 1 : 0),
            'created_by' => auth()->user()->id,
        ]);

        return response()->json(['message' => 'Category created.', 'id' => $category->id], 200);
    }

    public function edit(PolicyCategory $category)
    {
        $this->guard();

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'sort_order' => (int) $category->sort_order,
            'is_active' => ($category->is_active ? 1 : 0),
        ]);
    }

    public function update(PolicyCategoryRequest $request, PolicyCategory $category)
    {
        $this->guard();

        $category->fill([
            'name' => trim($request->name),
            'description' => (isset($request->description) && trim($request->description) !== '' ? trim($request->description) : null),
            'sort_order' => (isset($request->sort_order) && $request->sort_order !== '' ? (int) $request->sort_order : (int) $category->sort_order),
            'is_active' => ($request->input('is_active') == 1 ? 1 : 0),
        ]);

        if(!$category->isDirty()):
            return response()->json(['message' => 'No data Modified'], 304);
        endif;

        $category->updated_by = auth()->user()->id;
        $category->save();

        return response()->json(['message' => 'Data updated'], 200);
    }

    public function destroy($id)
    {
        $this->guard();

        $category = PolicyCategory::findOrFail($id);
        $category->updated_by = auth()->user()->id;
        $category->save();
        $category->delete();

        return response()->json(['message' => 'Category archived.'], 200);
    }

    public function restore($id)
    {
        $this->guard();

        $category = PolicyCategory::onlyTrashed()->findOrFail($id);
        $category->restore();

        return response()->json(['message' => 'Category restored.'], 200);
    }

    private function guard(): void
    {
        abort_unless(PolicyAssessmentService::canManage(), 403, 'You are not permitted to manage policy assessments.');
    }

    private function nextSortOrder(): int
    {
        return (int) PolicyCategory::withTrashed()->max('sort_order') + 1;
    }

    /** Tabulator sorters, whitelisted: unknown fields are dropped, dir is asc|desc only. */
    private function applySorters($query, Request $request, array $default): void
    {
        $applied = false;
        $sorters = (isset($request->sorters) && is_array($request->sorters) ? $request->sorters : []);
        foreach($sorters as $sort):
            $field = (is_array($sort) && isset($sort['field']) && is_string($sort['field']) ? $sort['field'] : '');
            $dir = (is_array($sort) && isset($sort['dir']) && strtolower((string) $sort['dir']) == 'desc' ? 'DESC' : 'ASC');
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
