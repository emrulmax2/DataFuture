<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\TutorAttendanceInformationDataSaveRequest;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceFeedStatus;
use App\Models\AttendanceInformation;
use App\Models\Employment;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TutorAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TutorAttendanceInformationDataSaveRequest $request)
    {
        // 
        $PlansDateList = PlansDateList::find($request->plan_date_list_id);

        $attendanceFind = AttendanceInformation::where("plans_date_list_id",$request->plan_date_list_id)->get()->first();
            if(!$attendanceFind) {
                
                AttendanceInformation::create([
                    'plans_date_list_id' =>$request->plan_date_list_id,
                    'tutor_id' => Auth::user()->id,
                    'start_time' => now(),
                    'note' => (isset($request->note)) ? $request->note : null,
                    'created_by' => Auth::user()->id
                ]);
                return response()->json(["data"=>["msg"=>"Class started",'tutor' =>8,'plandate'=>$request->plan_date_list_id]],206);
            } else {

                $attendanceInformation = AttendanceInformation::find($attendanceFind->id);
                $attendanceInformation->end_time = now();
                $attendanceInformation->updated_by = Auth::user()->id;
                
                if($attendanceInformation->isDirty()) {
                    $attendanceInformation->save();
                    return response()->json(["data"=>"Class Ended"],200);
                }
            }
        
        return response()->json(["data"=>"Something Went Wrong"],422);
    }
    public function check(Request $request)
    {
        $employment = Employment::where("punch_number",$request->punch_number)->get()->first();
        if($employment) {
            if($employment->employee->user_id != Auth::user()->id) {
                return response()->json(["data"=>'It is not your punch number'],304);
            }
            $planDateList = PlansDateList::find($request->plan_date_list_id);
            $plan = Plan::find($planDateList->plan_id);
            
            $attendanceFind = AttendanceInformation::where("plans_date_list_id",$request->plan_date_list_id)->get()->first();
            if($attendanceFind) {
                return response()->json(["data"=>'Attendance Start Found'],303);
            } else {
                if($plan->tutor_id!=Auth::user()->id) {
                    return response()->json(["data"=>'Not Matched Tutor',],302);
                } else {
                    return response()->json(["data"=>'Tutor Matched'],200);
                }
            }
        } else {
            return response()->json(["punch_number"=>'Invalid Punch Number'],402);
        }
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
