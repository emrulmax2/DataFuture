<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CourseQualification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseCreation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'semester_id',
        'course_id',
        'course_creation_qualification_id',
        'duration',
        'unit_length',
        'fees',
        'reg_fees',
        'is_workplacement',
        'has_evening_and_weekend',
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

    public function available(){
        return $this->hasOne(CourseCreationAvailability::class, 'course_creation_id', 'id')->latestOfMany();
    }

    public function instance(){
        return $this->hasMany(CourseCreationInstance::class, 'course_creation_id', 'id');
    }

    public function screl(){
        return $this->hasMany(StudentCourseRelation::class, 'course_creation_id', 'id');
    }

    public function venue(){
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function venues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class,'course_creation_venue', 'course_creation_id', 'venue_id')->withPivot('slc_code','id','deleted_at');
    }

}
