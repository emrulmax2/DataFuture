<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Semester;
use App\Models\CourseCreation;
use App\Http\Requests\CourseCreationsRequest;
use App\Models\AcademicYear;
use App\Models\CourseCreationVenue;
use App\Models\CourseQualification;
use App\Models\TermDeclaration;
use App\Models\Venue;

class CoursCreationController extends Controller
{
    public function index(){
        return view('pages.course-management.course-creation.index', [
            'title' => 'Course & Semester - London Churchill College',
            'subtitle' => 'Course Creations',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Course Creations', 'href' => 'javascript:void(0);']
            ],
            'courses' => Course::orderBy('name','asc')->get(),
            'semesters' => Semester::orderBy('id','desc')->get(),
            'qualifications' => CourseQualification::orderBy('name','asc')->get(),
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
                    
                    'venues' => isset($list->venues) && !empty($list->venues) ? $list->venues : '',
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
        $has_evening_and_weekend = (isset($request->has_evening_and_weekend) && $request->has_evening_and_weekend > 0 ? $request->has_evening_and_weekend : 0);
        $is_workplacement = (isset($request->is_workplacement) && $request->is_workplacement > 0 ? $request->is_workplacement : 0);
        $required_hours = ($is_workplacement == 1 && isset($request->required_hours) && $request->required_hours > 0 ? $request->required_hours : 0);
        $request->request->remove('is_workplacement');
        $request->request->remove('required_hours');
        $request->request->remove('has_evening_and_weekend');
        $venuList = $request->venue_id;
        $slcCode =$request->slc_code;

        $request->request->remove('venue_id');
        $request->request->remove('slc_code');

        $request->request->add(['has_evening_and_weekend' => $has_evening_and_weekend, 'is_workplacement' => $is_workplacement, 'required_hours' => $required_hours, 'created_by' => auth()->user()->id]);
        $courseCreation = CourseCreation::create($request->all());
        
        

        if($courseCreation)
        foreach($venuList as $key => $venueId):
            $courseCreationVenue = new CourseCreationVenue();
            $courseCreationVenue->course_creation_id =  $courseCreation->id;
            $courseCreationVenue->venue_id = $venueId;
            $courseCreationVenue->slc_code = $slcCode[$key];
            $courseCreationVenue->save();
        endforeach;

        return response()->json($courseCreation);
    }

    public function show($id) {

        return view('pages.course-management.course-creation.show', [
            'title' => 'Course & Semester - London Churchill College',
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
       
        $data = CourseCreation::with('venues')->where('id',$id)->get()->first();
        
        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CourseCreationsRequest $request){
        $CC_ID = $request->id;
        $has_evening_and_weekend = (isset($request->has_evening_and_weekend) && $request->has_evening_and_weekend > 0 ? $request->has_evening_and_weekend : 0);
        $is_workplacement = (isset($request->is_workplacement) && $request->is_workplacement > 0 ? $request->is_workplacement : 0);
        $required_hours = ($is_workplacement == 1 && isset($request->required_hours) && $request->required_hours > 0 ? $request->required_hours : 0);
        $courseDF = CourseCreation::where('id', $CC_ID)->update([
            'semester_id'=> $request->semester_id,
            'course_id'=> $request->course_id,
            'duration'=> $request->duration,
            'unit_length'=> $request->unit_length,
            'fees'=> (isset($request->fees) && $request->fees > 0 ? $request->fees : null),
            'reg_fees'=> (isset($request->reg_fees) && $request->reg_fees > 0 ? $request->reg_fees : null),
            'has_evening_and_weekend'=> $has_evening_and_weekend,
            'is_workplacement'=> $is_workplacement,
            'required_hours'=> $required_hours,
            'updated_by' => auth()->user()->id,
            'course_creation_qualification_id' => (isset($request->course_creation_qualification_id) && $request->course_creation_qualification_id > 0 ? $request->course_creation_qualification_id : null),
        ]);

        $venuList = $request->venue_id;
        $slcCode =$request->slc_code;
        if($courseDF)
        foreach($venuList as $key => $venueId):
            $courseCreationVenue = CourseCreationVenue::where('course_creation_id',$CC_ID)->where('venue_id',$venueId)->withTrashed()->get()->first();
            if($courseCreationVenue):
                if($courseCreationVenue->deleted_at!=NULL) {
                    $courseCreationVenue->restore();
                }
                $courseCreationVenue->slc_code = $slcCode[$key];
                
                $courseCreationVenue->save();
                
            else:
                $courseCreationVenue = new CourseCreationVenue();
                $courseCreationVenue->course_creation_id =  $CC_ID;
                $courseCreationVenue->venue_id = $venueId;
                $courseCreationVenue->slc_code = $slcCode[$key];
                $courseCreationVenue->save();
            endif;
        endforeach;

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

    public function venueDestroy($id,){
        $data = CourseCreationVenue::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = CourseCreation::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function getCourseListBySemester(Request $request) {
    
        $courseCreationList = CourseCreation::with('course')->whereIn('semester_id',$request->semesters)->get();

        foreach($courseCreationList as $courCreation) {
            $courseList[$courCreation->course->id]["id"] = $courCreation->course->id;
            $courseList[$courCreation->course->id]["name"] = $courCreation->course->name;
        }

        $courses = array_values($courseList);

        return response()->json($courses);
    }
}
