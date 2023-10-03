<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseRelation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_creation_id',
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
    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function creation(){
        return $this->belongsTo(CourseCreation::class, 'course_creation_id');
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
}
