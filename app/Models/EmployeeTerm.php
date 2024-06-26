<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_notice_period_id',
        'employment_ssp_term_id',
        'employment_period_id',
        'provision_end'
    ];

    public function SSP() {
        return $this->belongsTo(EmploymentSspTerm::class, 'employment_ssp_term_id');
    }
    public function notice() {
        return $this->belongsTo(EmployeeNoticePeriod::class, 'employee_notice_period_id');
    }
    public function period() {
        return $this->belongsTo(EmploymentPeriod::class, 'employment_period_id');
    }

    public function setProvisionEndAttribute($value) {  
        $this->attributes['provision_end'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }
    
    public function getProvisionEndAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
