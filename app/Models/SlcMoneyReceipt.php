<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcMoneyReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'course_creation_instance_id',
        'slc_agreement_id',
        'term_declaration_id',
        'session_term',
        'invoice_no',
        'slc_coursecode',
        'slc_payment_method_id',
        'entry_date',
        'payment_date',
        'amount',
        'discount',
        'payment_type',
        'remarks',
        'acc_transaction_id',
        'force_entry',
        'mailed_pdf_file',
        
        'received_by',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function agreement(){
        return $this->belongsTo(SlcAgreement::class, 'slc_agreement_id');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function crel(){
        return $this->belongsTo(StudentCourseRelation::class, 'student_course_relation_id');
    }

    public function received(){
        return $this->belongsTo(User::class, 'received_by');
    }

    public function method(){
        return $this->belongsTo(SlcPaymentMethod::class, 'slc_payment_method_id');
    }

    public function declaraton(){
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }

    public function setPaymentDateAttribute($value) {  
        $this->attributes['payment_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getPaymentDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setEntryDateAttribute($value) {  
        $this->attributes['entry_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getEntryDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function intallment() {
        return $this->hasOne(SlcInstallment::class, 'slc_money_receipt_id')->latestOfMany();
    }
}
