<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Semester;
use App\Models\CourseCreation;
use App\Http\Requests\CourseCreationsRequest;
use App\Models\AcademicYear;
use App\Models\CourseQualification;

class CoursCreationController extends Controller
{
    public function index(){
        return view('pages/course-creation/index', [
            'title' => 'Course Creations - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Course Creations', 'href' => 'javascript:void(0);']],
            'courses' => Course::all(),
            'semesters' => Semester::all(),
            'qualifications' => CourseQualification::all(),
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $course = (isset($request->course) && $request->course > 0 ? $request->course : '');
        $semester = (isset($request->semester) && $request->semester > 0 ? $request->semester : '');

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $query = CourseCreation::where('id', '!=', 0);
        if(!empty($queryStr)):
            $query->where('duration','LIKE','%'.$queryStr.'%');
            $query->orWhere('unit_length','LIKE','%'.$queryStr.'%');
            $query->orWhere('slc_code','LIKE','%'.$queryStr.'%');
        endif;
        if(!empty($course) && $course > 0 ):
            $query->where('course_id', $course);
        endif;
        if(!empty($semester) && $semester > 0 ):
            $query->where('semester_id', $semester);
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

        $query = CourseCreation::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('duration','LIKE','%'.$queryStr.'%');
            $query->orWhere('unit_length','LIKE','%'.$queryStr.'%');
            $query->orWhere('slc_code','LIKE','%'.$queryStr.'%');
        endif;
        if(!empty($course) && $course > 0 ):
            $query->where('course_id', $course);
        endif;
        if(!empty($semester) && $semester > 0 ):
            $query->where('semester_id', $semester);
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
                    'course' => $list->course->name,
                    'semester' => $list->semester->name,
                    'qualification' => isset($list->qualification->name) ? $list->qualification->name : '',
                    'duration' => $list->duration,
                    'unit_length' => $list->unit_length,
                    'slc_code'=> $list->slc_code,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(CourseCreationsRequest $request){
        $request->request->add(['created_by' => auth()->user()->id]);
        $courseCreation = CourseCreation::create($request->all());
        
        return response()->json($courseCreation);
    }

    public function show($id){
        return view('pages/course-creation/show', [
            'title' => 'Course Creations - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Course Creations', 'href' => route('course.creation')],
                ['label' => 'Details', 'href' => 'javascript:void(0);']
            ],
            'creation' => CourseCreation::find($id),
            'academic' => AcademicYear::all(),
        ]);
    }

    public function edit($id){
        $data = CourseCreation::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseCreationsRequest $request){
        $CC_ID = $request->id;
        $courseDF = CourseCreation::where('id', $CC_ID)->update([
            'semester_id'=> $request->semester_id,
            'course_id'=> $request->course_id,
            'duration'=> $request->duration,
            'unit_length'=> $request->unit_length,
            'slc_code'=> $request->slc_code,
            'updated_by' => auth()->user()->id
        ]);


        if($courseDF){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }
    }

    public function destroy($id){
        $data = CourseCreation::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = CourseCreation::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
