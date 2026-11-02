<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** /slc-registration › Bulk payment upload: one payment line of a remittance file and what became of it. */
class SlcPaymentUploadRow extends Model
{
    protected $fillable = ['slc_payment_upload_id', 'slc_payment_history_id', 'line_no', 'raw', 'data', 'ssn', 'amount', 'student_id', 'match_method', 'linked_by', 'linked_at',
        'chosen_agreement_id', 'agreement_by', 'agreement_at', 'skip_reason', 'skipped_by', 'skipped_at', 'slc_money_receipt_id', 'slc_installment_id', 'recorded_by', 'recorded_at'];

    protected $casts = ['data' => 'array', 'linked_at' => 'datetime', 'agreement_at' => 'datetime', 'skipped_at' => 'datetime', 'recorded_at' => 'datetime'];

    public function upload()
    {
        return $this->belongsTo(SlcPaymentUpload::class, 'slc_payment_upload_id');
    }
}
