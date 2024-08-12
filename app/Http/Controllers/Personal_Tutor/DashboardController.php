<?php

namespace App\Http\Controllers\Personal_Tutor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceInformation;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{


    public function index($id){

        $userData = User::find($id);
        $employee = Employee::where("user_id",$userData->id)->get()->first();

        $Query = DB::table('plans as plan')
        ->select('plan.*','academic_years.id as academic_year_id','academic_years.name as academic_year_name','terms.id as term_id','term_declarations.name as term_name','terms.term as term','course.name as course_name','module.module_name','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
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
        ->where('plan.personal_tutor_id', $id);

        

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

                    $termData[$list->term_id] = (object) [ 
                        'id' =>$list->term_id,
                        'name' => $list->term_name,   
                        "total_modules" => !isset($termData[$list->term_id]) ? 1 : $termData[$list->term_id]->total_modules,
                        
                    ];

                    $data[$list->term_id][] = (object) [
                        'id' => $list->id,
                        'sl' => $i,
                        'course' => $list->course_name,
                        'module' => $list->module_name,
                        'group'=> $list->group_name,           
                    ];

                    if(isset($termData[$list->term_id]))  
                        $termData[$list->term_id]->total_modules = count($data[$list->term_id]);
                    else 
                        $termData[$list->term_id] = 1;
                    $i++;
        
            endforeach;
        endif;
        $request = new Request();

        $request->merge([
            'plan_date' => "13-09-2023",
            'id' =>$id,
        ]);
        $todaysList = $this->latestList($request);
        $returnData = json_decode($todaysList->getContent(),true);
        function cmp($a, $b)
        {
            $end_timeB = date("Y-m-d ".$b['start_time']);
            $end_timeA = date("Y-m-d ".$a['start_time']);

            return strtotime($end_timeB)> strtotime($end_timeA);
            
        }
        
        usort($returnData, "cmp");
        return  view('pages.personal-tutor.dashboard.index', [
            'title' => 'Personal Tutor Dashboard - London Churchill College',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "termList" =>$termData,
            "data" => $data,
            "date" => date("d-m-Y"),
            "currenTerm" => $currentTerm,
            "todaysClassList" => $returnData["data"],
        ]);
    }
    public function latestList(Request $request) {
        
        $tutorId = isset($request->id) && !empty($request->id) ? $request->id : '';
        $plan_date = isset($request->plan_date) && !empty($request->plan_date) ? $request->plan_date : '';
        $plan_date = date('Y-m-d', strtotime($plan_date));

        $Query = DB::table('plans_date_lists as datelist')
                    ->select('datelist.*','plan.id as plan_id','plan.start_time','plan.tutor_id','plan.end_time','plan.virtual_room','course.name as course_name','module.module_name','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
                    ->leftJoin('plans as plan', 'datelist.plan_id', 'plan.id')
                    ->leftJoin('courses as course', 'plan.course_id', 'course.id')
                    ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
                    ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
                    ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
                    ->leftJoin('groups as group', 'plan.group_id', 'group.id')
                    ->leftJoin('users as user', 'plan.tutor_id', 'user.id');

        $Query->where('plan.tutor_id', $tutorId);
        
        $Query->where('datelist.date', $plan_date);



        $Query = $Query->orderBy('datelist.date', 'DESC')
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            
            foreach($Query as $list):
                
                $attendanceInformationFinder = AttendanceInformation::where("plans_date_list_id",$list->id)->get()->first();
                $foundAttendances = Attendance::where("plans_date_list_id",$list->id)->get()->first();
                $start_time = date("Y-m-d ".$list->start_time);
                $start_time = date('h:i A', strtotime($start_time));
                
                $end_time = date("Y-m-d ".$list->end_time);
                $end_time = date('h:i A', strtotime($end_time));
                
                $data[] = [
                    'id' => $list->id,
                    'plan_id' => $list->plan_id,
                    'sl' => $i,
                    'course' => $list->course_name,
                    'module' => $list->module_name,
                    'group'=> $list->group_name,
                    'tutor'=> $list->username,
                    
                    'tutor_id'=>$list->tutor_id,
                    "start_time" => $start_time,
                    "end_time" => $end_time,
                    'venue' => $list->venue_name,
                    "room" => $list->room_name,
                    'virtual_room'=> $list->virtual_room,
                    'lecture_type'=> "",
                    'captured_by'=> "",
                    'captured_at'=> "",
                    'join_request'=> "",
                    'status'=> "",     
                    "attendance_information" => ($attendanceInformationFinder) ?? null,    
                    "foundAttendances"  => ($foundAttendances) ?? null,           
                ];
                $i++;
            endforeach;
        endif;
        
        return response()->json([ 'data' => $data]);

    }

}
