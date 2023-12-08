<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'slc_registration_id',
        'confirmation_date',
        'attendance_year',
        'attendance_term',
        'session_term',
        'attendance_code_id',
        'note',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function registration(){
        return $this->belongsTo(SlcRegistration::class, 'slc_registration_id');
    }
    public function code(){
        return $this->belongsTo(AttendanceCode::class, 'attendance_code_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coc(){
        return $this->hasOne(SlcCoc::class, 'slc_attendance_id', 'id')->latestOfMany();
    }

    public function setConfirmationDateAttribute($value) {  
        $this->attributes['confirmation_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getConfirmationDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
