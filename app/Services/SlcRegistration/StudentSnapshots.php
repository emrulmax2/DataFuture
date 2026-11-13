<?php

namespace App\Services\SlcRegistration;

use Illuminate\Support\Facades\DB;

/**
 * What we hold for a set of students, loaded in a handful of queries: the values SLC's line is
 * checked against (Data check), scored against (Identify) and the awarding body and attendance
 * (Pre-confirm). "Their course" is the active course relation and its proposed course; the SLC
 * course code is the one on that course creation's venue (Course Creation › venues), as the
 * student's SLC tab reads it; the instance, year and fee are the course's instance in the
 * upload's academic year.
 */
class StudentSnapshots
{
    /**
     * @param int[] $ids
     * @return array<int, array> keyed by student id
     */
    public static function load(array $ids, ?int $academicYearId): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) {
            return [];
        }
        $out = [];

        $students = DB::table('students as s')
            ->leftJoin('titles as t', 't.id', '=', 's.title_id')
            ->leftJoin('sex_identifiers as x', 'x.id', '=', 's.sex_identifier_id')
            ->leftJoin('statuses as st', 'st.id', '=', 's.status_id')
            ->whereIn('s.id', $ids)->whereNull('s.deleted_at')
            ->get(['s.id', 's.registration_no', 's.title_id', 's.first_name', 's.last_name', 's.date_of_birth', 's.ssn_no', 's.sex_identifier_id', 't.name as title', 'x.name as sex', 'st.name as status_name', 'st.active as status_active']);
        foreach ($students as $s) {
            $out[$s->id] = [
                'id' => (int) $s->id,
                'reg_no' => (string) $s->registration_no,
                'title' => (string) $s->title,
                'first' => trim((string) $s->first_name),
                'last' => trim((string) $s->last_name),
                'gender' => $s->sex === 'Male' ? 'M' : ($s->sex === 'Female' ? 'F' : ''),
                'dob' => $s->date_of_birth ? substr((string) $s->date_of_birth, 0, 10) : '',
                'ssn' => trim((string) $s->ssn_no) ?: null,
                'url' => route('student.show', $s->id), // the student profile
                'status' => StudentStatus::of($s->status_name, $s->status_active), // shown beside the name
                'crel_id' => null, 'course_creation_id' => null, 'course_name' => '', 'intake' => '', 'awarding_body' => '', 'slc_code' => '', 'venue' => '',
                'instance' => null, 'addresses' => [], 'contact_id' => null, 'mobile' => '', 'emails' => [], 'abody' => null,
                'att' => ['held' => 0, 'attended' => 0, 'last' => null, 'physical' => 0, 'online' => 0, 'other' => 0, 'otherNames' => ''], 'att_from' => null, 'registration' => null,
            ];
        }
        $ids = array_keys($out);
        if (!$ids) {
            return [];
        }

        // contact → permanent and term-time addresses and the mobile number (the newest contact row, as the profile shows)
        $contacts = DB::table('student_contacts')->whereIn('student_id', $ids)->whereNull('deleted_at')->orderBy('id')->get(['id', 'student_id', 'permanent_address_id', 'term_time_address_id', 'mobile', 'institutional_email', 'personal_email'])->keyBy('student_id');
        $addressIds = $contacts->flatMap(fn ($c) => [$c->permanent_address_id, $c->term_time_address_id])->filter()->unique()->all();
        $addresses = $addressIds ? DB::table('addresses')->whereIn('id', $addressIds)->get(['id', 'address_line_1', 'address_line_2', 'city', 'post_code'])->keyBy('id') : collect();
        foreach ($contacts as $sid => $c) {
            $out[$sid]['contact_id'] = (int) $c->id;
            $out[$sid]['mobile'] = trim((string) $c->mobile);
            // the steps' search box finds students by email too (user, 13 Nov 2026)
            $out[$sid]['emails'] = array_values(array_unique(array_filter([trim((string) $c->institutional_email), trim((string) $c->personal_email)])));
            foreach (['permanent' => $c->permanent_address_id, 'term' => $c->term_time_address_id] as $type => $aid) {
                $a = $aid ? $addresses->get($aid) : null;
                if (!$a) {
                    continue;
                }
                if (collect($out[$sid]['addresses'])->firstWhere('id', (int) $a->id)) {
                    $out[$sid]['addresses'] = array_map(fn ($x) => $x['id'] === (int) $a->id ? $x + ['also' => $type] : $x, $out[$sid]['addresses']);
                    continue;
                }
                $out[$sid]['addresses'][] = [
                    'type' => $type, 'id' => (int) $a->id,
                    'text' => implode(', ', array_filter(array_map(fn ($v) => Text::titleCase($v), [$a->address_line_1, $a->address_line_2, $a->city]))),
                    'postcode' => strtoupper(trim((string) $a->post_code)),
                ];
            }
        }

        // the active course relation (newest if several) and its proposed course
        $crels = DB::table('student_course_relations as r')
            ->join('course_creations as cc', 'cc.id', '=', 'r.course_creation_id')
            ->leftJoin('courses as c', 'c.id', '=', 'cc.course_id')
            ->leftJoin('semesters as sm', 'sm.id', '=', 'cc.semester_id')
            ->leftJoin('awarding_bodies as ab', 'ab.id', '=', 'c.awarding_body_id')
            ->whereIn('r.student_id', $ids)->where('r.active', 1)->whereNull('r.deleted_at')
            ->orderBy('r.id')
            ->get(['r.id', 'r.student_id', 'r.course_creation_id', 'c.name as course_name', 'sm.name as intake', 'ab.name as awarding_body', 'cc.fees as creation_fees'])
            ->keyBy('student_id');
        $crelIds = $crels->pluck('id')->all();
        $proposed = $crelIds ? DB::table('student_proposed_courses')->whereIn('student_course_relation_id', $crelIds)->whereNull('deleted_at')->orderBy('id')->get(['student_course_relation_id', 'course_creation_id', 'venue_id'])->keyBy('student_course_relation_id') : collect();
        $creationIds = $crels->pluck('course_creation_id')->unique()->all();
        $venueCodes = $creationIds ? DB::table('course_creation_venue')->whereIn('course_creation_id', $creationIds)->whereNull('deleted_at')->get(['course_creation_id', 'venue_id', 'slc_code']) : collect();
        $venueIds = $proposed->pluck('venue_id')->merge($venueCodes->pluck('venue_id'))->filter()->unique()->all();
        $venueNames = $venueIds ? DB::table('venues')->whereIn('id', $venueIds)->pluck('name', 'id') : collect();
        $instances = $creationIds ? DB::table('course_creation_instances')->whereIn('course_creation_id', $creationIds)->whereNull('deleted_at')->orderBy('start_date')->orderBy('id')->get(['id', 'course_creation_id', 'academic_year_id', 'start_date', 'fees'])->groupBy('course_creation_id') : collect();

        foreach ($crels as $sid => $r) {
            $p = $proposed->get($r->id);
            $creationId = (int) ($p->course_creation_id ?? $r->course_creation_id);
            $code = $venueCodes->first(fn ($v) => (int) $v->course_creation_id === $creationId && $p && (int) $v->venue_id === (int) $p->venue_id);
            if (!$code) {
                // no venue on the proposed course: a course run at one venue has only one code
                $only = $venueCodes->where('course_creation_id', $creationId)->values();
                $code = $only->count() === 1 ? $only->first() : null;
            }
            $out[$sid]['crel_id'] = (int) $r->id;
            $out[$sid]['course_creation_id'] = (int) $r->course_creation_id;
            $out[$sid]['course_name'] = trim((string) $r->course_name);
            $out[$sid]['intake'] = trim((string) $r->intake);
            $out[$sid]['awarding_body'] = trim((string) $r->awarding_body);
            $out[$sid]['slc_code'] = self::code($code->slc_code ?? '');
            $out[$sid]['venue'] = trim((string) ($venueNames[$p->venue_id ?? $code->venue_id ?? 0] ?? '')); // the proposed course's venue, as Course Details shows it
            $list = $instances->get($r->course_creation_id, collect())->values();
            // the register counts from the start of the current year of study (the latest instance already started)
            $started = $list->filter(fn ($i) => substr((string) $i->start_date, 0, 10) <= now()->toDateString())->last() ?? $list->first();
            $out[$sid]['att_from'] = $started ? substr((string) $started->start_date, 0, 10) : null;
            foreach ($list as $rank => $inst) {
                if ($academicYearId && (int) $inst->academic_year_id === $academicYearId) {
                    $out[$sid]['instance'] = [
                        'id' => (int) $inst->id, 'start' => substr((string) $inst->start_date, 0, 10), 'rank' => $rank + 1,
                        'fee' => (float) ($inst->fees ?: $r->creation_fees),
                    ];
                }
            }
        }

        // awarding body registration on that course relation (the newest row)
        if ($crelIds) {
            $bodies = DB::table('student_awarding_body_details')->whereIn('student_course_relation_id', $crelIds)->whereNull('deleted_at')->orderBy('id')->get()->keyBy('student_course_relation_id');
            foreach ($out as $sid => $s) {
                $b = $s['crel_id'] ? $bodies->get($s['crel_id']) : null;
                if ($b) {
                    $out[$sid]['abody'] = [
                        'reference' => trim((string) $b->reference), 'course_code' => trim((string) $b->course_code),
                        'registration_date' => $b->registration_date ? substr((string) $b->registration_date, 0, 10) : null,
                        'expire_date' => $b->registration_expire_date ? substr((string) $b->registration_expire_date, 0, 10) : null,
                    ];
                }
            }
        }

        // the register since the start of the current year of study
        $since = [];
        foreach ($out as $sid => $s) {
            $since[$s['att_from'] ?? '2000-01-01'][] = $sid;
        }
        foreach ($since as $from => $group) {
            $rows = DB::table('attendances as a')
                ->join('attendance_feed_statuses as f', 'f.id', '=', 'a.attendance_feed_status_id')
                ->whereIn('a.student_id', $group)->whereNull('a.deleted_at')
                ->where('a.attendance_date', '>=', $from)->where('a.attendance_date', '<=', now()->toDateString())
                ->groupBy('a.student_id')
                ->selectRaw("a.student_id, COUNT(*) as held, SUM(f.attendance_count) as attended, MAX(CASE WHEN f.attendance_count = 1 THEN a.attendance_date END) as last, "
                    ."SUM(CASE WHEN f.code = 'P' THEN 1 ELSE 0 END) as physical, SUM(CASE WHEN f.code = 'O' THEN 1 ELSE 0 END) as online, "
                    ."GROUP_CONCAT(DISTINCT CASE WHEN f.attendance_count = 1 AND f.code NOT IN ('P', 'O') THEN f.name END ORDER BY f.name SEPARATOR ', ') as other_names")
                ->get();
            foreach ($rows as $a) {
                $out[$a->student_id]['att'] = [
                    'held' => (int) $a->held, 'attended' => (int) $a->attended, 'last' => $a->last ? substr((string) $a->last, 0, 10) : null,
                    // attended, split: in the room (P), online (O), and any other status that counts (Late, Excuse, Medical…)
                    'physical' => (int) $a->physical, 'online' => (int) $a->online, 'other' => max(0, (int) $a->attended - (int) $a->physical - (int) $a->online), 'otherNames' => (string) $a->other_names,
                ];
            }
        }

        // an SLC registration (Yes) already recorded for this academic year, on the student's ACTIVE course
        // relation only (user, 13 Nov 2026: an earlier course's registration doesn't count); the newest wins
        if ($academicYearId) {
            $regs = DB::table('slc_registrations')->whereIn('student_id', $ids)->where('academic_year_id', $academicYearId)
                ->where('slc_registration_status_id', 1)->whereNull('deleted_at')->orderBy('id')->get(['id', 'student_id', 'student_course_relation_id', 'registration_year', 'confirmation_date']);
            foreach ($regs as $g) {
                $sid = (int) $g->student_id;
                if (empty($out[$sid]['crel_id']) || (int) $g->student_course_relation_id !== (int) $out[$sid]['crel_id']) {
                    continue;
                }
                $out[$sid]['registration'] = ['id' => (int) $g->id, 'year' => (int) $g->registration_year, 'date' => $g->confirmation_date ? substr((string) $g->confirmation_date, 0, 10) : null];
            }
        }

        return $out;
    }

    /** Venue codes are blank or "n/a" on older courses. */
    public static function code(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return in_array($code, ['', 'N/A', 'NA', '-'], true) ? '' : $code;
    }
}
