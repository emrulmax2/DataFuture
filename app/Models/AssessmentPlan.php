<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentPlan extends Model
{
    use HasFactory,SoftDeletes;

     protected $fillable = [
        'plan_id',
        'course_module_base_assesment_id',
        'upload_user_type',
        'published_at',
        'visible_at',
        'resubmission_at',
        'resubmission_visible_at',
        'created_by',
        'updated_by',
    ];
    public function setPublishedAtAttribute($value) {  
        $this->attributes['published_at'] =  (!empty($value) ? date('Y-m-d H:i:s', strtotime($value)) : '');
    }
    
    public function getPublishedAtAttribute($value) {  
        return (!empty($value) ? date('d-m-Y H:i', strtotime($value)) : '');
    }


    
    
    public function setResubmissionAtAttribute($value) {  
        $this->attributes['resubmission_at'] =  (!empty($value) ? date('Y-m-d H:i:s', strtotime($value)) : '');
    }

    public function getResubmissionAtAttribute($value) {  
        return (!empty($value) ? date('d-m-Y  H:i', strtotime($value)) : '');
    }


    public function courseModuleBase(): BelongsTo
    {
        return $this->belongsTo(CourseModuleBaseAssesment::class,'course_module_base_assesment_id','id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function resultSubmissions(): HasMany
    {
        return $this->hasMany(ResultSubmission::class);
    }

    public function resultSubmissionByStaffs(): HasMany
    {
        return $this->hasMany(ResultSubmissionByStaff::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    
}
