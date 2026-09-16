<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\PermissionCategory;
use Illuminate\Http\Request;
use App\Http\Requests\PermissionCategoryRequest;
use App\Http\Requests\PermissionCategoryUpdateRequest;

class PermissionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.permissioncategory.index', [
            'title' => 'Permission Category - London Churchill College',
            'subtitle' => 'User Privilege',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Permission Category', 'href' => 'javascript:void(0);']
            ],
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $department = (isset($request->department) && $request->department > 0 ? (int) $request->department : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = PermissionCategory::with('department')->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            /* Searching "Finance" should find the department's categories as
               well as a category that happens to be called that. */
            $query->where(function($q) use($queryStr){
                $q->where('name','LIKE','%'.$queryStr.'%')
                    ->orWhereHas('department', function($d) use($queryStr){
                        $d->where('name','LIKE','%'.$queryStr.'%');
                    });
            });
        endif;
        if($department > 0):
            $query->where('department_id', $department);
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
                    'department' => $list->department->name ?? '',
                    'name' => $list->name,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermissionCategoryRequest $request)
    {
        $data = PermissionCategory::create([
            'department_id' => $request->department_id,
            'name'=> $request->name,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PermissionCategory  $permissionCategory
     * @return \Illuminate\Http\Response
     */
    public function show(PermissionCategory $permissionCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PermissionCategory  $permissionCategory
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        $data = PermissionCategory::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PermissionCategory  $permissionCategory
     * @return \Illuminate\Http\Response
     */
    public function update(PermissionCategoryUpdateRequest $request, PermissionCategory $dataId){      
        $data = PermissionCategory::where('id', $request->id)->update([
            'department_id' => $request->department_id,
            'name'=> $request->name,
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PermissionCategory  $permissionCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        $data = PermissionCategory::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = PermissionCategory::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}