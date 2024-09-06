<?php

namespace App\Http\Controllers\Attendance;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceDateSearchRequest;
use App\Http\Requests\AttendanceStoreRequest;
use App\Models\Assign;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceFeedStatus;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Traits\SendSmsTrait;

class AttendanceController extends Controller
{
    use SendSmsTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.attendance.index', [
            'title' => 'Attendance - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            'plandatelist' => PlansDateList::all(),
        ]);
    }

    public function list(AttendanceDateSearchRequest $request) {
        
        $plan_date = isset($request->plan_date) && !empty($request->plan_date) ? $request->plan_date : '';
        $plan_date = date('Y-m-d', strtotime($plan_date));
        $Query = DB::table('plans_date_lists as datelist')
                    ->select('datelist.*','plan.id as plan_id','plan.start_time','plan.end_time','plan.virtual_room','course.name as course_name','module.module_name','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
                    ->leftJoin('plans as plan', 'datelist.plan_id', 'plan.id')
                    ->leftJoin('courses as course', 'plan.course_id', 'course.id')
                    ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
                    ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
                    ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
                    ->leftJoin('groups as group', 'plan.group_id', 'group.id')
                    ->leftJoin('users as user', 'plan.tutor_id', 'user.id');
        
        //$Query = Student::orderByRaw(implode(',', $sorts));
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
                    "start_time" => $start_time,
                    "end_time" => $end_time,
                    'venue' => $list->venue_name,
                    "room" => $list->room_name,
                    'virtual_room'=> $list->virtual_room,
                    'lecture_type'=> "",
                    'captured_by'=> "",
                    'captured_at'=> "",
                    'join_request'=> "",
                    'status'=> ""                   
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(PlansDateList $data)
    {
        
        $Query = DB::table('plans_date_lists as datelist')
                    ->select('datelist.*','terms.name as term_name','terms.term as term','plan.id as plan_id','plan.start_time','plan.end_time','plan.virtual_room','course.name as course_name','module.module_name','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username")
                    ->leftJoin('plans as plan', 'datelist.plan_id', 'plan.id')
                    ->leftJoin('courses as course', 'plan.course_id', 'course.id')
                    ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
                    ->leftJoin('instance_terms as terms', 'module.instance_term_id', 'terms.id')
                    ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
                    ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
                    ->leftJoin('groups as group', 'plan.group_id', 'group.id')
                    ->leftJoin('users as user', 'plan.tutor_id', 'user.id')
                    ->where('datelist.id', $data->id);

        $Query = $Query->get();  
        

        foreach($Query as $list):

            $plan = Plan::find($list->plan_id);

            $start_time = date("Y-m-d ".$list->start_time);
            $start_time = date('h:i A', strtotime($start_time));
                
            $end_time = date("Y-m-d ".$list->end_time);
            $end_time = date('h:i A', strtotime($end_time));
            $assignStudentList = Assign::where("plan_id",$list->plan_id)->get();
            $attendanceFeedStatus = AttendanceFeedStatus::all();
            
            $data = [
                'plan_id' => $list->plan_id,
                'id' => $list->id,
                'plan' => $plan,
                'term_name' => $list->term_name,
                'term' => $list->term,
                'date' => date("d-m-Y",strtotime($list->date)),
                'course' => $list->course_name,
                'module' => $list->module_name,
                'group'=> $list->group_name,
                'tutor'=> $list->username,
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
            ];
        endforeach;
        return view('pages.attendance.create', [
            'title' => 'Attendance - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            'data' => $data,
            'dateListId' => PlansDateList::find($list->id)
        ]);
    }

    public function generatePDF(PlansDateList $data)
    {
        $Query = DB::table('plans_date_lists as datelist')
                    ->select('datelist.*','terms.name as term_name','terms.term as term','plan.id as plan_id','plan.start_time','plan.end_time','plan.virtual_room','course.name as course_name','module.module_name','venue.name as venue_name','room.name as room_name','group.name as group_name',"user.name as username",'course_creation.semester_id as semester_id' )
                    ->leftJoin('plans as plan', 'datelist.plan_id', 'plan.id')
                    ->leftJoin('courses as course', 'plan.course_id', 'course.id')
                    ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
                    ->leftJoin('instance_terms as terms', 'module.instance_term_id', 'terms.id')        
                    ->leftJoin('course_creation_instances as course_instance', 'terms.course_creation_instance_id', 'course_instance.id')
                    ->leftJoin('course_creations as course_creation', 'course_instance.course_creation_id', 'course_creation.id')
                    ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
                    ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
                    ->leftJoin('groups as group', 'plan.group_id', 'group.id')
                    ->leftJoin('users as user', 'plan.tutor_id', 'user.id')
                    ->where('datelist.id', $data->id);

        $Query = $Query->get();  
        

        foreach($Query as $list):
            $dateListId = PlansDateList::find($list->id);
            $plan = Plan::find($list->plan_id);

            $start_time = date("Y-m-d ".$list->start_time);
            $start_time = date('h:i A', strtotime($start_time));
                
            $end_time = date("Y-m-d ".$list->end_time);
            $end_time = date('h:i A', strtotime($end_time));
            $assignStudentList = Assign::where("plan_id",$list->plan_id)->get();
            $attendanceFeedStatus = AttendanceFeedStatus::all();

            $attendance = Attendance::where("plans_date_list_id",$data->id)->get();
            $attendanceFeedByAttendance = AttendanceFeedStatus::find($attendance[0]['id']);

            $semester = Semester::find($list->semester_id);
            //dd($semster);
            $data = [
                'plan_id' => $list->plan_id,
                'id' => $list->id,
                'plan' => $plan,
                'term_name' => $list->term_name,
                'term' => $list->term,
                'date' => date("d-m-Y",strtotime($list->date)),
                'course' => $list->course_name,
                'module' => $list->module_name,
                'group'=> $list->group_name,
                'tutor'=> $list->username,
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
            ];
        endforeach;
        $pdf = PDF::loadView(
            'pages.attendance.pdfprint', 
            compact('data','dateListId','attendanceFeedByAttendance','semester')
        )->setPaper('a4', 'portrait')
        ->setWarnings(false);
        $pdf->set_option('defaultMediaType', 'all');
        $pdf->set_option('isFontSubsettingEnabled', true);
        return $pdf->download("Feed Attendance List.pdf");
        //return view('pages.attendance.pdfprint', compact('data','dateListId','attendanceFeedByAttendance','semester'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AttendanceStoreRequest $request)
    {
        $insertCount = 0;
        $plan_id = (isset($request->plan_id) && $request->plan_id > 0 ? $request->plan_id : 0);
        $plan = Plan::find($plan_id);
        $class_time = (isset($plan->start_time) && !empty($plan->start_time) ? date('h:i A', strtotime($plan->start_time)) : '');
        $tutor_id = (isset($request->tutor_id) && $request->tutor_id > 0 ? $request->tutor_id : 0);
        $plan_date_list_id = (isset($request->plan_date_list_id) && $request->plan_date_list_id > 0 ? $request->plan_date_list_id : 0);
        $planDateList = PlansDateList::find($plan_date_list_id);
        $attendance_date = (isset($planDateList->date) && !empty($planDateList->date) ? date('Y-m-d', strtotime($planDateList->date)) : date('Y-m-d'));
        $attendances = (isset($request->attendances) && !empty($request->attendances) ? $request->attendances : []);
        if(!empty($attendances)):
            foreach($attendances as $planDateId => $atns):
                foreach($atns as $atn):
                    $attendance_feed_status_id = (isset($atn['attendance_feed_status_id']) && $atn['attendance_feed_status_id'] > 0 ? $atn['attendance_feed_status_id'] : 4);
                    $student_id = (isset($atn['student_id']) && $atn['student_id'] > 0 ? $atn['student_id'] : 0);
                    $data = [
                        'plans_date_list_id' => $planDateId,
                        'attendance_date' => $attendance_date,
                        'attendance_captured_at' => date('Y-m-d'),
                        'class_plan_id' => $plan_id,
                        'student_id' => ($student_id > 0 ? $student_id : null),
                        'attendance_feed_status_id' => $attendance_feed_status_id,
                        'sms_notification' => ($attendance_feed_status_id == 4 ? 1 : 0),
                        'notofication_date' => ($attendance_feed_status_id == 4 ? date('Y-m-d') : null),
                        'notofied_by' => $tutor_id
                    ];

                    $smsStatus = false;
                    $existAttendance = Attendance::where('plans_date_list_id', $planDateId)->where('class_plan_id', $plan_id)->where('student_id', $student_id)->get()->first();
                    if(isset($existAttendance->id) && $existAttendance->id > 0):
                        $smsStatus = ($existAttendance->attendance_feed_status_id != 4 &&  $data['attendance_feed_status_id'] == 4 ? true : false);
                        $data['updated_by'] = Auth::user()->id;
                        Attendance::where('id', $existAttendance->id)->update($data);
                    else:
                        $smsStatus = ($attendance_feed_status_id == 4 ? true : false);
                        $data['created_by'] = Auth::user()->id;
                        Attendance::create($data);
                    endif;

                    if($smsStatus):
                        $student = Student::find($student_id);
                        $message = 'Dear '.$student->full_name.'. You have missed class on '.date("d-m-Y",strtotime($attendance_date)).'. Module name: '.(isset($plan->creations->module_name) && !empty($plan->creations->module_name) ? $plan->creations->module_name : 'Undefined Module').', Group:'.(isset($plan->group->name) ? $plan->group->name : 'Undefined Group').', Time: '.$class_time;
                        //$sms = $this->sendSms($student->contact->mobile, $message);
                    endif;

                    $insertCount += 1;
                endforeach;
            endforeach;
        endif;

        if($insertCount):
            PlansDateList::where('id', $plan_date_list_id)->update(['feed_given' => 1]);
            return response()->json(["data success"], 200);
        else:
            return response()->json(["data could not save.", 422]);
        endif;
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
    public function update(Request $request, Attendance $attendance)
    {     
            $data = [
                'attendance_feed_status_id' => $request->attendance_feed_status_id,
                'updated_by' => Auth::user()->id,
            ];

            $attendance->fill($data);
            $attendance->save();
     
        if($attendance->id) 
            return response()->json(["data updated successfully"]);
        else 
            return response()->json(["data could not update.",422]);
    }

    public function updateAll(Request $request)
    {
    
        foreach ( $request->get('id') as  $value) {
            if(isset($request->attendance_feed[$value])) {
                $attendance = Attendance::find($value); 
                
                $data = [
                    'attendance_feed_status_id' => $request->attendance_feed[$value],
                    'updated_by' => Auth::user()->id,
                ];

                $attendance->fill($data);
                $attendance->save();
            }
        }
        if(isset($attendance->id)) 
            return response()->json(["all data updated successfully"]);
        else 
            return response()->json(["data could not update."],422);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        $data = Attendance::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Attendance::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
