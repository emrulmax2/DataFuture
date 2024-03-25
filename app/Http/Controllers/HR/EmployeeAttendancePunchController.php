<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeAttendancePunchHistory;
use App\Models\Employment;
use App\Models\VenueIpAddress;
use Illuminate\Http\Request;

class EmployeeAttendancePunchController extends Controller
{
    public function index(Request $request){
        $venueIpAddresses = VenueIpAddress::pluck('ip')->unique()->toArray();
        $requestIp = $request->getClientIp();
        return view('pages.hr.punch.index', [
            'title' => 'Live Attendance Punch - LCC Data Future Managment',
            'breadcrumbs' => [],
            'ip_check' => (!empty($venueIpAddresses) && in_array($requestIp, $venueIpAddresses) ? true : false)
        ]);
    }

    public function getAttendanceHistory(Request $request){
        $clockinno = $request->clockinno;
        $today = date('Y-m-d');

        $res = [];
        $employment = Employment::where('punch_number', $clockinno)->get()->first();
        $employee_id = (isset($employment->employee_id) && $employment->employee_id > 0) ? $employment->employee_id : '';
        $last_action_date = (isset($employment->last_action_date) && $employment->last_action_date != '') ? $employment->last_action_date : '';
        if(!empty($employment) && $employment->count() > 0):
            if($today == $last_action_date){
                $res['loc'] = (isset($employment->last_action) && $employment->last_action > 0) ? $employment->last_action : 'error';
            }else{
                $res['loc'] = 0;
            }
            $res['name'] = (isset($employment->employee->first_name) && !empty($employment->employee->first_name)) ? $employment->employee->first_name.' ' : '';
            $res['name'] .= (isset($employment->employee->last_name) && !empty($employment->employee->last_name)) ? $employment->employee->last_name : '';
        else:
            $res['loc'] = 'error';
            $res['name'] = '';
        endif;

        if(isset($res['loc']) && $res['loc'] !== "error" && $employee_id > 0):
            $dara               = array();
            $dara['employee_id'] = $employee_id;
            $dara['date']       = date('Y-m-d');
            $dara['time']       = date('H:i:s');
            $dara['ip']         = $request->ip();
            $dara['created_by'] = $employment->employee->user_id;
            
            EmployeeAttendancePunchHistory::create($dara);
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function store(Request $request){
        $clock_in_no            = $request->clock_in_no;
        $attendance_type        = $request->attendance_type;
        $today                  = date('Y-m-d');
        $time                   = date('H:i:s');
        $datetime               = date('jS F, Y H:i:s');

        $type_name = '';
        switch ($attendance_type):
            case 1:
                $type_name = 'Clock-In';
                break;
            case 2:
                $type_name = 'Break';
                break;
            case 3:
                $type_name = 'Break-Return';
                break;
            case 4:
                $type_name = 'Clock-Out';
                break;
        endswitch;

        $ipAddresses = VenueIpAddress::orderBy('id', 'ASC')->pluck('ip')->toArray();

        $employment = Employment::where('punch_number', $clock_in_no)->get()->first();
        $employee_id = $employment->employee_id;

        $data[] = '';
        $data['employee_id'] = $employee_id;
        $data['attendance_type'] = $attendance_type;
        $data['date'] = $today;
        $data['time'] = $time;
        $data['ip'] = $request->ip();
        $data['created_by'] = $employee_id;

        $res = [];
        $attendanceLive = EmployeeAttendanceLive::create($data);
        if($attendanceLive->id):
            $data = [];
            $data['last_action'] = $attendance_type;
            $data['last_action_date'] = $today;
            $data['last_action_time'] = $time;

            Employment::where('punch_number', $clock_in_no)->where('employee_id', $employee_id)->update($data);

            $res['message'] = '';
            if(!empty($ipAddresses) && !in_array($request->ip(), $ipAddresses)):
                $res['suc'] = 2;
                $res['msg'] = '<strong>'.$datetime.'</strong><br/>Your '.$type_name.' is recorded away from the campus. Please ensure this has been authorised by the HR/Department manager.';
            else:
                $res['suc'] = 1;
                $res['msg'] = 'Your punch for '.$type_name.' successfully recorde for the day '.$datetime.'.';
            endif;
        else:
            $res['suc'] = 2;
            $res['msg'] = 'Oops! Something went wrong. Please try later or contact with your HR/Department manager.';
        endif;

        return response()->json(['res' => $res], 200);
    }
}
