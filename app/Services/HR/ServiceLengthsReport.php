<?php

namespace App\Services\HR;

use App\Models\Employee;
use Carbon\Carbon;

/**
 * HR Portal › Reports › Employee Service Lengths, new version (user's employee-service-lengths design,
 * 26 Nov 2026).
 *
 * Same staff and filters as the old report (LengthServiceController::searchlist): status, type,
 * department, start/end date on the latest employment (started on or before; ended on or after),
 * ethnicity, nationality and gender.
 *
 * Service runs from the latest employment's start. Fixed here:
 *  - the old length came from a seconds difference turned into a date ("x Years, y months and z days"),
 *    one to three days out; here it is the calendar difference;
 *  - the old end was the working pattern's end_to only, and a future end_to was counted up to that
 *    date; here service ends at the earlier of the employment's ended_on and the working pattern's
 *    end_to, and an end in the future counts up to today;
 *  - staff with no start date were counted from today (0 years); here they are listed apart.
 */
class ServiceLengthsReport
{
    public const STATUSES = ['1' => 'Active', '0' => 'Inactive', '2' => 'All'];
    public const BANDS = [['Under 1 year', 0, 1], ['1 to 2 years', 1, 2], ['2 to 5 years', 2, 5], ['5 to 10 years', 5, 10], ['10 years and over', 10, 999]];

    public function staff(?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();

        return Employee::with(['employment', 'workingPattern', 'nationality'])
            ->whereIn('status', [0, 1])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get()
            ->map(function ($e) use ($today) {
                $date = fn ($v) => ($v && $v !== '0000-00-00' && strtotime($v)) ? date('Y-m-d', strtotime($v)) : null;
                $started = $date(optional($e->employment)->getRawOriginal('started_on'));
                $ended = $date(optional($e->employment)->getRawOriginal('ended_on'));
                $patternEnd = $date(optional($e->workingPattern)->getRawOriginal('end_to'));
                $r = [
                    'id' => $e->id,
                    'name' => trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))),
                    'works' => (string) ($e->employment->works_number ?? ''),
                    'status' => (int) $e->status,
                    'type' => $e->employment->employee_work_type_id ?? null,
                    'department' => $e->employment->department_id ?? null,
                    'started' => $started,
                    'ended' => $ended,
                    'patternEnd' => $patternEnd,
                    'ethnicityId' => $e->ethnicity_id,
                    'nationalityId' => $e->nationality_id,
                    'nationality' => (string) ($e->nationality->name ?? ''),
                    'genderId' => $e->sex_identifier_id,
                ];

                return $r + self::service($r, $today);
            })
            ->values()
            ->all();
    }

    /** Length of service and the next work anniversary, from the dates. */
    public static function service(array $r, Carbon $today): array
    {
        if (!$r['started']) return ['hasStart' => false];
        $start = Carbon::parse($r['started'])->startOfDay();
        $ends = array_filter([$r['ended'], $r['patternEnd']]);
        $end = $ends ? Carbon::parse(min($ends))->startOfDay() : null;
        $left = $end && $end->lte($today);
        $to = $left ? $end : $today;
        if ($to->lt($start)) $to = $start->copy(); // starts in the future: 0 so far
        $d = $start->diff($to);
        $years = $start->diffInDays($to) / 365.25;
        $band = 0;
        foreach (self::BANDS as $i => $b) {
            if ($d->y >= $b[1] && $d->y < $b[2]) $band = $i;
        }
        $out = [
            'hasStart' => true, 'end' => $end ? $end->format('Y-m-d') : null, 'left' => (bool) $left, 'future' => $end && !$left,
            'y' => $d->y, 'm' => $d->m, 'd' => $d->d, 'years' => round($years, 4), 'band' => $band, 'words' => self::words($d->y, $d->m, $d->d),
            'ann' => null, 'annDays' => null, 'annYears' => null,
        ];
        if (!$left) {
            $ann = self::onDay($today->year, $start->month, $start->day);
            if ($ann->lt($today)) $ann = self::onDay($today->year + 1, $start->month, $start->day);
            $out['ann'] = $ann->format('Y-m-d');
            $out['annDays'] = (int) $today->diffInDays($ann);
            $out['annYears'] = $ann->year - $start->year;
        }

        return $out;
    }

    private static function onDay(int $y, int $m, int $d): Carbon
    {
        if ($m === 2 && $d === 29 && !Carbon::create($y, 1, 1)->isLeapYear()) $d = 28;

        return Carbon::create($y, $m, $d)->startOfDay();
    }

    /** "4 years 8 months", "11 months", "1 day". */
    public static function words(int $y, int $m, int $d): string
    {
        $u = fn ($n, $w) => $n.' '.$w.($n === 1 ? '' : 's');
        if ($y) return $u($y, 'year').($m ? ' '.$u($m, 'month') : '');

        return $m ? $u($m, 'month') : $u($d, 'day');
    }

    public static function filters(array $in): array
    {
        $status = (string) ($in['status'] ?? '1');
        $day = fn ($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? (string) $v : '';
        $band = $in['band'] ?? '';

        return [
            'type' => (string) ($in['type'] ?? ''),
            'department' => (string) ($in['department'] ?? ''),
            'from' => $day($in['from'] ?? ''),
            'to' => $day($in['to'] ?? ''),
            'ethnicity' => (string) ($in['ethnicity'] ?? ''),
            'nationality' => (string) ($in['nationality'] ?? ''),
            'gender' => (string) ($in['gender'] ?? ''),
            'status' => array_key_exists($status, self::STATUSES) ? $status : '1',
            'band' => is_numeric($band) && isset(self::BANDS[(int) $band]) ? (int) $band : -1,
            'order' => ($in['order'] ?? 'long') === 'new' ? 'new' : 'long',
        ];
    }

    /** The staff the old filters keep. */
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

    /** Figures for the PDF: the people with a start date, sorted for the list, and the summaries. */
    public static function build(array $rows, array $f, Carbon $today): array
    {
        $list = array_values(array_filter($rows, fn ($r) => $r['hasStart']));
        $noStart = array_values(array_filter($rows, fn ($r) => !$r['hasStart']));
        $n = count($list);
        $years = array_column($list, 'years');
        sort($years);
        $median = $n ? ($n % 2 ? $years[intdiv($n, 2)] : ($years[$n / 2 - 1] + $years[$n / 2]) / 2) : 0;
        $longest = $list;
        usort($longest, fn ($a, $b) => $b['years'] <=> $a['years']);
        $byYear = [];
        foreach ($list as $p) {
            $y = (int) substr($p['started'], 0, 4);
            $byYear[$y] = ($byYear[$y] ?? 0) + 1;
        }
        ksort($byYear);
        $bands = array_fill(0, count(self::BANDS), 0);
        foreach ($list as $p) $bands[$p['band']]++;
        $shown = $f['band'] >= 0 ? array_values(array_filter($list, fn ($p) => $p['band'] === $f['band'])) : $list;
        usort($shown, $f['order'] === 'new'
            ? fn ($a, $b) => [$a['years'], $a['name']] <=> [$b['years'], $b['name']]
            : fn ($a, $b) => [$b['years'], $a['name']] <=> [$a['years'], $b['name']]);

        return [
            'list' => $list, 'shown' => $shown, 'noStart' => $noStart, 'n' => $n,
            'avg' => $n ? array_sum($years) / $n : 0, 'median' => $median, 'longest' => $longest[0] ?? null,
            'thisYear' => count(array_filter($list, fn ($p) => (int) substr($p['started'], 0, 4) === $today->year)),
            'byYear' => $byYear, 'bands' => $bands,
        ];
    }
}
