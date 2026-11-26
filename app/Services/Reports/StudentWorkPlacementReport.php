<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Reports › Student Work Placement (/reports/student-workplacement-reports), new version (user's
 * student-work-placement-report design, 25 Nov 2026).
 *
 * Students: the old search's (StudentResultReport::students), kept when they have work placement
 * records (the old report) or their course has a work placement requirement for their start date
 * (the rule of the student's Work Placement tab, StudentController::workplacement). The second
 * group is new: students who have not logged anything yet, which the old report left out.
 *
 * Figures: the old export's (StudentWorkplacementReportController::excelDownload). Required hours
 * come from the work placement set-ups (details → level hours → learning hours) of the student's
 * records, or of their course when they have none; completed hours are the Confirmed records' hours
 * by set-up, level and learning hours; the total completed is every Confirmed hour. Hours are shown
 * to two decimals (the old sheet printed float sums as they came, e.g. 146.45000000000002).
 */
class StudentWorkPlacementReport
{
    public function __construct(private StudentResultReport $picker)
    {
    }

    /** Set-ups with their levels and learning hours (both by name, as the old export orders them). */
    private function setups(): array
    {
        $out = [];
        foreach (DB::table('workplacement_details')->whereNull('deleted_at')->get(['id', 'name', 'hours', 'course_id', 'start_date', 'end_date']) as $d) {
            $out[(int) $d->id] = ['id' => (int) $d->id, 'name' => (string) $d->name, 'hours' => (float) $d->hours, 'course' => (int) $d->course_id,
                'start' => $d->start_date, 'end' => $d->end_date, 'levels' => []];
        }
        $levels = DB::table('level_hours')->whereIn('workplacement_details_id', array_keys($out) ?: [0])->orderBy('name')->orderBy('id')->get(['id', 'name', 'hours', 'workplacement_details_id']);
        $learning = DB::table('learning_hours')->whereIn('level_hours_id', $levels->pluck('id')->all() ?: [0])->orderBy('name')->orderBy('id')->get(['id', 'name', 'hours', 'level_hours_id'])->groupBy('level_hours_id');
        foreach ($levels as $l) {
            $out[(int) $l->workplacement_details_id]['levels'][(int) $l->id] = [
                'name' => (string) $l->name, 'hours' => (float) $l->hours,
                'learning' => collect($learning[$l->id] ?? [])->mapWithKeys(fn ($h) => [(int) $h->id => ['name' => (string) $h->name, 'hours' => (float) $h->hours]])->all(),
            ];
        }

        return $out;
    }

    /** Every found student with placement figures, by student id, newest id first (the old export's order). */
    public function students(array $f): array
    {
        $crels = $this->picker->students($f);
        if (! $crels) return [];
        $setups = $this->setups();
        $out = [];

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
                ->get(['s.id', 's.registration_no', 's.first_name', 's.last_name', 't.name as title', 'st.name as status', 'co.name as course', 'cc.course_id',
                    'sem.name as intake', 'scr.course_start_date', 'scr.course_creation_id']);
            $starts = DB::table('course_creation_availabilities as a')
                ->whereIn('a.course_creation_id', $details->pluck('course_creation_id')->filter()->unique()->all() ?: [0])->whereNull('a.deleted_at')
                ->orderBy('a.id')->pluck('a.course_start_date', 'a.course_creation_id');

            $records = DB::table('student_work_placements as w')
                ->leftJoin('module_creations as mc', fn ($j) => $j->on('mc.id', '=', 'w.assign_module_list_id')->whereNull('mc.deleted_at'))
                ->leftJoin('course_modules as cm', fn ($j) => $j->on('cm.id', '=', 'mc.course_module_id')->whereNull('cm.deleted_at'))
                ->whereIn('w.student_id', $chunk)->whereNull('w.deleted_at')
                ->orderBy('w.id')
                ->get(['w.student_id', 'w.workplacement_details_id as setup', 'w.level_hours_id as level', 'w.learning_hours_id as learning', 'w.status', 'w.hours',
                    'w.assign_module_list_id as creation', 'cm.name as module'])
                ->groupBy('student_id');

            foreach ($details as $d) {
                $sid = (int) $d->id;
                $mine = $records[$sid] ?? collect();
                $used = [];
                foreach ($mine as $r) {
                    if ($r->setup && isset($setups[(int) $r->setup])) $used[(int) $r->setup] = true;
                }
                $start = $d->course_start_date ?: ($starts[$d->course_creation_id] ?? null);
                $start = $start ? date('Y-m-d', strtotime($start)) : null;
                if (! $used && $start) {
                    // No records: the course's set-up for their start date (latest first, as the student's tab).
                    foreach (array_reverse($setups, true) as $s) {
                        if ($s['course'] === (int) $d->course_id && $s['start'] && $s['start'] <= $start && (! $s['end'] || $s['end'] >= $start)) {
                            $used[$s['id']] = true;
                            break;
                        }
                    }
                }
                if (! $used && $mine->isEmpty()) continue;

                $done = ['setup' => [], 'level' => [], 'learning' => []];
                $total = 0.0;
                $pending = 0.0;
                $modules = [];
                foreach ($mine as $r) {
                    $hours = (float) $r->hours;
                    if ($r->status === 'Pending') $pending += $hours;
                    if ($r->status !== 'Confirmed') continue;
                    $total += $hours;
                    // '' = no module recorded (the old sheet's unnamed "Module List" column).
                    $name = $r->creation ? (string) ($r->module ?? 'Unknown module') : '';
                    $modules[$name] = ($modules[$name] ?? 0) + $hours;
                    if (! $r->setup) continue;
                    $done['setup'][(int) $r->setup] = ($done['setup'][(int) $r->setup] ?? 0) + $hours;
                    if (! $r->level) continue;
                    $done['level'][(int) $r->setup][(int) $r->level] = ($done['level'][(int) $r->setup][(int) $r->level] ?? 0) + $hours;
                    if (! $r->learning) continue;
                    $done['learning'][(int) $r->setup][(int) $r->level][(int) $r->learning] = ($done['learning'][(int) $r->setup][(int) $r->level][(int) $r->learning] ?? 0) + $hours;
                }

                $out[$sid] = [
                    'id' => $sid,
                    'reg' => (string) $d->registration_no,
                    'name' => trim(($d->title ? $d->title.' ' : '').$d->first_name.' '.$d->last_name),
                    'status' => (string) $d->status,
                    'course' => (string) $d->course,
                    'intake' => (string) $d->intake,
                    'records' => $mine->isNotEmpty(),
                    'setups' => array_keys($used),
                    'done' => $done,
                    'total' => $total,
                    'pending' => $pending,
                    'modules' => $modules,
                ];
            }
        }
        krsort($out);

        return $out;
    }

    /** The screen's data: level columns by name (guided / non-guided inside each), one row per student. */
    public function build(array $f): array
    {
        $students = $this->students($f);
        $setups = $this->setups();

        // Columns: level names, each with its learning-hour names, over the set-ups in this result.
        $levels = [];
        foreach ($students as $s) {
            foreach ($s['setups'] as $id) {
                foreach ($setups[$id]['levels'] as $l) {
                    $levels[$l['name']] ??= [];
                    foreach ($l['learning'] as $h) $levels[$l['name']][$h['name']] = true;
                }
            }
        }
        ksort($levels, SORT_NATURAL);
        $cols = [];
        foreach ($levels as $name => $learning) {
            $names = array_keys($learning);
            sort($names, SORT_NATURAL);
            $cols[] = [$name, $names];
        }

        $lists = ['courses' => [], 'statuses' => [], 'intakes' => [], 'modules' => ['']];
        $index = ['modules'."\0" => 0];
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
            $need = [];
            $got = [];
            foreach ($cols as $c => [$levelName, $learningNames]) {
                $need[$c] = [0.0, array_fill(0, count($learningNames), 0.0)];
                $got[$c] = [0.0, array_fill(0, count($learningNames), 0.0)];
                foreach ($s['setups'] as $id) {
                    foreach ($setups[$id]['levels'] as $levelId => $l) {
                        if ($l['name'] !== $levelName) continue;
                        $need[$c][0] += $l['hours'];
                        $got[$c][0] += $s['done']['level'][$id][$levelId] ?? 0;
                        foreach ($l['learning'] as $learningId => $h) {
                            $k = array_search($h['name'], $learningNames, true);
                            $need[$c][1][$k] += $h['hours'];
                            $got[$c][1][$k] += $s['done']['learning'][$id][$levelId][$learningId] ?? 0;
                        }
                    }
                }
            }
            $required = array_sum(array_map(fn ($id) => $setups[$id]['hours'], $s['setups']));
            $mods = [];
            foreach ($s['modules'] as $name => $hours) $mods[] = [$at('modules', (string) $name), $this->round($hours)];
            $rows[] = [$s['id'], $s['reg'], $s['name'], $at('statuses', $s['status']), $at('courses', $s['course']), $at('intakes', $s['intake']),
                $this->rounded($need), $this->rounded($got), $this->round($required), $this->round($s['total']), $this->round($s['pending']), $mods, $s['records']];
        }

        return ['levels' => $cols] + $lists + ['rows' => $rows];
    }

    /** The old export: two header rows, set-up / level / learning columns, the module list, totals; plus its merges. */
    public function sheet(array $f): array
    {
        $students = $this->students($f);
        $setups = $this->setups();

        $used = [];
        foreach ($students as $s) {
            foreach ($s['setups'] as $id) $used[$id] = $setups[$id];
        }
        uasort($used, fn ($a, $b) => strcmp($a['name'], $b['name']));

        // Module columns: the modules of the students' classes; hours logged against that class's module.
        $assigned = [];
        if ($students) {
            $rows = DB::table('assigns as a')
                ->join('plans as p', 'p.id', '=', 'a.plan_id')
                ->join('module_creations as mc', 'mc.id', '=', 'p.module_creation_id')
                ->join('course_modules as cm', 'cm.id', '=', 'mc.course_module_id')
                ->whereIn('a.student_id', array_keys($students))
                ->whereNull('a.deleted_at')->whereNull('p.deleted_at')->whereNull('mc.deleted_at')->whereNull('cm.deleted_at')
                ->orderBy('a.id')
                ->get(['a.student_id', 'mc.id as creation', 'cm.name']);
            $hours = DB::table('student_work_placements')->whereIn('student_id', array_keys($students))->whereNull('deleted_at')
                ->where('status', 'Confirmed')->whereNotNull('assign_module_list_id')
                ->groupBy('student_id', 'assign_module_list_id')
                ->selectRaw('student_id, assign_module_list_id as creation, sum(hours) as hours')->get();
            $byCreation = [];
            foreach ($hours as $h) $byCreation[(int) $h->student_id][(int) $h->creation] = (float) $h->hours;
            foreach ($rows as $r) {
                $assigned[(int) $r->student_id][$r->name] = $byCreation[(int) $r->student_id][(int) $r->creation] ?? 0;
            }
        }
        $moduleNames = [];
        foreach ($assigned as $list) foreach ($list as $name => $_) $moduleNames[$name] = true;
        $moduleNames = array_keys($moduleNames);
        sort($moduleNames);
        $unassigned = false;
        foreach ($students as $s) {
            if (($s['modules'][''] ?? 0) > 0) $unassigned = true;
        }

        $headers = [['Student ID', 'Student Name', 'Status', 'Intake Semester', 'Course'], ['', '', '', '', '']];
        $merges = ['row1' => []];
        $col = 5;
        foreach ($used as $setup) {
            $from = $col;
            foreach ($setup['levels'] as $level) {
                array_push($headers[0], $setup['name'], $setup['name']);
                array_push($headers[1], $level['name'].' - Required', $level['name'].' - Completed');
                $col += 2;
                foreach ($level['learning'] as $learning) {
                    array_push($headers[0], $setup['name'], $setup['name']);
                    array_push($headers[1], $level['name'].' - '.$learning['name'].' - Required', $level['name'].' - '.$learning['name'].' - Completed');
                    $col += 2;
                }
            }
            if ($col > $from) $merges['row1'][] = ['span' => $col - $from, 'start_col_abs' => $from];
        }
        $from = $col;
        if ($unassigned) {
            $headers[0][] = 'Module List';
            $headers[1][] = '';
            $col++;
        }
        foreach ($moduleNames as $name) {
            $headers[0][] = 'Module List';
            $headers[1][] = $name;
            $col++;
        }
        if ($col > $from) $merges['row1'][] = ['span' => $col - $from, 'start_col_abs' => $from];
        array_push($headers[0], 'Total Hours Required', 'Total Hours Completed');
        array_push($headers[1], '', '');

        $h = fn ($v) => $v > 0 ? $this->round($v).' Hours' : '';
        $sheet = [];
        foreach ($students as $sid => $s) {
            $row = [$s['reg'], $s['name'], $s['status'] !== '' ? $s['status'] : 'N/A', $s['intake'], $s['course']];
            foreach ($used as $id => $setup) {
                $mine = in_array($id, $s['setups'], true);
                foreach ($setup['levels'] as $levelId => $level) {
                    $row[] = $mine ? $h($level['hours']) : '';
                    $row[] = $mine ? $h($s['done']['level'][$id][$levelId] ?? 0) : '';
                    foreach ($level['learning'] as $learningId => $learning) {
                        $row[] = $mine ? $h($learning['hours']) : '';
                        $row[] = $mine ? $h($s['done']['learning'][$id][$levelId][$learningId] ?? 0) : '';
                    }
                }
            }
            if ($unassigned) $row[] = $h($s['modules'][''] ?? 0);
            foreach ($moduleNames as $name) $row[] = isset($assigned[$sid][$name]) ? $h($assigned[$sid][$name]) : '';
            $required = array_sum(array_map(fn ($id) => $setups[$id]['hours'], $s['setups']));
            $row[] = $h($required);
            $row[] = $s['total'] > 0 ? $this->round($s['total']).' Hours' : '0 Hours';
            $sheet[] = $row;
        }

        return [$sheet, $headers, $moduleNames, $merges];
    }

    private function round(float $v): int|float
    {
        $v = round($v, 2);

        return floor($v) == $v ? (int) $v : $v;
    }

    private function rounded(array $levels): array
    {
        return array_map(fn ($l) => [$this->round($l[0]), array_map(fn ($v) => $this->round($v), $l[1])], $levels);
    }
}
