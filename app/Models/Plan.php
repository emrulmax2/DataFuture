<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
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

    public function cCreation(){
        return $this->belongsTo(CourseCreation::class, 'course_creation_id');
    }
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

    public function plansDateList(){
        return $this->hasMany(PlansDateList::class, 'plan_id', 'id');
    }
    
    public function getPlanDayAttribute($value) {
        $day = '';
        if($this->sat == 1){
            $day = 'Sat';
        }elseif($this->sun == 1){
            $day = 'Sun';
        }elseif($this->mon == 1){
            $day = 'Mon';
        }elseif($this->tue == 1){
            $day = 'Tue';
        }elseif($this->wed == 1){
            $day = 'Wed';
        }elseif($this->thu == 1){
            $day = 'Thu';
        }elseif($this->fri == 1){
            $day = 'Fri';
        }

        return $day;
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
            $q->whereNull('attendance')->orWhere('attendance', 1);
        });
    }

    public function tasks(){
        return $this->hasMany(PlanTask::class, 'plan_id', 'id');
    }

    public function attendance(){
        return $this->hasMany(Attendance::class, 'plan_id', 'id');
    }

    public function attendances(){
        return $this->hasMany(Attendance::class, 'plan_id', 'id');
    }

    public function results(){
        return $this->hasMany(Result::class, 'plan_id', 'id');
    }

    public function tutorial(){
        return $this->hasOne(Plan::class, 'parent_id', 'id')->where('class_type', 'Tutorial');//->latestOfMany();
    }

    public function theory(){
        return $this->belongsTo(Plan::class, 'parent_id');
    }

    public function getGeneratedDayMatchAttribute(){
        $planDay = '';
        if($this->sat == 1){
            $planDay = 'Sat';
        }elseif($this->sun == 1){
            $planDay = 'Sun';
        }elseif($this->mon == 1){
            $planDay = 'Mon';
        }elseif($this->tue == 1){
            $planDay = 'Tue';
        }elseif($this->wed == 1){
            $planDay = 'Wed';
        }elseif($this->thu == 1){
            $planDay = 'Thu';
        }elseif($this->fri == 1){
            $planDay = 'Fri';
        }

        $generateDays = PlansDateList::where('plan_id', $this->id)->get();
        if($generateDays->count() > 0 && $planDay != ''){
            $matchCount = 0;
            foreach($generateDays as $days):
                $classDay = date('D', strtotime($days->date));
                if($planDay == $classDay):
                    $matchCount += 1;
                endif;
            endforeach;
            return $matchCount > 0 ? true : false;
        }else{
            return true;
        }
    }
    
}
