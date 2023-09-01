<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CourseRequests;
use App\Http\Requests\CourseUpdateRequests;
use App\Models\Course;
use App\Models\User;
use App\Models\AwardingBody;
use App\Models\ModuleLevel;
use App\Models\SourceTuitionFee;
use Illuminate\Support\Facades\Cache;

class CourseController extends Controller
{
    public function index()
    {
        return view('pages/courses/index', [
            'title' => 'Courses - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Courses', 'href' => 'javascript:void(0);']],
            'bodies' => AwardingBody::all(),
            'fees' => SourceTuitionFee::all(),
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $total_rows = $count = Course::count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = Course::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('degree_offered','LIKE','%'.$queryStr.'%');
            $query->orWhere('pre_qualification','LIKE','%'.$queryStr.'%');
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
                    'bodies' => $list->body->name,
                    'fees' => $list->fee->name,
                    'degree_offered' => $list->degree_offered,
                    'pre_qualification'=> $list->pre_qualification,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(CourseRequests $request){
        $request->request->add(['created_by' => auth()->user()->id]);
        $course = Course::create($request->all());

        $courseAll = Course::all()->sortByAsc("name");
        Cache::forever('courses', $courseAll);

        return response()->json($course);
    }

    public function edit($id){
        $data = Course::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseUpdateRequests $request, Course $dataId){
        $coursesId = $request->id;
        $data = Course::where('id', $coursesId)->update([
            'name'=> $request->name,
            'degree_offered'=> $request->degree_offered,
            'pre_qualification'=> $request->pre_qualification,
            'awarding_body_id'=> $request->awarding_body_id,
            'source_tuition_fee_id'=> $request->source_tuition_fee_id,
            'updated_by' => auth()->user()->id
        ]);

        $courseAll = Course::all()->sortByAsc("name");
        Cache::forever('courses', $courseAll);

        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    public function show($id)
    {
        return view('pages/courses/show', [
            'title' => 'Courses - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Courses', 'href' => route('courses')],
                ['label' => 'Course Details', 'href' => 'javascript:void(0);']
            ],
            'course' => Course::find($id),
            'levels' => ModuleLevel::all()
        ]);
    }

    public function destroy($id){
        $data = Course::find($id)->delete();

        $courseAll = Course::all()->sortByAsc("name");
        Cache::forever('courses', $courseAll);

        return response()->json($data);
    }

    public function restore($id) {
        $data = Course::where('id', $id)->withTrashed()->restore();

        $courseAll = Course::all()->sortByAsc("name");
        Cache::forever('courses', $courseAll);

        response()->json($data);
    }
}
