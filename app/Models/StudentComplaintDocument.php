<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentComplaintDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_complaint_documents';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function request()
    {
        return $this->belongsTo(StudentComplaint::class, 'request_id');
    }
}
