<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTraining extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'provider',
        'location',
        'start_date',
        'end_date',
        'cost',
        'expire_date',
        'employee_document_id',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function document() {
        return $this->belongsTo(EmployeeDocuments::class, 'employee_document_id');
    }

    public function employee() {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
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
    
    public function setExpireDateAttribute($value) {  
        $this->attributes['expire_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }
    
    public function getExpireDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
