<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employment extends Model
{
    use HasFactory,SoftDeletes;
    

    protected $fillable = [
        'employee_id',
        'started_on',
        'ended_on',
        'punch_number',
        'employee_work_type_id',
        'works_number',
        'employee_job_title_id',
        'department_id',
        'office_telephone',
        'mobile',
        'email',
        
    ];


    public function employee() {
        
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
    public function employeeWorkType() {
        
        return $this->belongsTo(EmployeeWorkType::class, 'employee_work_type_id');
    }
    
    public function employeeJobTitle() {

        return $this->belongsTo(EmployeeJobTitle::class, 'employee_job_title_id');

    }
    public function department() {

        return $this->belongsTo(Department::class, 'department_id');
    }
    

    protected $dates = ['deleted_at'];

    protected $hidden = ['created_at', 'updated_at'];

    public function setStartedOnAttribute($value) {  

        $this->attributes['started_on'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    
    public function getStartedOnAttribute($value) {

        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
