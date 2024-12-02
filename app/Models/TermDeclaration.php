<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermDeclaration extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [ "id" ];

    protected $fillable = [
        'academic_year_id',
        'name',
        'term_type_id',
        'start_date',
        'end_date',
        'total_teaching_weeks',
        'teaching_start_date',
        'teaching_end_date',
        'revision_start_date',
        'revision_end_date',
        'exam_publish_date',
        'exam_publish_time',
        'exam_resubmission_publish_date',
        'exam_resubmission_publish_time',
        'created_by',
        'updated_by',
    ];

    public function updatedBy(): HasOne 
    {
        return $this->hasOne(User::class);
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class);
    }
    
    public function termType(){
        return $this->belongsTo(TermType::class, 'term_type_id');
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

    public function installments() {
        return $this->hasMany(SlcInstallment::class, 'term_declaration_id', 'id');
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

    public function setExamPublishDateAttribute($value) {  
        $this->attributes['exam_publish_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getExamPublishDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setExamResubmissionPublishDateAttribute($value) {  
        $this->attributes['exam_resubmission_publish_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getExamResubmissionPublishDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }


}
