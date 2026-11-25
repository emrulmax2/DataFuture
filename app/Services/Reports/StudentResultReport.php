<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Reports › Student Result Report, new version (user's student-result-report design, 25 Nov 2026).
 *
 * Students are picked as the old search picks them (the old screen posts to
 * StudentDataReportController::totalCount → callTheStudentListForGroup), with its bugs fixed:
 *  - Evening / Weekend: the old query filters student_course_relations.full_time, which does not
 *    exist, so "Yes" fails; "No" ("0") is skipped as empty. Here both filter the proposed course's
 *    full_time (where the old option list reads it from).
 *  - Master group: the old search ignores it unless an attendance semester is chosen. Here it
 *    narrows by the group's plans in any term, or in the chosen terms.
 *  - Student type / status: when the earlier filters match nobody, the old search drops them and
 *    returns everyone of that type or status. Here nobody stays nobody.
 *  - Deleted students are left out (the old count left them out, its id list did not).
 *  - Certificate claimed "No" (Progress Monitoring): not claimed, award record or not.
 *
 * Grades are the old board export's (StudentResultReportController::excelDownload): per student,
 * the latest published result of each unit (module name), and "completed" = Pass, Merit or
 * Distinction.
 */
class StudentResultReport
{
    const PASSED = ['P', 'M', 'D'];

    /** A unit a course's students rarely have (fewer than 1 in 10) goes under "Other" for that course. */
    const UNIT_SHARE = 0.1;

    const TYPES = ['UK', 'BOTH', 'OVERSEAS'];

    /** The request's filters, cleaned. */
    public static function filters(array $in): array
    {
        $ints = fn ($v) => array_values(array_unique(array_filter(array_map('intval', (array) ($v ?? [])))));
        $strings = fn ($v) => array_values(array_unique(array_filter(array_map(fn ($x) => trim((string) $x), (array) ($v ?? [])), 'strlen')));
        $evening = (string) ($in['evening'] ?? '');

        return [
            'intake' => $ints($in['intake'] ?? []),
            'attendance' => $ints($in['attendance'] ?? []),
            'course' => $ints($in['course'] ?? []),
            // Group names as stored (the old search matches them exactly).
            'group' => array_values(array_unique(array_filter(array_map('strval', (array) ($in['group'] ?? [])), fn ($x) => trim($x) !== ''))),
            'evening' => in_array($evening, ['Yes', 'No'], true) ? $evening : '',
            'type' => array_values(array_intersect($strings($in['type'] ?? []), self::TYPES)),
            'status' => $ints($in['status'] ?? []),
            // Progress Monitoring only: certificate claimed (the student's latest award record).
            'certificate' => in_array($in['certificate'] ?? '', ['Yes', 'No'], true) ? $in['certificate'] : '',
        ];
    }

    /** The old search needs at least one filter. */
    public static function chosen(array $f): bool
    {
        foreach ($f as $value) {
            if ($value !== [] && $value !== '') return true;
        }

        return false;
    }

    /**
     * The students the filters pick, as [student id => course relation id]: the active course
     * relation that matched (the first one, as Student::crel, when several did).
     */
    public function students(array $f): array
    {
        $q = DB::table('student_course_relations as scr')
            ->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->join('students as s', 's.id', '=', 'scr.student_id')
            ->where('scr.active', 1)
            ->whereNull('scr.deleted_at')
            ->whereNull('cc.deleted_at')
            ->whereNull('s.deleted_at');
        if ($f['intake']) $q->whereIn('cc.semester_id', $f['intake']);
        if ($f['course']) $q->whereIn('cc.course_id', $f['course']);
        if ($f['evening'] !== '') {
            $q->whereExists(fn ($s) => $s->from('student_proposed_courses as spc')
                ->whereColumn('spc.student_course_relation_id', 'scr.id')
                ->whereNull('spc.deleted_at')
                ->where('spc.full_time', $f['evening'] === 'Yes' ? 1 : 0));
        }
        if ($f['type']) $q->whereIn('scr.type', $f['type']);
        if ($f['status']) $q->whereIn('s.status_id', $f['status']);
        // Yes: the latest award record says requested. No: it does not, or there is none (the old
        // Progress search only found students whose record says "No").
        if (($f['certificate'] ?? '') !== '') {
            $claimed = fn ($sub) => $sub->from('student_awards as sa')
                ->whereColumn('sa.student_id', 's.id')->whereNull('sa.deleted_at')->where('sa.certificate_requested', 'Yes')
                ->whereRaw('sa.id = (select max(x.id) from student_awards x where x.student_id = s.id and x.deleted_at is null)');
            $f['certificate'] === 'Yes' ? $q->whereExists($claimed) : $q->whereNotExists($claimed);
        }

        // Attendance semester and/or master group: assigned to one of those plans.
        if ($f['attendance'] || $f['group']) {
            $plans = DB::table('plans as p')
                ->join('courses as c', 'c.id', '=', 'p.course_id')
                ->whereNull('p.deleted_at')
                ->whereNull('c.deleted_at');
            if ($f['attendance']) $plans->whereIn('p.term_declaration_id', $f['attendance']);
            if ($f['course']) $plans->whereIn('p.course_id', $f['course']);
            if ($f['group']) {
                $plans->whereIn('p.group_id', DB::table('groups')->whereIn('name', $f['group'])->whereNull('deleted_at')->select('id'));
            }
            $q->whereExists(fn ($s) => $s->from('assigns as a')
                ->whereColumn('a.student_id', 'scr.student_id')
                ->whereNull('a.deleted_at')
                ->whereIn('a.plan_id', $plans->select('p.id')));
        }

        $out = [];
        foreach ($q->orderBy('scr.id')->get(['scr.student_id', 'scr.id']) as $row) {
            $out[(int) $row->student_id] ??= (int) $row->id;
        }
        ksort($out);

        return $out;
    }

    /**
     * Every found student with their unit grades, in the old export's order (first published result
     * first; students with no result after them).
     *
     * @return array{units: string[], students: array<int, array>}
     */
    public function grid(array $f): array
    {
        $crels = $this->students($f);
        if (! $crels) return ['units' => [], 'students' => []];
        $ids = array_keys($crels);

        $students = [];
        foreach (array_chunk($ids, 2000) as $chunk) {
            $rows = DB::table('students as s')
                ->join('student_course_relations as scr', fn ($j) => $j->on('scr.student_id', '=', 's.id'))
                ->leftJoin('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
                ->leftJoin('courses as c', 'c.id', '=', 'cc.course_id')
                ->leftJoin('semesters as sem', 'sem.id', '=', 'cc.semester_id')
                ->leftJoin('statuses as st', 'st.id', '=', 's.status_id')
                ->leftJoin('titles as t', fn ($j) => $j->on('t.id', '=', 's.title_id')->whereNull('t.deleted_at'))
                ->whereIn('s.id', $chunk)
                ->whereIn('scr.id', array_map(fn ($id) => $crels[$id], $chunk))
                ->get(['s.id', 's.registration_no', 's.first_name', 's.last_name', 't.name as title', 'st.name as status',
                    'c.id as course_id', 'c.name as course', 'sem.name as intake', 'scr.id as crel']);
            foreach ($rows as $r) {
                $students[(int) $r->id] = [
                    'id' => (int) $r->id,
                    'reg' => (string) $r->registration_no,
                    'name' => trim(($r->title ? $r->title.' ' : '').$r->first_name.' '.$r->last_name),
                    'status' => (string) $r->status,
                    'course_id' => (int) $r->course_id,
                    'course' => (string) $r->course,
                    'intake' => (string) $r->intake,
                    'ref' => '',
                    'groups' => [],
                    'grades' => [],
                ];
            }

            // Awarding body reference: the relation's latest record (StudentCourseRelation::abody).
            $refs = DB::table('student_awarding_body_details')
                ->whereIn('student_course_relation_id', array_map(fn ($id) => $crels[$id], $chunk))
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get(['student_id', 'student_course_relation_id', 'reference']);
            foreach ($refs as $r) {
                if (isset($students[(int) $r->student_id]) && $crels[(int) $r->student_id] === (int) $r->student_course_relation_id) {
                    $students[(int) $r->student_id]['ref'] = (string) $r->reference;
                }
            }

            // Groups: those of the student's latest attendance semester, as the old export.
            $assigns = DB::table('assigns as a')
                ->join('plans as p', 'p.id', '=', 'a.plan_id')
                ->join('groups as g', 'g.id', '=', 'p.group_id')
                ->whereIn('a.student_id', $chunk)
                ->whereNull('a.deleted_at')->whereNull('p.deleted_at')->whereNull('g.deleted_at')
                ->orderBy('a.id')
                ->get(['a.student_id', 'p.term_declaration_id as term', 'g.name']);
            $byTerm = [];
            foreach ($assigns as $a) {
                $byTerm[(int) $a->student_id][(int) $a->term][$a->name] = true;
            }
            foreach ($byTerm as $sid => $terms) {
                if (isset($students[$sid])) $students[$sid]['groups'] = array_keys($terms[max(array_keys($terms))]);
            }
        }

        // Results: the latest published one per unit wins (the old export overwrites in published order).
        $order = [];
        $units = [];
        foreach (array_chunk($ids, 2000) as $chunk) {
            $results = DB::table('results as r')
                ->join('plans as p', 'p.id', '=', 'r.plan_id')
                ->join('module_creations as mc', 'mc.id', '=', 'p.module_creation_id')
                ->join('course_modules as cm', 'cm.id', '=', 'mc.course_module_id')
                ->leftJoin('grades as g', fn ($j) => $j->on('g.id', '=', 'r.grade_id')->whereNull('g.deleted_at'))
                ->whereIn('r.student_id', $chunk)
                ->whereNull('r.deleted_at')->whereNull('p.deleted_at')->whereNull('mc.deleted_at')->whereNull('cm.deleted_at')
                ->orderBy('r.published_at')->orderBy('r.id')
                ->get(['r.student_id', 'r.published_at', 'r.id', 'cm.name as unit', 'g.code']);
            foreach ($results as $r) {
                $sid = (int) $r->student_id;
                if (! isset($students[$sid])) continue;
                $order[$sid] ??= [(string) $r->published_at, (int) $r->id];
                $students[$sid]['grades'][$r->unit] = (string) ($r->code ?? '');
                $units[$r->unit] = true;
            }
        }

        // A null grade overwrites like any other, but leaves no result (the old export's blank cell).
        foreach ($students as &$s) {
            $s['grades'] = array_filter($s['grades'], 'strlen');
        }
        unset($s);

        uksort($students, function ($a, $b) use ($order) {
            if (isset($order[$a], $order[$b])) return $order[$a] <=> $order[$b];
            if (isset($order[$a]) !== isset($order[$b])) return isset($order[$a]) ? -1 : 1;

            return $a <=> $b;
        });

        $units = array_keys($units);
        sort($units);

        return ['units' => $units, 'students' => array_values($students)];
    }

    /** The screen's data: units by course, and one compact row per student. */
    public function build(array $f): array
    {
        $grid = $this->grid($f);
        $units = $grid['units'];
        $unitIndex = array_flip($units);

        $courses = [];
        foreach ($grid['students'] as $s) {
            $courses[$s['course_id']] ??= ['id' => $s['course_id'], 'name' => $s['course'], 'students' => 0, 'had' => []];
            $courses[$s['course_id']]['students']++;
            foreach ($s['grades'] as $unit => $code) {
                $courses[$s['course_id']]['had'][$unit] = ($courses[$s['course_id']]['had'][$unit] ?? 0) + 1;
            }
        }
        foreach ($courses as &$c) {
            $min = max(1, (int) ceil($c['students'] * self::UNIT_SHARE));
            $own = array_keys(array_filter($c['had'], fn ($n) => $n >= $min));
            sort($own);
            $c['units'] = array_map(fn ($u) => $unitIndex[$u], $own);
            $c['own'] = array_flip($own);
            $c['other'] = count($c['had']) - count($own);
        }
        unset($c);
        uasort($courses, fn ($a, $b) => strcmp($a['name'], $b['name']));
        $courseIndex = array_flip(array_keys($courses));

        $lists = ['statuses' => [], 'groups' => [], 'intakes' => []];
        $at = function (string $list, string $value) use (&$lists) {
            $i = array_search($value, $lists[$list], true);
            if ($i === false) {
                $lists[$list][] = $value;
                $i = count($lists[$list]) - 1;
            }

            return $i;
        };

        $rows = [];
        foreach ($grid['students'] as $s) {
            $c = $courses[$s['course_id']];
            $own = array_map(fn ($u) => $s['grades'][$units[$u]] ?? '', $c['units']);
            $other = [];
            foreach ($s['grades'] as $unit => $code) {
                if (! isset($c['own'][$unit])) $other[] = [$unitIndex[$unit], $code];
            }
            $rows[] = [
                $s['id'], $s['reg'], $s['name'], $at('statuses', $s['status']), $courseIndex[$s['course_id']],
                array_map(fn ($g) => $at('groups', $g), $s['groups']), $own, $other, $at('intakes', $s['intake']),
            ];
        }

        return [
            'units' => $units,
            'courses' => array_values(array_map(fn ($c) => ['id' => $c['id'], 'name' => $c['name'], 'units' => $c['units'], 'other' => $c['other']], $courses)),
            'statuses' => $lists['statuses'],
            'groups' => $lists['groups'],
            'intakes' => $lists['intakes'],
            'rows' => $rows,
        ];
    }

    /** The old board export's sheet: [rows, two header rows, unit list] for CustomArrayCollectionExport. */
    public function sheet(array $f): array
    {
        $grid = $this->grid($f);
        $units = $grid['units'];

        $headers = [1 => ['Student ID', 'Student Name', 'Status', 'Intake Semester', 'Course', 'Awarding Body Ref', 'Group'], 2 => array_fill(0, 7, '')];
        foreach ($units as $i => $unit) {
            $headers[1][] = $i === 0 ? 'Unit Numbers' : '';
            $headers[2][] = $unit;
        }
        array_push($headers[1], 'Completed New Units', 'Assessment Board Outcome');
        array_push($headers[2], '', '');

        $rows = [];
        $n = 1;
        foreach ($grid['students'] as $s) {
            $row = [$s['reg'], $s['name'], $s['status'], $s['intake'], $s['course'], $s['ref'], implode(', ', $s['groups'])];
            $done = 0;
            foreach ($units as $unit) {
                $code = $s['grades'][$unit] ?? '';
                if (in_array($code, self::PASSED, true)) $done++;
                $row[] = $code;
            }
            array_push($row, $done, '');
            $rows[$n++] = $row;
        }

        return [$rows, $headers, $units];
    }

    /**
     * Everything the new search form needs (Student Result and Result Grade Performance pages):
     * [id, name] pairs for the old page's semesters, terms, courses and statuses, plus options().
     */
    public function formOptions(iterable $semesters, iterable $courses, iterable $statuses): array
    {
        $pairs = fn ($items) => collect($items)->map(fn ($item) => [$item->id, $item->name])->values()->all();

        return array_merge($this->options(), [
            'intakes' => $pairs($semesters),
            'terms' => $pairs(\App\Models\TermDeclaration::all()->sortByDesc('id')),
            // Active courses only (user, 25 Nov 2026); a course the Old tab sends is added on the fly.
            'courses' => $pairs(collect($courses)->filter(fn ($course) => (int) $course->active === 1)->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)),
            'statuses' => $pairs(collect($statuses)->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)),
        ]);
    }

    /**
     * The form's lists. Master groups carry their courses and attendance semesters, so the list
     * narrows to the chosen ones in the browser (164 names).
     */
    public function options(): array
    {
        $groups = [];
        $rows = DB::table('groups')->whereNull('deleted_at')->whereNotNull('name')->where('name', '<>', '')
            ->get(['name', 'course_id', 'term_declaration_id']);
        foreach ($rows as $g) {
            $name = (string) $g->name;
            $groups[$name] ??= [$name, [], []];
            if ($g->course_id) $groups[$name][1][(int) $g->course_id] = true;
            if ($g->term_declaration_id) $groups[$name][2][(int) $g->term_declaration_id] = true;
        }
        uksort($groups, 'strnatcasecmp');

        return [
            'groups' => array_values(array_map(fn ($g) => [$g[0], array_keys($g[1]), array_keys($g[2])], $groups)),
            'types' => self::TYPES,
            'grades' => DB::table('grades')->whereNull('deleted_at')->whereNotNull('code')->where('code', '<>', '')->pluck('name', 'code')->all(),
        ];
    }
}
