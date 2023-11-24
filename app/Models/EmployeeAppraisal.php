<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAppraisal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'due_on',
        'completed_on',
        'next_due_on',
        'appraised_by',
        'reviewed_by',
        'total_score',
        'promotion_consideration',
        'notes',
        'created_by',
        'updated_by',
    ];
    
    
    protected $dates = ['deleted_at'];
    
    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function appraisedby(){
        return $this->belongsTo(Employee::class, 'appraised_by');
    }

    public function reviewedby(){
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }

    public function setDueOnAttribute($value){
        $this->attributes['due_on'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getDueOnAttribute($value){
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setCompletedOnAttribute($value){
        $this->attributes['completed_on'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getCompletedOnAttribute($value){
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setNextDueOnAttribute($value){
        $this->attributes['next_due_on'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getNextDueOnAttribute($value){
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
