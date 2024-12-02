<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultSubmission extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'plan_id',
        'student_id',
        'student_course_relation_id',
        'assessment_plan_id',
        'grade_id',
        'paper_id',
        'module_creation_id',
        'published_at',
        'module_code',
        'upload_user_type',
        'created_by',
        'updated_by'
    ];


    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function courseCreation()
    {
        return $this->belongsTo(CourseCreation::class);
    }

    public function assessmentPlan()
    {
        return $this->belongsTo(AssessmentPlan::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function moduleCreation()
    {
        return $this->belongsTo(ModuleCreation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function getPublishedAtAttribute($value)
    {
        return $value ? date('jS M, Y', strtotime($value)) : null;
    }

    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    public function getCreatedAtAttribute($value)
    {
        return date('jS M, Y H:i a', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? date('jS M, Y H:i a', strtotime($value)) : null;
    }
    
}
