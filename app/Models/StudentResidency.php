<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentResidency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'residency_status_id',
        'created_by',
        'updated_by',
    ];

    public function residencyStatus()
    {
        return $this->belongsTo(ResidencyStatus::class, 'residency_status_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
