<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentOtherDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'ethnicity_id',
        'disability_status',
        'disabilty_allowance',
        'is_education_qualification',
        'employment_status',
        'college_introduction',
        'gender_identity',
        'sexual_orientation_id',
        'religion_id',
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

    public function ethnicity(){
        return $this->belongsTo(Ethnicity::class, 'ethnicity_id');
    }
}
