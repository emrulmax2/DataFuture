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
        'company_name',
        'company_phone',
        'position',
        'start_date',
        'end_date',
        'continuing',
        'address_line_1',
        'address_line_2',
        'state',
        'post_code',
        'city',
        'country',
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
        return $this->hasMany(EmploymentReference::class, 'student_employment_id', 'id');
    }
}
