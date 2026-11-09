<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;

/**
 * Rule D: unusually high attendance. High attendance alone is not evidence of
 * anything; these point to classes and students worth a look.
 *
 * - A class at or above the threshold that is also well above similar classes
 *   (same course and class type in the term; the class type across the term when
 *   the course has fewer than 3 such classes).
 * - A student whose attendance rose sharply on their previous term.
 *
 * Cancelled class dates and student visits are left out.
 */
class HighAttendanceRule extends Rule
{
    public function key(): string
    {
        return 'high_attendance';
    }

    public function label(): string
    {
        return 'Unusually high attendance';
    }

    protected function setting(): string
    {
        return 'att_high_enabled';
    }

    public function detect(TermScope $t): array
    {
        return array_merge($this->classes($t), $this->students($t));
    }

    private function classes(TermScope $t): array
    {
        $attended = TermScope::in($t->attended);
        $plans = DB::select("SELECT p.id, p.course_id, COALESCE(p.class_type, '') AS class_type,
                COALESCE(NULLIF(p.tutor_id, 0), p.personal_tutor_id) AS teacher_id,
                COUNT(*) AS rows_n, SUM(a.attendance_feed_status_id IN ($attended)) AS attended_n,
                COUNT(DISTINCT a.plans_date_list_id) AS sessions, COUNT(DISTINCT a.student_id) AS students
            FROM attendances a
            JOIN plans_date_lists d ON d.id = a.plans_date_list_id
            JOIN plans p ON p.id = d.plan_id
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND d.deleted_at IS NULL
              AND COALESCE(d.status, '') <> 'Canceled' AND COALESCE(d.name, '') <> 'Visit'
            GROUP BY p.id, p.course_id, p.class_type, teacher_id", [$t->termId]);

        $byCourse = [];
        $byType = [];
        foreach ($plans as $p) {
            $p->rate = $p->rows_n ? 100 * $p->attended_n / $p->rows_n : 0;
            $byCourse[$p->course_id.'|'.$p->class_type][] = $p;
            $byType[$p->class_type][] = $p;
        }

        $min = AttendanceSettings::int('att_high_class_rate');
        $gap = AttendanceSettings::int('att_high_peer_gap');
        $minSessions = AttendanceSettings::int('att_high_min_sessions');
        $minStudents = AttendanceSettings::int('att_high_min_students');

        $out = [];
        foreach ($plans as $p) {
            if ($p->rate < $min || $p->sessions < $minSessions || $p->students < $minStudents) {
                continue;
            }
            $peers = array_filter($byCourse[$p->course_id.'|'.$p->class_type], fn ($x) => $x->id !== $p->id);
            $peerGroup = 'same course and class type';
            if (count($peers) < 3) {
                $peers = array_filter($byType[$p->class_type], fn ($x) => $x->id !== $p->id);
                $peerGroup = 'same class type this term';
            }
            if (! $peers) {
                continue;
            }
            $peerRows = array_sum(array_map(fn ($x) => $x->rows_n, $peers));
            $peerRate = $peerRows ? 100 * array_sum(array_map(fn ($x) => $x->attended_n, $peers)) / $peerRows : 0;
            if ($p->rate - $peerRate < $gap) {
                continue;
            }

            $label = $this->planLabel((int) $p->id);
            $out[] = $this->finding($t, [
                'type' => 'class_high_rate',
                'weight' => $this->points('att_points_high_class'),
                'event_key' => 'plan:'.$p->id.':high',
                'staff_user_id' => $p->teacher_id ? (int) $p->teacher_id : null,
                'plan_id' => (int) $p->id,
                'rows_count' => (int) $p->rows_n,
                'summary' => 'Attendance '.round($p->rate, 1).'% against '.round($peerRate, 1).'% in similar classes: '.$label,
                'evidence' => [
                    'class_rate' => round($p->rate, 1),
                    'peer_rate' => round($peerRate, 1),
                    'peer_group' => $peerGroup,
                    'peer_classes' => count($peers),
                    'class_dates' => (int) $p->sessions,
                    'students' => (int) $p->students,
                    'teacher' => $t->staffName($p->teacher_id ? (int) $p->teacher_id : null),
                    'note' => 'High attendance alone is not evidence of wrongdoing.',
                ],
            ]);
        }

        return $out;
    }

    private function students(TermScope $t): array
    {
        if (! $t->start) {
            return [];
        }
        $attended = TermScope::in($t->attended);
        $now = DB::select("SELECT a.student_id, COUNT(*) AS rows_n, SUM(a.attendance_feed_status_id IN ($attended)) AS attended_n
            FROM attendances a JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND COALESCE(d.status, '') <> 'Canceled' AND COALESCE(d.name, '') <> 'Visit'
            GROUP BY a.student_id HAVING rows_n >= 5", [$t->termId]);
        if (! $now) {
            return [];
        }

        // Each student's most recent earlier term with at least 5 records.
        $earlier = [];
        foreach (array_chunk(array_map(fn ($r) => (int) $r->student_id, $now), 500) as $chunk) {
            foreach (DB::select("SELECT a.student_id, p.term_declaration_id AS term_id, td.name AS term_name, td.start_date,
                    COUNT(*) AS rows_n, SUM(a.attendance_feed_status_id IN ($attended)) AS attended_n
                FROM attendances a JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
                JOIN term_declarations td ON td.id = p.term_declaration_id
                WHERE a.student_id IN (".TermScope::in($chunk).") AND td.start_date < ? AND p.term_declaration_id <> ?
                  AND a.deleted_at IS NULL AND COALESCE(d.status, '') <> 'Canceled' AND COALESCE(d.name, '') <> 'Visit'
                GROUP BY a.student_id, p.term_declaration_id, td.name, td.start_date HAVING rows_n >= 5", [$t->start, $t->termId]) as $r) {
                $sid = (int) $r->student_id;
                if (! isset($earlier[$sid]) || $r->start_date > $earlier[$sid]->start_date) {
                    $earlier[$sid] = $r;
                }
            }
        }

        $jump = AttendanceSettings::int('att_student_jump');
        $out = [];
        foreach ($now as $r) {
            $prev = $earlier[(int) $r->student_id] ?? null;
            if (! $prev) {
                continue;
            }
            $rate = 100 * $r->attended_n / $r->rows_n;
            $prevRate = 100 * $prev->attended_n / $prev->rows_n;
            if ($rate - $prevRate < $jump) {
                continue;
            }
            $out[] = $this->finding($t, [
                'type' => 'student_sudden_rise',
                'weight' => $this->points('att_points_student_jump'),
                'event_key' => 'stu:'.$r->student_id.':t:'.$t->termId.':rise',
                'student_id' => (int) $r->student_id,
                'rows_count' => (int) $r->rows_n,
                'summary' => 'Attendance rose from '.round($prevRate, 1).'% ('.$prev->term_name.') to '.round($rate, 1).'% this term',
                'evidence' => [
                    'this_term_rate' => round($rate, 1),
                    'previous_term' => $prev->term_name,
                    'previous_rate' => round($prevRate, 1),
                    'records_this_term' => (int) $r->rows_n,
                    'note' => 'A rise alone is not evidence of wrongdoing; check who entered and changed the records.',
                ],
            ]);
        }

        return $out;
    }

    private function planLabel(int $planId): string
    {
        $p = DB::table('plans as p')->leftJoin('module_creations as mc', 'mc.id', '=', 'p.module_creation_id')
            ->leftJoin('groups as g', 'g.id', '=', 'p.group_id')->where('p.id', $planId)
            ->first(['mc.module_name', 'mc.code', 'g.name as group_name', 'p.class_type']);

        return $p ? trim(($p->module_name ?? '').($p->code ? ' ('.$p->code.')' : '').' · '.($p->group_name ?? '').' · '.($p->class_type ?? ''), ' ·') : 'class #'.$planId;
    }
}
