<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One borrow: raised by the student, acted on by library staff.
 *
 * requested -> issued -> returned, with cancelled and not_collected as exits.
 * The due date is set at issue, not at request — the loan clock must not run
 * while a book sits on the hold shelf waiting to be collected.
 */
class LibraryBookIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'library_book_issues';

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NOT_COLLECTED = 'not_collected';

    /** Still holding a copy off the shelf, whether collected or not. */
    public const OPEN_STATUSES = [self::STATUS_REQUESTED, self::STATUS_ISSUED];

    public const TYPE_TAKE_HOME = 'take_home';
    public const TYPE_DAY_READING = 'day_reading';

    protected $fillable = [
        'reference', 'student_id', 'loan_type', 'ops_title_id', 'ops_copy_id', 'barcode',
        'title', 'author', 'isbn13', 'cover_url', 'campus', 'location', 'book_price',
        'status', 'requested_at', 'issued_at', 'due_at', 'returned_at',
        'renewals', 'fine_amount', 'fine_paid_at', 'created_by', 'updated_by',
        'expires_at', 'issued_by', 'returned_by', 'cancelled_by', 'cancel_reason', 'staff_note',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'expires_at' => 'datetime',
        'issued_at' => 'datetime',
        'due_at' => 'date',
        'returned_at' => 'datetime',
        'fine_paid_at' => 'datetime',
        'book_price' => 'decimal:2',
        'fine_amount' => 'decimal:2',
    ];

    /** Issues that still count against the borrowing limit. */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public function scopeClosed($query)
    {
        return $query->whereNotIn('status', self::OPEN_STATUSES);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function isAwaitingCollection(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    /**
     * Sequential, human-quotable, and unique per year.
     *
     * Built from the table's own max rather than a counter row, so it cannot
     * drift from what is actually stored.
     */
    public static function nextReference(): string
    {
        $prefix = 'LIB-'.date('Y').'-';

        $last = static::withTrashed()
            ->where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function logs()
    {
        return $this->hasMany(LibraryBookIssueLog::class)->orderBy('id');
    }

    public function isDayReading(): bool
    {
        return $this->loan_type === self::TYPE_DAY_READING;
    }

    /** Only take-home loans count against the borrowing limit. */
    public function scopeTakeHome($query)
    {
        return $query->where('loan_type', self::TYPE_TAKE_HOME);
    }

    public function scopeDayReading($query)
    {
        return $query->where('loan_type', self::TYPE_DAY_READING);
    }

    /**
     * Append one event to the trail.
     *
     * The actor is captured by name as well as id: staff leave, accounts are
     * deleted, and a log that reads "performed_by 47" is no use two years on.
     */
    public function log(string $action, ?string $fromStatus = null, ?string $note = null, string $actorType = 'staff'): LibraryBookIssueLog
    {
        $actor = $actorType === 'student' ? auth('student')->user() : auth()->user();

        return $this->logs()->create([
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $this->status,
            'performed_by' => $actor->id ?? null,
            'performed_by_type' => $actorType,
            'performed_by_name' => $actor->name ?? ($actor->full_name ?? null),
            'note' => $note,
        ]);
    }
}
