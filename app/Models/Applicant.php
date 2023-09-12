<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applicant extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['full_name'];

    protected $fillable = [
        'applicant_user_id',
        'application_no',
        'title_id',
        'first_name',
        'last_name',
        'photo',
        'date_of_birth',
        'gender',
        'submission_date',
        'status_id',
        'rejected_reason',
        'nationality_id',
        'country_id',
        'proof_type',
        'proof_id',
        'proof_expiredate',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function other(){
        return $this->hasOne(ApplicantOtherDetail::class, 'applicant_id', 'id');
    }

    public function contact(){
        return $this->hasOne(ApplicantContact::class, 'applicant_id', 'id');
    }

    public function course(){
        return $this->hasOne(ApplicantProposedCourse::class, 'applicant_id', 'id');
    }

    public function kin(){
        return $this->hasOne(ApplicantKin::class, 'applicant_id', 'id');
    }

    public function disability(){
        return $this->hasMany(ApplicantDisability::class, 'applicant_id', 'id');
    }

    public function quals(){
        return $this->hasMany(ApplicantQualification::class, 'applicant_id', 'id');
    }

    public function employment(){
        return $this->hasMany(ApplicantEmployment::class, 'applicant_id', 'id');
    }

    public function title(){
        return $this->belongsTo(Title::class, 'title_id');
    }

    public function nation(){
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    public function country(){
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function status(){
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function users(){
        return $this->belongsTo(ApplicantUser::class, 'applicant_user_id');
    }

    public function setDateOfBirthAttribute($value) {  
        $this->attributes['date_of_birth'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getDateOfBirthAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setSubmissionDateAttribute($value) {  
        $this->attributes['submission_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getSubmissionDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name.'';
    }

    public function emails(){
        return $this->hasMany(ApplicantEmail::class, 'applicant_id', 'id');
    }

    public function letters(){
        return $this->hasMany(ApplicantLetter::class, 'applicant_id', 'id');
    }

    public function sms(){
        return $this->hasMany(ApplicantSms::class, 'applicant_id', 'id');
    }

    public function docses(){
        return $this->hasMany(ApplicantDocument::class, 'applicant_id', 'id');
    }

    public function notes(){
        return $this->hasMany(ApplicantNote::class, 'applicant_id', 'id');
    }

    public function pendingTasks(){
        $tasks = $this->hasMany(ApplicantTask::class, 'applicant_id');
        $tasks->getQuery()->where('status', '=', 'Pending');
        return $tasks;
    }

    public function inProgressTasks(){
        $tasks = $this->hasMany(ApplicantTask::class, 'applicant_id');
        $tasks->getQuery()->where('status', '=', 'In Progress');
        return $tasks;
    }

    public function completedTasks(){
        $tasks = $this->hasMany(ApplicantTask::class, 'applicant_id');
        $tasks->getQuery()->where('status', '=', 'Completed');
        return $tasks;
    }

    public function allTasks(){
       return $this->hasMany(ApplicantTask::class, 'applicant_id');
    }

    public function setProofExpiredateAttribute($value) {  
        $this->attributes['proof_expiredate'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }
    public function getProofExpiredateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
