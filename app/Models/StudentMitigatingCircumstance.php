<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentMitigatingCircumstance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_mitigating_circumstances';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $casts = ['reasons' => 'array'];

    /** The reasons as the policy words them, keyed by what the form posts. */
    public const REASONS = [
        'personal_illness' => 'A personal illness',
        'bereavement' => 'Bereavement (near relative only)',
        'domestic_personal_problems' => 'Domestic and/or personal problems',
        'victim_of_serious_crime' => 'Victim of a serious crime',
        'other' => 'Other',
    ];

    public const EVIDENCE_METHODS = [
        'upload' => 'Uploaded with the form',
        'email' => 'To be sent to registry@lcc.ac.uk',
        'none' => 'No document to send',
    ];

    /**
     * The reasons in words, with the student's own wording where they picked
     * "Other" — the label on its own tells the panel nothing.
     *
     * @return array<int, string>
     */
    public function reasonLabels(): array
    {
        return collect($this->reasons ?? [])
            ->map(fn ($reason) => $reason === 'other'
                ? 'Other: '.($this->other_reason ?: 'not described')
                : (self::REASONS[$reason] ?? $reason))
            ->all();
    }

    public function evidenceMethodLabel(): string
    {
        return self::EVIDENCE_METHODS[$this->evidence_method] ?? 'Not stated';
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /** Attendance claims list days; assignment claims list deadlines. */
    public function absences()
    {
        return $this->hasMany(StudentMitigatingCircumstanceAbsence::class, 'request_id');
    }

    public function isAttendance(): bool
    {
        return $this->claim_type === 'attendance';
    }

    public function assignments()
    {
        return $this->hasMany(StudentMitigatingCircumstanceAssignment::class, 'request_id');
    }

    public function documents()
    {
        return $this->hasMany(StudentMitigatingCircumstanceDocument::class, 'request_id');
    }
}
