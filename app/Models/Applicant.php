<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
        'sex_identifier_id',
        'submission_date',
        'status_id',
        //'rejected_reason',
        'application_rejected_reason_id',
        'nationality_id',
        'country_id',
        'proof_type',
        'proof_id',
        'proof_expiredate',
        'agent_user_id',
        'is_referral_verified',
        'referral_code',
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

    public function HighestQualification(){
        return $this->hasOne(ApplicantQualification::class)->latestOfMany();
    }

    public function previousStudent(){
        return $this->belongsTo(Student::class, 'previous_student_id');
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
    public function setFirstNameAttribute($value) {  
        $this->attributes['first_name'] =  ucwords($value);
    }
    public function setLastNameAttribute($value) {  
        $this->attributes['last_name'] =  ucwords($value);
    }
    public function setDateOfBirthAttribute($value) {  
        $this->attributes['date_of_birth'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getDateOfBirthAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setSubmissionDateAttribute($value) {  
        $this->attributes['submission_date'] = (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
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

    public function proofs(){
        return $this->hasMany(ApplicantProofOfId::class, 'applicant_id', 'id');
    }

    public function proof(){
        return $this->hasOne(ApplicantProofOfId::class, 'applicant_id', 'id')->latestOfMany();
    }

    public function feeeligibilities(){
        return $this->hasMany(ApplicantFeeEligibility::class, 'applicant_id', 'id');
    }

    public function feeeligibility(){
        return $this->hasOne(ApplicantFeeEligibility::class, 'applicant_id', 'id')->latestOfMany();
    }

    public function sexid(){
        return $this->belongsTo(SexIdentifier::class, 'sex_identifier_id');
    }

    public function agent(){
        return $this->belongsTo(AgentUser::class, 'agent_user_id');
    }

    public function reason(){
        return $this->belongsTo(ApplicationRejectedReason::class, 'application_rejected_reason_id');
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo !== null && Storage::disk('local')->exists('public/applicants/'.$this->id.'/'.$this->photo)) {
            return Storage::disk('local')->url('public/applicants/'.$this->id.'/'.$this->photo);
        } else {
            return asset('build/assets/images/user_avatar.png');
        }
    }

    public function getCreationVenueStatusAttribute(){
        $proposed = ApplicantProposedCourse::where('applicant_id', $this->id)->get()->first();
        if(isset($proposed->id) && $proposed->id > 0){
            $courseVenue = CourseCreationVenue::where('course_creation_id', $proposed->course_creation_id)->where('venue_id', $proposed->venue_id)->get()->first();
            return ((isset($courseVenue->evening_and_weekend) && $courseVenue->evening_and_weekend == 1) && (isset($courseVenue->weekends) && $courseVenue->weekends > 0) ? true : false );
        }
        return false;
    }

}
