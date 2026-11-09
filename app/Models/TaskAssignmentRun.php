<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignmentRun extends Model
{
    public const MODE_LIVE = 'live';
    public const MODE_DRY = 'dry_run';

    protected $fillable = [
        'applicant_id', 'student_id', 'trigger', 'mode', 'facts', 'evaluations', 'created_task_ids', 'tasks_created', 'created_by',
    ];

    protected $casts = [
        'facts' => 'array',
        'evaluations' => 'array',
        'created_task_ids' => 'array',
        'tasks_created' => 'integer',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
