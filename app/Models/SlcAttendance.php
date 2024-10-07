<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'course_relation_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'slc_registration_id',
        'confirmation_date',
        'attendance_year',
        'term_declaration_id',
        'session_term',
        'attendance_code_id',
        'note',
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

    public function currentClaimAmount(){
        return $this->hasOne(SlcInstallment::class, 'slc_attendance_id', 'id');
    }
    public function registration(){
        return $this->belongsTo(SlcRegistration::class, 'slc_registration_id');
    }
    public function crel(){
        return $this->belongsTo(StudentCourseRelation::class, 'student_course_relation_id');
    }
    
    public function code(){
        return $this->belongsTo(AttendanceCode::class, 'attendance_code_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function term(){
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }

    public function coc(){
        return $this->hasMany(SlcCoc::class, 'slc_attendance_id', 'id');
    }

    public function setConfirmationDateAttribute($value) {  
        $this->attributes['confirmation_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getConfirmationDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function installment(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
