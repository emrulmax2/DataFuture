<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRolePermissionRequest;
use App\Models\DocumentRoleAndPermission;
use Illuminate\Http\Request;

class DocumentRoleAndPermissionController extends Controller
{
    public function index(){
        return view('pages.settings.filemanager.role-and-permission', [
            'title' => 'Filemanager Settings - London Churchill College',
            'subtitle' => 'Role & Permission Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Filemanager Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Role & Permission', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function store(DocumentRolePermissionRequest $request){
        $bank = DocumentRoleAndPermission::create([
            'display_name'=> $request->display_name,
            'type'=> $request->type,
            'create'=> (isset($request->create) && $request->create > 0 ? $request->create : 0),
            'read'=> (isset($request->read) && $request->read > 0 ? $request->read : 0),
            'update'=> (isset($request->update) && $request->update > 0 ? $request->update : 0),
            'delete'=> (isset($request->delete) && $request->delete > 0 ? $request->delete : 0),
            'created_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Document Role & Permission successfully created'], 200);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = DocumentRoleAndPermission::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('display_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('type','LIKE','%'.$queryStr.'%');
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
                    'display_name' => $list->display_name,
                    'type' => $list->type,
                    'create' => (isset($list->create) && $list->create > 0 ? 1 : 0),
                    'read' => (isset($list->read) && $list->read > 0 ? 1 : 0),
                    'update' => (isset($list->update) && $list->update > 0 ? 1 : 0),
                    'delete' => (isset($list->delete) && $list->delete > 0 ? 1 : 0),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function edit(Request $request){
        $data = DocumentRoleAndPermission::find($request->row_id);

        return response()->json($data);
    }

    public function update(DocumentRolePermissionRequest $request){
        $bank = DocumentRoleAndPermission::where('id', $request->id)->update([
            'display_name'=> $request->display_name,
            'type'=> $request->type,
            'create'=> (isset($request->create) && $request->create > 0 ? $request->create : 0),
            'read'=> (isset($request->read) && $request->read > 0 ? $request->read : 0),
            'update'=> (isset($request->update) && $request->update > 0 ? $request->update : 0),
            'delete'=> (isset($request->delete) && $request->delete > 0 ? $request->delete : 0),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Document Role & Permission successfully updated'], 200);
    }

    public function destroy($id){
        $data = DocumentRoleAndPermission::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = DocumentRoleAndPermission::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
