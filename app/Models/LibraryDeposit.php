<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The library's money ledger: the refundable bond, and fines taken at the desk.
 *
 * One row per attempt, not one per student: an abandoned checkout stays as
 * `pending` so a support query can see it, and the student simply starts a new
 * one. `heldFor()` is what the portal asks about.
 *
 * `type` separates the two kinds. A fine row records the *payment* — what was
 * tendered, when and by whom; the *charge* stays on `library_book_issues`,
 * because that is a property of the loan running late, not of any money
 * changing hands.
 */
class LibraryDeposit extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_FINE = 'fine';

    protected $table = 'library_deposits';

    protected $fillable = [
        'student_id', 'type', 'library_book_issue_id', 'amount', 'currency',
        'status', 'description', 'provider', 'provider_order_id',
        'provider_capture_id', 'provider_refund_id', 'payer_email',
        'collected_by', 'paid_at', 'refunded_at', 'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /** The loan a fine row settles. Null on a deposit. */
    public function issue()
    {
        return $this->belongsTo(LibraryBookIssue::class, 'library_book_issue_id');
    }

    public function scopeDeposits($query)
    {
        return $query->where('type', self::TYPE_DEPOSIT);
    }

    public function scopeFines($query)
    {
        return $query->where('type', self::TYPE_FINE);
    }

    public function isFine(): bool
    {
        return $this->type === self::TYPE_FINE;
    }

    /**
     * The deposit currently being held, if any.
     *
     * Scoped to deposits explicitly. Now that fines share the table, an
     * unscoped "latest paid row" would read a settled overdue charge as a bond
     * and let a student borrow without leaving one.
     */
    public static function heldFor($studentId): ?self
    {
        return static::where('student_id', $studentId)
            ->deposits()
            ->where('status', 'paid')
            ->latest('paid_at')
            ->first();
    }
}
