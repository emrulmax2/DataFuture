<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAttendanceLive extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'attendance_type',
        'date',
        'time',
        'employee_attendance_machine_id',
        'ip',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['deleted_at'];

    public function machine(){
        return $this->belongsTo(EmployeeAttendanceMachine::class, 'employee_attendance_machine_id');
    }
}
