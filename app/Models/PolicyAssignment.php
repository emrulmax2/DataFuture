<?php

namespace App\Models;

use App\Models\Relations\LivePolicyBadge;
use App\Support\PolicyLevel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * "This member of staff must pass this policy at this level." At most one
 * live row per (employee, policy, level) — PolicyAssessmentService::assign()
 * enforces that, since a unique index would also count soft-deleted rows. The
 * same policy can be held at several levels at once; each is its own test,
 * drawn as that level's mix of questions (PolicyLevel::MIX), and its first
 * pass earns that level's badge (see badge()).
 *
 * Only three states are stored (pending, failed, passed). What staff and HR
 * see — in progress, overdue, no attempts left — is derived in
 * display_status, so it can never drift from the underlying facts.
 */
class PolicyAssignment extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_FAILED = 'failed';
    const STATUS_PASSED = 'passed';

    const DISPLAY_LABELS = [
        'passed' => 'Passed',
        'locked' => 'No attempts left',
        'in_progress' => 'In progress',
        'overdue' => 'Overdue',
        'failed' => 'Failed – retake available',
        'pending' => 'Not started',
    ];

    protected $dates = ['deleted_at'];

    /** Matches the column default, so a new row reads the same before and after saving. */
    protected $attributes = [
        'level' => PolicyLevel::BEGINNER,
    ];

    protected $fillable = [
        'employee_id',
        'policy_document_id',
        'level',
        'policy_role_id',
        'assigned_by',
        'assigned_at',
        'due_date',
        'status',
        'attempts_count',
        'extra_attempts',
        'best_score',
        'last_score',
        'last_attempt_at',
        'passed_at',
        'policy_opened_at',
        'acknowledged_at',
        'last_reminded_at',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'assigned_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'passed_at' => 'datetime',
        'policy_opened_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'last_reminded_at' => 'datetime',
        'best_score' => 'decimal:2',
        'last_score' => 'decimal:2',
        'attempts_count' => 'integer',
        'extra_attempts' => 'integer',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function policy(){
        return $this->belongsTo(PolicyDocument::class, 'policy_document_id')->withTrashed();
    }

    public function assignedBy(){
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * The policy role this was assigned through; NULL when HR picked the
     * policy by hand. Still resolves after the role is archived — it is a
     * record of how the assignment was made, not a live link to the role.
     */
    public function role(){
        return $this->belongsTo(PolicyRole::class, 'policy_role_id')->withTrashed();
    }

    /**
     * The live badge this member of staff holds for this policy at this
     * level — whichever assignment earned it, so it is still found after the
     * assignment that earned it was archived and a new one created. NULL when
     * none has been earned or it was revoked. Eager-loads and whereHas()
     * correctly on all three columns.
     */
    public function badge(){
        return new LivePolicyBadge((new PolicyBadge())->newQuery(), $this);
    }

    public function attempts(){
        return $this->hasMany(PolicyAttempt::class, 'policy_assignment_id', 'id')->orderBy('attempt_no', 'DESC');
    }

    /**
     * The attempt currently being sat, if any. The status constraint sits
     * inside the one-of-many subquery so it picks the newest *open* attempt,
     * not the newest attempt that then happens to be open.
     */
    public function openAttempt(){
        return $this->hasOne(PolicyAttempt::class, 'policy_assignment_id', 'id')->ofMany(['id' => 'max'], function ($query) {
            $query->where('status', PolicyAttempt::STATUS_IN_PROGRESS);
        });
    }

    /**
     * A live assignment: not archived itself (SoftDeletes), on a policy that
     * is not deleted and is switched on, in a category that is not archived.
     * The one definition behind the staff dashboard count, the reminder cron,
     * the My HR tab and the staff list, so they cannot disagree.
     */
    public function scopeLive($query)
    {
        return $query->whereHas('policy', function ($q) {
            $q->whereNull('policy_documents.deleted_at')
                ->where('policy_documents.is_active', 1)
                ->whereHas('category');
        });
    }

    /**
     * What My HR › Policy Assessments lists: every live assignment, plus a
     * pass on a policy HR has since switched off, which stays visible as a
     * record (the policy and its category must still exist).
     */
    public function scopeVisibleToStaff($query)
    {
        return $query->where(function ($q) {
            $q->live()->orWhere(function ($passed) {
                $passed->where('policy_assignments.status', self::STATUS_PASSED)
                    ->whereHas('policy', function ($p) {
                        $p->whereNull('policy_documents.deleted_at')->whereHas('category');
                    });
            });
        });
    }

    /** NULL when the policy allows unlimited attempts. */
    public function maxAttemptsAllowed(): ?int
    {
        $policy = $this->policy;
        if(!$policy || $policy->max_attempts === null):
            return null;
        endif;

        return (int) $policy->max_attempts + (int) $this->extra_attempts;
    }

    /** NULL means unlimited. Never negative. */
    public function attemptsLeft(): ?int
    {
        $max = $this->maxAttemptsAllowed();
        if($max === null):
            return null;
        endif;

        return max(0, $max - (int) $this->attempts_count);
    }

    public function isOverdue(): bool
    {
        if($this->status == self::STATUS_PASSED || !$this->due_date):
            return false;
        endif;

        return $this->due_date->lt(Carbon::today());
    }

    /**
     * Whether a new test can be started. A passed assignment is final; the
     * policy (and its category) must still be live, with enough active
     * questions at every level to fill this assignment's test pattern (see
     * PolicyDocument::examReady()); and attempts must remain.
     *
     * Resuming an attempt already in progress is the caller's call — see the
     * staff take() action.
     */
    public function canAttempt(): bool
    {
        if($this->status == self::STATUS_PASSED):
            return false;
        endif;

        $policy = $this->policy;
        if(!$policy || $policy->trashed() || !$policy->is_active):
            return false;
        endif;
        if(!$policy->category):
            return false;
        endif;
        if(!$policy->examReady($this->levelKey())):
            return false;
        endif;

        $left = $this->attemptsLeft();

        return ($left === null || $left > 0);
    }

    public function hasOpenAttempt(): bool
    {
        if($this->relationLoaded('openAttempt')):
            return $this->openAttempt !== null;
        endif;

        return $this->attempts()->where('status', PolicyAttempt::STATUS_IN_PROGRESS)->exists();
    }

    /**
     * passed, locked, in_progress, overdue, failed or pending — checked in that
     * order, so an overdue test someone is halfway through reads as in
     * progress, and a failed one with no attempts left reads as locked.
     */
    public function getDisplayStatusAttribute(){
        if($this->status == self::STATUS_PASSED):
            return 'passed';
        endif;
        if($this->status == self::STATUS_FAILED && $this->attemptsLeft() === 0):
            return 'locked';
        endif;
        if($this->hasOpenAttempt()):
            return 'in_progress';
        endif;
        if($this->isOverdue()):
            return 'overdue';
        endif;
        if($this->status == self::STATUS_FAILED):
            return 'failed';
        endif;

        return 'pending';
    }

    /** The stored level, with a row that predates levels read as Beginner. */
    public function levelKey(): string
    {
        return (isset($this->level) && $this->level !== '' ? (string) $this->level : PolicyLevel::BEGINNER);
    }

    public function getLevelLabelAttribute(){
        return PolicyLevel::label($this->levelKey());
    }

    /** Assignments at one level; an unknown level matches nothing. */
    public function scopeOfLevel($query, ?string $level){
        return $query->where('policy_assignments.level', (PolicyLevel::isValid($level) ? $level : '__none__'));
    }

    public function getDisplayStatusLabelAttribute(){
        $status = $this->display_status;

        return (isset(self::DISPLAY_LABELS[$status]) ? self::DISPLAY_LABELS[$status] : 'Not started');
    }
}
