<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetSet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'budget_year_id',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function year(){
        return $this->belongsTo(BudgetYear::class, 'budget_year_id');
    }

    public function details(){
        return $this->hasMany(BudgetSetDetail::class, 'budget_set_id', 'id');
    }
}
