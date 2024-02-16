<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentWorkPlacement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'company_id',
        'company_supervisor_id',
        'start_date',
        'end_date',
        'hours',
        'contract_type',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function company(){
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function supervisor(){
        return $this->belongsTo(CompanySupervisor::class, 'company_supervisor_id');
    }

    public function setStartDateAttribute($value) {  
        $this->attributes['start_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getStartDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setEndDateAttribute($value) {  
        $this->attributes['end_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getEndDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function wbl(){
        return $this->hasMany(StudentWblProfile::class, 'student_work_placement_id', 'id');
    }
}
