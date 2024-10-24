<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $appends = ['full_name', 'photo', 'photo_url','referral_info'];

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
    public function getReferralInfoAttribute() {
        $refferalCode = ReferralCode::where('code', $this->referral_code)->get()->first();
        if(isset($refferalCode->agent_user_id)) {
            return Agent::where('agent_user_id',$refferalCode->agent_user_id)->get()->first();
        }elseif(isset($refferalCode->user_id)) {
            return Employee::where('user_id',$refferalCode->user_id)->get()->first();
        }elseif(isset($refferalCode->student_id)) {
            return Student::find($refferalCode->student_id);
        }
        return NULL;
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
    public function qualHigest(){
        return $this->hasOne(StudentQualification::class, 'student_id', 'id')->latestOfMany();
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

    // public function referral(){
    //     return $this->belongsTo(ReferralCode::class, 'status_id');
    // }

    public function crel(){
        if(Session::has('student_temp_course_relation_'.$this->id) && Session::get('student_temp_course_relation_'.$this->id) > 0):
            return $this->hasOne(StudentCourseRelation::class, 'student_id')->where('id', '=', Session::get('student_temp_course_relation_'.$this->id));
        else:
            return $this->hasOne(StudentCourseRelation::class, 'student_id')->where('active', '=', 1);
        endif;
    }

    public function getSessionkeyAttribute(){
        return 'student_temp_course_relation_'.$this->id;
    }

    public function courseRelationsList() {
        return $this->hasMany(StudentCourseRelation::class, 'student_id');
    }
    
    public function activeCR(){

        return $this->hasOne(StudentCourseRelation::class, 'student_id')->where('active', '=', 1)->latestOfMany();
        
    }
    public function ProofOfIdLatest() {
        return $this->hasOne(StudentProofOfId::class, 'student_id')->latestOfMany();
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

    public function getDueAttribute(){
        $activeCRel = (isset($this->crel->id) && $this->crel->id > 0 ? $this->crel->id : 0);
        $agreements = SlcAgreement::where('student_id', $this->id)->where('student_course_relation_id', $activeCRel)->orderBy('id', 'ASC')->get();
        $dueStatus = 2; /* Due Not Found */
        if($agreements->count() > 0):
            foreach($agreements as $agr):
                $ClaimAmount = (isset($agr->claim_amount) && $agr->claim_amount > 0 ? $agr->claim_amount : 0);
                $ReceivedAmount = (isset($agr->received_amount) && $agr->received_amount > 0 ? $agr->received_amount : 0);
                if($ClaimAmount > $ReceivedAmount):
                    $inst = SlcInstallment::where('slc_agreement_id', $agr->id)->orderBy('id', 'DESC')->get()->first();
                    $inst_date = (isset($inst->installment_date) && !empty($inst->installment_date) ? date('Y-m-d', strtotime($inst->installment_date)) : '');
                    if(!empty($inst_date)):
                        $inst_date = date('Y-m-d', strtotime('+30 Days', strtotime($inst_date)));
                        if($inst_date < date('Y-m-d')):
                            $dueStatus = 4; /* Due Found. And its over 30 days. Its a danger */
                        else:
                            $dueStatus = 3; /* Due Found. And its within 30 days. Its a warning */
                        endif;
                    else:
                        $dueStatus = 3; /* Due Found But Date Not Found. Its a warning.*/
                    endif;
                endif;
            endforeach;
        else:
            $dueStatus = 1; /* Agreement does not exist */
        endif;

        return $dueStatus;
    }

    public function getFlagHtmlAttribute(){
        $html = '';
        $studentNotFlag = DB::table('student_notes as sn')
                          ->select(DB::raw('COUNT(DISTINCT sn.student_flag_id) as TOTAL_FLAG'), 'sf.color')
                          ->leftJoin('student_flags as sf', 'sn.student_flag_id', 'sf.id')
                          ->where('sn.student_id', $this->id)
                          ->where('sn.is_flaged', 'Yes')->where('sn.flaged_status', 'Active')
                          ->groupBy('sf.color')->get();
        if($studentNotFlag->count() > 0):
            $html .= '<div class="inline-flex justify-end items-center mr-1">';
                foreach($studentNotFlag as $flag):
                    $html .= '<a href="'.route('student.notes', $this->id).'" class="relative flagLinks mr-2 text-'.strtolower($flag->color).'"><span class="flagCount bg-'.strtolower($flag->color).'">'.$flag->TOTAL_FLAG.'</span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="flag" class="lucide lucide-flag w-6 h-6"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" x2="4" y1="22" y2="15"></line></svg></a>';
                endforeach;
            $html .= '</div>';
        endif;

        return $html;
    }

    public function awarded(){
        $activeCRel = (isset($this->crel->id) && $this->crel->id > 0 ? $this->crel->id : 0);
        return $this->hasOne(StudentAward::class, 'student_id')->where('student_course_relation_id', $activeCRel)->latestOfMany();
    }
    
}
