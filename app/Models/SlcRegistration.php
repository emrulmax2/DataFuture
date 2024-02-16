<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'ssn',
        'confirmation_date',
        'academic_year_id',
        'registration_year',
        'slc_registration_status_id',
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

    public function attendances(){
        return $this->hasMany(SlcAttendance::class, 'slc_registration_id', 'id');
    }

    public function cocs(){
        return $this->hasMany(SlcCoc::class, 'slc_registration_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function regStatus(){
        return $this->belongsTo(SlcRegistrationStatus::class, 'slc_registration_status_id');
    }

    public function year(){
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function setConfirmationDateAttribute($value) {  
        $this->attributes['confirmation_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getConfirmationDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function instance(){
        return $this->belongsTo(CourseCreationInstance::class, 'course_creation_instance_id');
    }
}
