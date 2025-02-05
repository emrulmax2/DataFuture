<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccTransactionTag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'acc_transaction_id',
        'registration_no'
    ];

    public function transaction(){
        return $this->belongsTo(AccTransaction::class, 'acc_transaction_id');
    }
}
