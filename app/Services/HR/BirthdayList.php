<?php

namespace App\Services\HR;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeWorkType;
use Carbon\Carbon;

/**
 * HR Portal › Reports › Birthday List, new version (user's birthday-list-report design, 26 Nov 2026).
 *
 * Same staff as the old report (BirthdayReportController::searchlist): status Active (1),
 * Inactive (0) or All (0 and 1); type and department from the employee's latest employment.
 * Fixed here: the old list printed the month number where the day belongs ("January 01 1990"),
 * gave ages as "x Years, y months and z days" from a seconds difference, and listed staff with no
 * date of birth under January as 1 January 1970. Here dates are real, ages are in whole years,
 * and staff with no date of birth are listed apart.
 *
 * The screen gets the rows from staff() and works the dates out in the browser
 * (hr-birthday-report-new.js) with the same rules as enrich(); the PDF and the Excel use enrich().
 */
class BirthdayList
{
    public const STATUSES = ['1' => 'Active', '0' => 'Inactive', '2' => 'All'];
    public const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    /** Filter options for the form. */
    public function options(): array
    {
        return [
            'types' => EmployeeWorkType::orderBy('name')->get(['id', 'name'])->map(fn ($t) => [$t->id, $t->name])->all(),
            'departments' => Department::orderBy('name')->get(['id', 'name'])->map(fn ($d) => [$d->id, $d->name])->all(),
        ];
    }

    /** Active and inactive staff (the old "All"), one row each. */
    public function staff(): array
    {
        return Employee::with(['employment', 'sex', 'employment.employeeWorkType', 'employment.department'])
            ->whereIn('status', [0, 1])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get()
            ->map(function ($e) {
                $dob = $e->getRawOriginal('date_of_birth');
                $dob = ($dob && $dob !== '0000-00-00' && strtotime($dob)) ? date('Y-m-d', strtotime($dob)) : null;

                return [
                    'id' => $e->id,
                    'name' => trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))),
                    'works' => (string) ($e->employment->works_number ?? ''),
                    'gender' => (string) ($e->sex->name ?? ''),
                    'dob' => $dob,
                    'status' => (int) $e->status,
                    'type' => $e->employment->employee_work_type_id ?? null,
                    'typeName' => (string) ($e->employment->employeeWorkType->name ?? ''),
                    'department' => $e->employment->department_id ?? null,
                    'departmentName' => (string) ($e->employment->department->name ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    /** Clean filters from a request: month 0-12, status 1/0/2, type, department, q. */
    public static function filters(array $in): array
    {
        $status = (string) ($in['status'] ?? '1');

        return [
            'month' => max(0, min(12, (int) ($in['month'] ?? 0))),
            'status' => array_key_exists($status, self::STATUSES) ? $status : '1',
            'type' => (string) ($in['type'] ?? ''),
            'department' => (string) ($in['department'] ?? ''),
            'q' => trim((string) ($in['q'] ?? '')),
            'order' => ($in['order'] ?? 'cal') === 'next' ? 'next' : 'cal',
        ];
    }

    /** The staff the type, department, status and find-a-person filters keep (all months). */
    public function base(array $rows, array $f): array
    {
        $q = mb_strtolower($f['q']);

        return array_values(array_filter($rows, function ($r) use ($f, $q) {
            if ($f['status'] !== '2' && (string) $r['status'] !== $f['status']) return false;
            if ($f['type'] !== '' && (string) $r['type'] !== $f['type']) return false;
            if ($f['department'] !== '' && (string) $r['department'] !== $f['department']) return false;
            if ($q !== '' && !str_contains(mb_strtolower($r['name']), $q) && !str_contains(mb_strtolower($r['works']), $q)) return false;

            return true;
        }));
    }

    /** A birthday in a given year; 29 February falls on the 28th in other years. */
    public static function birthdayIn(int $year, int $month, int $day): Carbon
    {
        if ($month === 2 && $day === 29 && !Carbon::create($year, 1, 1)->isLeapYear()) {
            $day = 28;
        }

        return Carbon::create($year, $month, $day)->startOfDay();
    }

    public static function milestone(int $turns): bool
    {
        return $turns > 0 && ($turns % 10 === 0 || $turns === 65);
    }

    /** Next birthday, days to go, the age they turn, current age; null without a date of birth. */
    public static function enrich(array $r, Carbon $today): ?array
    {
        if (!$r['dob']) return null;
        [$y, $m, $d] = array_map('intval', explode('-', $r['dob']));
        $today = $today->copy()->startOfDay();
        $next = self::birthdayIn($today->year, $m, $d);
        if ($next->lt($today)) $next = self::birthdayIn($today->year + 1, $m, $d);
        $days = (int) $today->diffInDays($next);
        $turns = $next->year - $y;

        return $r + [
            'm' => $m, 'd' => $d, 'y' => $y,
            'next' => $next, 'days' => $days, 'turns' => $turns,
            'age' => $days === 0 ? $turns : $turns - 1,
            'milestone' => self::milestone($turns),
        ];
    }

    /**
     * Everything the PDF and the Excel need for one set of filters: the people of the month filter in
     * the chosen order, the 12-month wall from this month, and the staff with no date of birth.
     */
    public function build(array $f, ?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $base = $this->base($this->staff(), $f);

        $people = [];
        $noDob = [];
        foreach ($base as $r) {
            $p = self::enrich($r, $today);
            $p ? $people[] = $p : $noDob[] = $r;
        }

        $list = array_values(array_filter($people, fn ($p) => !$f['month'] || $p['m'] === $f['month']));
        usort($list, $f['order'] === 'next'
            ? fn ($a, $b) => [$a['days'], $a['name']] <=> [$b['days'], $b['name']]
            : fn ($a, $b) => [$a['m'], $a['d'], $a['name']] <=> [$b['m'], $b['d'], $b['name']]);

        // The wall: 12 months from the first of this month; each person once, in the month of their birthday.
        $wall = [];
        for ($i = 0; $i < 12; $i++) {
            $first = $today->copy()->startOfMonth()->addMonthsNoOverflow($i);
            $wall[] = ['first' => $first, 'days' => [], 'count' => 0];
        }
        foreach ($people as $p) {
            foreach ($wall as &$w) {
                if ($w['first']->month !== $p['m']) continue;
                $day = self::birthdayIn($w['first']->year, $p['m'], $p['d'])->day;
                $w['days'][$day] = ($w['days'][$day] ?? 0) + 1;
                $w['count']++;
            }
            unset($w);
        }

        return [
            'today' => $today,
            'filters' => $f,
            'people' => $people,
            'list' => $list,
            'wall' => $wall,
            'noDob' => $f['month'] ? [] : $noDob,
            'count' => count($base),
        ];
    }

    /** "All months · 119 staff" / "March birthdays · 11 staff". */
    public static function scope(array $data): string
    {
        $n = count($data['list']) + count($data['noDob']);

        return ($data['filters']['month'] ? self::MONTHS[$data['filters']['month'] - 1].' birthdays' : 'All months').' · '.$n.' staff';
    }
}
