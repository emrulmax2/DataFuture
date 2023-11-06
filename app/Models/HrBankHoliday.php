<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrBankHoliday extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hr_holiday_year_id',
        'name',
        'start_date',
        'end_date',
        'duration',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
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

    public function year(){
        return $this->belongsTo(HrHolidayYear::class, 'hr_holiday_year_id');
    }
}
