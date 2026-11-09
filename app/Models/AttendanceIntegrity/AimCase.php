<?php

namespace App\Models\AttendanceIntegrity;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** An investigation case. Its history is in aim_case_activities (append-only). */
class AimCase extends Model
{
    protected $table = 'aim_cases';

    protected $guarded = ['id'];

    public const STATUSES = [
        'New',
        'Under Review',
        'Awaiting Evidence',
        'Escalated',
        'Resolved – No Issue',
        'Resolved – Process Error',
        'Resolved – Substantiated',
        'Closed',
    ];

    /** Still being worked on. */
    public const OPEN_STATUSES = ['New', 'Under Review', 'Awaiting Evidence', 'Escalated'];

    public const SUBJECT_TYPES = ['staff' => 'Member of staff', 'student' => 'Student', 'class' => 'Class', 'other' => 'Other'];

    public function activities()
    {
        return $this->hasMany(AimCaseActivity::class, 'case_id')->orderBy('id');
    }

    public function evidenceFiles()
    {
        return $this->hasMany(AimCaseEvidence::class, 'case_id')->orderBy('id');
    }

    public function findings()
    {
        return $this->belongsToMany(AimFinding::class, 'aim_case_findings', 'case_id', 'finding_id');
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'assigned_investigator');
    }
}
