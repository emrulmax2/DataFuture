<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SebastianBergmann\CodeCoverage\Report\Xml\Totals;

class UniversityPaymentClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['proforma_total', 'invoice_total'];

    protected $fillable = [
        'proforma_no',
        'invoice_no',
        'semester_id',
        'course_id',
        'vendor_id',
        'claim_date',
        'acc_bank_id',
        'claim_amount',
        'status',
        'invoiced_at',
        'invoiced_by',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function semester(){
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function course(){
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function bank(){
        return $this->belongsTo(AccBank::class, 'acc_bank_id');
    }

    public function term(){
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }

    public function invoiced(){
        return $this->belongsTo(User::class, 'invoiced_by');
    }

    public function installments(){
        return $this->hasMany(UniversityPaymentClaimStudent::class, 'university_payment_claim_id');
    }

    public function getProformaTotalAttribute(){
        $total = 0;
        if($this->installments->count() > 0):
            foreach($this->installments as $inst):
                $total += (isset($inst->installment->amount) && $inst->installment->amount > 0 ? $inst->installment->amount : 0);
            endforeach;
        endif;

        return $total;
    }

    public function getInvoiceTotalAttribute(){
        $total = 0;
        if($this->installments->count() > 0):
            foreach($this->installments as $inst):
                if($inst->status == 2):
                    $total += (isset($inst->installment->amount) && $inst->installment->amount > 0 ? $inst->installment->amount : 0);
                endif;
            endforeach;
        endif;

        return $total;
    }
}
