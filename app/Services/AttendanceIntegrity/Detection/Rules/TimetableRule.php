<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;

/**
 * Rule G: attendance that does not fit the timetable.
 * - Present at a cancelled class date.
 * - Live attendance on a class date that was deleted.
 * - Present at two classes at overlapping times on the same day, both on campus
 *   (classes with a virtual room are online and left out).
 * - Present at a class the student is not assigned to (neither the class of the
 *   date nor the record's own class, which keeps students moved to another group).
 * Student visits are left out.
 */
class TimetableRule extends Rule
{
    public function key(): string
    {
        return 'timetable';
    }

    public function label(): string
    {
        return 'Timetable conflicts';
    }

    protected function setting(): string
    {
        return 'att_timetable_enabled';
    }

    public function detect(TermScope $t): array
    {
        return array_merge($this->cancelledOrDeleted($t), $this->overlaps($t), $this->notAssigned($t));
    }

    private function cancelledOrDeleted(TermScope $t): array
    {
        $present = TermScope::in($t->present);
        $rows = DB::select("SELECT a.plans_date_list_id AS session_id, d.plan_id, d.status, d.deleted_at AS session_deleted_at, d.canceled_at,
                COUNT(*) AS rows_n, SUM(a.attendance_feed_status_id IN ($present)) AS present_n,
                SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(a.updated_by, a.created_by) ORDER BY COALESCE(a.updated_at, a.created_at) DESC), ',', 1) AS last_editor,
                MAX(COALESCE(a.updated_at, a.created_at)) AS last_at
            FROM attendances a JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND COALESCE(d.name, '') <> 'Visit'
              AND (d.status = 'Canceled' OR d.deleted_at IS NOT NULL)
            GROUP BY a.plans_date_list_id, d.plan_id, d.status, d.deleted_at, d.canceled_at", [$t->termId]);

        $t->sessions(array_map(fn ($r) => $r->session_id, $rows));
        $out = [];
        foreach ($rows as $r) {
            $editor = $r->last_editor ? (int) $r->last_editor : null;
            if ($r->session_deleted_at) {
                $out[] = $this->finding($t, [
                    'type' => 'deleted_class_date',
                    'basis' => 'fact',
                    'weight' => $this->points('att_points_deleted_session'),
                    'event_key' => 'ses:'.$r->session_id.':deleted-date',
                    'staff_user_id' => $editor,
                    'plan_id' => (int) $r->plan_id,
                    'plans_date_list_id' => (int) $r->session_id,
                    'event_at' => $r->last_at,
                    'rows_count' => (int) $r->rows_n,
                    'summary' => $r->rows_n.' attendance records remain on a deleted class date: '.$t->sessionLabel((int) $r->session_id),
                    'evidence' => ['records' => (int) $r->rows_n, 'present' => (int) $r->present_n, 'class_date_deleted' => self::ukDateTime($r->session_deleted_at), 'last_changed_by' => $t->staffName($editor)],
                ]);
            } elseif ((int) $r->present_n > 0) {
                $out[] = $this->finding($t, [
                    'type' => 'present_at_cancelled_class',
                    'basis' => 'fact',
                    'weight' => $this->points('att_points_cancelled'),
                    'event_key' => 'ses:'.$r->session_id.':cancelled',
                    'staff_user_id' => $editor,
                    'plan_id' => (int) $r->plan_id,
                    'plans_date_list_id' => (int) $r->session_id,
                    'event_at' => $r->last_at,
                    'rows_count' => (int) $r->present_n,
                    'summary' => $r->present_n.' students marked present at a cancelled class: '.$t->sessionLabel((int) $r->session_id),
                    'evidence' => ['present' => (int) $r->present_n, 'records' => (int) $r->rows_n, 'cancelled_at' => self::ukDateTime($r->canceled_at), 'last_changed_by' => $t->staffName($editor)],
                ]);
            }
        }

        return $out;
    }

    private function overlaps(TermScope $t): array
    {
        $present = TermScope::in($t->present);
        // Present on campus on the class dates of this term, plus the same students' other classes those days.
        $rows = DB::select("SELECT a.id, a.student_id, a.plans_date_list_id AS session_id, d.date, p.start_time, p.end_time,
                COALESCE(a.updated_by, a.created_by) AS editor
            FROM attendances a JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND d.deleted_at IS NULL
              AND a.attendance_feed_status_id IN ($present) AND COALESCE(d.status, '') <> 'Canceled' AND COALESCE(d.name, '') <> 'Visit'
              AND TRIM(COALESCE(p.virtual_room, '')) = '' AND p.start_time IS NOT NULL AND p.end_time IS NOT NULL
            ORDER BY a.student_id, d.date, p.start_time", [$t->termId]);

        $byDay = [];
        foreach ($rows as $r) {
            $byDay[$r->student_id.'|'.$r->date][] = $r;
        }

        $out = [];
        foreach ($byDay as $list) {
            $n = count($list);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    [$x, $y] = [$list[$i], $list[$j]];
                    if ($x->session_id === $y->session_id || ! ($x->start_time < $y->end_time && $y->start_time < $x->end_time)) {
                        continue;
                    }
                    $t->sessions([(int) $x->session_id, (int) $y->session_id]);
                    $out[] = $this->finding($t, [
                        'type' => 'overlapping_classes',
                        'basis' => 'fact',
                        'weight' => $this->points('att_points_overlap'),
                        'event_key' => 'att:'.$y->id,
                        'fp' => 'overlap:'.$x->id,
                        'staff_user_id' => $y->editor ? (int) $y->editor : null,
                        'student_id' => (int) $y->student_id,
                        'plans_date_list_id' => (int) $y->session_id,
                        'attendance_id' => (int) $y->id,
                        'event_at' => $y->date.' '.$y->start_time,
                        'rows_count' => 2,
                        'summary' => 'Present at two campus classes at the same time on '.self::ukDate($y->date).': '
                            .$t->sessionLabel((int) $x->session_id).' and '.$t->sessionLabel((int) $y->session_id),
                        'evidence' => [
                            'first_class' => $t->sessionLabel((int) $x->session_id),
                            'second_class' => $t->sessionLabel((int) $y->session_id),
                            'first_marked_by' => $t->staffName($x->editor ? (int) $x->editor : null),
                            'second_marked_by' => $t->staffName($y->editor ? (int) $y->editor : null),
                            'other_attendance_id' => (int) $x->id,
                        ],
                    ]);
                }
            }
        }

        return $out;
    }

    private function notAssigned(TermScope $t): array
    {
        $present = TermScope::in($t->present);
        $rows = DB::select("SELECT a.student_id, d.plan_id, COUNT(*) AS rows_n, MIN(d.date) AS first_date, MAX(d.date) AS last_date,
                SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(a.updated_by, a.created_by) ORDER BY d.date DESC), ',', 1) AS editor,
                MIN(a.id) AS first_id
            FROM attendances a JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND a.attendance_feed_status_id IN ($present)
              AND COALESCE(d.name, '') <> 'Visit'
              AND NOT EXISTS (SELECT 1 FROM assigns s WHERE s.student_id = a.student_id AND s.plan_id IN (d.plan_id, COALESCE(a.plan_id, 0)))
            GROUP BY a.student_id, d.plan_id", [$t->termId]);

        $out = [];
        foreach ($rows as $r) {
            $label = $this->planLabel((int) $r->plan_id);
            $out[] = $this->finding($t, [
                'type' => 'not_assigned',
                'basis' => 'fact',
                'weight' => $this->points('att_points_not_assigned'),
                'event_key' => 'stu:'.$r->student_id.':plan:'.$r->plan_id.':not-assigned',
                'staff_user_id' => $r->editor ? (int) $r->editor : null,
                'student_id' => (int) $r->student_id,
                'plan_id' => (int) $r->plan_id,
                'event_at' => $r->last_date.' 00:00:00',
                'rows_count' => (int) $r->rows_n,
                'summary' => 'Marked present '.$r->rows_n.' '.((int) $r->rows_n === 1 ? 'time' : 'times').' at a class the student is not assigned to: '.$label,
                'evidence' => ['records' => (int) $r->rows_n, 'from' => self::ukDate($r->first_date), 'to' => self::ukDate($r->last_date), 'last_marked_by' => $t->staffName($r->editor ? (int) $r->editor : null)],
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
