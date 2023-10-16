<?php

namespace App\Http\Controllers;

use App\Models\PermissionTemplate;
use App\Http\Requests\PermissionTemplateRequest;
use App\Models\Role;
use App\Models\Department;
use App\Models\PermissionCategory;
use Illuminate\Http\Request;

class PermissionTemplateController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $role = (isset($request->role) && $request->role > 0 ? $request->role : '');
        $permissioncategory = (isset($request->permissioncategory) && $request->permissioncategory > 0 ? $request->permissioncategory : '');
        $department = (isset($request->department) && $request->department > 0 ? $request->department : '');

        $query = PermissionTemplate::where('id', '!=', 0);
        if(!empty($queryStr)):
            $query->where('role_id', $role);
            $query->where('permission_category_id', $permissioncategory);
            $query->where('department_id', $department);
            $query->where('type','LIKE','%'.$queryStr.'%');
        endif;
        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = PermissionTemplate::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('role_id', $role);
            $query->where('permission_category_id', $permissioncategory);
            $query->where('department_id', $department);
            $query->where('type','LIKE','%'.$queryStr.'%');
        endif;
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
                    'role_id' => $list->role->display_name,
                    'permission_category_id' => $list->permissioncategory->name,
                    'department_id' => $list->department->name,
                    'type' => $list->type,
                    'R' => ($list->R==1 ? 'Yes' : 'No'),
                    'W' => ($list->W==1 ? 'Yes' : 'No'),
                    'D' => ($list->D==1 ? 'Yes' : 'No'),
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
    public function store(PermissionTemplateRequest $request){
        $request->request->add(['created_by' => auth()->user()->id]);
        $data = PermissionTemplate::create($request->all());
        
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PermissionTemplate  $permissionTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        $data = PermissionTemplate::find($id);

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
     * @param  \App\Models\PermissionTemplate  $permissionTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(PermissionTemplateRequest $request){
        $pt_ID = $request->id;
        $courseDF = PermissionTemplate::where('id', $pt_ID)->update([
            'permission_category_id'=> $request->permission_category_id,
            'role_id'=> $request->role_id,
            'department_id'=> $request->department_id,
            'type'=> $request->type,
            'R' => (isset($request->R) ? $request->R : '0'),
            'W' => (isset($request->W) ? $request->W : '0'),
            'D' => (isset($request->D) ? $request->D : '0'),
            'updated_by' => auth()->user()->id
        ]);


        if($courseDF){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PermissionTemplate  $permissionTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        $data = PermissionTemplate::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = PermissionTemplate::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
