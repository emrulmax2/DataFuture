<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMitigatingCircumstanceAbsence extends Model
{
    use HasFactory;

    protected $table = 'student_mitigating_circumstance_absences';
    protected $guarded = ['id'];

    protected $casts = [
        'absent_from' => 'date',
        'absent_to' => 'date',
    ];

    /** The dates as one phrase: a single day has no "to". */
    public function period(): string
    {
        $from = $this->absent_from?->format('j F Y') ?: 'Date not recorded';

        return $this->absent_to
            ? $from.' to '.$this->absent_to->format('j F Y')
            : $from;
    }

    public function request()
    {
        return $this->belongsTo(StudentMitigatingCircumstance::class, 'request_id');
    }
}
