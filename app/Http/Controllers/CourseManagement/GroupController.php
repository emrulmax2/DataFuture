<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GroupsRequests;
use App\Http\Requests\GroupsUpdateRequests;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;

class GroupController extends Controller
{
    public function index()
    {
        return view('pages.course-management.groups.index', [
            'title' => 'Terms & Modules - LCC Data Future Managment',
            'subtitle' => 'Groups',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Groups', 'href' => 'javascript:void(0);']
            ],
            'courses' => Course::all()
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

        $query = Group::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $count = $query->count();
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
                    'course' => (isset($list->course->name) && !empty($list->course->name) ? $list->course->name : ''),
                    'name' => $list->name,
                    'evening_and_weekend' => (isset($list->evening_and_weekend) && $list->evening_and_weekend == '1' ? 'Yes' : 'No'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(GroupsRequests $request){
        $data = Group::create([
            'course_id'=> $request->course_id,
            'name'=> $request->name,
            'evening_and_weekend'=> (isset($request->evening_and_weekend) && $request->evening_and_weekend > 0 ? $request->evening_and_weekend : 0),
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = Group::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(GroupsUpdateRequests $request, Group $group){
        $data = Group::where('id', $request->id)->update([
            'course_id'=> $request->course_id,
            'name'=> $request->name,
            'evening_and_weekend'=> (isset($request->evening_and_weekend) && $request->evening_and_weekend > 0 ? $request->evening_and_weekend : 0),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    public function destroy($id){
        $data = Group::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Group::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}