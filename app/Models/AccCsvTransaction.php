<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AccCsvTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['receipt_url'];

    protected $fillable = [
        'acc_csv_file_id',
        'trans_date',
        'description',
        'amount',
        'transaction_type',
        'flow',
        'has_receipts',
        'cto_receipt_name',
        'cto_receipt_error',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function files(){
        return $this->belongsTo(AccCsvFile::class, 'acc_csv_file_id');
    }

    public function getReceiptUrlAttribute(){
        if ($this->cto_receipt_name !== null && Storage::disk('local')->exists('public/receipts/'.$this->cto_receipt_name)) {
            return Storage::disk('local')->url('public/receipts/'.$this->cto_receipt_name);
        }

        return false;
    }
}
