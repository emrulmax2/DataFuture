<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTutorMonitorTeamRequests;
use App\Http\Requests\UpdateTutorMonitorTeamRequests;
use App\Models\TutorMonitorTeam;
use Illuminate\Http\Request;

class TutorMonitorController extends Controller
{
    public function list(Request $request) {

        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $course = (isset($request->course) && $request->course > 0 ? $request->course : 0);

        $query = TutorMonitorTeam::where('course_id', $course);
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

        $query = TutorMonitorTeam::where('course_id', $course)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->orWhere('field_value','LIKE','%'.$queryStr.'%');
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
                    'name' => (isset($list->name) ? $list->name : ''),
                    'email' => (isset($list->email) ? $list->email : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(StoreTutorMonitorTeamRequests $request){
        $request->merge([
            'created_by' => auth()->user()->id
        ]);
        
        $courseDF = TutorMonitorTeam::create($request->all());
        
        return response()->json($courseDF);
    }

    public function edit($id){
        $data = TutorMonitorTeam::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(UpdateTutorMonitorTeamRequests $request){
        
        $course_id = $request->course_id;
        $courseDF = TutorMonitorTeam::where('id',  $request->id)->where('course_id', $course_id)->update([
            'name'=> $request->name,
            'email'=> $request->email,
            'updated_by' => auth()->user()->id
        ]);


        if($courseDF){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    public function destroy($id){
        $data = TutorMonitorTeam::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = TutorMonitorTeam::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

}
