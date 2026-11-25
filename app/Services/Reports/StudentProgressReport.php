<?php

namespace App\Services\Reports;

use App\Models\AcademicCriteria;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Reports › Progress Monitoring (/reports/student-progress-reports), new version (user's
 * progress-monitoring-report design, 25 Nov 2026).
 *
 * Students: the old search's, as on the Student Result Report (StudentResultReport::students), plus
 * Certificate claimed. Figures: the old report's (StudentProgressMonitoringReportController::
 * listProgressReport and excelDownload), per student, semester and module:
 *  - modules come from results published before now on classes the student is assigned to, by
 *    semester and module name; a module is complete when one of its results is Pass, Merit or
 *    Distinction (the academic criteria codes), otherwise incomplete ("failed");
 *  - attempts = its results; credits = the module's credit value when complete, by level
 *    (unit value 4 or 5);
 *  - semesters = the chosen attendance semesters, or those with results.
 * New: modules the student is assigned to with no published result (awaiting while the semester
 * runs, "no result" after), on screen only; credit-less tutorials are left out.
 */
class StudentProgressReport
{
    public function __construct(private StudentResultReport $picker)
    {
    }

    /** Every found student with their modules and certificate, by student id. */
    public function students(array $f): array
    {
        $crels = $this->picker->students($f);
        if (! $crels) return [];
        $ids = array_keys($crels);
        $passing = AcademicCriteria::pluck('code')->all();
        $chosen = $f['attendance'] ?: null;
        $now = now();

        $out = [];
        foreach (array_chunk($ids, 1000) as $chunk) {
            $relations = array_map(fn ($id) => $crels[$id], $chunk);
            $details = DB::table('students as s')
                ->join('student_course_relations as scr', 'scr.student_id', '=', 's.id')
                ->leftJoin('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
                ->leftJoin('courses as co', 'co.id', '=', 'cc.course_id')
                ->leftJoin('statuses as st', 'st.id', '=', 's.status_id')
                ->leftJoin('titles as t', fn ($j) => $j->on('t.id', '=', 's.title_id')->whereNull('t.deleted_at'))
                ->whereIn('s.id', $chunk)->whereIn('scr.id', $relations)
                ->get(['s.id', 's.registration_no', 's.first_name', 's.last_name', 't.name as title', 'st.name as status', 'co.name as course', 'scr.id as crel']);
            $intakes = DB::table('student_proposed_courses as spc')
                ->join('semesters as sem', 'sem.id', '=', 'spc.semester_id')
                ->whereIn('spc.student_course_relation_id', $relations)->whereNull('spc.deleted_at')
                ->orderBy('spc.id')->pluck('sem.name', 'spc.student_course_relation_id');
            foreach ($details as $d) {
                $out[(int) $d->id] = [
                    'id' => (int) $d->id,
                    'reg' => (string) $d->registration_no,
                    'name' => trim(($d->title ? $d->title.' ' : '').$d->first_name.' '.$d->last_name),
                    'status' => (string) $d->status,
                    'course' => (string) $d->course,
                    'intake' => (string) ($intakes[(int) $d->crel] ?? ''),
                    'group' => '',
                    'terms' => [],
                    'award' => null,
                ];
            }

            // The student's classes: every assigned plan id, and the live ones with a module.
            $assigned = [];
            $classes = [];
            $latest = [];
            $rows = DB::table('assigns as a')
                ->leftJoin('plans as p', fn ($j) => $j->on('p.id', '=', 'a.plan_id')->whereNull('p.deleted_at'))
                ->leftJoin('groups as g', fn ($j) => $j->on('g.id', '=', 'p.group_id')->whereNull('g.deleted_at'))
                ->whereIn('a.student_id', $chunk)->whereNull('a.deleted_at')
                ->orderBy('a.id')
                ->get(['a.student_id', 'a.plan_id', 'p.id as live', 'p.term_declaration_id as term', 'p.module_creation_id', 'g.name as group']);
            foreach ($rows as $a) {
                $sid = (int) $a->student_id;
                $assigned[$sid][(int) $a->plan_id] = true;
                if ($a->live && $a->module_creation_id) $classes[$sid][] = (int) $a->plan_id;
                if ($a->live && $a->group !== null && (! isset($latest[$sid]) || (int) $a->term >= $latest[$sid][0])) {
                    $latest[$sid] = [(int) $a->term, (string) $a->group];
                }
            }
            foreach ($latest as $sid => [, $group]) {
                if (isset($out[$sid])) $out[$sid]['group'] = $group;
            }

            // Results, latest first: each later (older) one overwrites the module's details, as the old loop does.
            $results = DB::table('results as r')
                ->join('plans as p', 'p.id', '=', 'r.plan_id')
                ->join('module_creations as mc', 'mc.id', '=', 'p.module_creation_id')
                ->join('course_modules as cm', 'cm.id', '=', 'mc.course_module_id')
                ->join('grades as gr', 'gr.id', '=', 'r.grade_id')
                ->leftJoin('groups as g', fn ($j) => $j->on('g.id', '=', 'p.group_id')->whereNull('g.deleted_at'))
                ->whereIn('r.student_id', $chunk)
                ->whereNull('r.deleted_at')->whereNull('p.deleted_at')->whereNull('mc.deleted_at')->whereNull('cm.deleted_at')->whereNull('gr.deleted_at')
                ->where('r.published_at', '<', $now)
                ->orderByDesc('r.published_at')->orderBy('r.plan_id')->orderBy('r.id') // ties as MySQL returns the old query's (read by the plan index)
                ->get(['r.student_id', 'r.plan_id', 'p.term_declaration_id as term', 'p.tutor_id', 'g.name as group', 'mc.status as mc_status',
                    'cm.status as cm_status', 'cm.name as module', 'cm.unit_value', 'cm.credit_value', 'gr.code']);
            $sets = [];
            $order = [];
            $tutors = [];
            foreach ($results as $r) {
                $sid = (int) $r->student_id;
                if (! isset($assigned[$sid][(int) $r->plan_id])) continue;
                $term = (int) $r->term;
                $order[$sid][$term] = true;
                $m = $sets[$sid][$term][$r->module] ?? ['result' => '', 'last' => (string) $r->code, 'attempts' => 0];
                if (in_array($r->code, $passing, true)) $m['result'] = $r->code;
                $m['group'] = (string) $r->group;
                $m['level'] = $r->unit_value;
                $m['credit'] = $r->credit_value;
                $m['status'] = strtoupper(substr((string) ($r->mc_status ?: $r->cm_status), 0, 1));
                $m['tutor'] = (int) $r->tutor_id;
                $m['attempts']++;
                $sets[$sid][$term][$r->module] = $m;
                $tutors[(int) $r->tutor_id] = true;
            }

            // Assigned modules with no published result in that semester: awaiting.
            $planIds = array_merge([], ...array_values($classes));
            $waiting = [];
            foreach (array_chunk(array_unique($planIds), 2000) as $plans) {
                $info = DB::table('plans as p')
                    ->join('module_creations as mc', 'mc.id', '=', 'p.module_creation_id')
                    ->join('course_modules as cm', 'cm.id', '=', 'mc.course_module_id')
                    ->leftJoin('groups as g', fn ($j) => $j->on('g.id', '=', 'p.group_id')->whereNull('g.deleted_at'))
                    ->whereIn('p.id', $plans)->whereNull('mc.deleted_at')->whereNull('cm.deleted_at')
                    ->get(['p.id', 'p.term_declaration_id as term', 'p.tutor_id', 'g.name as group', 'mc.status as mc_status', 'cm.status as cm_status',
                        'cm.name as module', 'cm.unit_value', 'cm.credit_value']);
                foreach ($info as $p) $waiting[(int) $p->id] = $p;
            }

            foreach ($chunk as $sid) {
                if (! isset($out[$sid])) continue;
                $terms = $chosen ?: array_keys($order[$sid] ?? []);
                foreach ($terms as $term) {
                    foreach ($sets[$sid][$term] ?? [] as $module => $m) {
                        $out[$sid]['terms'][$term][$module] = $m;
                    }
                }
                foreach ($classes[$sid] ?? [] as $planId) {
                    $p = $waiting[$planId] ?? null;
                    if (! $p || ($chosen && ! in_array((int) $p->term, $chosen, true)) || isset($sets[$sid][(int) $p->term][$p->module])) continue;
                    // Group and Personal Tutorials carry no credit and are never assessed.
                    if (! is_numeric($p->credit_value) || (float) $p->credit_value <= 0) continue;
                    $out[$sid]['terms'][(int) $p->term][$p->module] ??= [
                        'result' => '', 'last' => '', 'attempts' => 0, 'group' => (string) $p->group, 'level' => $p->unit_value,
                        'credit' => $p->credit_value, 'status' => strtoupper(substr((string) ($p->mc_status ?: $p->cm_status), 0, 1)), 'tutor' => (int) $p->tutor_id,
                    ];
                    $tutors[(int) $p->tutor_id] = true;
                }
                // Old reports list the chosen semesters in that order, else as the results came (latest first).
                $out[$sid]['order'] = $terms;
            }

            $awards = DB::table('student_awards as sa')
                ->leftJoin('qual_award_results as q', 'q.id', '=', 'sa.qual_award_result_id')
                ->whereIn('sa.student_id', $chunk)->whereNull('sa.deleted_at')
                ->whereRaw('sa.id = (select max(x.id) from student_awards x where x.student_id = sa.student_id and x.deleted_at is null)')
                ->get(['sa.student_id', 'sa.date_of_award', 'sa.certificate_requested', 'sa.date_of_certificate_requested', 'sa.certificate_requested_by',
                    'sa.certificate_received', 'sa.date_of_certificate_received', 'sa.certificate_released', 'sa.date_of_certificate_released', 'q.name as overall']);
            foreach ($awards as $a) {
                if (! isset($out[(int) $a->student_id])) continue;
                $out[(int) $a->student_id]['award'] = [
                    'awarded' => $this->date($a->date_of_award),
                    'claimed' => (string) $a->certificate_requested,
                    'claimed_on' => $this->date($a->date_of_certificate_requested),
                    'claimed_by' => (int) $a->certificate_requested_by,
                    'received' => (string) $a->certificate_received,
                    'received_on' => $this->date($a->date_of_certificate_received),
                    'released' => (string) $a->certificate_released,
                    'released_on' => $this->date($a->date_of_certificate_released),
                    'overall' => (string) $a->overall,
                ];
                $tutors[(int) $a->certificate_requested_by] = true;
            }

            $names = $this->staff(array_keys($tutors));
            foreach ($chunk as $sid) {
                if (! isset($out[$sid])) continue;
                foreach ($out[$sid]['terms'] as &$modules) {
                    foreach ($modules as &$m) $m['tutor'] = $names[$m['tutor']] ?? '';
                }
                unset($modules, $m);
                if ($out[$sid]['award']) $out[$sid]['award']['claimed_by'] = $names[$out[$sid]['award']['claimed_by']] ?? '';
            }
        }
        ksort($out);

        return $out;
    }

    /** Staff names as the old report prints them: the user's latest employee record, in capitals. */
    private function staff(array $userIds): array
    {
        $userIds = array_filter($userIds);
        if (! $userIds) return [];
        $live = DB::table('users')->whereIn('id', $userIds)->whereNull('deleted_at')->pluck('id')->all();

        return DB::table('employees as e')
            ->whereIn('e.user_id', $live ?: [0])
            ->whereRaw('e.id = (select max(x.id) from employees x where x.user_id = e.user_id)')
            ->get(['e.user_id', 'e.first_name', 'e.last_name'])
            ->mapWithKeys(fn ($e) => [(int) $e->user_id => strtoupper($e->first_name.' '.$e->last_name)])
            ->all();
    }

    private function date($value): string
    {
        return $value ? date('d-m-Y', strtotime($value)) : '';
    }

    /** Level of a module from its unit value (4 or 5 count, as the old report; 6 shown too). */
    private function level($unit): int
    {
        return is_numeric($unit) && in_array((int) $unit, [4, 5, 6], true) && (string) (int) $unit === trim((string) $unit) ? (int) $unit : 0;
    }

    /** The screen's data: compact rows with modules [term, unit, level, credit, group, tutor, result, last grade, attempts, status]. */
    public function build(array $f): array
    {
        $students = $this->students($f);
        $termIds = [];
        foreach ($students as $s) {
            foreach ($s['terms'] as $term => $_) $termIds[$term] = true;
        }
        $terms = DB::table('term_declarations')->whereIn('id', array_keys($termIds) ?: [0])->orderBy('start_date')->orderBy('id')->get(['id', 'name', 'end_date']);
        $today = now()->toDateString();
        $termIndex = [];
        foreach ($terms as $i => $t) $termIndex[(int) $t->id] = $i;

        $lists = ['units' => [], 'staff' => [], 'groups' => [], 'courses' => [], 'statuses' => [], 'intakes' => []];
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
        foreach ($students as $s) {
            $mods = [];
            foreach ($s['terms'] as $term => $modules) {
                foreach ($modules as $name => $m) {
                    $mods[] = [$termIndex[$term], $at('units', (string) $name), $this->level($m['level']), is_numeric($m['credit']) ? $m['credit'] + 0 : 0,
                        $at('groups', $m['group']), $at('staff', $m['tutor']), $m['result'], $m['last'], $m['attempts'], $m['status']];
                }
            }
            $a = $s['award'];
            $rows[] = [$s['id'], $s['reg'], $s['name'], $at('statuses', $s['status']), $at('courses', $s['course']), $at('intakes', $s['intake']),
                $at('groups', $s['group']), $mods,
                $a ? [$a['awarded'], $a['claimed'] === 'Yes', $a['claimed_on'], $a['claimed_by'], $a['received'] === 'Yes', $a['received_on'],
                    $a['released'] === 'Yes', $a['released_on'], $a['overall']] : null];
        }

        return [
            // [id, name, ended]: a module with no result is "awaiting" until its semester ends.
            'terms' => $terms->map(fn ($t) => [(int) $t->id, (string) $t->name, $t->end_date && $t->end_date < $today])->values()->all(),
            'grades' => DB::table('grades')->whereNull('deleted_at')->whereNotNull('code')->pluck('name', 'code')->all(),
            'passing' => AcademicCriteria::pluck('code')->all(),
        ] + $lists + ['rows' => $rows];
    }

    /** The old export's sheet: search, date and author lines, the 29 columns, module rows, semester and student totals. */
    public function sheet(array $f, string $criteria): array
    {
        $students = $this->students($f);
        $termNames = DB::table('term_declarations')->whereNull('deleted_at')->pluck('name', 'id');
        $me = Auth::id() ? Employee::where('user_id', Auth::id())->first() : null;
        $sheet = [
            1 => ['Search Criteria', $criteria],
            2 => ['Report created date time', now()->format('jS M Y h:i a')],
            3 => ['Created by', $me ? $me->full_name : ''],
            4 => [],
            5 => ['LCC ID', 'Status', 'Intake Semester', 'Course', 'Attendance Semester', 'Group', 'Module Serial', 'Module', 'Unit Value', 'Credit Value', 'Module Status',
                'Level 4 Unit Value', 'Level 4 Credit Value', 'Level 5 Unit Value', 'Level 5 Credit Value', 'Tutor', 'Results', 'Attempts', 'Complete', 'Incomplete',
                'Certificate Claimed', 'Certificate Claimed Date', 'Certificate Requested By', 'Certificate Received', 'Certificate Received Date',
                'Certificate Released', 'Certificate Released Date', 'Awarded Date', 'Overall Result'],
        ];
        $blank = array_fill(0, 29, '');
        foreach ($students as $s) {
            $done = $open = $credit = $l4u = $l4c = $l5u = $l5c = $count = 0;
            foreach ($s['order'] as $term) {
                $termDone = $termOpen = 0;
                $i = 1;
                foreach ($s['terms'][$term] ?? [] as $name => $m) {
                    if (! $m['attempts']) continue; // awaiting: not in the old export
                    $complete = $m['result'] !== '';
                    if ($complete) {
                        $done++;
                        $termDone++;
                        $credit += (float) $m['credit'];
                        if ((string) $m['level'] === '4' || $m['level'] == 4) { $l4c += (float) $m['credit']; $l4u++; }
                        if ((string) $m['level'] === '5' || $m['level'] == 5) { $l5c += (float) $m['credit']; $l5u++; }
                    } else {
                        $open++;
                        $termOpen++;
                    }
                    $count++;
                    $row = $blank;
                    $row[4] = $i > 1 ? '' : (string) ($termNames[$term] ?? '');
                    $row[5] = $m['group'];
                    $row[6] = $i++;
                    $row[7] = $name;
                    $row[8] = $m['level'];
                    $row[9] = $complete ? $m['credit'] : '';
                    $row[10] = $m['status'];
                    $row[15] = $m['tutor'];
                    $row[16] = $m['result'];
                    $row[17] = $m['attempts'];
                    $sheet[] = $row;
                }
                if (count($s['order']) > 1) {
                    $row = $blank;
                    $row[18] = $termDone;
                    $row[19] = $termOpen;
                    $sheet[] = $row;
                }
            }
            $a = $s['award'];
            $sheet[] = [$s['reg'], $s['status'], $s['intake'], $s['course'], '', '', '', $count, '', $this->num($credit), '',
                $l4u, $this->num($l4c), $l5u, $this->num($l5c), '', '', '', $done, $open,
                $a['claimed'] ?? '', $a['claimed_on'] ?? '', $a['claimed_by'] ?? '', $a['received'] ?? '', $a['received_on'] ?? '',
                $a['released'] ?? '', $a['released_on'] ?? '', $a['awarded'] ?? '', $a['overall'] ?? ''];
        }

        return $sheet;
    }

    private function num(float $v): int|float
    {
        return floor($v) == $v ? (int) $v : $v;
    }
}
