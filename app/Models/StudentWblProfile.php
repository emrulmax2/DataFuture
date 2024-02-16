<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentWblProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_work_placement_id',
        'company_id',
        'weif_form_provided_date',
        'weif_form_provided_status',
        'received_completed_weif_form_date',
        'received_completed_weif_form_status',
        'work_hour_update_term_date',
        'work_hour_update_term_status',
        'work_exp_handbook_complete_date',
        'work_exp_handbook_complete_status',
        'work_exp_handbook_checked_date',
        'work_exp_handbook_checked_status',
        'emp_handbook_sent_date',
        'emp_handbook_sent_status',
        'emp_letter_sent_date',
        'emp_letter_sent_status',
        'emp_confirm_rec_date',
        'emp_confirm_rec_status',
        'company_visit_date',
        'company_visit_status',
        'record_std_meeting_date',
        'record_std_meeting_status',
        'record_all_contact_student_date',
        'record_all_contact_student_status',
        'email_sent_emp_date',
        'email_sent_emp_status',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function placement(){
        return $this->belongsTo(StudentWorkPlacement::class, 'student_work_placement_id');
    }

    public function company(){
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    
    
    public function setWeifFormProvidedDateAttribute($value) {  
        $this->attributes['weif_form_provided_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getWeifFormProvidedDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setReceivedCompletedWeifFormDateAttribute($value) {  
        $this->attributes['received_completed_weif_form_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getReceivedCompletedWeifFormDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setWorkHourUpdateTermDateAttribute($value) {  
        $this->attributes['work_hour_update_term_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getWorkHourUpdateTermDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setWorkExpHandbookCompleteDateAttribute($value) {  
        $this->attributes['work_exp_handbook_complete_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getWorkExpHandbookCompleteDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setWorkExpHandbookCheckedDateAttribute($value) {  
        $this->attributes['work_exp_handbook_checked_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getWorkExpHandbookCheckedDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setEmpHandbookSentDateAttribute($value) {  
        $this->attributes['emp_handbook_sent_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getEmpHandbookSentDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setEmpLetterSentDateAttribute($value) {  
        $this->attributes['emp_letter_sent_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getEmpLetterSentDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setEmpConfirmRecDateAttribute($value) {  
        $this->attributes['emp_confirm_rec_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getEmpConfirmRecDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setCompanyVisitDateAttribute($value) {  
        $this->attributes['company_visit_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getCompanyVisitDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setRecordStdMeetingDateAttribute($value) {  
        $this->attributes['record_std_meeting_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getRecordStdMeetingDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setRecordAllContactStudentDateAttribute($value) {  
        $this->attributes['record_all_contact_student_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getRecordAllContactStudentDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function setEmailSentEmpDateAttribute($value) {  
        $this->attributes['email_sent_emp_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getEmailSentEmpDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
