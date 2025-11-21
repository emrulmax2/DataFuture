<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniversityPaymentClaimStudent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'university_payment_claim_id',
        'student_id',
        'slc_installment_id',
        'status',
    ];

    public function claim(){
        return $this->belongsTo(UniversityPaymentClaim::class, 'university_payment_claim_id');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function installment(){
        return $this->belongsTo(SlcInstallment::class, 'slc_installment_id');
    }

}
