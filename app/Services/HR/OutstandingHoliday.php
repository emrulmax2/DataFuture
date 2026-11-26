<?php

namespace App\Services\HR;

use App\Http\Controllers\HR\Reports\OutstandingHolidayReportController as Old;
use App\Models\Employee;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\HrHolidayYear;

/**
 * HR Portal › Reports › Outstanding Holiday, new version (user's outstanding-holiday-report design, 26 Nov 2026).
 *
 * The figures are the old report's (OutstandingHolidayReportController::list / export), worked out by its own
 * methods so the two versions cannot drift: the dates are clamped to the holiday year; each employee's
 * working pattern for the year, entitlement for the part of the range the pattern covers, plus or minus the
 * year's adjustment; taken = taken + booked approved holiday + auto-booked bank holidays; balance = allocation
 * minus taken; value = balance hours × the pattern's active hourly rate. Only people with a pattern in the
 * range are listed, in first name order, as on the old screen.
 */
class OutstandingHoliday
{
    public const STATUSES = ['2' => 'All', '1' => 'Active', '0' => 'Inactive'];

    public function __construct(private Old $old)
    {
    }

    /** The old rules for the inputs: dates clamped to the holiday year, status 2 = all. */
    public static function filters(array $in): ?array
    {
        $year = HrHolidayYear::find((int) ($in['year'] ?? 0));
        if (!$year) return null;
        $day = function ($v) {
            $v = trim((string) $v);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $v, $m)) return "$m[3]-$m[2]-$m[1]";

            return '';
        };
        $start = date('Y-m-d', strtotime($year->start_date));
        $end = date('Y-m-d', strtotime($year->end_date));
        $from = $day($in['from'] ?? '');
        $to = $day($in['to'] ?? '');
        $status = (string) ($in['status'] ?? '2');

        return [
            'year' => $year->id, 'yearStart' => $start, 'yearEnd' => $end,
            'yearName' => date('Y', strtotime($start)).' to '.date('Y', strtotime($end)),
            'from' => $from === '' || $from < $start ? $start : $from,
            'to' => $to === '' || $to > $end ? $end : $to,
            'status' => array_key_exists($status, self::STATUSES) ? $status : '2',
        ];
    }

    public function rows(array $f): array
    {
        $q = Employee::with(['employment.department'])->orderBy('first_name', 'ASC');
        if ($f['status'] !== '2') $q->where('status', (int) $f['status']);
        $out = [];
        foreach ($q->get() as $e) {
            $active = $this->old->employeePossibleActivePattern($e->id, $f['year']);
            if ($active <= 0) continue;
            $pattern = EmployeeWorkingPattern::find($active);
            $eff = isset($pattern->effective_from) && $pattern->effective_from != '' && $pattern->effective_from != '0000-00-00' ? date('Y-m-d', strtotime($pattern->effective_from)) : '';
            $endTo = isset($pattern->end_to) && $pattern->end_to != '' && $pattern->end_to != '0000-00-00' ? date('Y-m-d', strtotime($pattern->end_to)) : '';
            if (!($eff < $f['to'] && ($endTo == '' || $endTo > $f['from']))) continue;
            $psd = $f['from'] < $eff && ($eff <= $f['to'] || $eff <= date('Y-m-d')) ? $eff : $f['from'];
            $ped = $endTo != '' && $endTo < $f['to'] ? $endTo : $f['to'];

            $entitlement = $this->old->employeeHolidayEntitlement($e->id, $f['year'], $active, $psd, $ped);
            $adj = $this->old->employeeHolidayAdjustment($e->id, $f['year'], $active);
            $adjHours = isset($adj['hours']) && $adj['hours'] > 0 ? $adj['hours'] : 0;
            $allocation = (int) (($adj['opt'] ?? 1) == 2 ? $entitlement - $adjHours : $entitlement + $adjHours);
            $bank = (int) ($this->old->employeeAutoBookedBankHoliday($e->id, $f['year'], $pattern->id, $psd, $ped)['bank_holiday_total'] ?? 0);
            $leave = $this->old->employeeExistingLeaveHours($e->id, $f['year'], $active, $psd, $ped);
            $taken = (int) ($leave['taken'] ?? 0);
            $booked = (int) ($leave['booked'] ?? 0);
            $total = $taken + $booked + $bank;
            $balance = $allocation - $total;
            $pay = EmployeeWorkingPatternPay::where('employee_working_pattern_id', $active)->where('active', 1)->orderBy('id', 'DESC')->first();
            $rate = isset($pay->hourly_rate) && $pay->hourly_rate > 0 ? (float) $pay->hourly_rate : 0.0;

            $out[] = [
                'id' => $e->id,
                'name' => trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))),
                'full' => $e->full_name,
                'dept' => (string) ($e->employment->department->name ?? ''),
                'active' => $e->status == 1 ? 1 : 0,
                'a' => $allocation,
                't' => $total,
                'taken' => $taken,
                'booked' => $booked,
                'bank' => $bank,
                'b' => $balance,
                'rate' => $rate,
                'v' => round($balance / 60 * $rate, 2),
            ];
        }

        return $out;
    }

    /** "138:05", "-21:38", as the old screen prints hours. */
    public static function hm(int $m): string
    {
        $a = abs($m);

        return ($m < 0 ? '-' : '').intdiv($a, 60).':'.str_pad((string) ($a % 60), 2, '0', STR_PAD_LEFT);
    }

    public const ATTENTION = ['over', 'left', 'none', 'noalloc'];

    /** The list as the page shows it (card, department, search, sort), for the Excel download. */
    public static function shown(array $rows, array $in): array
    {
        $att = (string) ($in['att'] ?? '');
        $dept = (string) ($in['dept'] ?? '');
        $q = mb_strtolower(trim((string) ($in['q'] ?? '')));
        $rows = array_values(array_filter($rows, function ($p) use ($att, $dept, $q) {
            if ($att === 'over' && $p['b'] >= 0) return false;
            if ($att === 'left' && !(!$p['active'] && $p['b'] > 0)) return false;
            if ($att === 'none' && !($p['a'] > 0 && $p['t'] === 0)) return false;
            if ($att === 'noalloc' && $p['a'] !== 0) return false;
            if (($in['deptSet'] ?? '') === '1' && $p['dept'] !== $dept) return false;
            if ($q !== '' && !str_contains(mb_strtolower($p['name'].' '.$p['dept']), $q)) return false;

            return true;
        }));
        $sort = (string) ($in['sort'] ?? 'screen');
        if ($sort === 'vhi') usort($rows, fn ($a, $b) => $b['v'] <=> $a['v']);
        if ($sort === 'vlo') usort($rows, fn ($a, $b) => $a['v'] <=> $b['v']);
        if ($sort === 'used') usort($rows, fn ($a, $b) => [$a['a'] ? $a['t'] / $a['a'] : 9, $b['b']] <=> [$b['a'] ? $b['t'] / $b['a'] : 9, $a['b']]);

        return $rows;
    }
}
