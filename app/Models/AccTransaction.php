<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AccTransaction extends Model
{
    use HasFactory, SoftDeletes;

    //protected $appends = ['doc_url'];

    protected $fillable = [
        'transaction_code',
        'audiotr_ansaction_code',
        'transaction_date',
        'transaction_date_2',
        'cheque_no',
        'cheque_date',
        'invoice_no',
        'invoice_date',
        'acc_category_id',
        'acc_bank_id',
        'acc_method_id',
        'transaction_type',
        'detail',
        'description',
        'new_description',
        'transaction_amount',
        'transaction_doc_name',
        'parent',
        'audit_status',
        'transfer_id',
        'transfer_type',
        'transfer_bank_id',
        'created_by',
        'updated_by',
    ];


    public function getDocUrlAttribute(){
        if($this->transaction_doc_name !== null && $this->transaction_doc_name != '' && Storage::disk('s3')->exists('public/transactions/'.$this->transaction_doc_name)) {
            return Storage::disk('s3')->url('public/transactions/'.$this->transaction_doc_name);
        }else{
            return '';
        }
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function category(){
        return $this->belongsTo(AccCategory::class, 'acc_category_id');
    }

    public function bank(){
        return $this->belongsTo(AccBank::class, 'acc_bank_id');
    }

    public function tbank(){
        return $this->belongsTo(AccBank::class, 'transfer_bank_id');
    }
}
