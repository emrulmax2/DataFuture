<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstanceTerm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_creation_instance_id',
        'term_declaration_id',
        'session_term',
        'start_date',
        'end_date',
        'total_teaching_weeks',
        'teaching_start_date',
        'teaching_end_date',
        'revision_start_date',
        'revision_end_date',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function instance(){
        return $this->belongsTo(CourseCreationInstance::class, 'course_creation_instance_id');
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

    public function setTeachingStartDateAttribute($value) {  
        $this->attributes['teaching_start_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getTeachingStartDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setTeachingEndDateAttribute($value) {  
        $this->attributes['teaching_end_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getTeachingEndDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setRevisionStartDateAttribute($value) {  
        $this->attributes['revision_start_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getRevisionStartDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setRevisionEndDateAttribute($value) {  
        $this->attributes['revision_end_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getRevisionEndDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function termDeclaration(){
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }

}
