<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Reports › Student Expected Result (/reports/student-expected-result-reports), new version (user's
 * student-expected-result-report design, 25 Nov 2026).
 *
 * Students: the old search's (StudentResultReport::students); an attendance semester is required,
 * as on the old form. Answers: the old export's (StudentExpectedResultReportController::
 * excelDownload), per student and attendance semester, from the student's classes in that semester
 * on their own course (the class's course name = the student's course name):
 *  - a unit is a Theory class, or a class with no type that is not a Group Tutorial;
 *  - "No" when the student was made inactive in that class (assigns.attendance = 0), else "Yes";
 *  - when a student has several classes of a unit, or several groups, the earliest assignment wins
 *    (the old loop runs newest first and overwrites).
 */
class StudentExpectedResultReport
{
    public function __construct(private StudentResultReport $picker)
    {
    }

    /**
     * Every found student with their semesters on their course: [student id => details + terms],
     * in the old export's order (students by their newest unit assignment), plus who had no classes.
     */
    public function students(array $f): array
    {
        $crels = $this->picker->students($f);
        $terms = $f['attendance'];
        if (! $crels || ! $terms) return ['students' => [], 'unplaced' => 0];

        $out = [];
        $order = [];
        foreach (array_chunk(array_keys($crels), 1000) as $chunk) {
            $relations = array_map(fn ($id) => $crels[$id], $chunk);
            $details = DB::table('students as s')
                ->join('student_course_relations as scr', 'scr.student_id', '=', 's.id')
                ->leftJoin('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
                ->leftJoin('courses as co', 'co.id', '=', 'cc.course_id')
                ->leftJoin('semesters as sem', 'sem.id', '=', 'cc.semester_id')
                ->leftJoin('statuses as st', 'st.id', '=', 's.status_id')
                ->leftJoin('titles as t', fn ($j) => $j->on('t.id', '=', 's.title_id')->whereNull('t.deleted_at'))
                ->whereIn('s.id', $chunk)->whereIn('scr.id', $relations)
                ->get(['s.id', 's.registration_no', 's.first_name', 's.last_name', 't.name as title', 'st.name as status', 'co.name as course', 'sem.name as intake', 'scr.id as crel'])
                ->keyBy('id');
            // Awarding body reference: the relation's latest record (StudentCourseRelation::abody).
            $refs = DB::table('student_awarding_body_details')->whereIn('student_course_relation_id', $relations)->whereNull('deleted_at')
                ->orderBy('id')->pluck('reference', 'student_course_relation_id');

            $assigns = DB::table('assigns as a')
                ->join('plans as p', 'p.id', '=', 'a.plan_id')
                ->leftJoin('course_creations as pcc', fn ($j) => $j->on('pcc.id', '=', 'p.course_creation_id')->whereNull('pcc.deleted_at'))
                ->leftJoin('courses as pco', fn ($j) => $j->on('pco.id', '=', 'pcc.course_id')->whereNull('pco.deleted_at'))
                ->leftJoin('groups as g', fn ($j) => $j->on('g.id', '=', 'p.group_id')->whereNull('g.deleted_at'))
                ->leftJoin('module_creations as mc', fn ($j) => $j->on('mc.id', '=', 'p.module_creation_id')->whereNull('mc.deleted_at'))
                ->leftJoin('course_modules as cm', fn ($j) => $j->on('cm.id', '=', 'mc.course_module_id')->whereNull('cm.deleted_at'))
                ->whereIn('a.student_id', $chunk)->whereIn('p.term_declaration_id', $terms)
                ->whereNull('a.deleted_at')->whereNull('p.deleted_at')
                ->orderByDesc('a.id')
                ->get(['a.id', 'a.student_id', 'a.attendance', 'p.term_declaration_id as term', 'p.class_type', 'pco.name as course', 'g.name as group', 'mc.id as creation', 'cm.name as module']);

            foreach ($assigns as $a) {
                $sid = (int) $a->student_id;
                $d = $details[$sid] ?? null;
                if (! $d || $a->course === null || $a->course !== $d->course) continue;
                $term = (int) $a->term;
                $out[$sid] ??= [
                    'id' => $sid, 'reg' => (string) $d->registration_no, 'name' => trim(($d->title ? $d->title.' ' : '').$d->first_name.' '.$d->last_name),
                    'status' => (string) $d->status, 'course' => (string) $d->course, 'intake' => (string) $d->intake, 'ref' => (string) ($refs[(int) $d->crel] ?? ''),
                    'terms' => [],
                ];
                $out[$sid]['terms'][$term]['group'] = (string) ($a->group ?? '');
                $out[$sid]['terms'][$term]['units'] ??= [];
                if (! $a->creation || $a->module === null) continue;
                $unit = $a->class_type === 'Theory' || ($a->class_type === null && ! str_contains($a->module, 'Group Tutorial'));
                if (! $unit) continue;
                $out[$sid]['terms'][$term]['units'][$a->module] = $a->attendance === 0 || $a->attendance === '0' ? 'No' : 'Yes';
                $order[$sid] ??= (int) $a->id; // their newest unit assignment (the loop runs newest first)
            }
        }
        // Students with a unit come first in the old export's order; the rest (no unit class) after.
        uksort($out, fn ($a, $b) => ($order[$b] ?? -1) <=> ($order[$a] ?? -1) ?: $a <=> $b);
        foreach ($out as &$s) $s['exported'] = isset($order[$s['id']]);
        unset($s);

        return ['students' => $out, 'unplaced' => count($crels) - count($out)];
    }

    /** The screen's data: one row per student and attendance semester. */
    public function build(array $f): array
    {
        $found = $this->students($f);
        $termNames = DB::table('term_declarations')->whereIn('id', $f['attendance'] ?: [0])->pluck('name', 'id');
        $lists = ['terms' => [], 'units' => [], 'courses' => [], 'groups' => [], 'statuses' => [], 'intakes' => []];
        $index = [];
        $at = function (string $list, string $value) use (&$lists, &$index) {
            $key = $list."\0".$value;
            if (! isset($index[$key])) {
                $lists[$list][] = $value;
                $index[$key] = count($lists[$list]) - 1;
            }

            return $index[$key];
        };

        $rows = [];
        foreach ($found['students'] as $s) {
            foreach ($f['attendance'] as $term) {
                if (! isset($s['terms'][$term])) continue;
                $t = $s['terms'][$term];
                $units = [];
                foreach ($t['units'] as $name => $answer) $units[] = [$at('units', (string) $name), $answer === 'Yes' ? 'Y' : 'N'];
                $rows[] = [$s['id'], $s['reg'], $s['name'], $at('statuses', $s['status']), $at('courses', $s['course']),
                    $at('terms', (string) ($termNames[$term] ?? '')), $at('groups', $t['group']), $units, $s['ref'], $at('intakes', $s['intake'])];
            }
        }

        return $lists + ['rows' => $rows, 'unplaced' => $found['unplaced']];
    }

    /** The old export's sheet: two header rows, a row per student and chosen semester, units by name. */
    public function sheet(array $f): array
    {
        $found = $this->students($f)['students'];
        $termNames = DB::table('term_declarations')->whereNull('deleted_at')->whereIn('id', $f['attendance'] ?: [0])->pluck('name', 'id');
        $units = [];
        foreach ($found as $s) {
            if (! $s['exported']) continue;
            foreach ($s['terms'] as $t) foreach ($t['units'] as $name => $_) $units[$name] = true;
        }
        $units = array_keys($units);
        sort($units);

        $headers = [1 => ['Student ID', 'Student Name', 'Status', 'Intake Semester', 'Course', 'Awarding Body Ref', 'Attendance Term', 'Group'], 2 => array_fill(0, 8, '')];
        foreach ($units as $i => $name) {
            $headers[1][] = $i === 0 ? 'Unit Numbers' : '';
            $headers[2][] = $name;
        }

        $rows = [];
        $n = 1;
        foreach ($found as $s) {
            if (! $s['exported']) continue; // the old export lists students with at least one unit
            foreach ($f['attendance'] as $term) {
                if (! isset($s['terms'][$term])) continue;
                $row = [$s['reg'], $s['name'], $s['status'], $s['intake'], $s['course'], $s['ref'], (string) ($termNames[$term] ?? ''), $s['terms'][$term]['group']];
                foreach ($units as $name) $row[] = $s['terms'][$term]['units'][$name] ?? '';
                $rows[$n++] = $row;
            }
        }

        return [$rows, $headers, $units];
    }
}
