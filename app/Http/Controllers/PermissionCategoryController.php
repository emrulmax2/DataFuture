<?php

namespace App\Http\Controllers;

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
        return view('pages/permissioncategory/index', [
            'title' => 'Permission Category - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Permission Category', 'href' => 'javascript:void(0);']
            ],
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

        $query = PermissionCategory::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $total_rows = $query->count();
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