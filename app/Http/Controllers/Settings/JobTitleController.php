<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobTitleRequest;
use App\Http\Requests\JobTitleUpdateRequest;
use App\Models\Department;
use App\Models\EmployeeJobTitle;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.settings.job-title.index', [
            'title' => 'Job Titles - London Churchill College',
            'subtitle' => 'Job Titles',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Job Titles', 'href' => 'javascript:void(0);']
            ],
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            /* Arrives from the count link on the Department list, so the page
               opens already narrowed to that department. */
            'selectedDepartment' => $request->query('department_id'),
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        /* The holder count comes back with the row rather than per row, so a
           page of titles is one query and not one per title. Two counts: the
           table shows active staff only, while the archive guard still has to
           see every employment pointing at the title, leavers included. */
        $query = EmployeeJobTitle::with('department:id,name')
            ->withCount([
                'employments',
                'employments as active_employments_count' => function($q){
                    $q->whereHas('employee', function($sq){
                        $sq->where('status', 1);
                    });
                },
            ])
            ->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if(isset($request->department_id) && $request->department_id > 0):
            $query->where('department_id', $request->department_id);
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->name,
                    'department_id' => $list->department_id,
                    /* Null reads as "not assigned" in the table rather than as
                       a blank cell that looks like a missing value. */
                    'department' => optional($list->department)->name,
                    'in_use' => (int) $list->active_employments_count,
                    'holders' => (int) $list->employments_count,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(JobTitleRequest $request)
    {
        $data = EmployeeJobTitle::create([
            'name'=> $request->name,
            'department_id' => $request->department_id,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = EmployeeJobTitle::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(JobTitleUpdateRequest $request){
        $data = EmployeeJobTitle::where('id', $request->id)->update([
            'name'=> $request->name,
            'department_id' => $request->department_id,
            'updated_by' => auth()->user()->id
        ]);

        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    /**
     * Archive a title.
     *
     * Refused while staff still hold it. Soft-deleting one in use would leave
     * those employments pointing at a record that no longer appears anywhere,
     * and the job title would quietly vanish from their profile.
     */
    public function destroy($id){
        $jobTitle = EmployeeJobTitle::withCount('employments')->findOrFail($id);

        if($jobTitle->employments_count > 0){
            return response()->json([
                'message' => 'This job title is held by '.$jobTitle->employments_count.' '.
                    \Illuminate\Support\Str::plural('employee', $jobTitle->employments_count).
                    '. Move them to another title first.',
            ], 422);
        }

        $data = $jobTitle->delete();
        return response()->json($data);
    }

    /**
     * Job titles with no department yet.
     *
     * The Department list uses this twice: to decide whether the link button
     * is worth showing at all, and to fill the picker once it is pressed.
     */
    /**
     * Job titles filed under one department, for the dependent dropdown on
     * the employee form. Most titles are still unlinked, so a department with
     * nothing against it falls back to the full list rather than handing the
     * form an empty required field; `filtered` tells the caller which it got.
     */
    public function byDepartment($departmentId)
    {
        $department = Department::find($departmentId);

        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $titles = EmployeeJobTitle::where('department_id', $department->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $filtered = $titles->isNotEmpty();

        if (!$filtered) {
            $titles = EmployeeJobTitle::orderBy('name')->get(['id', 'name']);
        }

        return response()->json([
            'filtered' => $filtered,
            'department' => $department->name,
            'total' => $titles->count(),
            'data' => $titles,
        ]);
    }

    public function unassigned()
    {
        $titles = EmployeeJobTitle::whereNull('department_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'total' => $titles->count(),
            'data' => $titles,
        ]);
    }

    /**
     * Put a set of unassigned titles into one department.
     *
     * Restricted to titles that are actually unassigned: this is a bulk fill
     * for the backlog, not a way to move titles between departments by
     * accident. Anything already assigned is left alone and reported back.
     */
    public function assign(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'job_title_ids' => 'required|array|min:1',
            'job_title_ids.*' => 'integer',
        ]);

        $department = Department::findOrFail($data['department_id']);

        $assignable = EmployeeJobTitle::whereIn('id', $data['job_title_ids'])
            ->whereNull('department_id')
            ->pluck('id');

        $skipped = count($data['job_title_ids']) - $assignable->count();

        if ($assignable->isEmpty()) {
            return response()->json([
                'message' => 'Those job titles already belong to a department.',
            ], 422);
        }

        EmployeeJobTitle::whereIn('id', $assignable)->update([
            'department_id' => $department->id,
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json([
            'assigned' => $assignable->count(),
            'skipped' => $skipped,
            'department' => $department->name,
            'message' => $assignable->count().' '.\Illuminate\Support\Str::plural('job title', $assignable->count())
                .' linked to '.$department->name.'.'
                .($skipped > 0 ? ' '.$skipped.' were already assigned and left unchanged.' : ''),
        ]);
    }

    public function restore($id) {
        $data = EmployeeJobTitle::where('id', $id)->withTrashed()->restore();

        return response()->json($data);
    }
}
