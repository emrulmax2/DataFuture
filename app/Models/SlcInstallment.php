<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'slc_attendance_id',
        'slc_agreement_id',
        'installment_date',
        'amount',
        'term',
        'session_term',
        'attendance_term',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agreement(){
        return $this->belongsTo(SlcAgreement::class, 'slc_agreement_id');
    }

    public function attendance(){
        return $this->belongsTo(SlcAttendance::class, 'slc_attendance_id');
    }

    public function setInstallmentDateAttribute($value) {  
        $this->attributes['installment_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getInstallmentDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
