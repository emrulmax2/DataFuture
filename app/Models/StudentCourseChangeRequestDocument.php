<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseChangeRequestDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_course_change_request_documents';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function request()
    {
        return $this->belongsTo(StudentCourseChangeRequest::class, 'student_course_change_request_id');
    }
}
