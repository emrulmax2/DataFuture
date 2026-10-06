<?php

namespace App\Models;

use App\Support\PolicyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A badge a member of staff earned by passing a policy test at a level:
 * bronze for Beginner, silver for Intermediate, gold for Expert.
 *
 * Awarded once, on an assignment's first pass (PolicyAssessmentService::
 * submitAttempt), and kept when that assignment is later archived — the
 * badge is the lasting record. At most one live badge per (employee, policy,
 * level), enforced in the service. HR revokes a badge by soft-deleting it
 * (revoked_by says who) and can restore it.
 */
class PolicyBadge extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'employee_id',
        'policy_document_id',
        'level',
        'policy_assignment_id',
        'policy_attempt_id',
        'score',
        'awarded_at',
        'revoked_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function policy(){
        return $this->belongsTo(PolicyDocument::class, 'policy_document_id')->withTrashed();
    }

    /** The assignment it was earned on — kept even after that assignment is archived. */
    public function assignment(){
        return $this->belongsTo(PolicyAssignment::class, 'policy_assignment_id')->withTrashed();
    }

    /** The attempt that passed. */
    public function attempt(){
        return $this->belongsTo(PolicyAttempt::class, 'policy_attempt_id')->withTrashed();
    }

    public function revokedBy(){
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /** Badges at one level; an unknown level matches nothing. */
    public function scopeOfLevel($query, ?string $level){
        return $query->where('policy_badges.level', (PolicyLevel::isValid($level) ? $level : '__none__'));
    }

    public function getLevelLabelAttribute(){
        return PolicyLevel::label($this->level);
    }

    /** 'Bronze', 'Silver' or 'Gold'. */
    public function getBadgeNameAttribute(){
        return PolicyLevel::badgeName((string) $this->level);
    }
}
