<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Semester;
use App\Models\CourseCreation;
use App\Http\Requests\CourseCreationsRequest;
use App\Models\AcademicYear;
use App\Models\CourseQualification;
use App\Models\TermDeclaration;
use App\Models\Venue;

class CoursCreationController extends Controller
{
    public function index(){
        return view('pages.course-management.course-creation.index', [
            'title' => 'Course & Semester - LCC Data Future Managment',
            'subtitle' => 'Course Creations',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Course Creations', 'href' => 'javascript:void(0);']
            ],
            'courses' => Course::all(),
            'semesters' => Semester::all(),
            'qualifications' => CourseQualification::all(),
            'venues' => Venue::all(),
        ]);
    }

    public function list(Request $request) {
        
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $course = (isset($request->course) && $request->course > 0 ? $request->course : '');
        $semester = (isset($request->semester) && $request->semester > 0 ? $request->semester : '');

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
                    'venue_id' => isset($list->venue->name) && !empty($list->venue->name) ? $list->venue->name : '',
                    'fees' => isset($list->fees) && !empty($list->fees) ? '£'.number_format($list->fees, 2) : '',
                    'reg_fees' => isset($list->reg_fees) && !empty($list->reg_fees) ? '£'.number_format($list->reg_fees, 2) : '',
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

    public function show($id) {

        return view('pages.course-management.course-creation.show', [
            'title' => 'Course & Semester - LCC Data Future Managment',
            'subtitle' => 'Course Creation Details',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Course Creations', 'href' => route('course.creation')],
                ['label' => 'Details', 'href' => 'javascript:void(0);']
            ],
            'creation' => CourseCreation::find($id),
            'academic' => AcademicYear::all(),
            'termDeclarations' => TermDeclaration::orderBy('id', 'DESC')->get(),
        ]);
    }

    public function edit($id) {

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
            'venue_id'=> (isset($request->venue_id) && $request->venue_id > 0 ? $request->venue_id : null),
            'fees'=> (isset($request->fees) && $request->fees > 0 ? $request->fees : null),
            'reg_fees'=> (isset($request->reg_fees) && $request->reg_fees > 0 ? $request->reg_fees : null),
            'is_workplacement'=> (isset($request->is_workplacement) && $request->is_workplacement > 0 ? $request->is_workplacement : 0),
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
