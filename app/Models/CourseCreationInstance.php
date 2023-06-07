<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCreationInstance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_creation_id',
        'academic_year_id',
        'start_date',
        'end_date',
        'total_teaching_week',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function creation(){
        return $this->belongsTo(CourseCreation::class, 'course_creation_id');
    }

    public function year(){
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
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

    public function terms(){
        return $this->hasMany(InstanceTerm::class, 'course_creation_instance_id', 'id');
    }
}
