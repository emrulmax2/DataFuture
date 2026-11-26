<?php

namespace App\Services\HR;

use App\Models\Employee;
use Carbon\Carbon;

/**
 * HR Portal › Reports › Employee Starter Information, new version (user's employee-starter-information
 * design, 26 Nov 2026).
 *
 * Same staff and filters as the old report (StarterReportController::list): status, type, department,
 * start/end date on the latest employment (started on or before; ended on or after), ethnicity,
 * nationality and gender. The old list was in record order; here it is newest start first, and staff
 * with no start date are listed apart.
 */
class StarterReport
{
    public const STATUSES = ['1' => 'Active', '0' => 'Inactive', '2' => 'All'];
    public const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    public function staff(?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();

        return Employee::with(['employment', 'nationality'])
            ->whereIn('status', [0, 1])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get()
            ->map(function ($e) use ($today) {
                $date = fn ($v) => ($v && $v !== '0000-00-00' && strtotime($v)) ? date('Y-m-d', strtotime($v)) : null;
                $started = $date(optional($e->employment)->getRawOriginal('started_on'));
                $r = [
                    'id' => $e->id,
                    'name' => trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))),
                    'works' => (string) ($e->employment->works_number ?? ''),
                    'started' => $started,
                    'ended' => $date(optional($e->employment)->getRawOriginal('ended_on')),
                    'status' => (int) $e->status,
                    'type' => $e->employment->employee_work_type_id ?? null,
                    'department' => $e->employment->department_id ?? null,
                    'ethnicityId' => $e->ethnicity_id,
                    'nationalityId' => $e->nationality_id,
                    'nationality' => (string) ($e->nationality->name ?? ''),
                    'genderId' => $e->sex_identifier_id,
                    'days' => null,
                    'ago' => '',
                ];
                if ($started) {
                    $s = Carbon::parse($started);
                    $r['days'] = (int) $s->diffInDays($today, false);
                    $r['ago'] = self::ago($s, $today);
                }

                return $r;
            })
            ->values()
            ->all();
    }

    /** "10 days ago", "2 months ago", "1 year 1 month ago", "starts in 5 days". */
    public static function ago(Carbon $start, Carbon $today): string
    {
        $days = (int) $start->diffInDays($today, false);
        if ($days < 0) return 'starts in '.(-$days).' day'.($days === -1 ? '' : 's');
        if ($days === 0) return 'today';
        if ($days < 60) return $days.' day'.($days === 1 ? '' : 's').' ago';
        $d = $start->diff($today);
        $u = fn ($n, $w) => $n.' '.$w.($n === 1 ? '' : 's');

        return ($d->y ? $u($d->y, 'year').($d->m ? ' '.$u($d->m, 'month') : '') : $u($d->m, 'month')).' ago';
    }

    public static function filters(array $in): array
    {
        $status = (string) ($in['status'] ?? '1');
        $day = fn ($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? (string) $v : '';

        return [
            'type' => (string) ($in['type'] ?? ''),
            'department' => (string) ($in['department'] ?? ''),
            'from' => $day($in['from'] ?? ''),
            'to' => $day($in['to'] ?? ''),
            'ethnicity' => (string) ($in['ethnicity'] ?? ''),
            'nationality' => (string) ($in['nationality'] ?? ''),
            'gender' => (string) ($in['gender'] ?? ''),
            'status' => array_key_exists($status, self::STATUSES) ? $status : '1',
            'year' => (int) ($in['year'] ?? 0),
            'month' => isset($in['month']) && $in['month'] !== '' ? (int) $in['month'] : -1,
        ];
    }

    public static function rows(array $staff, array $f): array
    {
        return array_values(array_filter($staff, function ($r) use ($f) {
            if ($f['status'] !== '2' && (string) $r['status'] !== $f['status']) return false;
            if ($f['type'] !== '' && (string) $r['type'] !== $f['type']) return false;
            if ($f['department'] !== '' && (string) $r['department'] !== $f['department']) return false;
            if ($f['from'] !== '' && !($r['started'] && $r['started'] <= $f['from'])) return false;
            if ($f['to'] !== '' && !($r['ended'] && $r['ended'] >= $f['to'])) return false;
            if ($f['ethnicity'] !== '' && (string) $r['ethnicityId'] !== $f['ethnicity']) return false;
            if ($f['nationality'] !== '' && (string) $r['nationalityId'] !== $f['nationality']) return false;
            if ($f['gender'] !== '' && (string) $r['genderId'] !== $f['gender']) return false;

            return true;
        }));
    }

    /** Figures, the year × month grid and the list (newest first, or one year/month picked on the grid). */
    public static function build(array $rows, array $f, Carbon $today): array
    {
        $list = array_values(array_filter($rows, fn ($r) => $r['started']));
        $noStart = array_values(array_filter($rows, fn ($r) => !$r['started']));
        $grid = [];
        $byMonth = array_fill(0, 12, 0);
        foreach ($list as $p) {
            $y = (int) substr($p['started'], 0, 4);
            $m = (int) substr($p['started'], 5, 2) - 1;
            $grid[$y] ??= array_fill(0, 12, 0);
            $grid[$y][$m]++;
            $byMonth[$m]++;
        }
        krsort($grid);
        $busiest = $list ? array_search(max($byMonth), $byMonth, true) : null;
        $shown = array_values(array_filter($list, fn ($p) => !$f['year'] || ((int) substr($p['started'], 0, 4) === $f['year'] && ($f['month'] < 0 || (int) substr($p['started'], 5, 2) - 1 === $f['month']))));
        usort($shown, fn ($a, $b) => [$b['started'], $a['name']] <=> [$a['started'], $b['name']]);

        return [
            'list' => $list, 'shown' => $shown, 'noStart' => $noStart, 'n' => count($list),
            'thisYear' => count(array_filter($list, fn ($p) => (int) substr($p['started'], 0, 4) === $today->year)),
            'last90' => count(array_filter($list, fn ($p) => $p['days'] !== null && $p['days'] >= 0 && $p['days'] <= 90)),
            'noWorks' => count(array_filter($list, fn ($p) => $p['works'] === '')),
            'grid' => $grid, 'byMonth' => $byMonth, 'busiest' => $busiest,
        ];
    }
}
