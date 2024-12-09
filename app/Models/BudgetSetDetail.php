<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetSetDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'budget_set_id',
        'budget_name_id',
        'amount',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function set(){
        return $this->belongsTo(BudgetSet::class, 'budget_set_id');
    }

    public function names(){
        return $this->belongsTo(BudgetName::class, 'budget_name_id');
    }
}
