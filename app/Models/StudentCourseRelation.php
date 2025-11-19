<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseRelation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_creation_id',
        'course_start_date',
        'course_end_date',
        'type',
        'student_id',
        'active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at','course_start_date','course_end_date'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function creation(){
        return $this->belongsTo(CourseCreation::class, 'course_creation_id');
    }

    public function activeCreation(){
        return $this->belongsTo(CourseCreation::class, 'course_creation_id')->where('active', 1);
    }
    public function course(): HasOneThrough
    {
        return $this->hasOneThrough(Course::class, CourseCreation::class,'id','id','course_creation_id','course_id');
    }

    public function semester(): HasOneThrough
    {
        return $this->hasOneThrough(Semester::class, CourseCreation::class,'id','id','course_creation_id','semester_id');
    }

    public function propose(){
        return $this->hasOne(StudentProposedCourse::class, 'student_course_relation_id', 'id')->latestOfMany();
    }

    public function abody(){
        return $this->hasOne(StudentAwardingBodyDetails::class, 'student_course_relation_id', 'id')->latestOfMany();
    }

    public function feeeligibility(){
        return $this->hasOne(StudentFeeEligibility::class, 'student_course_relation_id', 'id')->latestOfMany();
    }

    public function setCourseStartDateAttribute($value) {  
        $this->attributes['course_start_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getCourseStartDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setCourseEndDateAttribute($value) {  
        $this->attributes['course_end_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getCourseEndDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

}
