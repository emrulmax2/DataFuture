<?php

namespace App\Services\Reports;

use App\Models\AcademicCriteria;
use App\Models\AttendanceCriteria;
use Illuminate\Support\Facades\DB;

/**
 * Reports › Result Grade Performance (/reports/student-performance-reports), new version (user's
 * result-grade-performance-report design, 25 Nov 2026).
 *
 * Students: the old search's, as on the Student Result Report (StudentResultReport::students, which
 * fixes the old search's filter bugs). Figures: the old export's
 * (StudentPerformanceReportController::excelDownload), per student and attendance semester:
 *  - expected = top academic points (Distinction) × the term's Pass/Merit/Distinction results
 *    + top attendance points;
 *  - achieved = the points of those results + the attendance band's points for the term's
 *    attendance percentage (present ÷ all, from the student's class dates).
 * Results count when published before now, on a class the student is assigned to; every result
 * counts, retakes included. The semesters are those of the student's classes.
 *
 * When attendance semesters are chosen, only those semesters are counted (the design's rule; the
 * old export always listed every semester).
 */
class StudentPerformanceReport
{
    public function __construct(private StudentResultReport $picker)
    {
    }

    /** Points from the two criteria tables, looked up as the old export does (first match by id). */
    private function criteria(): array
    {
        $academic = AcademicCriteria::orderBy('id')->get(['code', 'point']);
        $points = [];
        foreach ($academic as $c) {
            $points[$c->code] ??= (float) $c->point;
        }

        return [
            'points' => $points,
            'topAcademic' => (float) AcademicCriteria::orderBy('point', 'desc')->value('point'),
            'topAttendance' => (float) AttendanceCriteria::orderBy('point', 'desc')->value('point'),
            'bands' => AttendanceCriteria::orderBy('id')->get(['range_from', 'range_to', 'point'])
                ->map(fn ($b) => [(float) $b->range_from, (float) $b->range_to, (float) $b->point])->all(),
        ];
    }

    /** The attendance band's points for a percentage (the old export: number_format, then round). */
    private function attendancePoints(array $bands, int $present, int $total): float
    {
        $avg = $total ? round((float) str_replace(',', '', number_format($present / $total * 100, 2))) : 0;
        foreach ($bands as [$from, $to, $point]) {
            if ($from <= $avg && $to >= $avg) return $point;
        }

        return 0;
    }

    /**
     * Every found student with their semesters, by student id: details, and per term
     * [expected, achieved, term status ('No' or '')], latest term first.
     */
    public function students(array $f): array
    {
        $crels = $this->picker->students($f);
        if (! $crels) return [];
        $ids = array_keys($crels);
        $c = $this->criteria();
        $only = $f['attendance'] ? array_flip($f['attendance']) : null;

        $out = [];
        foreach (array_chunk($ids, 1000) as $chunk) {
            $details = DB::table('students as s')
                ->join('student_course_relations as scr', 'scr.student_id', '=', 's.id')
                ->leftJoin('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
                ->leftJoin('courses as co', 'co.id', '=', 'cc.course_id')
                ->leftJoin('statuses as st', 'st.id', '=', 's.status_id')
                ->leftJoin('titles as t', fn ($j) => $j->on('t.id', '=', 's.title_id')->whereNull('t.deleted_at'))
                ->whereIn('s.id', $chunk)
                ->whereIn('scr.id', array_map(fn ($id) => $crels[$id], $chunk))
                ->get(['s.id', 's.registration_no', 's.first_name', 's.last_name', 't.name as title', 'st.name as status', 'co.name as course', 'scr.id as crel']);
            // Intake: the relation's latest proposed course (StudentCourseRelation::propose).
            $intakes = DB::table('student_proposed_courses as spc')
                ->join('semesters as sem', 'sem.id', '=', 'spc.semester_id')
                ->whereIn('spc.student_course_relation_id', array_map(fn ($id) => $crels[$id], $chunk))
                ->whereNull('spc.deleted_at')
                ->orderBy('spc.id')
                ->pluck('sem.name', 'spc.student_course_relation_id');
            foreach ($details as $d) {
                $out[(int) $d->id] = [
                    'id' => (int) $d->id,
                    'reg' => (string) $d->registration_no,
                    'name' => trim(($d->title ? $d->title.' ' : '').$d->first_name.' '.$d->last_name),
                    'status' => (string) $d->status,
                    'course' => (string) $d->course,
                    'intake' => (string) ($intakes[(int) $d->crel] ?? ''),
                    'terms' => [],
                ];
            }

            // Classes: every assigned plan id counts for results and attendance; live plans give the
            // semesters, and the last assignment in a semester gives its status.
            $plans = [];
            $status = [];
            $assigns = DB::table('assigns as a')
                ->leftJoin('plans as p', fn ($j) => $j->on('p.id', '=', 'a.plan_id')->whereNull('p.deleted_at'))
                ->whereIn('a.student_id', $chunk)->whereNull('a.deleted_at')
                ->orderBy('a.id')
                ->get(['a.student_id', 'a.plan_id', 'a.attendance', 'p.id as live', 'p.term_declaration_id as term']);
            foreach ($assigns as $a) {
                $sid = (int) $a->student_id;
                $plans[$sid][(int) $a->plan_id] = true;
                if ($a->live && $a->term) {
                    $status[$sid][(int) $a->term] = ($a->attendance !== null && (int) $a->attendance !== 1) ? 'No' : '';
                }
            }

            $academic = [];
            $results = DB::table('results as r')
                ->join('plans as p', 'p.id', '=', 'r.plan_id')
                ->join('grades as g', 'g.id', '=', 'r.grade_id')
                ->whereIn('r.student_id', $chunk)
                ->whereNull('r.deleted_at')->whereNull('p.deleted_at')->whereNull('g.deleted_at')
                ->where('r.published_at', '<', now())
                ->get(['r.student_id', 'r.plan_id', 'p.term_declaration_id as term', 'g.code']);
            foreach ($results as $r) {
                $sid = (int) $r->student_id;
                if (! isset($plans[$sid][(int) $r->plan_id], $c['points'][$r->code])) continue;
                $academic[$sid][(int) $r->term][0] = ($academic[$sid][(int) $r->term][0] ?? 0) + $c['points'][$r->code];
                $academic[$sid][(int) $r->term][1] = ($academic[$sid][(int) $r->term][1] ?? 0) + 1;
            }

            // Attendance: the student's records on dates of their classes, by the record's class semester.
            $attendance = [];
            $rows = DB::table('attendances as a')
                ->join('plans_date_lists as d', 'd.id', '=', 'a.plans_date_list_id')
                ->join('plans as p', 'p.id', '=', 'a.plan_id')
                ->join('attendance_feed_statuses as f', 'f.id', '=', 'a.attendance_feed_status_id')
                ->whereIn('a.student_id', $chunk)
                ->whereNull('a.deleted_at')->whereNull('d.deleted_at')->whereNull('p.deleted_at')
                ->whereExists(fn ($q) => $q->from('assigns as x')
                    ->whereColumn('x.student_id', 'a.student_id')->whereColumn('x.plan_id', 'd.plan_id')->whereNull('x.deleted_at'))
                ->groupBy('a.student_id', 'p.term_declaration_id')
                ->selectRaw('a.student_id, p.term_declaration_id as term, sum(f.attendance_count = 1) as present, count(*) as total')
                ->get();
            foreach ($rows as $r) {
                $attendance[(int) $r->student_id][(int) $r->term] = [(int) $r->present, (int) $r->total];
            }

            $used = [];
            foreach ($status as $byTerm) $used += $byTerm;
            $terms = DB::table('term_declarations')->whereNull('deleted_at')
                ->whereIn('id', array_keys($used) ?: [0])
                ->orderByDesc('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
            foreach ($chunk as $sid) {
                if (! isset($out[$sid])) continue;
                foreach ($terms as $term) {
                    if (! isset($status[$sid][$term]) || ($only && ! isset($only[$term]))) continue;
                    [$present, $total] = $attendance[$sid][$term] ?? [0, 0];
                    [$points, $counted] = $academic[$sid][$term] ?? [0, 0];
                    $out[$sid]['terms'][$term] = [
                        $c['topAcademic'] * $counted + $c['topAttendance'],
                        $this->attendancePoints($c['bands'], $present, $total) + $points,
                        $status[$sid][$term],
                    ];
                }
            }
        }
        ksort($out);

        return $out;
    }

    /** The screen's data: semesters in date order and one compact row per student. */
    public function build(array $f): array
    {
        $students = $this->students($f);
        $termIds = [];
        foreach ($students as $s) {
            foreach ($s['terms'] as $term => $_) $termIds[$term] = true;
        }
        $terms = DB::table('term_declarations')->whereIn('id', array_keys($termIds) ?: [0])
            ->orderBy('start_date')->orderBy('id')->get(['id', 'name']);
        $termIndex = [];
        foreach ($terms as $i => $t) $termIndex[(int) $t->id] = $i;

        $lists = ['courses' => [], 'statuses' => [], 'intakes' => []];
        $at = function (string $list, string $value) use (&$lists) {
            $i = array_search($value, $lists[$list], true);
            if ($i === false) {
                $lists[$list][] = $value;
                $i = count($lists[$list]) - 1;
            }

            return $i;
        };

        $rows = [];
        foreach ($students as $s) {
            $cells = [];
            foreach ($s['terms'] as $term => [$expected, $achieved, $no]) {
                $cells[] = [$termIndex[$term], $this->num($expected), $this->num($achieved), $no === 'No' ? 1 : 0];
            }
            usort($cells, fn ($a, $b) => $a[0] <=> $b[0]);
            $rows[] = [$s['id'], $s['reg'], $s['name'], $at('courses', $s['course']), $at('statuses', $s['status']), $cells, $at('intakes', $s['intake'])];
        }

        $c = $this->criteria();

        return [
            'points' => ['attendance' => $this->num($c['topAttendance']), 'academic' => $this->num($c['topAcademic']), 'grades' => array_map(fn ($p) => $this->num($p), $c['points'])],
            'terms' => $terms->map(fn ($t) => [(int) $t->id, (string) $t->name])->values()->all(),
            'courses' => $lists['courses'],
            'statuses' => $lists['statuses'],
            'intakes' => $lists['intakes'],
            'rows' => $rows,
        ];
    }

    /** The old export's sheet: a row per student and semester (latest first), then the student's totals. */
    public function sheet(array $f): array
    {
        $students = $this->students($f);
        $names = DB::table('term_declarations')->pluck('name', 'id');
        $sheet = [1 => ['Term Name', 'Term Status', 'Terms Student ID', 'Course', 'Intake Semester', 'Status', 'Student ID', 'Expected Performance', 'Achieved Performance', 'Grand Expected', 'Grand Achieved']];
        foreach ($students as $s) {
            $expected = 0;
            $achieved = 0;
            foreach ($s['terms'] as $term => [$e, $a, $no]) {
                $sheet[] = [(string) $names[$term], $no, $s['reg'], $s['course'], $s['intake'], $s['status'], '', $e ? $this->num($e) : '', $a ? $this->num($a) : '', '', ''];
                $expected += $e;
                $achieved += $a;
            }
            $sheet[] = ['', '', '', '', '', '', $s['reg'], '', '', $this->num($expected), $this->num($achieved)];
        }

        return $sheet;
    }

    private function num(float $v): int|float
    {
        return floor($v) == $v ? (int) $v : $v;
    }
}
