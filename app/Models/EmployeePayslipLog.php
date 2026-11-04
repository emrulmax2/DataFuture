<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayslipLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pay_slip_upload_sync_id',
        'employee_id',
        'user_id',
        'impersonator_id',
        'event',
        'type',
        'month_year',
        'holiday_year_id',
        'file_name',
        'ip_address',
        'user_agent',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function impersonator(){
        return $this->belongsTo(User::class, 'impersonator_id')->withTrashed();
    }

    public function holidayYear(){
        return $this->belongsTo(HrHolidayYear::class, 'holiday_year_id');
    }
}
