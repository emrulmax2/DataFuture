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
        'ip',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['deleted_at'];

}
