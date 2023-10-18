<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentQualification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'awarding_body',
        'highest_academic',
        'subjects',
        'result',
        'degree_award_date',
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

    public function setDegreeAwardDateAttribute($value) {  
        $this->attributes['degree_award_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getDegreeAwardDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
