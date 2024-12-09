<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetRequisitionTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_requisition_id',
        'acc_transaction_id',

        'created_by',
        'updated_by',
    ];

    public function requisition(){
        return $this->belongsTo(BudgetRequisition::class, 'budget_requisition_id');
    }

    public function transaction(){
        return $this->belongsTo(AccTransaction::class, 'acc_transaction_id');
    }
}
