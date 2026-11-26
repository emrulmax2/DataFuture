<?php

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\EmployeeWorkingPattern;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * HR Portal › Reports › Employee Sick Leave, new version (user's employee-sick-leave design, 26 Nov 2026).
 *
 * Same days as the old report (SickLeaveController::list): approved sick leave (leave_type 3) with an active
 * leave day, on leaves of more than 0 days; "Last N days" runs from today minus N days to today; custom dates
 * run From to To (To empty = today); with no period the old report has no date limit, kept here as "All
 * dates". Contracted hours come from the latest active working pattern, as before. Rows deleted from the
 * leave tables (soft deletes) are left out here; the old report would count them (there are none today).
 */
class SickLeave
{
    public const PERIODS = ['7' => 'Last 7 days', '30' => 'Last 30 days', '45' => 'Last 45 days', '60' => 'Last 60 days', '90' => 'Last 90 days', 'custom' => 'Custom dates', 'all' => 'All dates'];

    public static function filters(array $in): array
    {
        $day = function ($v) {
            $v = trim((string) $v);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $v, $m)) return "$m[3]-$m[2]-$m[1]";

            return '';
        };
        $period = (string) ($in['period'] ?? '30');
        $period = array_key_exists($period, self::PERIODS) ? $period : '30';
        $today = Carbon::today()->toDateString();
        $from = $to = '';
        if (ctype_digit($period)) {
            $from = Carbon::today()->subDays((int) $period)->toDateString();
            $to = $today;
        } elseif ($period === 'custom') {
            $from = $day($in['from'] ?? '');
            $to = $day($in['to'] ?? '') ?: $today;
            if ($from === '') $period = 'all'; // the old report: no From and no Days means no date limit
        }
        $emp = array_values(array_filter(array_map('intval', (array) ($in['emp'] ?? [])), fn ($i) => $i > 0));

        return ['period' => $period, 'label' => self::PERIODS[$period], 'from' => $from, 'to' => $to, 'emp' => $emp, 'today' => $today];
    }

    /** One row per employee with sick leave in the range: dates in order, contracted hours. */
    public function rows(array $f): array
    {
        $q = DB::table('employee_leaves')
            ->join('employee_leave_days', 'employee_leave_days.employee_leave_id', '=', 'employee_leaves.id')
            ->where('employee_leaves.leave_type', 3)->where('employee_leaves.status', 'Approved')
            ->where('employee_leave_days.status', 'Active')->where('employee_leaves.days', '>', 0)
            ->whereNull('employee_leaves.deleted_at')->whereNull('employee_leave_days.deleted_at');
        if ($f['emp']) $q->whereIn('employee_leaves.employee_id', $f['emp']);
        if ($f['period'] !== 'all') $q->whereBetween('employee_leave_days.leave_date', [$f['from'], $f['to']]);
        $days = $q->orderBy('employee_leave_days.leave_date')->get(['employee_leaves.employee_id', 'employee_leave_days.leave_date']);
        if ($days->isEmpty()) return [];

        $byEmp = $days->groupBy('employee_id');
        $patterns = EmployeeWorkingPattern::whereIn('employee_id', $byEmp->keys())->where('active', 1)->orderByDesc('id')->get()->groupBy('employee_id')->map->first();
        $employees = Employee::with('employment.employeeJobTitle')->whereIn('id', $byEmp->keys())->get()->keyBy('id');

        return $byEmp->map(function ($rows, $id) use ($patterns, $employees) {
            $e = $employees->get($id);
            $hours = $patterns->get($id)->contracted_hour ?? '';

            return [
                'id' => (int) $id,
                'name' => $e ? trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))) : 'Employee '.$id,
                'first' => (string) ($e->first_name ?? ''),
                'job' => (string) ($e->employment->employeeJobTitle->name ?? ''),
                'active' => $e && $e->status == 1 ? 1 : 0,
                'hours' => !empty($hours) ? (string) $hours : '00:00',
                'dates' => $rows->pluck('leave_date')->map(fn ($d) => substr((string) $d, 0, 10))->values()->all(),
            ];
        })->sortBy(fn ($r) => mb_strtolower($r['name']))->values()->all();
    }

    /** The range the page draws: the period, or for All dates the first sick day to today. */
    public static function span(array $f, array $rows): array
    {
        if ($f['period'] !== 'all') return [$f['from'], $f['to']];
        $all = array_merge(...array_map(fn ($r) => $r['dates'], $rows ?: [['dates' => []]]));

        return $all ? [min($all), max(max($all), $f['today'])] : [$f['today'], $f['today']];
    }

    /** Runs of consecutive days: [['2026-11-02', '2026-11-03'], ['2026-11-09']]. */
    public static function spells(array $dates): array
    {
        $out = [];
        foreach ($dates as $d) {
            $n = count($out);
            if ($n && (strtotime($d) - strtotime(end($out[$n - 1]))) <= 86400 + 3600) $out[$n - 1][] = $d;
            else $out[] = [$d];
        }

        return $out;
    }

    /** The list as the page shows it (selected day, sort), for the Excel download. */
    public static function shown(array $rows, array $in): array
    {
        $day = (string) ($in['day'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) $rows = array_values(array_filter($rows, fn ($r) => in_array($day, $r['dates'], true)));
        $month = (string) ($in['month'] ?? '');
        if (preg_match('/^\d{4}-\d{2}$/', $month)) $rows = array_values(array_filter($rows, fn ($r) => (bool) array_filter($r['dates'], fn ($d) => str_starts_with($d, $month))));
        $sort = (string) ($in['sort'] ?? 'name');
        if ($sort === 'most') usort($rows, fn ($a, $b) => count($b['dates']) <=> count($a['dates']) ?: strcasecmp($a['name'], $b['name']));
        if ($sort === 'recent') usort($rows, fn ($a, $b) => end($b['dates']) <=> end($a['dates']) ?: strcasecmp($a['name'], $b['name']));

        return $rows;
    }
}
