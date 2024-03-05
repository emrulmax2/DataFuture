<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLeave extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'hr_holiday_year_id',
        'employee_working_pattern_id',
        'leave_type',
        'from_date',
        'to_date',
        'days',
        'is_fraction',
        'note',
        'status',
        'approved_by',
        'approver_note',
        'approved_at',
        'canceled_by',
        'canceled_note',
        'canceled_at',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function employee() {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function year() {
        return $this->belongsTo(HrHolidayYear::class, 'hr_holiday_year_id');
    }

    public function pattern() {
        return $this->belongsTo(EmployeeWorkingPattern::class, 'employee_working_pattern_id');
    }

    public function leaveDays(){
        return $this->hasMany(EmployeeLeaveDay::class, 'employee_leave_id', 'id');
    }

    public function approved(){
        return $this->belongsTo(User::class, 'approved_by');
    }
}
