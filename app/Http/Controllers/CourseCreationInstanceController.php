<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseCreationInstance;
use App\Http\Requests\CourseCreationInstanceRequest;
use App\Models\CourseCreation;

class CourseCreationInstanceController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $creationid = (isset($request->creationid) && $request->creationid > 0 ? $request->creationid : 0);

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $query = CourseCreationInstance::where('course_creation_id', $creationid);
        if(!empty($queryStr)):
            $query->where('start_date','LIKE','%'.$queryStr.'%');
            $query->orWhere('end_date','LIKE','%'.$queryStr.'%');
            $query->orWhere('total_teaching_week','LIKE','%'.$queryStr.'%');
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

        $query = CourseCreationInstance::where('course_creation_id', $creationid)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('start_date','LIKE','%'.$queryStr.'%');
            $query->orWhere('end_date','LIKE','%'.$queryStr.'%');
            $query->orWhere('total_teaching_week','LIKE','%'.$queryStr.'%');
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
                    'academic_year' => $list->year->name,
                    'start_date' => $list->start_date,
                    'end_date' => $list->end_date,
                    'total_teaching_week' => $list->total_teaching_week,
                    'deleted_at' => $list->deleted_at,
                    'has_terms' => $list->terms->count()
                ];
                
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(CourseCreationInstanceRequest $request){
        $request->request->add(['created_by' => auth()->user()->id]);
        $courseCreationInst = CourseCreationInstance::create($request->all());
        
        return response()->json($courseCreationInst);
    }

    public function edit($id){
        $data = CourseCreationInstance::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseCreationInstanceRequest $request){
        $CCI_ID = $request->id;
        $course_creation_id = $request->course_creation_id;
        $request->request->add(['updated_by' => auth()->user()->id]);

        $courseCreationInstance = CourseCreationInstance::find($CCI_ID);
        $courseCreationInstance->fill($request->all());
        $courseCreationInstance->save();
        
        if($courseCreationInstance->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    public function destroy($id){
        $data = CourseCreationInstance::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = CourseCreationInstance::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
