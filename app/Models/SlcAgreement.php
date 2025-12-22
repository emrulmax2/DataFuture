<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcAgreement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'slc_registration_id',
        'slc_coursecode',
        'is_self_funded',
        'date',
        'year',
        'commission_amount',
        'fees',
        'no_of_installment',
        'discount',
        'total',
        'note',
        'has_due',

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

    public function scr(){
        return $this->belongsTo(StudentCourseRelation::class, 'student_course_relation_id');
    }

    public function instance(){
        return $this->belongsTo(CourseCreationInstance::class, 'course_creation_instance_id');
    }

    public function setDateAttribute($value) {  
        $this->attributes['date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function installments(){
        return $this->hasMany(SlcInstallment::class, 'slc_agreement_id', 'id');
    }

    public function registration(){
        return $this->belongsTo(SlcRegistration::class, 'slc_registration_id');
    }

    public function payments(){
        return $this->hasMany(SlcMoneyReceipt::class, 'slc_agreement_id', 'id');
    }

    public function getClaimAmountAttribute(){
        $claimAmount = 0;
        if(isset($this->installments) && $this->installments->count() > 0):
            foreach($this->installments as $inst):
                $claimAmount += $inst->amount;
            endforeach;
        endif;

        return $claimAmount;
    }

    public function getReceivedAmountAttribute(){
        $receivedAmount = 0;
        if(isset($this->payments) && $this->payments->count() > 0):
            foreach($this->payments as $pay):
                if($pay->payment_type == 'Refund'):
                    $receivedAmount -= $pay->amount;
                else:
                    $receivedAmount += $pay->amount;
                endif;
            endforeach;
        endif;

        return $receivedAmount;
    }

    public function getOnlyReceivedAmountAttribute(){
        $receivedAmount = 0;
        if(isset($this->payments) && $this->payments->count() > 0):
            foreach($this->payments as $pay):
                if($pay->payment_type != 'Refund'):
                    $receivedAmount += $pay->amount;
                endif;
            endforeach;
        endif;

        return $receivedAmount;
    }

    public function getRefundAmountAttribute(){
        $refundAmount = 0;
        if(isset($this->payments) && $this->payments->count() > 0):
            foreach($this->payments as $pay):
                if($pay->payment_type == 'Refund'):
                    $refundAmount += $pay->amount;
                endif;
            endforeach;
        endif;

        return $refundAmount;
    }

    public function getClaimTillTodayAttribute(){
        $claimAmount = 0;
        if(isset($this->installments) && $this->installments->count() > 0):
            foreach($this->installments as $inst):
                if(!empty($inst->installment_date) && $inst->installment_date <= date('Y-m-d')):
                    $claimAmount += $inst->amount;
                endif;
            endforeach;
        endif;

        return $claimAmount;
    }

    public function getDueToDateAttribute(){
        return ($this->received_amount - $this->claim_till_today);
    }
}
