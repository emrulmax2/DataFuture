<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'module_creation_id',
        'instance_term_id',
        'academic_year_id',
        'course_creation_id',
        'term_declaration_id',
        'venue_id',
        'rooms_id',
        'group_id',
        'name',
        'start_time',
        'end_time',
        'label',
        'sat',
        'sun',
        'mon',
        'tue',
        'wed',
        'thu',
        'fri',
        'module_enrollment_key',
        'submission_date',
        'tutor_id',
        'personal_tutor_id',
        'class_type',
        'virtual_room',
        'note',
        'created_by',
        'updated_by'
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

    public function creations(){
        return $this->belongsTo(ModuleCreation::class, 'module_creation_id');
    }

    public function venu(){
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function room(){
        return $this->belongsTo(Room::class, 'rooms_id');
    }

    public function group(){
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function tutor(){
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function personalTutor(){
        return $this->belongsTo(User::class, 'personal_tutor_id');
    }

    public function dates(){
        return $this->hasMany(PlansDateList::class, 'plan_id', 'id');
    }

    public function setSubmissionDateAttribute($value) {  
        $this->attributes['submission_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getSubmissionDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function attenTerm(){
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }

    public function assign(){
        return $this->hasMany(Assign::class, 'plan_id', 'id');
    }

    public function activeAssign(){
        return $this->hasMany(Assign::class, 'plan_id', 'id')->where(function($q){
            $q->whereNull('attendance')->orWhere('attendance', 1)->orWhere('attendance', '');
        });
    }

    public function tasks(){
        return $this->hasMany(PlanTask::class, 'plan_id', 'id');
    }
    
}
