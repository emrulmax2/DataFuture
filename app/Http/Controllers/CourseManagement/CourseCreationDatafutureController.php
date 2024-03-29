<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseCreation;
use App\Models\CourseCreationDatafuture;
use App\Http\Requests\CourseCreationDatafutureRequest;

class CourseCreationDatafutureController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $creationid = (isset($request->creationid) && $request->creationid > 0 ? $request->creationid : 0);

        $query = CourseCreationDatafuture::where('course_creation_id', $creationid);
        if(!empty($queryStr)):
            $query->where('field_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_type','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_value','LIKE','%'.$queryStr.'%');
            $query->orWhere('field_desc','LIKE','%'.$queryStr.'%');
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

        $query = CourseCreationDatafuture::where('course_creation_id', $creationid)->orderByRaw(implode(',', $sorts));
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


    public function store(CourseCreationDatafutureRequest $request){
        $request->merge([
            'created_by' => auth()->user()->id
        ]);
        
        $CourseCreationDatafuture = CourseCreationDatafuture::create($request->all());
        
        return response()->json($CourseCreationDatafuture);
    }

    public function edit($id){
        $data = CourseCreationDatafuture::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseCreationDatafutureRequest $request){
        $dfID = $request->id;
        $course_creation_id = $request->course_creation_id;
        $courseDF = CourseCreationDatafuture::where('id', $dfID)->where('course_creation_id', $course_creation_id)->update([
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
        $data = CourseCreationDatafuture::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = CourseCreationDatafuture::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
