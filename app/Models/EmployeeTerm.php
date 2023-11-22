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
}
