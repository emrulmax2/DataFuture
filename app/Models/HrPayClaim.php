<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A payment claim settled in the Operations HR pay portal.
 *
 * DataFuture does not own these — it receives them when Accounts pushes a paid
 * batch across, so the attendance report can show what someone claimed in a
 * month next to what they worked.
 *
 * Totals are grouped by `pay_cycle`, the month payroll actually ran the money
 * in. The claimant's own `period` text is kept for reference but never summed:
 * it is free-form over there and routinely names two months at once.
 */
class HrPayClaim extends Model
{
    use HasFactory;

    protected $table = 'hr_pay_claims';

    protected $fillable = [
        'source_id', 'reference', 'doc_type', 'employee_id', 'payroll_number',
        'staff_name', 'staff_email', 'pay_cycle', 'pay_cycle_label', 'period',
        'for_summary', 'subtotal', 'lines', 'submitted_at', 'paid_at',
        'pushed_at', 'pushed_by_email',
    ];

    protected $casts = [
        'pay_cycle' => 'date',
        'subtotal' => 'decimal:2',
        'lines' => 'array',
        'submitted_at' => 'datetime',
        'paid_at' => 'datetime',
        'pushed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /** Claims whose pay cycle falls in the month containing $date. */
    public function scopeForMonth($query, $date)
    {
        return $query->whereDate('pay_cycle', date('Y-m-01', strtotime($date)));
    }

    /**
     * What each employee claimed in one month, keyed by employee id.
     *
     * Read once per report rather than per row: the attendance report lists
     * every clocking employee, and asking the database per person turns one
     * query into sixty.
     *
     * @return array<int,float>
     */
    public static function monthlyTotalsByEmployee($date): array
    {
        return static::query()
            ->forMonth($date)
            ->whereNotNull('employee_id')
            ->selectRaw('employee_id, SUM(subtotal) AS total')
            ->groupBy('employee_id')
            ->pluck('total', 'employee_id')
            ->map(fn ($v) => (float) $v)
            ->all();
    }
}
