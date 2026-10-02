<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeAttendancePunchHistory;
use App\Models\Employment;
use App\Models\User;
use App\Models\VenueIpAddress;
use Illuminate\Http\Request;

class EmployeeAttendancePunchController extends Controller
{
    /** Clock-In is the only action this terminal offers without the privilege. */
    private const TYPE_CLOCK_IN = 1;

    /**
     * May this punch number use the break and clock-out buttons on the terminal?
     *
     * Governed by the "Allow All Services" privilege. Without it the terminal
     * is a clock-in point only, and the person ends their day from their own
     * dashboard instead.
     *
     * Read through `User::priv()` rather than off a table, so it follows
     * `config('privileges.source')` and the super-admin bypass exactly as the
     * privilege screen does. The two must never be able to disagree about what
     * someone is allowed.
     *
     * Denied whenever the answer is not a clear yes — an unknown punch number,
     * an employee with no linked user account, a missing permission row. The
     * permission is stored only when granted, so absent means no.
     */
    private function allowsAllServices(?Employment $employment): bool
    {
        if (!config('attendance.punch_restrict_services', true)) {
            return true;
        }

        $userId = optional(optional($employment)->employee)->user_id;

        if (!$userId) {
            return false;
        }

        $user = User::find($userId);

        return $user ? (int) ($user->priv()['all_services'] ?? 0) === 1 : false;
    }

    /**
     * The refusal a terminal shows when an action is not this person's to make.
     *
     * Shaped like every other response here — `suc` 2 with a message — so the
     * terminal renders it through the path it already has for a failed punch.
     */
    private function serviceDenied(): array
    {
        return [
            'suc' => 2,
            'msg' => 'You can only clock in at this terminal. Please use your dashboard for breaks and clocking out, or speak to HR.',
        ];
    }

    public function index(Request $request){
        $venueIpAddresses = VenueIpAddress::pluck('ip')->unique()->toArray();
        $requestIp = $request->getClientIp();
        return view('pages.hr.punch.index', [
            'title' => 'HR Portal - London Churchill College',
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
            $res['name'] = (isset($employment->employee->full_name) && !empty($employment->employee->full_name)) ? $employment->employee->full_name.' ' : '';
            /* Drives which buttons the terminal offers. The guards on the two
               write endpoints are what actually enforce it — this only spares
               someone pressing a button that was never going to work. */
            $res['all_services'] = $this->allowsAllServices($employment);
        else:
            $res['loc'] = 'error';
            $res['name'] = '';
            $res['all_services'] = false;
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

    public function storeAttendance(Request $request){
        $clock_in_no            = $request->clockinno;
        $attendance_type        = $request->type;
        $today                  = date('Y-m-d');
        $time                   = date('H:i:s');
        $datetime               = date('jS F, Y H:i:s');

        $type_name = '';
        switch ($attendance_type):
            case 2:
                $type_name = 'Break';
                break;
            case 4:
                $type_name = 'Clock-Out';
                break;
        endswitch;

        $ipAddresses = VenueIpAddress::orderBy('id', 'ASC')->pluck('ip')->toArray();

        $employment = Employment::where('punch_number', $clock_in_no)->get()->first();

        /* An unknown punch number reached a live, unauthenticated endpoint and
           was read straight through, so the next line fatalled on null. Answered
           in the shape the terminal already handles instead. */
        if (!$employment || !$employment->employee_id):
            return response()->json(['res' => [
                'suc' => 2,
                'msg' => 'That clock-in number was not recognised. Please try again or speak to HR.',
            ]], 200);
        endif;

        /* Checked here, not only in the terminal's JavaScript. This endpoint
           takes a punch number and an action from an unauthenticated page, so
           a hidden button is a courtesy and this is the control. */
        if ((int) $attendance_type !== self::TYPE_CLOCK_IN && !$this->allowsAllServices($employment)):
            return response()->json(['res' => $this->serviceDenied()], 200);
        endif;

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

        /* An unknown punch number reached a live, unauthenticated endpoint and
           was read straight through, so the next line fatalled on null. Answered
           in the shape the terminal already handles instead. */
        if (!$employment || !$employment->employee_id):
            return response()->json(['res' => [
                'suc' => 2,
                'msg' => 'That clock-in number was not recognised. Please try again or speak to HR.',
            ]], 200);
        endif;

        /* Checked here, not only in the terminal's JavaScript. This endpoint
           takes a punch number and an action from an unauthenticated page, so
           a hidden button is a courtesy and this is the control. */
        if ((int) $attendance_type !== self::TYPE_CLOCK_IN && !$this->allowsAllServices($employment)):
            return response()->json(['res' => $this->serviceDenied()], 200);
        endif;

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
