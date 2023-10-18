<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEmployment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'address_id',
        'company_name',
        'company_phone',
        'position',
        'start_date',
        'end_date',
        'continuing',
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
    
    public function reference(){
        return $this->hasMany(StudentEmploymentReference::class, 'student_employment_id', 'id');
    }
    
    public function address(){
        return $this->belongsTo(Address::class, 'address_id');
    }
}
