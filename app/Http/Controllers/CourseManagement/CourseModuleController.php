<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\ModuleLevels;
use App\Models\CourseModule;
use App\Http\Requests\CourseModuleRequests;
use App\Models\AssessmentType;
use App\Models\Grade;

use App\Exports\ArrayCollectionExport;
use Maatwebsite\Excel\Facades\Excel;

class CourseModuleController extends Controller
{

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $course = (isset($request->course) && $request->course > 0 ? $request->course : 0);

        $query = CourseModule::where('course_id', $course);
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('code','LIKE','%'.$queryStr.'%');
            $query->orWhere('status','LIKE','%'.$queryStr.'%');
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
                    'class_type' => (isset($list->class_type) && !empty($list->class_type) ? $list->class_type : ''),
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
        $assementTypes = AssessmentType::all();
        $gradesList = Grade::all();
        return view('pages.course-management.modules.show', [
            'title' => 'Course & Semester - London Churchill College',
            'subtitle' => 'Courses Module Details',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Courses', 'href' => route('courses')],
                ['label' => 'Course Details', 'href' => route('courses.show', $modules->course_id)],
                ['label' => 'Module Details', 'href' => 'javascript:void(0);']
            ],
            'module' => $modules,
            'assementTypes' =>$assementTypes,
            'gradesList' =>$gradesList
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
            'class_type'=> (isset($request->class_type) && !empty($request->class_type) ? $request->class_type : null),
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

    public function exportCourseModule($course_id){
        $course = Course::find($course_id);
        $courseModules = CourseModule::where('course_id', $course_id)->orderBy('name', 'ASC')->get();

        $theCollection = [];
        $theCollection[1][0] = 'ID';
        $theCollection[1][1] = 'Name';
        $theCollection[1][2] = 'Level';
        $theCollection[1][3] = 'Code';
        $theCollection[1][4] = 'Credit Value';
        $theCollection[1][5] = 'Unit Value';
        $theCollection[1][6] = 'Status';
        $theCollection[1][7] = 'Class Type';
        $theCollection[1][8] = 'Active';

        $row = 2;
        if(!empty($courseModules) && $courseModules->count() > 0):
            foreach($courseModules as $cmod):
                $theCollection[$row][0] = $cmod->ID;
                $theCollection[$row][1] = $cmod->name;
                $theCollection[$row][2] = (isset($cmod->level->name) && !empty($cmod->level->name) ? $cmod->level->name : '');
                $theCollection[$row][3] = (isset($cmod->code) && !empty($cmod->code) ? $cmod->code : '');
                $theCollection[$row][4] = (isset($cmod->credit_value) && !empty($cmod->credit_value) ? $cmod->credit_value : '');
                $theCollection[$row][5] = (isset($cmod->unit_value) && !empty($cmod->unit_value) ? $cmod->unit_value : '');
                $theCollection[$row][6] = (isset($cmod->status) && !empty($cmod->status) ? ucfirst($cmod->status) : '');
                $theCollection[$row][7] = (isset($cmod->class_type) && !empty($cmod->class_type) ? ucfirst($cmod->class_type) : '');
                $theCollection[$row][8] = (isset($cmod->active) && !empty($cmod->active) && $cmod->active == 1 ? 'Active' : 'Inactive');

                $row += 1;
            endforeach;
        endif;

        return Excel::download(new ArrayCollectionExport($theCollection), str_replace(' ', '_', $course).'_Modules.xlsx');
    }

}
