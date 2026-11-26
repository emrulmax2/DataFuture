<?php

namespace App\Services\HR;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * HR Portal › Reports › Employee Record Card, new version (user's employee-record-card design, 26 Nov 2026).
 *
 * Same staff and filters as the old report (RecordCardController::list): status, type, department,
 * start/end date on the latest employment (started on or before; ended on or after), ethnicity,
 * nationality and gender. Fixed here: the old card showed the first name as "Surname" and the last name
 * as "Forename", and "Grade" repeated the job title (there is no grade on file; the card shows the
 * department instead). The address gets its town and post code, and the emergency contact its name and
 * relationship.
 */
class RecordCardReport
{
    public const STATUSES = ['1' => 'Active', '0' => 'Inactive', '2' => 'All'];
    public const COMPANY = 'London Churchill College';
    /** Details every record should have; landline, emergency landline/email and car registration are optional. */
    public const KEY = ['title', 'forename', 'surname', 'dob', 'gender', 'ethnicity', 'nationality', 'ni', 'works', 'jobTitle', 'department', 'started', 'address', 'mobile', 'email', 'eName', 'eMob', 'disability'];

    public function staff(?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();

        return Employee::with(['title', 'sex', 'ethnicity', 'nationality', 'address', 'employment', 'employment.employeeJobTitle', 'employment.department', 'employment.employeeWorkType', 'emergencyContact', 'emergencyContact.kin'])
            ->whereIn('status', [0, 1])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get()
            ->map(function ($e) use ($today) {
                $date = fn ($v) => ($v && $v !== '0000-00-00' && strtotime($v)) ? date('Y-m-d', strtotime($v)) : null;
                $a = $e->address;
                $ec = $e->emergencyContact;
                $street = implode(', ', array_filter([trim((string) ($a->address_line_1 ?? '')), trim((string) ($a->address_line_2 ?? ''))]));
                $town = trim(trim((string) ($a->city ?? '')).' '.trim((string) ($a->post_code ?? '')));
                // Profile photo when the file is there (as Employee::photo_url); otherwise the card shows initials.
                $photoFile = $e->photo ? 'public/employees/'.$e->id.'/'.$e->photo : null;
                $hasPhoto = $photoFile && Storage::disk('local')->exists($photoFile);
                $r = [
                    'id' => $e->id,
                    'photo' => $hasPhoto ? Storage::disk('local')->url($photoFile) : null,
                    'photoPath' => $hasPhoto ? Storage::disk('local')->path($photoFile) : null,
                    'title' => trim((string) ($e->title->name ?? '')),
                    'forename' => trim((string) $e->first_name),
                    'surname' => trim((string) $e->last_name),
                    'dob' => $date($e->getRawOriginal('date_of_birth')),
                    'gender' => (string) ($e->sex->name ?? ''),
                    'ethnicity' => (string) ($e->ethnicity->name ?? ''),
                    'nationality' => (string) ($e->nationality->name ?? ''),
                    'ni' => trim((string) $e->ni_number),
                    'works' => (string) ($e->employment->works_number ?? ''),
                    'jobTitle' => (string) ($e->employment->employeeJobTitle->name ?? ''),
                    'department' => (string) ($e->employment->department->name ?? ''),
                    'typeName' => (string) ($e->employment->employeeWorkType->name ?? ''),
                    'started' => $date(optional($e->employment)->getRawOriginal('started_on')),
                    'ended' => $date(optional($e->employment)->getRawOriginal('ended_on')),
                    'address' => implode(', ', array_filter([$street, $town])),
                    'telephone' => trim((string) $e->telephone),
                    'mobile' => trim((string) $e->mobile),
                    'email' => trim((string) $e->email),
                    'eName' => trim((string) ($ec->emergency_contact_name ?? '')),
                    'eRel' => trim((string) ($ec->kin->name ?? '')),
                    'eTel' => trim((string) ($ec->emergency_contact_telephone ?? '')),
                    'eMob' => trim((string) ($ec->emergency_contact_mobile ?? '')),
                    'eEmail' => trim((string) ($ec->emergency_contact_email ?? '')),
                    'disability' => (string) ($e->disability_status ?? ''),
                    'carReg' => trim((string) $e->car_reg_number),
                    'status' => (int) $e->status,
                    // filters (as the old list())
                    'typeId' => $e->employment->employee_work_type_id ?? null,
                    'departmentId' => $e->employment->department_id ?? null,
                    'ethnicityId' => $e->ethnicity_id,
                    'nationalityId' => $e->nationality_id,
                    'genderId' => $e->sex_identifier_id,
                ];
                $r['age'] = $r['dob'] ? Carbon::parse($r['dob'])->diffInYears($today) : null;
                $r['service'] = null;
                if ($r['started']) {
                    $start = Carbon::parse($r['started']);
                    $to = ($r['ended'] && $r['ended'] <= $today->format('Y-m-d')) ? Carbon::parse($r['ended']) : $today;
                    if ($to->gte($start)) {
                        $d = $start->diff($to);
                        $u = fn ($n, $w) => $n.' '.$w.($n === 1 ? '' : 's');
                        $r['service'] = $d->y ? $u($d->y, 'year').($d->m ? ', '.$u($d->m, 'month') : '') : $u($d->m, 'month');
                    }
                }
                $r['missing'] = array_values(array_filter(self::KEY, fn ($k) => $r[$k] === '' || $r[$k] === null));

                return $r;
            })
            ->values()
            ->all();
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
            'q' => trim((string) ($in['q'] ?? '')),
            'ids' => array_values(array_filter(array_map('intval', explode(',', (string) ($in['ids'] ?? ''))))),
        ];
    }

    /** The staff the old filters keep; `q` narrows by name or works number; `ids` picks single cards. */
    public static function rows(array $staff, array $f): array
    {
        $q = mb_strtolower($f['q']);

        return array_values(array_filter($staff, function ($r) use ($f, $q) {
            if ($f['ids']) return in_array($r['id'], $f['ids'], true);
            if ($f['status'] !== '2' && (string) $r['status'] !== $f['status']) return false;
            if ($f['type'] !== '' && (string) $r['typeId'] !== $f['type']) return false;
            if ($f['department'] !== '' && (string) $r['departmentId'] !== $f['department']) return false;
            if ($f['from'] !== '' && !($r['started'] && $r['started'] <= $f['from'])) return false;
            if ($f['to'] !== '' && !($r['ended'] && $r['ended'] >= $f['to'])) return false;
            if ($f['ethnicity'] !== '' && (string) $r['ethnicityId'] !== $f['ethnicity']) return false;
            if ($f['nationality'] !== '' && (string) $r['nationalityId'] !== $f['nationality']) return false;
            if ($f['gender'] !== '' && (string) $r['genderId'] !== $f['gender']) return false;
            if ($q !== '' && !str_contains(mb_strtolower($r['title'].' '.$r['forename'].' '.$r['surname'].' '.$r['works']), $q)) return false;

            return true;
        }));
    }
}
