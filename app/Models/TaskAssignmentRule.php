<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskAssignmentRule extends Model
{
    use SoftDeletes;

    public const TRIGGERS = [
        'application_submitted' => 'Application submitted',
        'student_created' => 'Student created',
        'student_status_changed' => 'Student status changed',
    ];

    /* Who a trigger's rules are about: applicant rules read applicant facts and assign
       Applicant-phase tasks; student rules read student facts and assign Live-phase tasks. */
    public const SUBJECT_APPLICANT = 'applicant';
    public const SUBJECT_STUDENT = 'student';

    public const TRIGGER_SUBJECTS = [
        'application_submitted' => self::SUBJECT_APPLICANT,
        'student_created' => self::SUBJECT_STUDENT,
        'student_status_changed' => self::SUBJECT_STUDENT,
    ];

    public static function subjectOf(?string $trigger): string
    {
        return self::TRIGGER_SUBJECTS[$trigger] ?? self::SUBJECT_APPLICANT;
    }

    /** trigger => label for one subject. */
    public static function triggersFor(string $subject): array
    {
        return array_filter(self::TRIGGERS, fn ($label, $key) => self::subjectOf($key) === $subject, ARRAY_FILTER_USE_BOTH);
    }

    protected $fillable = [
        'task_list_id', 'trigger', 'exclusive_group', 'priority', 'active', 'notes', 'after_assign_job', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'priority' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(TaskList::class, 'task_list_id');
    }

    public function conditions()
    {
        return $this->hasMany(TaskAssignmentRuleCondition::class, 'rule_id')->orderBy('sort_order');
    }

    public function scopeActive($q)
    {
        return $q->where('active', 1);
    }

    public function scopeForTrigger($q, string $trigger)
    {
        return $q->where('trigger', $trigger);
    }

    public function scopeForSubject($q, string $subject)
    {
        return $q->whereIn('trigger', array_keys(self::triggersFor($subject)));
    }
}
