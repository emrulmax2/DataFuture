<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAwardingBodyDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_course_relation_id',
        'reference',
        'course_code',
        'registration_date',
        'registration_expire_date',
        'registration_document_verified',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function setRegistrationDateAttribute($value) {  
        $this->attributes['registration_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getRegistrationDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setRegistrationExpireDateAttribute($value) {  
        $this->attributes['registration_expire_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    public function getRegistrationExpireDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function studentcrel(){
        return $this->belongsTo(StudentCourseRelation::class, 'student_course_relation_id');
    }
}
