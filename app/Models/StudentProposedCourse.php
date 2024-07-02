<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProposedCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_id',
        'semester_id',
        'academic_year_id',
        'student_loan',
        'student_finance_england',
        'fund_receipt',
        'applied_received_fund',
        'full_time',
        'other_funding',
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

    public function semester(){
        return $this->belongsTo(Semester::class, 'semester_id');
    }
    
    public function venue(){
        return $this->belongsTo(Venue::class, 'venue_id');
    }
    
    
    public function academicYear(){
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
