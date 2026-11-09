<?php

namespace App\Services\TaskAssignment;

use App\Models\Applicant;
use App\Models\AwardingBody;
use App\Models\Country;
use App\Models\Course;
use App\Models\ResidencyStatus;
use Carbon\Carbon;

/**
 * The vocabulary a task assignment rule can test, and how each fact is read
 * off an application.
 *
 * Staff never type a column name: the settings screen offers these keys,
 * with the operators and the value control each type allows. Adding a fact
 * is one entry in fields() and one line in build().
 */
class ApplicationFacts
{
    public const OPERATORS = [
        'bool'   => ['is' => 'is'],
        'select' => ['is' => 'is', 'is_not' => 'is not', 'in' => 'is any of', 'not_in' => 'is none of'],
        'number' => ['eq' => 'equals', 'neq' => 'does not equal', 'gte' => 'is at least', 'lte' => 'is at most'],
    ];

    public const FUNDING = ['Independently/Private', 'Funding Body', 'Sponsor', 'Student Loan', 'Others'];

    public const EMPLOYMENT = [
        'Full Time', 'Part Time', 'Fixed Term', 'Contractor', 'Zero Hour', 'Seasonal',
        'Agency or Temp', 'Consultant', 'Office Holder', 'Volunteer', 'Unemployed',
    ];

    /** key => [label, type, group, options source] */
    public static function fields(): array
    {
        return [
            'has_qualification'    => ['label' => 'Has formal qualifications',          'type' => 'bool',   'group' => 'Applicant'],
            'age'                  => ['label' => 'Age at submission',                   'type' => 'number', 'group' => 'Applicant'],
            'nationality_id'       => ['label' => 'Nationality',                         'type' => 'select', 'group' => 'Applicant', 'options' => 'countries'],
            'residency_status_id'  => ['label' => 'Residency status',                    'type' => 'select', 'group' => 'Applicant', 'options' => 'residency_statuses'],
            'disability_status'    => ['label' => 'Declared a disability',               'type' => 'bool',   'group' => 'Applicant'],
            'convicted'            => ['label' => 'Declared a criminal conviction',      'type' => 'bool',   'group' => 'Applicant'],
            'employment_status'    => ['label' => 'Employment status',                   'type' => 'select', 'group' => 'Applicant', 'options' => 'employment'],
            'course_id'            => ['label' => 'Course',                              'type' => 'select', 'group' => 'Course',    'options' => 'courses'],
            'degree_offered'       => ['label' => 'Award / level',                       'type' => 'select', 'group' => 'Course',    'options' => 'degrees'],
            'awarding_body_id'     => ['label' => 'Awarding body',                       'type' => 'select', 'group' => 'Course',    'options' => 'awarding_bodies'],
            'franchise_course'     => ['label' => 'Franchise course',                    'type' => 'bool',   'group' => 'Course'],
            'evening_weekend'      => ['label' => 'Evening & weekend (full time)',       'type' => 'bool',   'group' => 'Course'],
            'student_loan'         => ['label' => 'Funding',                             'type' => 'select', 'group' => 'Course',    'options' => 'funding'],
            'is_agent_application' => ['label' => 'Submitted by an agent',               'type' => 'bool',   'group' => 'Source'],
            'referral_verified'    => ['label' => 'Has a verified referral code',        'type' => 'bool',   'group' => 'Source'],
        ];
    }

    /** The catalogue with every option list resolved, for the settings screen. */
    public static function catalogue(): array
    {
        $out = [];
        foreach (self::fields() as $key => $f) {
            $out[$key] = [
                'key'       => $key,
                'label'     => $f['label'],
                'type'      => $f['type'],
                'group'     => $f['group'],
                'operators' => self::OPERATORS[$f['type']],
                'options'   => isset($f['options']) ? self::optionsFor($f['options']) : null,
            ];
        }
        return $out;
    }

    /** [value => label] for a select field's source. */
    public static function optionsFor(string $source): array
    {
        switch ($source) {
            case 'courses':
                return Course::where('active', 1)->orderBy('name')->pluck('name', 'id')->toArray();
            case 'degrees':
                return Course::whereNotNull('degree_offered')->where('degree_offered', '!=', '')
                    ->distinct()->orderBy('degree_offered')->pluck('degree_offered', 'degree_offered')->toArray();
            case 'awarding_bodies':
                return AwardingBody::orderBy('name')->pluck('name', 'id')->toArray();
            case 'countries':
                return Country::where('active', 1)->orderBy('name')->pluck('name', 'id')->toArray();
            case 'residency_statuses':
                return ResidencyStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id')->toArray();
            case 'funding':
                return array_combine(self::FUNDING, self::FUNDING);
            case 'employment':
                return array_combine(self::EMPLOYMENT, self::EMPLOYMENT);
        }
        return [];
    }

    /** Every fact for one application, keyed as fields() is. */
    public static function build(Applicant $a): array
    {
        $a->loadMissing(['other', 'quals', 'course.creation.course', 'residency', 'criminalConviction']);
        $other = $a->other; $course = $a->course; $cc = optional($course)->creation; $c = optional($cc)->course;

        $age = null;
        if (!empty($a->date_of_birth)) {
            foreach (['d-m-Y', 'Y-m-d'] as $f) {
                try { $age = Carbon::createFromFormat($f, $a->date_of_birth)->age; break; } catch (\Throwable $e) {}
            }
        }

        return [
            'has_qualification'    => isset($other->is_edication_qualification) && (int) $other->is_edication_qualification === 1 && $a->quals->count() > 0,
            'age'                  => $age,
            'nationality_id'       => $a->nationality_id ?? null,
            'residency_status_id'  => optional($a->residency)->residency_status_id,
            'disability_status'    => isset($other->disability_status) && (int) $other->disability_status === 1,
            'convicted'            => (int) (optional($a->criminalConviction)->have_you_been_convicted ?? 0) === 1,
            'employment_status'    => optional($other)->employment_status,
            'course_id'            => optional($cc)->course_id,
            'degree_offered'       => optional($c)->degree_offered,
            'awarding_body_id'     => optional($c)->awarding_body_id,
            'franchise_course'     => strcasecmp((string) optional($c)->franchise_course, 'Yes') === 0,
            'evening_weekend'      => (int) (optional($course)->full_time ?? 0) === 1,
            'student_loan'         => optional($course)->student_loan,
            'is_agent_application' => !empty($a->agent_user_id),
            'referral_verified'    => !empty($a->referral_code) && (int) ($a->is_referral_varified ?? 0) === 1,
        ];
    }

    /** "Course is any of HND in Business, HND in Computing" - one condition as staff read it. */
    public static function describe(string $field, string $operator, $value, ?array $catalogue = null): string
    {
        $catalogue = $catalogue ?? self::catalogue();
        $f = $catalogue[$field] ?? null;
        if (!$f) {
            return $field . ' ' . $operator;
        }
        $op = $f['operators'][$operator] ?? $operator;
        $vals = is_array($value) ? $value : [$value];
        $labels = array_map(function ($v) use ($f) {
            if ($f['type'] === 'bool') return filter_var($v, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
            return $f['options'][$v] ?? (string) $v;
        }, $vals);
        return $f['label'] . ' ' . $op . ' ' . implode(', ', $labels);
    }

    /** Does one condition hold against a facts array? */
    public static function test(array $facts, string $field, string $operator, $value): bool
    {
        $have = $facts[$field] ?? null;
        $list = is_array($value) ? array_map('strval', $value) : [(string) $value];
        switch ($operator) {
            case 'is':     return is_bool($have) ? $have === filter_var($value, FILTER_VALIDATE_BOOLEAN) : (string) $have === (string) $value;
            case 'is_not': return (string) $have !== (string) $value;
            case 'in':     return in_array((string) $have, $list, true);
            case 'not_in': return !in_array((string) $have, $list, true);
            case 'eq':     return $have !== null && (float) $have == (float) $value;
            case 'neq':    return $have === null || (float) $have != (float) $value;
            case 'gte':    return $have !== null && (float) $have >= (float) $value;
            case 'lte':    return $have !== null && (float) $have <= (float) $value;
        }
        return false;
    }
}
