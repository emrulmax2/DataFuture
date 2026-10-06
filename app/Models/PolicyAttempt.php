<?php

namespace App\Models;

use App\Support\PolicyLevel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One sitting of a policy test. The questions drawn for it live in
 * policy_attempt_answers; the pass mark is copied here when it starts so a
 * later change to the policy does not rewrite this result. The level is
 * copied from the assignment too: it is the level of the test, which sets the
 * mix of question levels drawn (PolicyLevel::MIX).
 *
 * A timed attempt also copies the policy's time limit and fixes the moment
 * it runs out (expires_at). The clock is the server's: it keeps running if
 * the page is closed, and the attempt is marked when it reaches zero.
 */
class PolicyAttempt extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED = 'submitted';

    /** Seconds after the clock runs out that a submission is still marked: slack for the page's auto-submit to arrive. */
    const GRACE_SECONDS = 30;

    /** Seconds before the clock runs out from which an incomplete submission is accepted (a browser clock running slightly fast). */
    const EARLY_SECONDS = 5;

    protected $dates = ['deleted_at'];

    /** Matches the column default. */
    protected $attributes = [
        'level' => PolicyLevel::BEGINNER,
    ];

    protected $fillable = [
        'policy_assignment_id',
        'employee_id',
        'policy_document_id',
        'level',
        'attempt_no',
        'status',
        'started_at',
        'submitted_at',
        'total_questions',
        'correct_count',
        'score',
        'pass_mark',
        'passed',
        'time_limit_minutes',
        'expires_at',
        'timed_out',
        'tab_exits',
        'away_seconds',
        'ip_address',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'attempt_no' => 'integer',
        'total_questions' => 'integer',
        'correct_count' => 'integer',
        'pass_mark' => 'integer',
        'score' => 'decimal:2',
        'time_limit_minutes' => 'integer',
        'expires_at' => 'datetime',
        'timed_out' => 'boolean',
        'tab_exits' => 'integer',
        'away_seconds' => 'integer',
    ];

    public function assignment(){
        return $this->belongsTo(PolicyAssignment::class, 'policy_assignment_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function policy(){
        return $this->belongsTo(PolicyDocument::class, 'policy_document_id')->withTrashed();
    }

    public function answers(){
        return $this->hasMany(PolicyAttemptAnswer::class, 'policy_attempt_id', 'id')->orderBy('sort_order', 'ASC');
    }

    /** Each time the test tab or window was left during this attempt, in order. */
    public function events(){
        return $this->hasMany(PolicyAttemptEvent::class, 'policy_attempt_id', 'id')->orderBy('left_at', 'ASC')->orderBy('id', 'ASC');
    }

    /** The badge this attempt earned, if it was the assignment's first pass and the badge is still live. */
    public function awardedBadge(){
        return $this->hasOne(PolicyBadge::class, 'policy_attempt_id', 'id');
    }

    public function getLevelLabelAttribute(){
        return PolicyLevel::label($this->level);
    }

    /** A timed attempt has a moment its clock runs out. */
    public function isTimed(): bool
    {
        return $this->expires_at !== null;
    }

    /** Seconds left on the clock, never negative. NULL for an untimed attempt. */
    public function secondsRemaining(): ?int
    {
        if(!$this->isTimed()):
            return null;
        endif;

        return max(0, Carbon::now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * The clock is at (or within a few seconds of) zero, so a submission no
     * longer has to be complete: whatever is unanswered is marked wrong.
     */
    public function acceptsIncomplete(): bool
    {
        return ($this->isTimed() && Carbon::now()->gte($this->expires_at->copy()->subSeconds(self::EARLY_SECONDS)));
    }

    /**
     * The clock ran out long enough ago that nothing sent now can count —
     * the page auto-submits at zero, so an answer arriving after the grace
     * period was not given within the time allowed.
     */
    public function isPastGrace(): bool
    {
        return ($this->isTimed() && Carbon::now()->gt($this->expires_at->copy()->addSeconds(self::GRACE_SECONDS)));
    }
}
