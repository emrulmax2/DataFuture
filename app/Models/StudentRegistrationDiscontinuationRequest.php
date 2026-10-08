<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentRegistrationDiscontinuationRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_registration_discontinuation_requests';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function documents()
    {
        return $this->hasMany(StudentRegistrationDiscontinuationDocument::class, 'request_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
