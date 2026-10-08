<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentComplaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_complaints';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    /** Who they spoke to already, in words, for a ticket or a receipt. */
    public function spokenToLabel(): string
    {
        if (! $this->spoken_to_anyone) {
            return 'Not answered';
        }

        return $this->spoken_to_anyone === 'yes'
            ? 'Yes — '.($this->spoken_to_details ?: 'no details given')
            : 'No';
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function documents()
    {
        return $this->hasMany(StudentComplaintDocument::class, 'request_id');
    }
}
