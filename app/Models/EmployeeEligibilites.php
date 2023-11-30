<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEligibilites extends Model
{
    use HasFactory;
    protected $guard = ['id'];
    
    protected $fillable = [
        'employee_id',
        'eligible_to_work',
        'employee_work_permit_type_id',
        'workpermit_number',
        'workpermit_expire',
        'document_type',
        'doc_number',
        'doc_expire',
        'doc_issue_country',
    ];
    

    public function setWorkpermitExpireAttribute($value) {  
        $this->attributes['workpermit_expire'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }
    public function getWorkpermitExpireAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function employee() {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function employeeWorkPermitType() {
        return $this->belongsTo(EmployeeWorkPermitType::class, 'employee_work_permit_type_id');
    }

    
    public function employeeDocType() {
        return $this->belongsTo(EmployeeWorkDocumentType::class, 'document_type');
    }
    
    public function docIssueCountry() {
        return $this->belongsTo(Country::class, 'doc_issue_country');
    }

}
