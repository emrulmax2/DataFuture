<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantInterview extends Model
{
    use HasFactory,SoftDeletes;

    //protected $guard = ['id'];
    protected $fillable = ['user_id', 'applicant_id','applicant_task_id','applicant_document_id','interview_date','start_time','end_time','interview_result','created_by'];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function setInterviewDateAttribute($value) {  
        $this->attributes['interview_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getInterviewDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    public function getStartTimeAttribute($value) {
        return (!empty($value) ? date('h:i a', strtotime($value)) : '');
    }
    public function getEndTimeAttribute($value) {
        return (!empty($value) ? date('h:i a', strtotime($value)) : '');
    }
    public function applicant(){
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function task(){
        return $this->belongsTo(ApplicantTask::class, 'applicant_task_id');
    }

    public function document() {
        return $this->belongsTo(ApplicantDocument::class, 'applicant_document_id');
    }
    
}
