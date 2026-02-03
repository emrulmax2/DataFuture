<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCriminalConviction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'have_you_been_convicted',
        'criminal_conviction_details',
        'criminal_declaration',
        'created_by',
        'updated_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
