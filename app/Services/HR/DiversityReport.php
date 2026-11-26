<?php

namespace App\Services\HR;

use App\Models\Employee;

/**
 * HR Portal › Reports › Diversity Information, new version (user's diversity-information-report design,
 * 26 Nov 2026).
 *
 * Same staff as the old report (DiversityReportController::list): status Active (1), Inactive (0) or
 * All (0 and 1); type, department, start and end date from the latest employment, with the old
 * rules (started on or before the start date; ended on or after the end date, so staff with no end
 * date drop out once an end date is set); ethnicity, nationality and gender by id.
 * The old last column, headed "Status", is the employee's disability status: here it is "Disability".
 *
 * The screen gets staff() once and filters in the browser (hr-diversity-report-new.js); the PDF and
 * the Excel use rows(). Ethnic group and detail are worked out here for all three.
 */
class DiversityReport
{
    public const STATUSES = ['1' => 'Active', '0' => 'Inactive', '2' => 'All'];
    public const GROUPS = ['Asian', 'White', 'Black', 'Mixed', 'Other', 'Not known'];
    public const DIMS = ['gender' => 'Gender', 'ethnic' => 'Ethnic group', 'nation' => 'Nationality', 'disability' => 'Disability'];

    /** Broad ethnic group; the two wordings in use ("Asian or Asian British - …", "Asian - … British") land together. */
    public static function group(string $e): string
    {
        if ($e === '' || preg_match('/^(Not known|Information refused|Prefer not to say|Not available)$/i', $e)) return 'Not known';
        if (preg_match('/mixed/i', $e)) return 'Mixed';
        if (preg_match('/Asian|Chinese|Filipino/i', $e)) return 'Asian';
        if (preg_match('/Black/i', $e)) return 'Black';
        if (preg_match('/White|Gypsy|Traveller|Roma|Showman/i', $e)) return 'White';

        return 'Other';
    }

    /** The group's parts, with the two wordings merged ("Bangladeshi", "African", "Other White"…). */
    public static function detail(string $e): string
    {
        if ($e === '') return 'Not recorded';
        $rules = [
            '/^Mixed.*Asian/i' => 'White and Asian', '/^Mixed.*African/i' => 'White and Black African', '/^Mixed.*Caribbean/i' => 'White and Black Caribbean', '/mixed/i' => 'Other mixed',
            '/Bangladeshi/' => 'Bangladeshi', '/Pakistani/' => 'Pakistani', '/Indian/' => 'Indian', '/Chinese/' => 'Chinese', '/Filipino/' => 'Filipino', '/other Asian/i' => 'Other Asian',
            '/Caribbean/' => 'Caribbean', '/African/' => 'African', '/other Black/i' => 'Other Black',
            '/Gypsy|Traveller/' => 'Gypsy or Traveller', '/Showman/' => 'Showman / Showwoman', '/^White - Roma$/' => 'Roma', '/^White - Polish$/' => 'Polish',
            '/^White - (English|British|Scottish)/' => 'British', '/^White - Irish$/' => 'Irish', '/other White/i' => 'Other White', '/^White$/' => 'White, not specified',
            '/^Arab$/' => 'Arab', '/other ethnic/i' => 'Other background',
            '/^(Information refused|Prefer not to say)$/i' => 'Prefer not to say',
        ];
        foreach ($rules as $re => $label) {
            if (preg_match($re, $e)) return $label;
        }

        return $e;
    }

    /** Active and inactive staff (the old "All"), one row each. */
    public function staff(): array
    {
        return Employee::with(['employment', 'sex', 'ethnicity', 'nationality', 'employment.employeeWorkType', 'employment.department'])
            ->whereIn('status', [0, 1])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get()
            ->map(function ($e) {
                $eth = (string) ($e->ethnicity->name ?? '');
                $date = fn ($v) => ($v && $v !== '0000-00-00' && strtotime($v)) ? date('Y-m-d', strtotime($v)) : null;

                return [
                    'id' => $e->id,
                    'name' => trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))),
                    'works' => (string) ($e->employment->works_number ?? ''),
                    'genderId' => $e->sex_identifier_id,
                    'gender' => (string) ($e->sex->name ?? ''),
                    'ethnicityId' => $e->ethnicity_id,
                    'ethnicity' => $eth,
                    'ethGroup' => self::group($eth),
                    'ethDetail' => self::detail($eth),
                    'nationalityId' => $e->nationality_id,
                    'nationality' => (string) ($e->nationality->name ?? ''),
                    'disability' => (string) ($e->disability_status ?? ''),
                    'status' => (int) $e->status,
                    'type' => $e->employment->employee_work_type_id ?? null,
                    'typeName' => (string) ($e->employment->employeeWorkType->name ?? ''),
                    'department' => $e->employment->department_id ?? null,
                    'departmentName' => (string) ($e->employment->department->name ?? ''),
                    'started' => $date(optional($e->employment)->getRawOriginal('started_on')),
                    'ended' => $date(optional($e->employment)->getRawOriginal('ended_on')),
                ];
            })
            ->values()
            ->all();
    }

    public static function filters(array $in): array
    {
        $status = (string) ($in['status'] ?? '1');
        $day = fn ($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? (string) $v : '';
        $dim = (string) ($in['dim'] ?? 'ethnic');

        return [
            'type' => (string) ($in['type'] ?? ''),
            'department' => (string) ($in['department'] ?? ''),
            'from' => $day($in['from'] ?? ''),
            'to' => $day($in['to'] ?? ''),
            'ethnicity' => (string) ($in['ethnicity'] ?? ''),
            'nationality' => (string) ($in['nationality'] ?? ''),
            'gender' => (string) ($in['gender'] ?? ''),
            'status' => array_key_exists($status, self::STATUSES) ? $status : '1',
            'dim' => array_key_exists($dim, self::DIMS) ? $dim : 'ethnic',
            'pick' => (string) ($in['pick'] ?? ''),
        ];
    }

    /** The staff the filters keep, as the old list() picks them. */
    public function rows(array $f, ?array $staff = null): array
    {
        return array_values(array_filter($staff ?? $this->staff(), function ($r) use ($f) {
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

    /** The four ways of colouring the dots; nationality keeps the four largest and folds the rest. */
    public static function dimension(string $dim, array $list): array
    {
        $tally = [];
        if ($dim === 'nation') {
            foreach ($list as $p) {
                if ($p['nationality'] !== '') $tally[$p['nationality']] = ($tally[$p['nationality']] ?? 0) + 1;
            }
            arsort($tally);
            $top = array_slice(array_keys($tally), 0, 4);
            $of = fn ($p) => $p['nationality'] === '' ? 'Not recorded' : (in_array($p['nationality'], $top, true) ? $p['nationality'] : 'Other nationalities');
            $order = array_merge($top, ['Other nationalities', 'Not recorded']);
        } elseif ($dim === 'gender') {
            $of = fn ($p) => $p['gender'] !== '' ? $p['gender'] : 'Not recorded';
            $order = ['Male', 'Female', 'Other', 'Information refused', 'Not available', 'Not recorded'];
        } elseif ($dim === 'disability') {
            $of = fn ($p) => $p['disability'] === 'Yes' ? 'Yes' : ($p['disability'] === 'No' ? 'No' : 'Not recorded');
            $order = ['No', 'Yes', 'Not recorded'];
        } else {
            $of = fn ($p) => $p['ethGroup'];
            $order = self::GROUPS;
        }
        $counts = [];
        foreach ($list as $p) {
            $k = $of($p);
            $counts[$k] = ($counts[$k] ?? 0) + 1;
        }
        $keys = array_values(array_filter($order, fn ($k) => isset($counts[$k])));
        foreach (array_keys($counts) as $k) {
            if (!in_array($k, $keys, true)) $keys[] = $k; // a value outside the usual list still shows
        }

        return ['of' => $of, 'keys' => $keys, 'counts' => $counts, 'label' => self::DIMS[$dim]];
    }
}
