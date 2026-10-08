<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentMitigatingCircumstanceDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_mitigating_circumstance_documents';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function request()
    {
        return $this->belongsTo(StudentMitigatingCircumstance::class, 'request_id');
    }
}
