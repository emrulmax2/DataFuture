<?php

namespace App\Services;

use App\Http\Controllers\Settings\LibrarySettingController;
use App\Models\LibraryBookIssue;
use App\Models\Option;
use Carbon\Carbon;

/**
 * The borrowing rules, read from Site Settings → Library Management.
 *
 * Every screen and every write goes through here rather than reading options
 * directly, so a rule change lands everywhere at once and there is one place
 * that decides what an unset option means.
 */
class LibraryRules
{
    /** Used when an option has never been saved. */
    private const DEFAULTS = [
        'library_deposit_amount' => 20,
        /* Only the fallback for an unsaved option — the live value is set in
           Site Settings → Deposit Rule. Off here so a fresh install cannot
           lock every student out before anyone has configured PayPal. */
        'library_require_deposit' => 0,
        'library_hold_days' => 3,
        'library_max_take_home_books' => 3,
        'library_loan_period' => 14,
        'library_max_renewals' => 2,
        'library_overdue_penalty' => 0.20,
        'library_grace_period' => 0,
    ];

    private array $values;

    public function __construct()
    {
        $this->values = Option::where('category', LibrarySettingController::CATEGORY)
            ->pluck('value', 'name')
            ->toArray();
    }

    private function get(string $name): float
    {
        $value = $this->values[$name] ?? null;

        return is_numeric($value) ? (float) $value : (float) self::DEFAULTS[$name];
    }

    public function depositAmount(): float
    {
        return round($this->get('library_deposit_amount'), 2);
    }

    public function requiresDeposit(): bool
    {
        return (int) $this->get('library_require_deposit') === 1;
    }

    /** Days a booked copy waits on the hold shelf before it goes back. */
    public function holdDays(): int
    {
        return max((int) $this->get('library_hold_days'), 1);
    }

    public function maxBooks(): int
    {
        return (int) $this->get('library_max_take_home_books');
    }

    public function loanPeriodDays(): int
    {
        return (int) $this->get('library_loan_period');
    }

    public function maxRenewals(): int
    {
        return (int) $this->get('library_max_renewals');
    }

    public function penaltyPerDay(): float
    {
        return round($this->get('library_overdue_penalty'), 2);
    }

    public function graceDays(): int
    {
        return (int) $this->get('library_grace_period');
    }

    public function dueDateFrom(?Carbon $from = null): Carbon
    {
        return ($from ?: Carbon::now())->copy()->startOfDay()->addDays($this->loanPeriodDays());
    }

    /**
     * A day-reading book is due back the day it was taken out — it is read in
     * the library, not borrowed, so the loan period does not apply.
     */
    public function dayReadingDueDate(): Carbon
    {
        return Carbon::now()->startOfDay();
    }

    /**
     * What a loan owes right now.
     *
     * Whole days only, and measured from the start of each day, so a book due
     * today is never "one day late" because of the hour it is looked at. A
     * returned loan is frozen at what it was settled for.
     */
    public function fineFor(LibraryBookIssue $loan): float
    {
        /* A request awaiting collection has no due date yet — the loan clock
           starts when staff hand the book over, not when it was booked. */
        if (!$loan->isOpen() || !$loan->due_at):
            return (float) $loan->fine_amount;
        endif;

        $overdueDays = $loan->due_at->copy()->startOfDay()->diffInDays(Carbon::now()->startOfDay(), false)
            - $this->graceDays();

        if ($overdueDays <= 0):
            return 0.0;
        endif;

        return round($overdueDays * $this->penaltyPerDay(), 2);
    }

    public function daysRemaining(LibraryBookIssue $loan): int
    {
        return $loan->due_at
            ? (int) Carbon::now()->startOfDay()->diffInDays($loan->due_at->copy()->startOfDay(), false)
            : 0;
    }

    /**
     * Why this student cannot borrow right now, or null if they can.
     *
     * Returns the reason rather than a boolean because every caller — the
     * banner, the disabled button, the guard on the write — wants to say why.
     */
    public function blockedReason($studentId, bool $depositHeld): ?string
    {
        if ($this->requiresDeposit() && !$depositHeld):
            return 'Pay your refundable deposit to start borrowing.';
        endif;

        /* Day reading never leaves the building and is handed back the same
           day, so it does not consume the take-home allowance. */
        $open = LibraryBookIssue::where('student_id', $studentId)->takeHome()->open()->count();

        if ($open >= $this->maxBooks()):
            return 'You are holding the maximum of '.$this->maxBooks().' books. Return one to borrow another.';
        endif;

        $overdue = LibraryBookIssue::where('student_id', $studentId)
            ->open()
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', Carbon::now()->startOfDay())
            ->exists();

        if ($overdue):
            return 'You have an overdue book. Return it before borrowing again.';
        endif;

        return null;
    }
}
