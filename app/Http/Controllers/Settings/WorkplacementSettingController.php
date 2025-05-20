<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkplacementSettingStoreRequest;
use App\Http\Requests\WorkplacementSettingTypeRequest;
use App\Http\Requests\WorkplacementSettingUpdateRequest;
use App\Models\WorkplacementSetting;
use App\Models\WorkplacementSettingType;
use Illuminate\Http\Request;

class WorkplacementSettingController extends Controller
{
    public function index(){
        return view('pages.settings.workplacement.wp-setting', [
            'title' => 'Workplacement Setting - London Churchill College',
            'subtitle' => 'Course Parameters',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Workplacement Setting', 'href' => 'javascript:void(0);']
            ],
            'workplacement_settings' => WorkplacementSetting::with(['workplacement_settng_types'])->get()
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $total_rows = WorkplacementSetting::count();
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

        $query = WorkplacementSetting::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
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
                    'name' => $list->name,
                    'type' => $list->type,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(WorkplacementSettingStoreRequest $request){
        $data = WorkplacementSetting::create([
            'name'=> (isset($request->name) && !empty($request->name) ? $request->name : null),
            'active' => 1,
            'created_by' => auth()->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Workplacement setting created successfully!',
        ], 200);
    }

    public function edit($id){
        $data = WorkplacementSetting::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(WorkplacementSettingUpdateRequest $request){
        $data = WorkplacementSetting::where('id', $request->id)->update([
            'name'=> (isset($request->name) && !empty($request->name) ? $request->name : null),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Workplacement setting updated successfully!',
        ], 200);
    }

    public function destroy($id){
        $data = WorkplacementSetting::find($id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Workplacement setting deleted successfully!',
        ]);
    }

    public function type_store(WorkplacementSettingTypeRequest $request){
        $data = WorkplacementSettingType::create([
            'workplacement_setting_id'=> (isset($request->workplacement_setting_id) && !empty($request->workplacement_setting_id) ? $request->workplacement_setting_id : null),
            'type'=> (isset($request->type) && !empty($request->type) ? $request->type : null),
            'active' => 1,
            'created_by' => auth()->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Workplacement setting type created successfully!',
        ], 200);
    }

    public function type_edit($id){
        $data = WorkplacementSettingType::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function type_update(WorkplacementSettingTypeRequest $request){
        $data = WorkplacementSettingType::where('id', $request->id)->update([
            'type'=> (isset($request->type) && !empty($request->type) ? $request->type : null),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Workplacement setting type updated successfully!',
        ], 200);
    }

    public function type_destroy($id){
        $data = WorkplacementSettingType::find($id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Workplacement setting type deleted successfully!',
        ]);
    }

    public function restore($id) {
        $data = WorkplacementSetting::where('id', $id)->withTrashed()->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Workplacement setting restored successfully!',
        ]);
    }
}
