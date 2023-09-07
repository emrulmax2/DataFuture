<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\ModuleLevels;
use App\Models\CourseModule;
use App\Http\Requests\CourseModuleRequests;

class CourseModuleController extends Controller
{

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $course = (isset($request->course) && $request->course > 0 ? $request->course : 0);

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $query = CourseModule::where('course_id', $course);
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('code','LIKE','%'.$queryStr.'%');
            $query->orWhere('status','LIKE','%'.$queryStr.'%');
        endif;
        $total_rows = $query->count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = CourseModule::where('course_id', $course)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('code','LIKE','%'.$queryStr.'%');
            $query->orWhere('status','LIKE','%'.$queryStr.'%');
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
                    'code' => $list->code,
                    'status' => ucfirst($list->status),
                    'credit_value' => $list->credit_value,
                    'unit_value' => $list->unit_value,
                    'active' => $list->active,
                    'level' => (isset($list->level->name) && !empty($list->level->name) ? $list->level->name : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(CourseModuleRequests $request){
        $request->merge([
            'active' => (isset($request->active) && !empty($request->active) ? $request->active : 0),
            'created_by' => auth()->user()->id
        ]);
        
        $courseModule = CourseModule::create($request->all());
        
        return response()->json($courseModule);
    }

    public function show($id){
        $modules = CourseModule::find($id);
        return view('pages/modules/show', [
            'title' => 'Courses Module - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Course Modules', 'href' => route('course.module.show', $modules->course_id)],
                ['label' => 'Module Details', 'href' => 'javascript:void(0);']
            ],
            'module' => $modules
        ]);
    }

    public function updateStatus(Request $request){
        $courseModule = CourseModule::where('id', $request->id)->update([
            'active' => $request->status
        ]);

        return response()->json($courseModule);
    }

    public function edit($id){
        $data = CourseModule::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseModuleRequests $request){
        $courseModuleID = $request->id;
        $courseModule = CourseModule::where('id', $courseModuleID)->update([
            'name'=> $request->name,
            'code'=> $request->code,
            'status'=> $request->status,
            'credit_value'=> $request->credit_value,
            'unit_value'=> $request->unit_value,
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json($courseModule);


        if($courseModule->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    public function destroy($id){
        $data = CourseModule::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = CourseModule::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

}
