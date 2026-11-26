<?php

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeWorkingPattern;
use App\Models\HrBankHoliday;
use Illuminate\Support\Facades\DB;

/**
 * HR Portal › Reports › Employee Hour Report, new version (user's employee-hour-report design, 26 Nov 2026).
 *
 * Same people and counting as the old report (HolidayHourReportController): everyone with an attendance
 * row in the range, in first name order; worked = total_work_hour on rows with a clock-in; holiday =
 * leave_hour on Holiday rows (leave_status 1) plus bank holidays for people whose bank holidays are
 * booked automatically (the hours of their working pattern on that weekday). An empty To means today.
 *
 * Fixed here (bank holidays, old report unchanged):
 * - the old report only looked at holiday years that start or end inside the range, so a range inside a
 *   holiday year (such as December to January) never counted its bank holidays; every bank holiday in the
 *   range counts here;
 * - the old working-pattern lookup was not limited to the employee (an unbracketed orWhere), so the hours
 *   could come from someone else's pattern; here it is the employee's own pattern in effect on that day.
 */
class HourReport
{
    public static function range(array $in): array
    {
        $day = function ($v) {
            $v = trim((string) $v);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $v, $m)) return "$m[3]-$m[2]-$m[1]";

            return '';
        };
        $from = $day($in['from'] ?? '');
        $to = $day($in['to'] ?? '');

        return ['from' => $from, 'to' => $to !== '' ? $to : date('Y-m-d'), 'toGiven' => $to !== ''];
    }

    /** One row per employee, in the old report's order. Minutes throughout. */
    public function rows(string $from, string $to): array
    {
        $agg = EmployeeAttendance::where('date', '>=', $from)->where('date', '<=', $to)
            ->groupBy('employee_id')
            ->select('employee_id',
                DB::raw("SUM(CASE WHEN clockin_system IS NOT NULL AND clockin_system != '00:00' THEN total_work_hour ELSE 0 END) AS worked"),
                DB::raw('SUM(CASE WHEN leave_status = 1 THEN leave_hour ELSE 0 END) AS holiday'))
            ->get()->keyBy('employee_id');
        if ($agg->isEmpty()) return [];

        $employees = Employee::with(['employment.employeeJobTitle', 'employment.employeeWorkType', 'payment'])
            ->whereIn('id', $agg->keys())->orderBy('first_name', 'ASC')->get();
        $bank = $this->bankHolidays($employees, $from, $to);

        return $employees->map(function ($e) use ($agg, $bank) {
            $a = $agg->get($e->id);
            $w = (int) $a->worked;
            $h = (int) $a->holiday + ($bank[$e->id] ?? 0);

            return [
                'id' => $e->id,
                'name' => trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))),
                'full' => $e->full_name,
                'job' => (string) ($e->employment->employeeJobTitle->name ?? ''),
                'type' => (string) ($e->employment->employeeWorkType->name ?? ''),
                'works' => (string) ($e->employment->works_number ?? ''),
                'ni' => (string) ($e->ni_number ?? ''),
                'status' => $e->status == 1 ? 'Active' : 'Inactive',
                'w' => $w,
                'h' => $h,
                'bh' => $bank[$e->id] ?? 0,
                't' => $w + $h,
            ];
        })->values()->all();
    }

    /** employee id => bank holiday minutes in the range (auto-booked bank holidays only). */
    private function bankHolidays($employees, string $from, string $to): array
    {
        $days = HrBankHoliday::where('start_date', '>=', $from)->where('start_date', '<=', $to)->pluck('start_date')
            ->map(fn ($d) => date('Y-m-d', strtotime($d)))->unique()->values();
        $ids = $employees->filter(fn ($e) => ($e->payment->bank_holiday_auto_book ?? '') === 'Yes')->pluck('id');
        if ($days->isEmpty() || $ids->isEmpty()) return [];

        $patterns = EmployeeWorkingPattern::with('patterns')->whereIn('employee_id', $ids)->orderBy('id', 'DESC')->get()->groupBy('employee_id');
        $out = [];
        foreach ($ids as $id) {
            foreach ($days as $d) {
                $p = ($patterns[$id] ?? collect())->first(function ($p) use ($d) {
                    $start = $p->getRawOriginal('effective_from');
                    $end = $p->getRawOriginal('end_to');

                    return $start && $start <= $d && (!$end || $end >= $d);
                });
                $detail = $p ? $p->patterns->firstWhere('day', (int) date('N', strtotime($d))) : null;
                if ($detail && !empty($detail->total) && $detail->total !== '00:00') {
                    $out[$id] = ($out[$id] ?? 0) + self::minutes($detail->total);
                }
            }
        }

        return $out;
    }

    public static function minutes(string $hm): int
    {
        $p = explode(':', $hm);

        return (int) ($p[0] ?? 0) * 60 + (int) ($p[1] ?? 0);
    }

    /** "388:20", as the old screen and export print hours. */
    public static function hm(int $m): string
    {
        return intdiv($m, 60).':'.str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT);
    }

    /** Weeks in the range, to one decimal place (13 for 1 Sep to 30 Nov, 4.4 for October). */
    public static function weeks(string $from, string $to): float
    {
        return max(0.1, round(((strtotime($to) - strtotime($from)) / 86400 + 1) / 7, 1));
    }

    /** Histogram band size in hours: a round number near a quarter of 40 hours a week over the range (100 for 13 weeks). */
    public static function bandStep(float $weeks): int
    {
        $want = $weeks * 40 / 4;
        $best = 5;
        foreach ([5, 10, 20, 25, 50, 100, 200, 250, 500, 1000] as $s) {
            if (abs($s - $want) < abs($best - $want)) $best = $s;
        }

        return $best;
    }

    /** Job titles with fewer than this many staff are grouped as "Other job titles". */
    public const SMALL_JOB = 4;

    /** The list as the page shows it: band, job title, view, search and sort (for the Excel download). */
    public static function shown(array $rows, array $in, float $weeks): array
    {
        $step = self::bandStep($weeks);
        $bands = array_map(fn ($k) => [$k * $step, $k === 5 ? PHP_INT_MAX : ($k + 1) * $step], range(0, 5));
        $jobs = array_count_values(array_map(fn ($r) => $r['job'], $rows));
        $band = isset($in['band']) && $in['band'] !== '' && isset($bands[(int) $in['band']]) ? $bands[(int) $in['band']] : null;
        $job = (string) ($in['job'] ?? '');
        $q = mb_strtolower(trim((string) ($in['q'] ?? '')));
        $view = (string) ($in['view'] ?? 'all');
        $rows = array_values(array_filter($rows, function ($r) use ($band, $job, $jobs, $q, $view, $weeks) {
            $hours = $r['t'] / 60;
            $wk = $r['w'] / 60 / $weeks;
            if ($band && ($hours < $band[0] || $hours >= $band[1])) return false;
            if ($job === '__other' && $jobs[$r['job']] >= self::SMALL_JOB) return false;
            if ($job !== '' && $job !== '__other' && $r['job'] !== ($job === '__none' ? '' : $job)) return false;
            if ($q !== '' && !str_contains(mb_strtolower($r['name'].' '.$r['job']), $q)) return false;
            if ($view === 'hol' && $r['h'] <= 0) return false;
            if ($view === 'hi' && $wk <= 40) return false;
            if ($view === 'lo' && $wk >= 5) return false;

            return true;
        }));
        $sort = (string) ($in['sort'] ?? 'screen');
        if ($sort === 'hi') usort($rows, fn ($a, $b) => $b['t'] <=> $a['t']);
        if ($sort === 'lo') usort($rows, fn ($a, $b) => $a['t'] <=> $b['t']);
        if ($sort === 'hol') usort($rows, fn ($a, $b) => [$b['h'], $b['t']] <=> [$a['h'], $a['t']]);

        return $rows;
    }
}
