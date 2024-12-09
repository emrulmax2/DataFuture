<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function setStartDateAttribute($value) {  
        $this->attributes['start_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }
    
    public function getStartDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setEndDateAttribute($value) {  
        $this->attributes['end_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getEndDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function budget() {
        return $this->hasOne(BudgetSet::class, 'budget_year_id', 'id');
    }
}
