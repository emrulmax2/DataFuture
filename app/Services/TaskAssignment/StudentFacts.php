<?php

namespace App\Services\TaskAssignment;

use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentProposedCourse;
use App\Models\Venue;
use Carbon\Carbon;

/**
 * What a student task rule can test, read off a student record — the student
 * counterpart of ApplicationFacts (same operators, same value controls on the
 * settings screen). Course facts come from the student's *active* course
 * relation (not crel(), which follows a staff member's session), and the study
 * details from the proposed course that belongs to it.
 *
 * previous_status_id is only known when a status change triggers the rules;
 * status_id is the student's status at that moment (the new one).
 */
class StudentFacts
{
    public const OPERATORS = ApplicationFacts::OPERATORS;

    public static function fields(): array
    {
        return [
            'status_id'            => ['label' => 'Status',                              'type' => 'select', 'group' => 'Student', 'options' => 'student_statuses'],
            'previous_status_id'   => ['label' => 'Previous status (on a status change)', 'type' => 'select', 'group' => 'Student', 'options' => 'student_statuses'],
            'age'                  => ['label' => 'Age',                                 'type' => 'number', 'group' => 'Student'],
            'nationality_id'       => ['label' => 'Nationality',                         'type' => 'select', 'group' => 'Student', 'options' => 'countries'],
            'residency_status_id'  => ['label' => 'Residency status',                    'type' => 'select', 'group' => 'Student', 'options' => 'residency_statuses'],
            'has_qualification'    => ['label' => 'Has formal qualifications',          'type' => 'bool',   'group' => 'Student'],
            'disability_status'    => ['label' => 'Declared a disability',               'type' => 'bool',   'group' => 'Student'],
            'convicted'            => ['label' => 'Declared a criminal conviction',      'type' => 'bool',   'group' => 'Student'],
            'employment_status'    => ['label' => 'Employment status',                   'type' => 'select', 'group' => 'Student', 'options' => 'employment'],
            'course_id'            => ['label' => 'Course',                              'type' => 'select', 'group' => 'Course',  'options' => 'courses'],
            'degree_offered'       => ['label' => 'Award / level',                       'type' => 'select', 'group' => 'Course',  'options' => 'degrees'],
            'awarding_body_id'     => ['label' => 'Awarding body',                       'type' => 'select', 'group' => 'Course',  'options' => 'awarding_bodies'],
            'franchise_course'     => ['label' => 'Franchise course',                    'type' => 'bool',   'group' => 'Course'],
            'semester_id'          => ['label' => 'Intake (semester)',                   'type' => 'select', 'group' => 'Course',  'options' => 'semesters'],
            'venue_id'             => ['label' => 'Venue',                               'type' => 'select', 'group' => 'Course',  'options' => 'venues'],
            'evening_weekend'      => ['label' => 'Evening & weekend (full time)',       'type' => 'bool',   'group' => 'Course'],
            'student_loan'         => ['label' => 'Funding',                             'type' => 'select', 'group' => 'Course',  'options' => 'funding'],
            'is_agent_application' => ['label' => 'Applied through an agent',            'type' => 'bool',   'group' => 'Source'],
            'referral_verified'    => ['label' => 'Has a verified referral code',        'type' => 'bool',   'group' => 'Source'],
        ];
    }

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

    public static function optionsFor(string $source): array
    {
        switch ($source) {
            case 'student_statuses':
                return Status::where('type', 'Student')->orderBy('name')->pluck('name', 'id')->toArray();
            case 'semesters':
                return Semester::orderByDesc('id')->pluck('name', 'id')->toArray();
            case 'venues':
                return Venue::orderBy('name')->pluck('name', 'id')->toArray();
        }

        return ApplicationFacts::optionsFor($source);
    }

    /** Every fact for one student; $context carries what the trigger knows (previous_status_id). */
    public static function build(Student $s, array $context = []): array
    {
        $s->loadMissing(['other', 'quals', 'activeCR.creation.course', 'residency', 'criminalConviction', 'applicant']);
        $cr = $s->activeCR;
        $cc = optional($cr)->creation;
        $c = optional($cc)->course;
        $proposed = $cr ? StudentProposedCourse::where('student_id', $s->id)->where('student_course_relation_id', $cr->id)->orderByDesc('id')->first() : null;
        $proposed = $proposed ?: StudentProposedCourse::where('student_id', $s->id)->orderByDesc('id')->first();
        $other = $s->other;

        $age = null;
        if (!empty($s->date_of_birth)) {
            foreach (['d-m-Y', 'Y-m-d'] as $f) {
                try { $age = Carbon::createFromFormat($f, $s->date_of_birth)->age; break; } catch (\Throwable $e) {}
            }
        }

        return [
            'status_id'            => $s->status_id !== null ? (int) $s->status_id : null,
            'previous_status_id'   => isset($context['previous_status_id']) && $context['previous_status_id'] !== '' ? (int) $context['previous_status_id'] : null,
            'age'                  => $age,
            'nationality_id'       => $s->nationality_id ?? null,
            'residency_status_id'  => optional($s->residency)->residency_status_id,
            'has_qualification'    => isset($other->is_education_qualification) && (int) $other->is_education_qualification === 1 && $s->quals->count() > 0,
            'disability_status'    => isset($other->disability_status) && (int) $other->disability_status === 1,
            'convicted'            => (int) (optional($s->criminalConviction)->have_you_been_convicted ?? 0) === 1,
            'employment_status'    => optional($other)->employment_status,
            'course_id'            => optional($cc)->course_id ?? optional(optional($proposed)->creation)->course_id,
            'degree_offered'       => optional($c)->degree_offered,
            'awarding_body_id'     => optional($c)->awarding_body_id,
            'franchise_course'     => strcasecmp((string) optional($c)->franchise_course, 'Yes') === 0,
            'semester_id'          => optional($cc)->semester_id ?? optional($proposed)->semester_id,
            'venue_id'             => optional($proposed)->venue_id,
            'evening_weekend'      => (int) (optional($proposed)->full_time ?? 0) === 1,
            'student_loan'         => optional($proposed)->student_loan,
            'is_agent_application' => !empty(optional($s->applicant)->agent_user_id),
            'referral_verified'    => !empty($s->referral_code) && (int) ($s->is_referral_varified ?? 0) === 1,
        ];
    }

    public static function describe(string $field, string $operator, $value, ?array $catalogue = null): string
    {
        return ApplicationFacts::describe($field, $operator, $value, $catalogue ?? self::catalogue());
    }

    public static function test(array $facts, string $field, string $operator, $value): bool
    {
        return ApplicationFacts::test($facts, $field, $operator, $value);
    }

    /** The facts class for a rule subject (TaskAssignmentRule::SUBJECT_*). */
    public static function forSubject(string $subject): string
    {
        return $subject === \App\Models\TaskAssignmentRule::SUBJECT_STUDENT ? self::class : ApplicationFacts::class;
    }
}
