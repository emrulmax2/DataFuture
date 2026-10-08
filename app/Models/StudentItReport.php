<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentItReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_it_reports';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function documents()
    {
        return $this->hasMany(StudentItReportDocument::class, 'request_id');
    }
}
