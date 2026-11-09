<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rule B: status changes.
 *
 * 1. From the attendance history (attendance_audit_logs), from the day it was
 *    switched on: every status change with its before and after, classified
 *    (Absent > Present, Excused > Present, Late > Present, Present > Absent),
 *    late corrections, and records changed repeatedly.
 * 2. Before the history existed: a record that is Present now but still carries
 *    the absence notice the class register writes only when it saves a student
 *    Absent, and clears on any other register save. The single-change screen
 *    leaves it alone, so the record was Absent, then changed to Present by its
 *    last editor. Records the history already covers are left out.
 *
 * Other edits before the history are counted elsewhere as "changed, previous
 * value unknown"; nothing is guessed from updated_at.
 *
 * Changes made by the excuse review (approve / reject) are an approved process
 * and are not scored.
 */
class AmendmentRule extends Rule
{
    public function key(): string
    {
        return 'amendment';
    }

    public function label(): string
    {
        return 'Changes to attendance';
    }

    protected function setting(): string
    {
        return 'att_amend_enabled';
    }

    public function detect(TermScope $t): array
    {
        $out = [];
        if (Schema::hasTable('attendance_audit_logs')) {
            $out = $this->fromHistory($t);
        }

        return array_merge($out, $this->fromAbsenceNotice($t));
    }

    private function fromHistory(TermScope $t): array
    {
        $logs = DB::select('SELECT l.id, l.attendance_id, l.student_id, l.plans_date_list_id, d.plan_id, l.changed_by, l.changed_at,
                l.old_status_id, l.new_status_id, l.is_late_correction, l.minutes_after_class, l.change_reason, l.source,
                CASE WHEN l.changed_by IS NULL THEN 0 WHEN '.TermScope::linked('l.changed_by').' THEN 1 ELSE 0 END AS linked
            FROM attendance_audit_logs l
            JOIN plans_date_lists d ON d.id = l.plans_date_list_id
            JOIN plans p ON p.id = d.plan_id
            LEFT JOIN attendances a ON a.id = l.attendance_id
            LEFT JOIN plans pa ON pa.id = a.plan_id
            LEFT JOIN plans pp ON pp.id = p.parent_id AND p.parent_id > 0
            WHERE p.term_declaration_id = ? AND l.action = "updated"
              AND l.old_status_id IS NOT NULL AND l.new_status_id IS NOT NULL AND l.old_status_id <> l.new_status_id
            ORDER BY l.id', [$t->termId]);

        $t->sessions(array_map(fn ($l) => $l->plans_date_list_id, $logs));
        $t->staffNames(array_map(fn ($l) => $l->changed_by, $logs));
        $statusNames = $this->statusNames();

        $out = [];
        $perRecord = [];
        foreach ($logs as $l) {
            $old = (int) $l->old_status_id;
            $new = (int) $l->new_status_id;
            $perRecord[(int) $l->attendance_id][] = $l;
            if ($l->source === 'excuse_review') {
                continue;
            }

            [$type, $weight] = $this->classify($t, $old, $new, (bool) $l->linked);
            $change = ($statusNames[$old] ?? $old).' > '.($statusNames[$new] ?? $new);
            $who = $t->staffName($l->changed_by ? (int) $l->changed_by : null);
            $common = [
                'basis' => 'fact',
                'event_key' => 'att:'.$l->attendance_id,
                'staff_user_id' => $l->changed_by ? (int) $l->changed_by : null,
                'student_id' => $l->student_id ? (int) $l->student_id : null,
                'plan_id' => (int) $l->plan_id,
                'plans_date_list_id' => (int) $l->plans_date_list_id,
                'attendance_id' => (int) $l->attendance_id,
                'event_at' => $l->changed_at,
                'evidence' => [
                    'change' => $change,
                    'changed_by' => $who,
                    'changed_at' => self::ukDateTime($l->changed_at),
                    'changed_by_teaches_class' => (bool) $l->linked,
                    'after_class' => $l->minutes_after_class !== null ? TermScope::duration((int) $l->minutes_after_class) : null,
                    'reason' => $l->change_reason,
                    'screen' => $l->source,
                    'history_id' => (int) $l->id,
                ],
            ];

            if ($type) {
                $out[] = $this->finding($t, $common + [
                    'type' => $type,
                    'weight' => $weight,
                    'fp' => 'log:'.$l->id,
                    'summary' => $change.' by '.$who.': '.$t->sessionLabel((int) $l->plans_date_list_id),
                ]);
            }
            if ($l->is_late_correction) {
                $out[] = $this->finding($t, $common + [
                    'type' => 'late_correction',
                    'weight' => $this->points('att_points_late_correction'),
                    'fp' => 'late:'.$l->id,
                    'summary' => 'Late correction ('.$change.', '.TermScope::duration((int) $l->minutes_after_class).' after the class) by '.$who
                        .($l->change_reason ? ' — reason: "'.mb_substr($l->change_reason, 0, 120).'"' : ''),
                ]);
            }
        }

        $min = AttendanceSettings::int('att_repeated_min');
        foreach ($perRecord as $attendanceId => $changes) {
            if (count($changes) < $min) {
                continue;
            }
            $last = end($changes);
            $out[] = $this->finding($t, [
                'type' => 'repeated_change',
                'basis' => 'fact',
                'weight' => $this->points('att_points_repeated'),
                'event_key' => 'att:'.$attendanceId,
                'fp' => 'repeat',
                'staff_user_id' => $last->changed_by ? (int) $last->changed_by : null,
                'student_id' => $last->student_id ? (int) $last->student_id : null,
                'plan_id' => (int) $last->plan_id,
                'plans_date_list_id' => (int) $last->plans_date_list_id,
                'attendance_id' => (int) $attendanceId,
                'event_at' => $last->changed_at,
                'rows_count' => count($changes),
                'summary' => 'Status changed '.count($changes).' times: '.$t->sessionLabel((int) $last->plans_date_list_id),
                'evidence' => ['changes' => array_map(fn ($c) => [
                    'change' => ($statusNames[(int) $c->old_status_id] ?? $c->old_status_id).' > '.($statusNames[(int) $c->new_status_id] ?? $c->new_status_id),
                    'by' => $t->staffName($c->changed_by ? (int) $c->changed_by : null),
                    'at' => self::ukDateTime($c->changed_at),
                ], $changes)],
            ]);
        }

        return $out;
    }

    private function fromAbsenceNotice(TermScope $t): array
    {
        $historyCheck = Schema::hasTable('attendance_audit_logs')
            ? 'AND NOT EXISTS (SELECT 1 FROM attendance_audit_logs l WHERE l.attendance_id = a.id AND l.action = "updated" AND l.old_status_id <> l.new_status_id)'
            : '';

        $rows = DB::select('SELECT a.id, a.student_id, a.plans_date_list_id, d.plan_id, a.updated_by, a.updated_at, a.created_by, a.created_at,
                a.notofication_date, a.attendance_feed_status_id AS status_id,
                CASE WHEN '.TermScope::linked('a.updated_by').' THEN 1 ELSE 0 END AS linked
            '.TermScope::FROM.'
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND a.updated_by IS NOT NULL
              AND a.attendance_feed_status_id IN ('.TermScope::in($t->present).')
              AND a.notofication_date IS NOT NULL AND (a.sms_notification = 1 OR a.email_notification = 1)
              '.$historyCheck, [$t->termId]);

        $t->sessions(array_map(fn ($r) => $r->plans_date_list_id, $rows));
        $t->staffNames(array_merge(array_map(fn ($r) => $r->updated_by, $rows), array_map(fn ($r) => $r->created_by, $rows)));
        $statusNames = $this->statusNames();

        $out = [];
        foreach ($rows as $r) {
            $who = $t->staffName((int) $r->updated_by);
            $out[] = $this->finding($t, [
                'type' => $r->linked ? 'absent_to_present_own_class' : 'absent_to_present_notice',
                'basis' => 'fact',
                'weight' => $r->linked ? $this->points('att_points_abs_present_own') : $this->points('att_points_abs_present_notice'),
                'event_key' => 'att:'.$r->id,
                'fp' => 'notice',
                'staff_user_id' => (int) $r->updated_by,
                'student_id' => (int) $r->student_id,
                'plan_id' => (int) $r->plan_id,
                'plans_date_list_id' => (int) $r->plans_date_list_id,
                'attendance_id' => (int) $r->id,
                'event_at' => $r->updated_at,
                'summary' => 'Absent > '.($statusNames[(int) $r->status_id] ?? 'Present').' by '.$who.' (absence notice recorded '.self::ukDate($r->notofication_date).'): '
                    .$t->sessionLabel((int) $r->plans_date_list_id),
                'evidence' => [
                    'how_known' => 'The class register saved this student Absent on '.self::ukDate($r->notofication_date).' (absence notice recorded); the record is '
                        .($statusNames[(int) $r->status_id] ?? 'Present').' now, last changed by '.$who.'. The earlier history was not recorded.',
                    'first_entered_by' => $t->staffName((int) $r->created_by),
                    'first_entered_at' => self::ukDateTime($r->created_at),
                    'changed_by' => $who,
                    'changed_at' => self::ukDateTime($r->updated_at),
                    'changed_by_teaches_class' => (bool) $r->linked,
                ],
            ]);
        }

        return $out;
    }

    /** [type, points] for one status change (type null = not listed). */
    private function classify(TermScope $t, int $old, int $new, bool $linked): array
    {
        $wasAttended = in_array($old, $t->attended, true);
        $nowAttended = in_array($new, $t->attended, true);
        $nowPresent = in_array($new, $t->present, true);

        if (! $wasAttended && $nowPresent) {
            return [$linked ? 'absent_to_present_own_class' : 'absent_to_present', $linked ? $this->points('att_points_abs_present_own') : $this->points('att_points_abs_present_audit')];
        }
        if (! $wasAttended && $nowAttended) {
            return ['absent_to_excused', 0];
        }
        if (in_array($old, $t->excused, true) && $nowPresent) {
            return ['excused_to_present', 0];
        }
        if ($old === 5 && $new === 1) {
            return ['late_to_present', 0];
        }
        if ($wasAttended && ! $nowAttended) {
            return ['present_to_absent', $this->points('att_points_present_absent')];
        }

        return ['other_change', 0];
    }

    private function statusNames(): array
    {
        static $names = null;

        return $names ??= DB::table('attendance_feed_statuses')->pluck('name', 'id')->map(fn ($v) => (string) $v)->all();
    }
}
