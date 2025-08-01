<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetRequisitionHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'budget_requisition_id',
        'approver',
        'status',
        'note',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function requisition(){
        return $this->belongsTo(BudgetRequisition::class, 'budget_requisition_id');
    }
}
