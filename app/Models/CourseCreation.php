<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CourseQualification;

class CourseCreation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'semester_id',
        'course_id',
        'course_creation_qualification_id',
        'duration',
        'unit_length',
        'slc_code',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function course(){
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function semester(){
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function qualification(){
        return $this->belongsTo(CourseQualification::class, 'course_creation_qualification_id');
    }

    public function availability(){
        return $this->hasMany(CourseCreationAvailability::class, 'course_creation_id', 'id');
    }
}
