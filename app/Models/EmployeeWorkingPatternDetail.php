<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWorkingPatternDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_working_pattern_id',
        'day',
        'day_name',
        'start',
        'end',
        'paid_br',
        'unpaid_br',
        'total',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    public function pattern(){
        return $this->belongsTo(EmployeeWorkingPattern::class, 'employee_working_pattern_id');
    }
}
