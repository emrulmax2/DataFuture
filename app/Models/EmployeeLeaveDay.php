<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLeaveDay extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_leave_days';

    protected $fillable = [
        'employee_leave_id',
        'leave_date',
        'hour',
        'is_fraction',
        'status',
        'is_taken',
        'was_absent_day',
        'supervision_status',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function leave() {
        return $this->belongsTo(EmployeeLeave::class, 'employee_leave_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function uuser() {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
