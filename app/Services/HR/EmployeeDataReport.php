<?php

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternPay;

/**
 * HR Portal › Reports › Employee Data Report (user's employee-data-report design, 26 Nov 2026).
 *
 * The same 49 fields as the old form, the same tables and the same rules for which record a value comes from:
 * the latest employment, eligibility, emergency contact and payment setting; the active bank detail when the
 * payment method is Bank Transfer; contracted hours from the active open working pattern; salary and hourly
 * rate from the latest active pay on the latest active pattern.
 *
 * Fixed here (the old export):
 * - columns follow the order the fields were ticked, and every row has every column; the old headings
 *   were in form order but the cells were in table order, so Line Manager and the office fields swapped,
 *   and a missing bank detail or working pattern dropped cells and shifted the row left;
 * - the Department and start-date filters work (the old ones were never read) and the start dates filter
 *   Started On alone (the old code also asked Ended On to fall between them);
 * - Status says Active / Inactive / Other (the old column always said Active);
 * - Permit Type no longer needs a proof-of-ID type to be shown;
 * - dates are all dd-mm-yyyy, addresses and lists drop empty parts, and phone numbers, sort codes and
 *   account numbers stay text so leading zeros are kept.
 */
class EmployeeDataReport
{
    public const GROUPS = [
        'personal' => 'Personal info',
        'employment' => 'Employment info',
        'eligibility' => 'Eligibility',
        'emergency' => 'Emergency contacts',
        'payments' => 'Payments info',
        'working' => 'Working patterns',
    ];

    /** key => [group, label on the form, column heading, sensitive] */
    public const FIELDS = [
        'title_id' => ['personal', 'Title', 'Title', false],
        'last_name' => ['personal', 'Last Name', 'Last Name', false],
        'first_name' => ['personal', 'First Name(s)', 'First Name(s)', false],
        'date_of_birth' => ['personal', 'Date of Birth', 'Date of Birth', true],
        'sex_identifier_id' => ['personal', 'Sex', 'Sex', false],
        'nationality_id' => ['personal', 'Nationality', 'Nationality', false],
        'ethnicity_id' => ['personal', 'Ethnic Origin', 'Ethnic Origin', true],
        'ni_number' => ['personal', 'NI Number', 'NI Number', true],
        'disability_status' => ['personal', 'Disabled', 'Disabled', true],
        'email' => ['personal', 'Email', 'Email', false],
        'telephone' => ['personal', 'Telephone', 'Telephone', false],
        'mobile' => ['personal', 'Mobile', 'Mobile', false],
        'address_id' => ['personal', 'Address', 'Address', false],
        'status' => ['personal', 'Status', 'Status', false],

        'started_on' => ['employment', 'Started On', 'Started On', false],
        'ended_on' => ['employment', 'Ended On', 'Ended On', false],
        'employee_work_type_id' => ['employment', 'Type', 'Type', false],
        'works_number' => ['employment', 'Work Number', 'Work Number', false],
        'punch_number' => ['employment', 'Punch Number', 'Punch Number', false],
        'employee_job_title_id' => ['employment', 'Job Title', 'Job Title', false],
        'department_id' => ['employment', 'Department', 'Department', false],
        'site_location' => ['employment', 'Site Location', 'Site Location', false],
        'line_manager' => ['employment', 'Line Manager', 'Line Manager', false],
        'office_telephone' => ['employment', 'Office Telephone', 'Office Telephone', false],
        'office_mobile' => ['employment', 'Office Mobile', 'Office Mobile', false],
        'office_email' => ['employment', 'Email', 'Work Email', false],

        'eligible_to_work' => ['eligibility', 'Eligible to work in UK', 'Eligible to work in UK', false],
        'employee_work_permit_type_id' => ['eligibility', 'Permit Type', 'Permit Type', false],
        'workpermit_number' => ['eligibility', 'Work Permit Number', 'Work Permit Number', true],
        'workpermit_expire' => ['eligibility', 'Work Permit Expire Date', 'Work Permit Expire Date', false],
        'document_type' => ['eligibility', 'Proof of ID Type', 'Proof of ID Type', false],
        'doc_number' => ['eligibility', 'ID Number', 'ID Number', true],
        'doc_expire' => ['eligibility', 'Expiry Date', 'ID Expiry Date', false],
        'doc_issue_country' => ['eligibility', 'Issuing Country', 'Issuing Country', false],

        'emergency_contact_name' => ['emergency', 'Emergency Contact Name', 'Emergency Contact Name', false],
        'kins_relation_id' => ['emergency', 'Kin Relation', 'Kin Relation', false],
        'emergency_contact_telephone' => ['emergency', 'Telephone', 'Emergency Telephone', false],
        'emergency_contact_mobile' => ['emergency', 'Mobile', 'Emergency Mobile', false],
        'emergency_contact_email' => ['emergency', 'Email', 'Emergency Email', false],
        'emergency_address_id' => ['emergency', 'Address', 'Emergency Address', false],

        'pay_frequency' => ['payments', 'Pay Frequency', 'Pay Frequency', false],
        'tax_code' => ['payments', 'Tax Code', 'Tax Code', false],
        'payment_method' => ['payments', 'Payment Method', 'Payment Method', false],
        'sort_code' => ['payments', 'Sort Code', 'Sort Code', true],
        'ac_no' => ['payments', 'Account Number', 'Account Number', true],

        'effective_from' => ['working', 'Effective From', 'Working Pattern Effective From', false],
        'contracted_hour' => ['working', 'Contracted Hour(s)', 'Contracted Hour(s)', false],
        'salary' => ['working', 'Salary', 'Salary', true],
        'hourly_rate' => ['working', 'Hourly Rate', 'Hourly Rate', true],
    ];

    public const STATUSES = ['1' => 'Active', '0' => 'Inactive', '2' => 'All'];

    public static function filters(array $in): array
    {
        $day = function ($v) {
            $v = trim((string) $v);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $v, $m)) return "$m[3]-$m[2]-$m[1]";

            return '';
        };
        $status = (string) ($in['status'] ?? '1');

        return [
            'type' => (string) ($in['type'] ?? ''),
            'department' => (string) ($in['department'] ?? ''),
            'gender' => (string) ($in['gender'] ?? ''),
            'from' => $day($in['from'] ?? ''),
            'to' => $day($in['to'] ?? ''),
            'status' => array_key_exists($status, self::STATUSES) ? $status : '1',
        ];
    }

    /** Ticked field keys in the order they were ticked; unknown keys and repeats dropped. */
    public static function fields($in): array
    {
        return array_values(array_unique(array_filter((array) $in, fn ($k) => is_string($k) && isset(self::FIELDS[$k]))));
    }

    public function query(array $f)
    {
        $q = Employee::query();
        if ($f['status'] !== '2') $q->where('status', (int) $f['status']);
        if ($f['gender'] !== '') $q->where('sex_identifier_id', $f['gender']);
        if ($f['type'] !== '' || $f['department'] !== '' || $f['from'] !== '' || $f['to'] !== '') {
            $q->whereHas('employment', function ($e) use ($f) {
                if ($f['type'] !== '') $e->where('employee_work_type_id', $f['type']);
                if ($f['department'] !== '') $e->where('department_id', $f['department']);
                if ($f['from'] !== '') $e->whereDate('started_on', '>=', $f['from']);
                if ($f['to'] !== '') $e->whereDate('started_on', '<=', $f['to']);
            });
        }

        return $q;
    }

    public function count(array $f): int
    {
        return $this->query($f)->count();
    }

    /** Heading row first, then one row per employee (first name order, as the old export). */
    public function sheet(array $f, array $fields): array
    {
        $employees = $this->query($f)
            ->with(['title', 'address', 'ethnicity', 'nationality', 'sex', 'venues', 'lineManagers.user', 'payment',
                'employment.employeeWorkType', 'employment.employeeJobTitle', 'employment.department',
                'eligibilities.employeeWorkPermitType', 'eligibilities.employeeDocType', 'eligibilities.docIssueCountry',
                'emergencyContact.kin', 'emergencyContact.address'])
            ->orderBy('first_name')->orderBy('last_name')->orderBy('id')
            ->get();
        $ids = $employees->pluck('id');
        $need = fn (array $keys) => (bool) array_intersect($keys, $fields);
        $banks = $need(['sort_code', 'ac_no'])
            ? EmployeeBankDetail::whereIn('employee_id', $ids)->where('active', 1)->orderBy('id')->get()->groupBy('employee_id')->map->first()
            : collect();
        $open = $need(['effective_from', 'contracted_hour'])
            ? EmployeeWorkingPattern::whereIn('employee_id', $ids)->where('active', 1)->whereNull('end_to')->orderBy('id')->get()->groupBy('employee_id')->map->first()
            : collect();
        $pays = collect();
        if ($need(['salary', 'hourly_rate'])) {
            $latest = EmployeeWorkingPattern::whereIn('employee_id', $ids)->where('active', 1)->orderByDesc('id')->get()->groupBy('employee_id')->map->first();
            $byPattern = EmployeeWorkingPatternPay::whereIn('employee_working_pattern_id', $latest->pluck('id'))->where('active', 1)->orderByDesc('id')->get()->groupBy('employee_working_pattern_id')->map->first();
            $pays = $latest->map(fn ($p) => $byPattern->get($p->id));
        }

        $rows = [array_merge(['Serial'], array_map(fn ($k) => self::FIELDS[$k][2], $fields))];
        foreach ($employees as $i => $e) {
            $row = [$i + 1];
            foreach ($fields as $k) {
                $v = $this->value($e, $k, $banks, $open, $pays);
                // Money and hours stay numbers in the sheet; everything else is text (leading zeros kept).
                $row[] = in_array($k, ['salary', 'hourly_rate', 'contracted_hour'], true) && is_numeric($v) ? $v + 0 : $v;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function value(Employee $e, string $k, $banks, $open, $pays): string
    {
        $em = $e->employment;
        $el = $e->eligibilities;
        $ec = $e->emergencyContact;
        $s = fn ($v) => trim((string) ($v ?? ''));

        switch ($k) {
            case 'title_id': return $s($e->title->name ?? '');
            case 'sex_identifier_id': return $s($e->sex->name ?? '');
            case 'nationality_id': return $s($e->nationality->name ?? '');
            case 'ethnicity_id': return $s($e->ethnicity->name ?? '');
            case 'date_of_birth': return self::date($e->getRawOriginal('date_of_birth'));
            case 'status': return $e->status === 1 ? 'Active' : ($e->status === 0 ? 'Inactive' : 'Other');
            case 'address_id': return self::address($e->address);
            case 'started_on': return self::date($em ? $em->getRawOriginal('started_on') : null);
            case 'ended_on': return self::date($em->ended_on ?? null);
            case 'employee_work_type_id': return $s($em->employeeWorkType->name ?? '');
            case 'employee_job_title_id': return $s($em->employeeJobTitle->name ?? '');
            case 'department_id': return $s($em->department->name ?? '');
            case 'works_number':
            case 'punch_number':
            case 'office_telephone': return $s($em->$k ?? '');
            case 'office_mobile': return $s($em->mobile ?? '');
            case 'office_email': return $s($em->email ?? '');
            case 'site_location': return $e->venues->pluck('name')->filter()->implode(', ');
            case 'line_manager': return $e->lineManagers->map(fn ($l) => $l->user->full_name ?? '')->filter()->implode(', ');
            case 'employee_work_permit_type_id': return $s($el->employeeWorkPermitType->name ?? '');
            case 'document_type': return $s($el->employeeDocType->name ?? '');
            case 'doc_issue_country': return $s($el->docIssueCountry->name ?? '');
            case 'workpermit_expire': return self::date($el ? $el->getRawOriginal('workpermit_expire') : null);
            case 'doc_expire': return self::date($el ? $el->getRawOriginal('doc_expire') : null);
            case 'eligible_to_work':
            case 'workpermit_number':
            case 'doc_number': return $s($el->$k ?? '');
            case 'kins_relation_id': return $s($ec->kin->name ?? '');
            case 'emergency_address_id': return self::address($ec->address ?? null);
            case 'emergency_contact_name':
            case 'emergency_contact_telephone':
            case 'emergency_contact_mobile':
            case 'emergency_contact_email': return $s($ec->$k ?? '');
            case 'pay_frequency':
            case 'tax_code':
            case 'payment_method': return $s($e->payment->$k ?? '');
            case 'sort_code':
            case 'ac_no':
                // As the old export: bank details only for people paid by Bank Transfer.
                return ($e->payment->payment_method ?? '') === 'Bank Transfer' ? $s($banks->get($e->id)->$k ?? '') : '';
            case 'effective_from':
                $p = $open->get($e->id);

                return self::date($p ? $p->getRawOriginal('effective_from') : null);
            case 'contracted_hour': return $s($open->get($e->id)->contracted_hour ?? '');
            case 'salary':
            case 'hourly_rate': return $s($pays->get($e->id)->$k ?? '');
            default: return $s($e->$k ?? '');
        }
    }

    public static function date($v): string
    {
        if (!$v || !strtotime($v)) return '';
        $d = date('Y-m-d', strtotime($v));

        return $d < '1901-01-01' ? '' : date('d-m-Y', strtotime($d));
    }

    public static function address($a): string
    {
        if (!$a) return '';

        return collect([$a->address_line_1, $a->address_line_2, $a->city, $a->state, $a->post_code, $a->country ? ucwords((string) $a->country) : ''])
            ->map(fn ($x) => trim((string) $x))->filter()->implode(', ');
    }
}
