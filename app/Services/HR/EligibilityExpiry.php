<?php

namespace App\Services\HR;

use App\Models\Employee;
use Carbon\Carbon;

/**
 * HR Portal › Reports › Eligibility Expiry, new version (user's eligibility-expiry-report design, 26 Nov 2026).
 *
 * Same records as the old report (EligibilityReportController): every employee with their latest
 * eligibility record, two documents — the visa / work permit (workpermit_number, workpermit_expire) and
 * the passport or ID document (document type, doc_number, doc_expire). The old name search is kept
 * (first or last name contains the text).
 * Fixed here: an empty date is not expired. The old visa list read an empty date as 1 January 1970
 * ("Expired"); empty dates and 1970 placeholders are "No date recorded" here. Days are calendar days.
 * New filters from the design (type, department, expiry range, ethnicity, nationality, gender, status)
 * work on this version only; the old one has the name search alone.
 */
class EligibilityExpiry
{
    public const KINDS = ['visa' => 'Visa and work permit', 'doc' => 'Passport and ID document'];
    public const BANDS = [
        'x' => ['Expired', 'date has passed'],
        's30' => ['Within 30 days', 'act now'],
        's90' => ['31 to 90 days', 'start the check'],
        's180' => ['91 to 180 days', 'coming up'],
        'ok' => ['Later', 'more than 180 days'],
        'nd' => ['No date recorded', 'cannot be tracked'],
    ];
    public const STATUSES = ['' => 'All statuses', '1' => 'Active', '0' => 'Inactive', 'other' => 'Other'];

    public static function day(?string $v): ?string
    {
        if (!$v || !strtotime($v)) return null;
        $d = date('Y-m-d', strtotime($v));

        return $d < '1971-01-01' ? null : $d; // empty dates stored as 1970 are placeholders
    }

    public static function band(?int $days): string
    {
        if ($days === null) return 'nd';

        return $days < 0 ? 'x' : ($days <= 30 ? 's30' : ($days <= 90 ? 's90' : ($days <= 180 ? 's180' : 'ok')));
    }

    public function staff(?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();

        return Employee::with(['eligibilities', 'eligibilities.employeeWorkPermitType', 'eligibilities.employeeDocType', 'eligibilities.docIssueCountry', 'employment', 'nationality'])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get()
            ->map(function ($e) use ($today) {
                $el = $e->eligibilities;
                $days = fn ($d) => $d ? (int) $today->diffInDays(Carbon::parse($d), false) : null;
                $visaDate = self::day($el ? $el->getRawOriginal('workpermit_expire') : null);
                $docDate = self::day($el ? $el->getRawOriginal('doc_expire') : null);
                $r = [
                    'id' => $e->id,
                    'name' => trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))),
                    'first' => (string) $e->first_name,
                    'last' => (string) $e->last_name,
                    'status' => (int) $e->status,
                    'type' => $e->employment->employee_work_type_id ?? null,
                    'department' => $e->employment->department_id ?? null,
                    'ethnicityId' => $e->ethnicity_id,
                    'nationalityId' => $e->nationality_id,
                    'nationality' => (string) ($e->nationality->name ?? ''),
                    'genderId' => $e->sex_identifier_id,
                    'visa' => [
                        'num' => trim((string) ($el->workpermit_number ?? '')),
                        'what' => (string) ($el->employeeWorkPermitType->name ?? ''),
                        'date' => $visaDate,
                        'epoch' => $el && $el->getRawOriginal('workpermit_expire') && !$visaDate,
                    ],
                    'doc' => [
                        'num' => trim((string) ($el->doc_number ?? '')),
                        'what' => (string) ($el->employeeDocType->name ?? ''),
                        'country' => (string) ($el->docIssueCountry->name ?? ''),
                        'date' => $docDate,
                        'epoch' => $el && $el->getRawOriginal('doc_expire') && !$docDate,
                    ],
                ];
                foreach (['visa', 'doc'] as $k) {
                    $r[$k]['days'] = $days($r[$k]['date']);
                    $r[$k]['band'] = self::band($r[$k]['days']);
                }

                return $r;
            })
            ->values()
            ->all();
    }

    public static function filters(array $in): array
    {
        $day = fn ($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? (string) $v : '';
        $status = (string) ($in['status'] ?? '');
        $band = (string) ($in['band'] ?? '');

        return [
            'kind' => ($in['kind'] ?? 'visa') === 'doc' ? 'doc' : 'visa',
            'q' => trim((string) ($in['q'] ?? '')),
            'type' => (string) ($in['type'] ?? ''), 'department' => (string) ($in['department'] ?? ''),
            'from' => $day($in['from'] ?? ''), 'to' => $day($in['to'] ?? ''),
            'ethnicity' => (string) ($in['ethnicity'] ?? ''), 'nationality' => (string) ($in['nationality'] ?? ''), 'gender' => (string) ($in['gender'] ?? ''),
            'status' => array_key_exists($status, self::STATUSES) ? $status : '',
            'band' => array_key_exists($band, self::BANDS) ? $band : '',
            'ym' => preg_match('/^\d{4}-\d{1,2}$/', (string) ($in['ym'] ?? '')) ? (string) $in['ym'] : '',
        ];
    }

    /** The people the filters keep (the expiry range drops records with no date, as in the design). */
    public static function rows(array $staff, array $f): array
    {
        $q = mb_strtolower($f['q']);

        return array_values(array_filter($staff, function ($r) use ($f, $q) {
            $d = $r[$f['kind']]['date'];
            if ($q !== '' && !str_contains(mb_strtolower($r['first']), $q) && !str_contains(mb_strtolower($r['last']), $q) && !str_contains(mb_strtolower($r['name']), $q)) return false;
            if ($f['status'] === '1' && $r['status'] !== 1) return false;
            if ($f['status'] === '0' && $r['status'] !== 0) return false;
            if ($f['status'] === 'other' && in_array($r['status'], [0, 1], true)) return false;
            if ($f['type'] !== '' && (string) $r['type'] !== $f['type']) return false;
            if ($f['department'] !== '' && (string) $r['department'] !== $f['department']) return false;
            if ($f['ethnicity'] !== '' && (string) $r['ethnicityId'] !== $f['ethnicity']) return false;
            if ($f['nationality'] !== '' && (string) $r['nationalityId'] !== $f['nationality']) return false;
            if ($f['gender'] !== '' && (string) $r['genderId'] !== $f['gender']) return false;
            if (($f['from'] !== '' || $f['to'] !== '') && (!$d || ($f['from'] !== '' && $d < $f['from']) || ($f['to'] !== '' && $d > $f['to']))) return false;

            return true;
        }));
    }

    /** Band counts, the 12 months from this month, and the list (needs attention, a band or a month). */
    public static function build(array $rows, array $f, Carbon $today): array
    {
        $k = $f['kind'];
        $counts = array_fill_keys(array_keys(self::BANDS), 0);
        foreach ($rows as $r) $counts[$r[$k]['band']]++;
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $m0 = $today->copy()->startOfMonth()->addMonthsNoOverflow($i);
            $months[] = ['first' => $m0, 'key' => $m0->year.'-'.($m0->month - 1),
                'people' => array_values(array_filter($rows, fn ($r) => $r[$k]['date'] && $r[$k]['days'] >= 0 && substr($r[$k]['date'], 0, 7) === $m0->format('Y-m')))];
        }
        if ($f['ym'] !== '') {
            $m = array_values(array_filter($months, fn ($m) => $m['key'] === $f['ym']))[0] ?? null;
            $shown = $m ? $m['people'] : [];
            $title = $m ? 'Expiring in '.$m['first']->format('F Y') : 'Expiring';
        } elseif ($f['band'] !== '') {
            $shown = array_values(array_filter($rows, fn ($r) => $r[$k]['band'] === $f['band']));
            $title = self::BANDS[$f['band']][0];
        } else {
            $shown = array_values(array_filter($rows, fn ($r) => in_array($r[$k]['band'], ['s30', 's90', 'x'], true)));
            $title = 'Needs attention';
        }
        $order = ['s30', 's90', 's180', 'ok', 'x', 'nd'];
        usort($shown, function ($a, $b) use ($k, $order) {
            $c = array_search($a[$k]['band'], $order, true) <=> array_search($b[$k]['band'], $order, true);
            if ($c) return $c;
            if ($a[$k]['band'] === 'x') return [$b[$k]['days'], $a['name']] <=> [$a[$k]['days'], $b['name']];
            if ($a[$k]['band'] === 'nd') return strcasecmp($a['name'], $b['name']);

            return [$a[$k]['days'], $a['name']] <=> [$b[$k]['days'], $b['name']];
        });

        return ['rows' => $rows, 'counts' => $counts, 'months' => $months, 'shown' => $shown, 'title' => $title];
    }

    /** "In 10 days", "In 3 months", "Expired 2 months ago", "Expires today". */
    public static function when(?int $days): string
    {
        if ($days === null) return 'No date';
        $a = abs($days);
        $span = $a < 60 ? $a.' day'.($a === 1 ? '' : 's') : ($a < 730 ? round($a / 30.44).' months' : rtrim(rtrim(number_format($a / 365.25, 1), '0'), '.').' years');
        if ($days === 0) return 'Expires today';

        return $days < 0 ? 'Expired '.$span.' ago' : 'In '.$span;
    }
}
