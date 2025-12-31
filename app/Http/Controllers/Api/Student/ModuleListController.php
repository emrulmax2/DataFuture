<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModuleListForDashboardResource;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Room;
use App\Models\Student;
use App\Models\StudentUser;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;

class ModuleListController extends Controller
{
    public function index(Request $request) 
    {
        //api call parameter for selected student id
        $selectedStudentId = $request->query('selected_student_id', null);
        $theUser = $request->user();
        if (!$theUser) {
            return response()->json(['success' => false, 'error' => 'No authenticated user found.'], 401);
        }


        // Generate a unique cache key for the user
        $cacheKey = 'modulelist_data_user_' . $theUser->id;

        // Check if the data is already cached
        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($theUser, $selectedStudentId) {

            $moduleListData = $this->moduleList($theUser->id, $selectedStudentId);

            return new ModuleListForDashboardResource($moduleListData);
        
        });
        return response()->json([
            'success' => true,
            'message' => 'Module list data retrieved successfully.',
            'data' => $data,
        ], 200);
    }

    protected function moduleList($student_user_id, $selectedStudentId = null) {

        $userData = StudentUser::find($student_user_id);
        if ($selectedStudentId) {
            $student = $studentData = Student::with('crel', 'course')->find($selectedStudentId);
        } else {
            $student = $studentData = Student::with('crel', 'course')->where("student_user_id", $userData->id)->orderBy('id', 'DESC')->first();
        }

        $Query = DB::table('plans as plan')
        ->select('plan.*','academic_years.id as academic_year_id','academic_years.name as academic_year_name','terms.id as term_id','term_declarations.name as term_name','terms.term as term','course.name as course_name','module.module_name','module.class_type as module_class_type','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
        ->leftJoin('courses as course', 'plan.course_id', 'course.id')
        ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
        ->leftJoin('instance_terms as terms', 'module.instance_term_id', 'terms.id')
        ->leftJoin('term_declarations', 'term_declarations.id', 'terms.term_declaration_id')
        ->leftJoin('course_creation_instances as course_relation_instances', 'terms.course_creation_instance_id','course_relation_instances.id')
        ->leftJoin('course_creations as course_relation', 'course_relation_instances.course_creation_id','course_relation.id')
        ->leftJoin('academic_years', 'course_relation_instances.academic_year_id','academic_years.id')
        ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
        ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
        ->leftJoin('groups as group', 'plan.group_id', 'group.id')
        ->leftJoin('users as user', 'plan.tutor_id', 'user.id')
        ->leftJoin('assigns', 'assigns.plan_id', 'plan.id')
        ->where('assigns.student_id', $studentData->id);
        //->where('plan.parent_id', 0);

        

        $Query = $Query
                 ->orderBy('plan.term_declaration_id','DESC')
                 ->get();

        $data = array();
        $currentTerm = 0;
        if(!empty($Query)):
            $i = 1;
            
            foreach($Query as $list):
                    
                    if($currentTerm==0)
                        $currentTerm = $list->term_id;
                        //PlansDateList::
                    $termData[$list->term_id] = (object) [ 
                        'id' =>$list->term_id,
                        'name' => $list->term_name,   
                        "total_modules" => !isset($termData[$list->term_id]) ? 1 : $termData[$list->term_id]->total_modules,
                        
                    ];
                    $tutor = User::with('employee')->where("id",$list->tutor_id)->get()->first();
                    $pTutor = User::with('employee')->where("id",$list->personal_tutor_id)->get()->first();

                    $getClassDatesForStudent =  PlansDateList::where('plan_id',$list->id)->get();
                    
                    $start_time = date("Y-m-d ".$list->start_time);
                    $start_time = date('h:i A', strtotime($start_time));
                    
                    $end_time = date("Y-m-d ".$list->end_time);
                    $end_time = date('h:i A', strtotime($end_time));

                    $tutorial = Plan::where('parent_id', $list->id)->where('class_type', 'Tutorial')->get()->first();
                    $has_tutorial = (isset($tutorial->id) && $tutorial->id > 0 ? true : false); 
                    $data[$list->term_id][] = (object) [
                        'id' => $list->id,
                        'sl' => $i,
                        'parent_id' => $list->parent_id,
                        'course' => $list->course_name,
                        'tutor_photo' => isset($tutor->employee->photo_url) ? $tutor->employee->photo_url : "",
                        'personal_tutor_photo' => isset($pTutor->employee->photo_url) ? $pTutor->employee->photo_url : "",
                        'classType' => ($list->class_type!="")  ? $list->class_type : $list->module_class_type,
                        'module' => $list->module_name,
                        'group'=> $list->group_name,
                        'venue' =>Venue::find($list->venue_id),           
                        'room' =>Room::find($list->rooms_id),   
                        'virtual_room' =>$list->virtual_room,   
                        'plan_dates' => $getClassDatesForStudent,
                        'start_time' =>$start_time,           
                        'end_time' =>$end_time, 
                        'has_tutorial' => $has_tutorial,
                        'p_tutor_photo' => isset($tutorial->personalTutor->employee->photo_url) ? $tutorial->personalTutor->employee->photo_url : ""              
                    ];
                    
                    if(isset($termData[$list->term_id]))  
                        $termData[$list->term_id]->total_modules = count($data[$list->term_id]);
                    else 
                        $termData[$list->term_id] = 1;
                    $i++;
        
            endforeach;
        endif;

        usort($data[$currentTerm], fn($a, $b) => strcmp($a->module, $b->module));

        return ["termList" =>$termData,
            "data" => $data,
            "currenTerm" => $currentTerm ];
    }
}
