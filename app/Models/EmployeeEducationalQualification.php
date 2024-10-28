<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeEducationalQualification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'highest_qualification_on_entry_id',
        'qualification_name',
        'award_body',
        'award_date',

        'created_by',
        'updated_by',
    ];


    protected $dates = ['deleted_at'];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function setAwardDateAttribute($value) {  
        $this->attributes['award_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getAwardDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
