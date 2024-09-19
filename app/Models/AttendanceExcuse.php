<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceExcuse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_task_id',
        'reason',
        'remarks',
        'status',
        'attendance_types',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function days(){
        return $this->hasMany(AttendanceExcuseDay::class, 'attendance_excuse_id');
    }

    public function documents(){
        return $this->hasMany(AttendanceExcuseDocument::class, 'attendance_excuse_id');
    }

    public function getStatusLabelAttribute(){
        if($this->status == 1):
            return 'Reviewed & Rejected';
        elseif($this->status == 2):
            return 'Reviewed & Approved';
        elseif($this->status == 0):
            return 'Pending';
        else:
            return '';
        endif;
    }
}
