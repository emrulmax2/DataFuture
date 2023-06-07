<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCreationAvailability extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_creation_id',
        'admission_date',
        'admission_end_date',
        'course_start_date',
        'course_end_date',
        'last_joinning_date',
        'type',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function creation(){
        return $this->belongsTo(CourseCreation::class, 'course_creation_id');
    }

    public function setAdmissionDateAttribute($value) {
        $this->attributes['admission_date'] = (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getAdmissionDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setAdmissionEndDateAttribute($value) {
        $this->attributes['admission_end_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getAdmissionEndDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setCourseStartDateAttribute($value) { 
        $this->attributes['course_start_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getCourseStartDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setCourseEndDateAttribute($value) {  
        $this->attributes['course_end_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getCourseEndDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setLastJoinningDateAttribute($value) {  
        $this->attributes['last_joinning_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getLastJoinningDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
