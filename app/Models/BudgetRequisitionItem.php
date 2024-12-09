<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetRequisitionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'budget_requisition_id',
        'description',
        'quantity',
        'price',
        'total',
        
        'active',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];
}
