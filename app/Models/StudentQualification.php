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
        'qualification_grade_id',
        'degree_award_date',
        'created_by',
        'updated_by',
        'highest_qualification_on_entry_id',
        'hesa_qualification_subject_id',
        'qualification_type_identifier_id',
        'previous_provider_id',
        'hesa_exam_sitting_venue_id',
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
    public function highest_qualification_on_entries(){
        return $this->belongsTo(HighestQualificationOnEntry::class, 'highest_qualification_on_entry_id');
    }

    public function hesa_qualification_subjects(){
        return $this->belongsTo(HesaQualificationSubject::class, 'hesa_qualification_subject_id');
    }

    public function qualification_type_identifiers(){
        return $this->belongsTo(QualificationTypeIdentifier::class, 'qualification_type_identifier_id');
    }

    public function previous_providers(){
        return $this->belongsTo(PreviousProvider::class, 'previous_provider_id');
    }
    
    public function setDegreeAwardDateAttribute($value) {  
        $this->attributes['degree_award_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    public function getDegreeAwardDateAttribute($value) {
        return (!empty($value) && $value != '0000-00-00' ? date('d-m-Y', strtotime($value)) : '');
    }

    public function grade(){
        return $this->belongsTo(QualificationGrade::class, 'qualification_grade_id');
    }
}
