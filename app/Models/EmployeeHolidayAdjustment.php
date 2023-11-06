<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeHolidayAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'hr_holiday_year_id',
        'employee_working_pattern_id',
        'operator',
        'hours',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function year(){
        return $this->belongsTo(HrHolidayYear::class, 'hr_holiday_year_id');
    }

    public function pattern(){
        return $this->belongsTo(EmployeeWorkingPattern::class, 'employee_working_pattern_id');
    }
}
