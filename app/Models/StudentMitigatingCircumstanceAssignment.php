<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMitigatingCircumstanceAssignment extends Model
{
    use HasFactory;

    protected $table = 'student_mitigating_circumstance_assignments';
    protected $guarded = ['id'];
    /* Cast rather than $dates: Laravel 10 only honours $dates for models that
       still declare it alongside casts, and the deadline is read as a date in
       the ticket, the receipt and the confirmation page. */
    protected $casts = ['submission_date' => 'date'];

    public function request()
    {
        return $this->belongsTo(StudentMitigatingCircumstance::class, 'request_id');
    }
}
