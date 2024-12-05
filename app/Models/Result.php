<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'id',
        'assessment_plan_id',
        'published_at',
        'is_primary',
        'term_declaration_id',
        'plan_id',
        'student_id',
        'grade_id',
        'created_at', 
        'created_by',
        'updated_by'
    ];
    
    public function plan(){
        return $this->belongsTo(Plan::class);
    }
    public function term(){
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }
    public function grade(){
        return $this->belongsTo(Grade::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    
    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assementPlan() {
        return $this->belongsTo(AssessmentPlan::class,'assessment_plan_id');
    }

    public function setPublishedAtAttribute($value) {  
        $this->attributes['published_at'] =  (!empty($value) ? date('Y-m-d H:i', strtotime($value)) : '');
    }

    public function getPublishedAtAttribute($value) {
        return (!empty($value) ? date('d-m-Y H:i', strtotime($value)) : '');
    }

    public function setCreatedAtAttribute($value) {  
        $this->attributes['created_at'] =  (!empty($value) ? date('Y-m-d H:i', strtotime($value)) : '');
    }

    public function getCreatedAtAttribute($value) {
        return (!empty($value) ? date('d-m-Y H:i', strtotime($value)) : '');
    }

    public function assessmentPlan() {
        return $this->belongsTo(AssessmentPlan::class,'assessment_plan_id');
    }

}
