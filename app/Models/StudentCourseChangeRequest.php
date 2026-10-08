<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseChangeRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_course_change_requests';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function proposedCourse()
    {
        return $this->belongsTo(Course::class, 'proposed_course_id');
    }

    public function documents()
    {
        return $this->hasMany(StudentCourseChangeRequestDocument::class, 'student_course_change_request_id');
    }
}
