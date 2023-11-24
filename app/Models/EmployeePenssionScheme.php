<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePenssionScheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'employee_info_penssion_scheme_id',
        'joining_date',
        'date_left',
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
    public function penssion(){
        return $this->belongsTo(EmployeeInfoPenssionScheme::class, 'employee_info_penssion_scheme_id');
    }

    public function setJoiningDateAttribute($value) {  
        $this->attributes['joining_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getJoiningDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setDateLeftAttribute($value) {  
        $this->attributes['date_left'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getDateLeftAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
