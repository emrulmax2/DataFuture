<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWorkingPattern extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'effective_from',
        'end_to',
        'contracted_hour',
        'active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function setEffectiveFromAttribute($value) {  
        $this->attributes['effective_from'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getEffectiveFromAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setEndToAttribute($value) {  
        $this->attributes['end_to'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getEndToAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function patterns(){
        return $this->hasMany(EmployeeWorkingPatternDetail::class, 'employee_working_pattern_id', 'id');
    }

    public function pays(){
        return $this->hasMany(EmployeeWorkingPatternPay::class, 'employee_working_pattern_id', 'id');
    }
}
