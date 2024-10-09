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
        'assessment_plan_id',
        'published_at',
        'is_primary',
        'plan_id',
        'student_id',
        'grade_id',
        'created_by',
        'updated_by'
    ];
    
    public function plan(){
        return $this->belongsTo(Plan::class);
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
}
