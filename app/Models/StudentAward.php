<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAward extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'date_of_award',
        'qual_award_result_id',
        'certificate_requested',
        'date_of_certificate_requested',
        'certificate_requested_by',
        'certificate_received',
        'date_of_certificate_received',
        'certificate_released',
        'date_of_certificate_released',
        'certificate_released_by',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function qual(){
        return $this->belongsTo(QualAwardResult::class, 'qual_award_result_id');
    }

    public function requested(){
        return $this->belongsTo(User::class, 'certificate_requested_by');
    }

    public function released(){
        return $this->belongsTo(User::class, 'certificate_released_by');
    }

    public function setDateOfAwardAttribute($value) {  
        $this->attributes['date_of_award'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getDateOfAwardAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setDateOfCertificateRequestedAttribute($value) {  
        $this->attributes['date_of_certificate_requested'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getDateOfCertificateRequestedAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : null);
    }

    public function setDateOfCertificateReceivedAttribute($value) {  
        $this->attributes['date_of_certificate_received'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getDateOfCertificateReceivedAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : null);
    }

    public function setDateOfCertificateReleasedAttribute($value) {  
        $this->attributes['date_of_certificate_released'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getDateOfCertificateReleasedAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : null);
    }
}
