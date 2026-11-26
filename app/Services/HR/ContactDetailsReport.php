<?php

namespace App\Services\HR;

use App\Models\Employee;

/**
 * HR Portal › Reports › Employee Contact Details, new version (user's employee-contact-details design,
 * 26 Nov 2026).
 *
 * Same staff and filters as the old report (EmployeeContactDetailController::list): status, type,
 * department, start/end date (latest employment: started on or before, ended on or after), ethnicity,
 * nationality and gender. Fixed here: the old Post Code column read employees.post_code, which does not
 * exist (always empty); the post code and city come from the address. The emergency contact's name and
 * relationship, on file but never shown before, are shown.
 */
class ContactDetailsReport
{
    public const STATUSES = ['1' => 'Active', '0' => 'Inactive', '2' => 'All'];
    public const GAPS = ['phone' => 'No phone number', 'email' => 'No email', 'address' => 'No address', 'emergency' => 'No emergency number'];

    public function staff(): array
    {
        return Employee::with(['employment', 'address', 'emergencyContact', 'emergencyContact.kin'])
            ->whereIn('status', [0, 1])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get()
            ->map(function ($e) {
                $a = $e->address;
                $street = trim(implode(', ', array_filter([trim((string) ($a->address_line_1 ?? '')), trim((string) ($a->address_line_2 ?? ''))])));
                $town = trim(implode(' ', array_filter([trim((string) ($a->city ?? '')), trim((string) ($a->post_code ?? ''))])));
                $ec = $e->emergencyContact;
                $date = fn ($v) => ($v && $v !== '0000-00-00' && strtotime($v)) ? date('Y-m-d', strtotime($v)) : null;
                $r = [
                    'id' => $e->id,
                    'name' => trim(preg_replace('/\s+/', ' ', ($e->first_name ?? '').' '.($e->last_name ?? ''))),
                    'works' => (string) ($e->employment->works_number ?? ''),
                    'tel' => trim((string) $e->telephone),
                    'mob' => trim((string) $e->mobile),
                    'email' => trim((string) $e->email),
                    'address' => trim(implode(', ', array_filter([$street, $town]))),
                    'eName' => trim((string) ($ec->emergency_contact_name ?? '')),
                    'eRel' => trim((string) ($ec->kin->name ?? '')),
                    'eTel' => trim((string) ($ec->emergency_contact_telephone ?? '')),
                    'eMob' => trim((string) ($ec->emergency_contact_mobile ?? '')),
                    'eEmail' => trim((string) ($ec->emergency_contact_email ?? '')),
                    // filters (as the old list())
                    'status' => (int) $e->status,
                    'type' => $e->employment->employee_work_type_id ?? null,
                    'department' => $e->employment->department_id ?? null,
                    'started' => $date(optional($e->employment)->getRawOriginal('started_on')),
                    'ended' => $date(optional($e->employment)->getRawOriginal('ended_on')),
                    'ethnicityId' => $e->ethnicity_id,
                    'nationalityId' => $e->nationality_id,
                    'genderId' => $e->sex_identifier_id,
                ];
                $r['gaps'] = self::gaps($r);

                return $r;
            })
            ->values()
            ->all();
    }

    /** What a record lacks: a phone of their own, an email, an address, an emergency number. */
    public static function gaps(array $r): array
    {
        $g = [];
        if ($r['tel'] === '' && $r['mob'] === '') $g[] = 'phone';
        if ($r['email'] === '') $g[] = 'email';
        if ($r['address'] === '') $g[] = 'address';
        if ($r['eTel'] === '' && $r['eMob'] === '') $g[] = 'emergency';

        return $g;
    }

    public static function filters(array $in): array
    {
        $status = (string) ($in['status'] ?? '1');
        $day = fn ($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? (string) $v : '';
        $gap = (string) ($in['gap'] ?? '');

        return [
            'q' => trim((string) ($in['q'] ?? '')),
            'type' => (string) ($in['type'] ?? ''),
            'department' => (string) ($in['department'] ?? ''),
            'status' => array_key_exists($status, self::STATUSES) ? $status : '1',
            'from' => $day($in['from'] ?? ''),
            'to' => $day($in['to'] ?? ''),
            'ethnicity' => (string) ($in['ethnicity'] ?? ''),
            'nationality' => (string) ($in['nationality'] ?? ''),
            'gender' => (string) ($in['gender'] ?? ''),
            'gap' => ($gap === 'ok' || array_key_exists($gap, self::GAPS)) ? $gap : '',
        ];
    }

    /** The staff the old filters keep (before find-a-person and the gap picker). */
    public static function base(array $rows, array $f): array
    {
        return array_values(array_filter($rows, function ($r) use ($f) {
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

    /** Find a person (name, works number, phone, email or address; phones also without spaces), then the gap. */
    public static function shown(array $base, array $f): array
    {
        $q = mb_strtolower($f['q']);
        $digits = preg_replace('/\s+/', '', $q);

        return array_values(array_filter($base, function ($r) use ($f, $q, $digits) {
            if ($q !== '') {
                $text = mb_strtolower(preg_replace('/\s+/', ' ', implode(' ', [$r['name'], $r['works'], $r['tel'], $r['mob'], $r['email'], $r['address'], $r['eName'], $r['eTel'], $r['eMob'], $r['eEmail']])));
                $phones = preg_replace('/\s+/', '', $r['tel'].' '.$r['mob'].' '.$r['eTel'].' '.$r['eMob']);
                if (!str_contains($text, $q) && !(preg_match('/\d/', $digits) && str_contains($phones, $digits))) return false;
            }
            if ($f['gap'] === 'ok' && $r['gaps']) return false;
            if ($f['gap'] !== '' && $f['gap'] !== 'ok' && !in_array($f['gap'], $r['gaps'], true)) return false;

            return true;
        }));
    }
}
