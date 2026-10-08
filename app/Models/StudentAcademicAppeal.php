<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAcademicAppeal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_academic_appeals';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    /** The grounds as the policy words them. */
    public const GROUNDS = [
        'mitigating_circumstances' => 'Mitigating circumstances',
        'procedural_irregularity' => 'Procedural irregularity / unfair conduct of assessment',
    ];

    public const EVIDENCE_METHODS = [
        'upload' => 'Uploaded with the form',
        'email' => 'To be sent to registry@lcc.ac.uk',
        'none' => 'No documents to send',
    ];

    public function groundsLabel(): string
    {
        return self::GROUNDS[$this->grounds] ?? 'Not stated';
    }

    public function evidenceMethodLabel(): string
    {
        return self::EVIDENCE_METHODS[$this->evidence_method] ?? 'Not stated';
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function documents()
    {
        return $this->hasMany(StudentAcademicAppealDocument::class, 'request_id');
    }
}
