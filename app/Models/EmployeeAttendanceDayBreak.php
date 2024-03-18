<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendanceDayBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_attendance_id',
        'employee_id',
        'date',
        'start',
        'end',
        'total',

        'created_by',
        'updated_by',
    ];
}
