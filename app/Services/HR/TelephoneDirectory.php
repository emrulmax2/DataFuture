<?php

namespace App\Services\HR;

use App\Models\Employee;

/**
 * HR Portal › Reports › Employee Telephone Directory, new version (user's employee-telephone-directory
 * design, 26 Nov 2026).
 *
 * Same staff and filters as the old report (TelephoneDirectoryController::searchlist): status, type,
 * department, start/end date on the latest employment (started on or before; ended on or after),
 * ethnicity, nationality and gender; filed by the first letter of the first name, as before.
 * New here: UK numbers are printed in one format (+44 → 0, standard spacing); a telephone that repeats
 * the mobile is shown once; landlines are told apart from mobiles.
 */
class TelephoneDirectory
{
    public const STATUSES = ['1' => 'Active', '0' => 'Inactive', '2' => 'All'];
    public const CHIPS = ['land' => 'Has a landline', 'nomob' => 'No mobile', 'noemail' => 'No email'];

    /** One format for UK numbers; anything else is left as typed. */
    public static function phone(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '' || !preg_match('/\d/', $raw)) return '';
        $c = preg_replace('/[\s().-]/', '', $raw);
        if (preg_match('/^(?:\+|00)?44(?:0)?(\d{10})$/', $c, $m)) {
            $d = '0'.$m[1];
        } elseif (preg_match('/^7\d{9}$/', $c)) {
            $d = '0'.$c;
        } else {
            $d = $c;
        }
        if (!preg_match('/^0\d{10}$/', $d)) return $raw;
        if (str_starts_with($d, '07')) return substr($d, 0, 5).' '.substr($d, 5);
        if (str_starts_with($d, '02')) return substr($d, 0, 3).' '.substr($d, 3, 4).' '.substr($d, 7);
        if (preg_match('/^01(1\d|\d1)/', $d)) return substr($d, 0, 4).' '.substr($d, 4, 3).' '.substr($d, 7); // 0161 496 0000

        return substr($d, 0, 5).' '.substr($d, 5);
    }

    private static function digits(string $v): string
    {
        return preg_replace('/\D/', '', $v);
    }

    public function staff(): array
    {
        return Employee::with(['employment'])
            ->whereIn('status', [0, 1])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get()
            ->map(function ($e) {
                $date = fn ($v) => ($v && $v !== '0000-00-00' && strtotime($v)) ? date('Y-m-d', strtotime($v)) : null;
                $name = trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? '')));
                $first = mb_strtoupper(mb_substr(trim((string) $e->first_name), 0, 1));
                $mob = self::phone($e->mobile);
                $tel = self::phone($e->telephone);
                $dup = $tel !== '' && $mob !== '' && self::digits($tel) === self::digits($mob);
                if ($dup) $tel = '';
                $isMobile = fn ($v) => str_starts_with(self::digits($v), '07');
                // A "mobile" that is a landline number, or a telephone that is a mobile, keeps its own label.
                return [
                    'id' => $e->id,
                    'name' => $name,
                    'letter' => preg_match('/^[A-Z]$/', $first) ? $first : '#',
                    'mob' => $mob,
                    'tel' => $tel,
                    'land' => $tel !== '' && !$isMobile($tel),
                    'dup' => $dup,
                    'email' => trim((string) $e->email),
                    'works' => (string) ($e->employment->works_number ?? ''),
                    'status' => (int) $e->status,
                    'type' => $e->employment->employee_work_type_id ?? null,
                    'department' => $e->employment->department_id ?? null,
                    'started' => $date(optional($e->employment)->getRawOriginal('started_on')),
                    'ended' => $date(optional($e->employment)->getRawOriginal('ended_on')),
                    'ethnicityId' => $e->ethnicity_id,
                    'nationalityId' => $e->nationality_id,
                    'genderId' => $e->sex_identifier_id,
                ];
            })
            ->values()
            ->all();
    }

    public static function filters(array $in): array
    {
        $status = (string) ($in['status'] ?? '1');
        $day = fn ($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? (string) $v : '';
        $chip = (string) ($in['chip'] ?? '');
        $letter = strtoupper((string) ($in['letter'] ?? ''));

        return [
            'type' => (string) ($in['type'] ?? ''), 'department' => (string) ($in['department'] ?? ''),
            'from' => $day($in['from'] ?? ''), 'to' => $day($in['to'] ?? ''),
            'ethnicity' => (string) ($in['ethnicity'] ?? ''), 'nationality' => (string) ($in['nationality'] ?? ''), 'gender' => (string) ($in['gender'] ?? ''),
            'status' => array_key_exists($status, self::STATUSES) ? $status : '1',
            'q' => trim((string) ($in['q'] ?? '')),
            'chip' => array_key_exists($chip, self::CHIPS) ? $chip : '',
            'letter' => preg_match('/^([A-Z]|#)$/', $letter) ? $letter : '',
        ];
    }

    /** The old filters. */
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

    /** Search (name, email or digits of a number), quick filter and letter, as on screen. */
    public static function shown(array $rows, array $f): array
    {
        $q = mb_strtolower($f['q']);
        $d = preg_replace('/\s+/', '', $q);

        return array_values(array_filter($rows, function ($p) use ($f, $q, $d) {
            if ($q !== '' && !str_contains(mb_strtolower($p['name']), $q) && !str_contains(mb_strtolower($p['email']), $q)
                && !(preg_match('/\d/', $d) && str_contains(self::digits($p['mob'].$p['tel']), self::digits($d)))) return false;
            if ($f['chip'] === 'land' && !$p['land']) return false;
            if ($f['chip'] === 'nomob' && $p['mob'] !== '') return false;
            if ($f['chip'] === 'noemail' && $p['email'] !== '') return false;
            if ($f['letter'] !== '' && $p['letter'] !== $f['letter']) return false;

            return true;
        }));
    }

    /** People by letter (A–Z, then #), each letter's people by name. */
    public static function byLetter(array $list): array
    {
        $g = [];
        foreach ($list as $p) $g[$p['letter']][] = $p;
        uksort($g, fn ($a, $b) => ($a === '#') <=> ($b === '#') ?: strcmp($a, $b));
        foreach ($g as &$people) usort($people, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return $g;
    }
}
