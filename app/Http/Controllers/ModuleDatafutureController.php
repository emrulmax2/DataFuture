<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ModuleDatafutureRequest;
use App\Models\ModuleDatafuture;

class ModuleDatafutureController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $module = (isset($request->module) && $request->module > 0 ? $request->module : 0);

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $query = ModuleDatafuture::where('course_module_id', $module);
        if(!empty($queryStr)):
            $query->where('field_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_type','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_value','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_desc','LIKE','%'.$queryStr.'%');
        endif;
        $total_rows = $query->count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = ModuleDatafuture::where('course_module_id', $module)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('field_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_type','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_value','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_desc','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;
        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'field_name' => $list->field_name,
                    'field_type' => $list->field_type,
                    'field_value' => $list->field_value,
                    'field_desc' => $list->field_desc,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(ModuleDatafutureRequest $request){
        $request->merge([
            'created_by' => auth()->user()->id
        ]);
        
        $moduleDatafuture = ModuleDatafuture::create($request->all());
        
        return response()->json($moduleDatafuture);
    }

    public function edit($id){
        $data = ModuleDatafuture::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(ModuleDatafutureRequest $request){
        $dfID = $request->id;
        $course_module_id = $request->course_module_id;
        $courseDF = ModuleDatafuture::where('id', $dfID)->where('course_module_id', $course_module_id)->update([
            'field_name'=> $request->field_name,
            'field_type'=> $request->field_type,
            'field_value'=> $request->field_value,
            'field_desc'=> $request->field_desc,
            'updated_by' => auth()->user()->id
        ]);


        if($courseDF){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    public function destroy($id){
        $data = ModuleDatafuture::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ModuleDatafuture::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
