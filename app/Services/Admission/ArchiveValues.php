<?php

namespace App\Services\Admission;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Reads applicant_archives rows the way staff would: a field label and the old
 * and new values with ids resolved to names ("Venue: Campus A → Campus B").
 * Lookup tables are loaded once, on first use.
 */
class ArchiveValues
{
    public const TABLE_TITLES = [
        'applicants'                 => 'Personal details updated',
        'applicant_contacts'         => 'Contact details updated',
        'applicant_kin'              => 'Next of kin updated',
        'applicant_proposed_courses' => 'Proposed course updated',
        'applicant_other_details'    => 'Other details updated',
        'applicant_disabilities'     => 'Disabilities updated',
        'applicant_users'            => 'Login email changed',
        'applicant_residencies'      => 'Residency updated',
        'applicant_criminal_convictions' => 'Criminal convictions updated',
    ];

    /** field => [label, lookup] ; lookup is a table name, 'bool', 'list:<table>' or null. */
    private const FIELDS = [
        'title_id'                   => ['Title', 'titles'],
        'first_name'                 => ['First name', null],
        'last_name'                  => ['Last name', null],
        'date_of_birth'              => ['Date of birth', null],
        'sex_identifier_id'          => ['Sex identifier', 'sex_identifiers'],
        'nationality_id'             => ['Nationality', 'countries'],
        'country_id'                 => ['Country of birth', 'countries'],
        'photo'                      => ['Photo', null],
        'address_line_1'             => ['Address line 1', null],
        'address_line_2'             => ['Address line 2', null],
        'city'                       => ['City', null],
        'state'                      => ['County', null],
        'post_code'                  => ['Post code', null],
        'country'                    => ['Country', null],
        'mobile'                     => ['Mobile', null],
        'home'                       => ['Home phone', null],
        'email'                      => ['Email', null],
        'name'                       => ['Name', null],
        'kins_relation_id'           => ['Relation', 'kins_relations'],
        'course_creation_id'         => ['Course', 'course_creations'],
        'semester_id'                => ['Semester', 'semesters'],
        'venue_id'                   => ['Venue', 'venues'],
        'academic_year_id'           => ['Academic year', 'academic_years'],
        'student_loan'               => ['Funding', null],
        'student_finance_england'    => ['Applied to Student Finance England', 'bool'],
        'fund_receipt'               => ['Already receiving funds', 'bool'],
        'applied_received_fund'      => ['Previous SLC / government loan', 'bool'],
        'other_funding'              => ['Other funding', null],
        'full_time'                  => ['Evening & weekend (full time)', 'bool'],
        'ethnicity_id'               => ['Ethnicity', 'ethnicities'],
        'care_leaver_id'             => ['Care leaver', 'care_leavers'],
        'disability_status'          => ['Declared a disability', 'bool'],
        'disabilty_allowance'        => ['Disability allowance', 'bool'],
        'is_edication_qualification' => ['Formal qualification', 'bool'],
        'employment_status'          => ['Employment status', null],
        'college_introduction'       => ['How they heard about the college', null],
        'disabilitiy_id'             => ['Disabilities', 'list:disabilities'],
    ];

    private array $cache = [];

    /** @return array{0:string,1:string,2:string} label, old, new */
    public function describe(string $table, string $field, $old, $new): array
    {
        [$label, $lookup] = self::FIELDS[$field] ?? [Str::ucfirst(str_replace('_', ' ', preg_replace('/_id$/', '', $field))), null];
        if ($table === 'applicant_kin' && in_array($field, ['name', 'mobile', 'email'], true)) {
            $label = 'Next of kin '.Str::lower($label);
        }
        return [$label, $this->value($lookup, $old), $this->value($lookup, $new)];
    }

    private function value(?string $lookup, $v): string
    {
        $v = trim((string) $v);
        if ($v === '') {
            return '—';
        }
        if ($lookup === null) {
            return Str::limit($v, 80);
        }
        if ($lookup === 'bool') {
            return in_array(Str::lower($v), ['1', 'yes', 'true'], true) ? 'Yes' : (in_array(Str::lower($v), ['0', 'no', 'false'], true) ? 'No' : $v);
        }
        if (str_starts_with($lookup, 'list:')) {
            $names = $this->names(substr($lookup, 5));
            $parts = array_filter(array_map('trim', explode(',', $v)), 'strlen');
            return $parts ? implode(', ', array_map(fn ($p) => $names[(int) $p] ?? $p, $parts)) : '—';
        }
        return $this->names($lookup)[(int) $v] ?? $v;
    }

    private function names(string $table): array
    {
        if (isset($this->cache[$table])) {
            return $this->cache[$table];
        }
        try {
            if ($table === 'course_creations') {
                return $this->cache[$table] = DB::table('course_creations')
                    ->leftJoin('courses', 'courses.id', '=', 'course_creations.course_id')
                    ->leftJoin('semesters', 'semesters.id', '=', 'course_creations.semester_id')
                    ->get(['course_creations.id', 'courses.name as course', 'semesters.name as semester'])
                    ->mapWithKeys(fn ($r) => [$r->id => trim($r->course.($r->semester ? ' · '.$r->semester : ''))])->all();
            }
            return $this->cache[$table] = DB::table($table)->pluck('name', 'id')->all();
        } catch (\Throwable $e) {
            return $this->cache[$table] = [];
        }
    }
}
