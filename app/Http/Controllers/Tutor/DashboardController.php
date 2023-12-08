<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceFeedStatus;
use App\Models\AttendanceInformation;
use App\Models\CourseModule;
use App\Models\ELearningActivitySetting;
use App\Models\Employee;
use App\Models\InstanceTerm;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlanContent;
use App\Models\PlanContentUpload;
use App\Models\PlanParticipant;
use App\Models\PlansDateList;
use App\Models\PlanTask;
use App\Models\PlanTaskUpload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    
    public function list(Request $request) {
        
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

        if(!empty($tutorId)): $Query->where('plan.tutor_id', $tutorId); endif;
        
        if(!empty($plan_date)): $Query->where('datelist.date', $plan_date); endif;

        $total_rows = $Query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $Query = $Query->orderByRaw(implode(',', $sorts))->skip($offset)
               ->take($limit)
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
        return response()->json(['last_page' => $last_page, 'data' => $data]);

    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $userData = User::find($id);
        $employee = Employee::where("user_id",$userData->id)->get()->first();

        $Query = DB::table('plans as plan')
        ->select('plan.*','academic_years.id as academic_year_id','academic_years.name as academic_year_name','terms.id as term_id','terms.name as term_name','terms.name as term_name','terms.term as term','course.name as course_name','module.module_name','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
        ->leftJoin('courses as course', 'plan.course_id', 'course.id')
        ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
        ->leftJoin('instance_terms as terms', 'module.instance_term_id', 'terms.id')
        ->leftJoin('course_creation_instances as course_relation_instances', 'terms.course_creation_instance_id','course_relation_instances.id')
        ->leftJoin('course_creations as course_relation', 'course_relation_instances.course_creation_id','course_relation.id')
        ->leftJoin('academic_years', 'course_relation_instances.academic_year_id','academic_years.id')
        ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
        ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
        ->leftJoin('groups as group', 'plan.group_id', 'group.id')
        ->leftJoin('users as user', 'plan.tutor_id', 'user.id')
        ->where('plan.tutor_id', $id);
         

        $Query = $Query
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            
            foreach($Query as $list):
               
                    $termData[$list->term_id] = (object) [ 
                        'id' =>$list->term_id,
                        'name' => $list->term_name,   
                        "total_modules" => count($Query),
                    ];

                    $data[$list->term_id][] = (object) [
                        'id' => $list->id,
                        'sl' => $i,
                        'course' => $list->course_name,
                        'module' => $list->module_name,
                        'group'=> $list->group_name,           
                    ];

                    $i++;
           
            endforeach;
        endif;
        
        return view('pages.tutor.dashboard.index', [
            'title' => 'Tutor Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "termList" =>$termData,
            "data" => $data,
            "date" => date("d-m-Y"),
        ]);
    }

    public function showNew(){
        return view('pages.tutor.dashboard.index-new', [
            'title' => 'Tutor Dashboard New - LCC Data Future Managment',
            'breadcrumbs' => []
        ]);
    }

    public function attendanceFeedShow(User $tutor,PlansDateList $plandate)
    {
        $attendanceInformation = AttendanceInformation::where("plans_date_list_id",$plandate->id)->get()->first();
        $employee = Employee::where("user_id",Auth::user()->id)->get()->first();
        
        if($attendanceInformation) {

            if($attendanceInformation->tutor_id != Auth::user()->id) {
                return redirect()->route('tutor-dashboard.show',Auth::user()->id);
            }

            $classStart = date("Y-m-d ".$attendanceInformation->start_time);
            
            if($attendanceInformation->end_time)
                $classEnd = date("Y-m-d ".$attendanceInformation->end_time);
            else
                $classEnd = date("Y-m-d h:i:s",strtotime(now()));

            if(!isset($attendanceInformation->end_time))
                $now = strtotime($classEnd) - strtotime($classStart);
            else 
                $now = strtotime($classEnd) - strtotime($attendanceInformation->start_time);

        }

        $Query = DB::table('plans_date_lists as datelist')
                    ->select('datelist.*','terms.name as term_name','terms.term as term','plan.id as plan_id','plan.tutor_id','plan.start_time','plan.end_time','plan.virtual_room','course.name as course_name','module.module_name','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
                    ->leftJoin('plans as plan', 'datelist.plan_id', 'plan.id')
                    ->leftJoin('courses as course', 'plan.course_id', 'course.id')
                    ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
                    ->leftJoin('instance_terms as terms', 'module.instance_term_id', 'terms.id')
                    ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
                    ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
                    ->leftJoin('groups as group', 'plan.group_id', 'group.id')
                    ->leftJoin('users as user', 'plan.tutor_id', 'user.id')
                    ->where('datelist.id', $plandate->id);

        $Query = $Query->get();  
        

        foreach($Query as $list):

            $plan = Plan::find($list->plan_id);

            $start_time = date("Y-m-d ".$list->start_time);
            $start_time = date('h:i A', strtotime($start_time));
            $end_time = date("Y-m-d ".$list->end_time);
            $end_time = date('h:i A', strtotime($end_time));
            
            
            $assignStudentList = Assign::where("plan_id",$list->plan_id)->get();

            $attendanceFeedStatus = AttendanceFeedStatus::all();

            $attendanceFeed = Attendance::where("plans_date_list_id",$plandate->id)->get();
            $attendance = [];
            $FeedCount = [];
            foreach($attendanceFeed  as $feed){
                $attendance[$feed->student_id] =$feed->attendance_feed_status_id;
                if(!isset($FeedStatus[$feed->attendance_feed_status_id])) {
                    $FeedCount[$feed->attendance_feed_status_id] = 0;
                }
                $FeedCount[$feed->attendance_feed_status_id] +=1;
            }
            
            $data = [
                'plan_id' => $list->plan_id,
                'id' => $list->id,
                'plan' => $plan,
                'term_name' => $list->term_name,
                'term' => $list->term,
                'date' => date("l jS \of F Y",strtotime($list->date)),
                'course' => $list->course_name,
                'module' => $list->module_name,
                'group'=> $list->group_name,
                'tutor'=> $list->username,
                'tutor_id' => $list->tutor_id,
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
                'assignStudentList' => $assignStudentList,  
                'AttendanceFeedStatus' => $attendanceFeedStatus,    
                "feedCount" => $FeedCount,
                'attendanceFeed' => $attendance, 
                'employee' => $employee,
                'attendanceInformation' => $attendanceInformation ,
                'classTakenTimeMin' => date('i', ($now)),      
                'classTakenTimeSeconds' => date('s', ($now)),   
                'classTakenTimeHour' => date('h', ($now)),     
            ];
        endforeach;
        return view('pages.tutor.attendance.create', [
            'title' => 'Attendance - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            'data' => $data,
        ]);
    }

    public function showCourseContent(Plan $plan) {

        $userData = User::find(Auth::user()->id);
        $employee = Employee::where("user_id",$userData->id)->get()->first();

        $tutor = Employee::where("user_id",$plan->tutor->id)->get()->first();
        
        $personalTutor = isset($plan->personalTutor->id) ? Employee::where("user_id",$plan->personalTutor->id)->get()->first() : "";
        
        $planTask = PlanTask::where("plan_id",$plan->id)->get();  
        
        $studentAssign = Assign::where('plan_id', $plan->id)->get();
        $studentListCount = $studentAssign->count();
        // $planParticipant = PlanParticipant::where('plan_id', $plan->id)->get();
        // $participantList = $planParticipant->count();
        $planDates = $planDateList = PlansDateList::where("plan_id",$plan->id)->get();
        $eLearningActivites = ELearningActivitySetting::all();
        $planDateWiseContent = [];
        foreach($planDates as $classDate) {
            $content = PlanContent::where("plans_date_list_id", $classDate->id)->get();
            foreach($content as $singleContent){
                $uploads = PlanContentUpload::where("plan_content_id",$singleContent->id)->get();
    
                $planDateWiseContent[$classDate->id] = (object) [
                    "task" => $content,
                    "taskUploads" => $uploads,
                ];
            }
            
        }
        $allPlanTasks = [];

            foreach($planTask as $task){
                $uploads = PlanTaskUpload::where("plan_task_id",$task->id)->get();

                $allPlanTasks[$task->id] = (object) [
                    "task"=> $task,
                    "taskUploads" => $uploads
                ]; 
            }
        
        $moduleCreations = ModuleCreation::find($plan->creations->id);
                    $data = (object) [
                        'id' => $plan->id,
                        'term_name' => $moduleCreations->term->name,
                        'course' => $plan->course->name,
                        
                        'classType' => $plan->creations->class_type,
                        'module' => $plan->creations->module_name,
                        'group'=> $plan->group->name,           
                        'room'=> $plan->room->name,           
                        'venue'=> $plan->venu->name,           
                        'tutor'=> ($tutor->full_name) ?? null,           
                        'personalTutor'=> ($personalTutor->full_name) ?? null,           
                    ];

                
       
        return view('pages.tutor.module.view', [
            'title' => 'Attendance - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            "plan" => $plan,
            "user" => $userData,
            "employee" => $employee,
            "data" => $data,
            'planTasks' => $allPlanTasks,
            'planDates' => $planDateWiseContent,
            'planDateList' => $planDateList,
            'eLearningActivites' => $eLearningActivites,
            'studentCount' => $studentListCount,
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
