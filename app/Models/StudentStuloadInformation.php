<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentStuloadInformation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function instance(){
        return $this->belongsTo(CourseCreationInstance::class, 'course_creation_instance_id');
    }

    public function df(){
        return $this->hasOne(StudentCourseSessionDatafuture::class, 'student_stuload_information_id', 'id');
    }
}
