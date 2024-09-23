<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $appends = ['full_name', 'photo', 'photo_url'];

    protected $fillable = [
        'applicant_user_id',
        'applicant_id',
        'student_user_id',
        'application_no',
        'registration_no',
        'ssn_no',
        'uhn_no',
        'title_id',
        'first_name',
        'last_name',
        'photo',
        'date_of_birth',
        'marital_status',
        'sex_identifier_id',
        'submission_date',
        'status_id',
        'rejected_reason',
        'nationality_id',
        'country_id',
        'referral_code',
        'is_referral_varified',
        'created_by',
        'updated_by', 
    ];

    protected $dates = ['deleted_at'];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo !== null && Storage::disk('local')->exists('public/students/'.$this->id.'/'.$this->photo)) {
            return Storage::disk('local')->url('public/students/'.$this->id.'/'.$this->photo);
        } else {
            return asset('build/assets/images/user_avatar.png');
        }
    }

    public function getPhotoAttribute($value){
        return $value;
    }

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

    /*public function otherPerInfo(){
        return $this->hasOne(StudentOtherPersonalInformation::class, 'student_id', 'id');
    }*/

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
        return $this->belongsTo(StudentUser::class, 'student_user_id');
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
        return (isset($this->title->name) ? $this->title->name.' ' : '').$this->first_name . ' ' . $this->last_name.'';
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

    public function consents(){
        return $this->hasMany(StudentConsent::class, 'student_id', 'id');
    }

    public function referral(){
        return $this->belongsTo(ReferralCode::class, 'status_id');
    }

    public function crel(){
        if(Session::has('student_temp_course_relation_'.$this->id) && Session::get('student_temp_course_relation_'.$this->id) > 0):
            return $this->hasOne(StudentCourseRelation::class, 'student_id')->where('id', '=', Session::get('student_temp_course_relation_'.$this->id));
        else:
            return $this->hasOne(StudentCourseRelation::class, 'student_id')->where('active', '=', 1);
        endif;
    }
    public function courseRelationsList() {
        return $this->hasMany(StudentCourseRelation::class, 'student_id');
    }
    
    public function activeCR(){
        return $this->hasOne(StudentCourseRelation::class, 'student_id')->where('active', '=', 1)->latestOfMany();
    }

    public function otherCrels(){
        return $this->hasMany(StudentCourseRelation::class, 'student_id')->where('active', '!=', 1);
    }

    public function sexid(){
        return $this->belongsTo(SexIdentifier::class, 'sex_identifier_id');
    }

    public function getAssignedTermsAttribute(){
        $cp_ids = Assign::where('student_id', $this->id)->pluck('plan_id')->unique()->toArray();
        if(!empty($cp_ids)):
            $term_decs = Plan::whereIn('id', $cp_ids)->pluck('term_declaration_id')->unique()->toArray();
            if(!empty($term_decs)):
                return TermDeclaration::whereIn('id', $term_decs)->get();
            else:
                return false;
            endif;
        else:
            return false;
        endif;
    }

    public function termStatus(){
        return $this->hasOne(StudentAttendanceTermStatus::class, 'student_id')->latestOfMany();
    }

    public function award(){
        $activeCRel = (isset($this->activeCR->id) && $this->activeCR->id > 0 ? $this->activeCR->id : 0);
        return $this->hasOne(StudentAwardingBodyDetails::class, 'student_id')->where('student_course_relation_id', $activeCRel)->latestOfMany();
    }
    
}
