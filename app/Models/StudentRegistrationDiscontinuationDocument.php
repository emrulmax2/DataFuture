<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentRegistrationDiscontinuationDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_registration_discontinuation_documents';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function request()
    {
        return $this->belongsTo(StudentRegistrationDiscontinuationRequest::class, 'request_id');
    }
}
