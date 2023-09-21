<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $appends = ['full_name'];

    protected $fillable = [
        'applicant_user_id',
        'applicant_id',
        'user_id',
        'application_no',
        'title_id',
        'first_name',
        'last_name',
        'photo',
        'date_of_birth',
        'marital_status',
        'gender',
        'submission_date',
        'status_id',
        'rejected_reason',
        'nationality_id',
        'country_id',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function other(){
        return $this->hasOne(StudentOtherDetail::class, 'student_id', 'id');
    }

    public function contact(){
        return $this->hasOne(StudentContact::class, 'student_id', 'id');
    }

    public function course(){
        return $this->hasOne(StudentProposedCourse::class, 'student_id', 'id');
    }

    public function kin(){
        return $this->hasOne(StudentKin::class, 'student_id', 'id');
    }

    public function otherPerInfo(){
        return $this->hasOne(StudentOtherPersonalInformation::class, 'student_id', 'id');
    }

    public function disability(){
        return $this->hasMany(StudentDisability::class, 'student_id', 'id');
    }

    public function quals(){
        return $this->hasMany(StudentQualification::class, 'student_id', 'id');
    }

    public function employment(){
        return $this->hasMany(StudentEmployment::class, 'student_id', 'id');
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
        return $this->belongsTo(User::class, 'user_id');
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
        return $this->hasMany(StudentEmail::class, 'student_id', 'id');
    }

    public function letters(){
        return $this->hasMany(StudentLetter::class, 'student_id', 'id');
    }

    public function sms(){
        return $this->hasMany(StudentSms::class, 'student_id', 'id');
    }

    public function docses(){
        return $this->hasMany(StudentDocument::class, 'student_id', 'id');
    }

    public function notes(){
        return $this->hasMany(StudentNote::class, 'student_id', 'id');
    }

    public function pendingTasks(){
        $tasks = $this->hasMany(StudentTask::class, 'student_id');
        $tasks->getQuery()->where('status', '=', 'Pending');
        return $tasks;
    }

    public function inProgressTasks(){
        $tasks = $this->hasMany(StudentTask::class, 'student_id');
        $tasks->getQuery()->where('status', '=', 'In Progress');
        return $tasks;
    }

    public function completedTasks(){
        $tasks = $this->hasMany(StudentTask::class, 'student_id');
        $tasks->getQuery()->where('status', '=', 'Completed');
        return $tasks;
    }

    public function allTasks(){
       return $this->hasMany(StudentTask::class, 'student_id');
    }
    
}
