<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamResultPrev extends Model
{
    use HasFactory, SoftDeletes;

    protected $table =  "exam_result_prev";

    protected $guarded = ['id'];

    protected $fillable = [

        'student_id',
        'course_id',
        'course_module_id',
        'grade',
        'status',
        'paperID',
        'module_no',
        'semester_id', 
        'created_at',
        'awarding_body_id',
        'exam_date',
        'updated_by',
    ];
    
    public function student() {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function semester() {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
    public function courseModule() {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }
    public function awardingBody() {
        return $this->belongsTo(AwardingBody::class, 'awarding_body_id');
    }

    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by');
    }


    public function setUpdatedAtAttribute($value) {  
        $this->attributes['updated_at'] =  (!empty($value) ? date('Y-m-d H:i:s', strtotime($value)) : '');
    }

    public function getUpdatedAtAttribute($value) {
        return (!empty($value) ? date('d-m-Y H:i:s', strtotime($value)) : '');
    }

    public function setCreatedAtAttribute($value) {  
        $this->attributes['created_at'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getCreatedAtAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
