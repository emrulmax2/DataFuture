<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'employee_working_pattern_id',
        'employee_working_pattern_pay_id',
        'date',
        'clockin_contract',
        'clockin_punch',
        'clockin_system',
        'clockout_contract',
        'clockout_punch',
        'clockout_system',
        'total_break',
        'break_details_html',
        'paid_break',
        'unpadi_break',
        'adjustment',
        'total_work_hour',
        'employee_leave_day_id',
        'leave_status',
        'leave_adjustment',
        'leave_hour',
        'note',
        'user_issues',
        'isses_field',
        'overtime_status',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function pattern(){
        return $this->belongsTo(EmployeeWorkingPattern::class, 'employee_working_pattern_id');
    }

    public function pay(){
        return $this->belongsTo(EmployeeWorkingPatternPay::class, 'employee_working_pattern_pay_id');
    }

    public function leaveDay(){
        return $this->belongsTo(EmployeeLeaveDay::class, 'employee_leave_day_id');
    }

    public function breaks(){
        return $this->hasMany(EmployeeAttendanceDayBreak::class, 'employee_attendance_id', 'id');
    }

    public function getClockInLocationAttribute(){
        $res = [];
        $liveAttendance = EmployeeAttendanceLive::where('employee_id', $this->employee_id)->where('attendance_type', 1)
                          ->where('date', $this->date)->orderBy('id', 'DESC')->get()->first();
        if(isset($liveAttendance->id) && $liveAttendance->id > 0):
            if(isset($liveAttendance->ip) && !empty($liveAttendance->ip)):
                $venuIp = VenueIpAddress::where('ip', $liveAttendance->ip)->get()->first();
                if(isset($venuIp->venue->name) && !empty($venuIp->venue->name)):
                    $res['suc'] = 1;
                    $res['ip'] = $venuIp->ip;
                    $res['venue'] = $venuIp->venue->name;
                else:
                    $res['suc'] = 0;
                    $res['ip'] = $liveAttendance->ip;
                    $res['venue'] = '';
                endif;
            else:
                $res['suc'] = 0;
                $res['ip'] = '';
                $res['venue'] = '';
            endif;
        else:
            $res['suc'] = 2;
            $res['ip'] = '';
            $res['venue'] = '';
        endif;

        return $res;
    }

    public function getClockOutLocationAttribute(){
        $res = [];
        $liveAttendance = EmployeeAttendanceLive::where('employee_id', $this->employee_id)->where('attendance_type', 4)
                          ->where('date', $this->date)->orderBy('id', 'DESC')->get()->first();
        if(isset($liveAttendance->id) && $liveAttendance->id > 0):
            if(isset($liveAttendance->ip) && !empty($liveAttendance->ip)):
                $venuIp = VenueIpAddress::where('ip', $liveAttendance->ip)->get()->first();
                if(isset($venuIp->venue->name) && !empty($venuIp->venue->name)):
                    $res['suc'] = 1;
                    $res['ip'] = $venuIp->ip;
                    $res['venue'] = $venuIp->venue->name;
                else:
                    $res['suc'] = 0;
                    $res['ip'] = $liveAttendance->ip;
                    $res['venue'] = '';
                endif;
            else:
                $res['suc'] = 0;
                $res['ip'] = '';
                $res['venue'] = '';
            endif;
        else:
            $res['suc'] = 2;
            $res['ip'] = '';
            $res['venue'] = '';
        endif;

        return $res;
    }

    public function getBreakTimeAttribute() {
        $minutes = (isset($this->attributes['total_break']) && $this->attributes['total_break'] > 0 ? $this->attributes['total_break'] : 0);
        $hours = (intval(trim($minutes)) / 60 >= 1) ? intval(intval(trim($minutes)) / 60) : '00';
        $mins = (intval(trim($minutes)) % 60 != 0) ? intval(trim($minutes)) % 60 : '00';
     
        $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
        $hourMins .= ':';
        $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;

        return $hourMins;
    }

    public function getWorkHourAttribute() {
        $minutes = (isset($this->attributes['total_work_hour']) && $this->attributes['total_work_hour'] > 0 ? $this->attributes['total_work_hour'] : 0);
        $hours = (intval(trim($minutes)) / 60 >= 1) ? intval(intval(trim($minutes)) / 60) : '00';
        $mins = (intval(trim($minutes)) % 60 != 0) ? intval(trim($minutes)) % 60 : '00';
     
        $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
        $hourMins .= ':';
        $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;

        return $hourMins;
    }

    public function getLeavesHourAttribute() {
        $leave_status = (isset($this->attributes['leave_status']) && $this->attributes['leave_status'] > 0 ? $this->attributes['leave_status'] : 0);
        $minutes = ($leave_status > 0 && isset($this->attributes['leave_hour']) && $this->attributes['leave_hour'] > 0 ? $this->attributes['leave_hour'] : 0);
        $hours = (intval(trim($minutes)) / 60 >= 1) ? intval(intval(trim($minutes)) / 60) : '00';
        $mins = (intval(trim($minutes)) % 60 != 0) ? intval(trim($minutes)) % 60 : '00';
     
        $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
        $hourMins .= ':';
        $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;

        return $hourMins;
    }

    public function getAllowedBreakAttribute() {
        $paid_break = (isset($this->attributes['paid_break']) && $this->attributes['paid_break'] > 0 ? $this->attributes['paid_break'] : '00:00');
        $padiMinute = 0;
        $str = explode(':', $paid_break);
        $padiMinute += (isset($str[0]) && $str[0] != '') ? $str[0] * 60 : 0;
        $padiMinute += (isset($str[1]) && $str[1] != '') ? $str[1] : 0;

        $unpadi_break = (isset($this->attributes['unpadi_break']) && $this->attributes['unpadi_break'] > 0 ? $this->attributes['unpadi_break'] : '00:00');
        $unPadiMinute = 0;
        $str = explode(':', $unpadi_break);
        $unPadiMinute += (isset($str[0]) && $str[0] != '') ? $str[0] * 60 : 0;
        $unPadiMinute += (isset($str[1]) && $str[1] != '') ? $str[1] : 0;

        return ($padiMinute + $unPadiMinute);
    }
}
