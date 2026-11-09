<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rule H: recorded attendance against other evidence the system holds. There is
 * no card swipe, QR check-in or learning-platform record; a missing record is
 * never treated as proof of absence.
 *
 * - Changed to Present after the student's own excuse for that class (saying they
 *   were absent) was rejected, and no excuse for it was approved (strong). When
 *   the register already showed Present before the decision, it is listed as a
 *   conflict between the register and the student's claim (weaker).
 * - Present although an excuse for that class was approved (it should then be
 *   Excuse / Medical / Exceptional).
 * - Present on a day the student reported being absent in a mitigating
 *   circumstances claim.
 * - Present at a class date with no class start record (the tutor never
 *   started the class in the system), from the "check entries from" date.
 */
class EvidenceRule extends Rule
{
    public function key(): string
    {
        return 'evidence';
    }

    public function label(): string
    {
        return 'Other evidence';
    }

    protected function setting(): string
    {
        return 'att_evidence_enabled';
    }

    public function detect(TermScope $t): array
    {
        return array_merge($this->excuses($t), $this->mitigating($t), $this->noStartRecord($t));
    }

    private function excuses(TermScope $t): array
    {
        $present = TermScope::in($t->present);
        $rows = DB::select("SELECT a.id, a.student_id, a.plans_date_list_id, d.plan_id, COALESCE(a.updated_by, a.created_by) AS editor,
                COALESCE(a.updated_at, a.created_at) AS changed_at, a.updated_by, a.updated_at, x.best_status, x.excuse_id
            FROM attendances a
            JOIN plans_date_lists d ON d.id = a.plans_date_list_id
            JOIN plans p ON p.id = d.plan_id
            JOIN (SELECT e.student_id, ed.plans_date_list_id, MAX(e.status) AS best_status, MAX(e.id) AS excuse_id
                  FROM attendance_excuses e JOIN attendance_excuse_days ed ON ed.attendance_excuse_id = e.id
                  WHERE e.deleted_at IS NULL AND ed.deleted_at IS NULL AND e.status IN (1, 2)
                  GROUP BY e.student_id, ed.plans_date_list_id) x ON x.student_id = a.student_id AND x.plans_date_list_id = a.plans_date_list_id
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND a.attendance_feed_status_id IN ($present)", [$t->termId]);

        if (! $rows) {
            return [];
        }
        $t->sessions(array_map(fn ($r) => $r->plans_date_list_id, $rows));
        $excuses = DB::table('attendance_excuses')->whereIn('id', array_map(fn ($r) => (int) $r->excuse_id, $rows))
            ->get(['id', 'status', 'attendance_types', 'reason', 'remarks', 'actioned_by', 'actioned_at'])->keyBy('id');
        $t->staffNames(array_merge(array_map(fn ($r) => $r->editor, $rows), $excuses->pluck('actioned_by')->all()));

        $out = [];
        foreach ($rows as $r) {
            $e = $excuses[(int) $r->excuse_id] ?? null;
            $rejected = (int) $r->best_status === 1;
            // Changed (not just first entered) after the excuse was decided.
            $changedAfter = $rejected && $r->updated_by && $r->updated_at && $e && $e->actioned_at && substr((string) $r->updated_at, 0, 19) >= substr((string) $e->actioned_at, 0, 19);
            $who = $t->staffName($r->editor ? (int) $r->editor : null);
            [$type, $basis, $weight] = $rejected
                ? ($changedAfter
                    ? ['present_after_rejected_excuse', 'fact', $this->points('att_points_rejected_excuse')]
                    : ['register_conflicts_with_excuse', 'anomaly', $this->points('att_points_excuse_conflict')])
                : ['present_with_approved_excuse', 'anomaly', $this->points('att_points_approved_excuse')];
            $out[] = $this->finding($t, [
                'type' => $type,
                'basis' => $basis,
                'weight' => $weight,
                'event_key' => 'att:'.$r->id,
                'fp' => 'excuse',
                'staff_user_id' => $r->editor ? (int) $r->editor : null,
                'student_id' => (int) $r->student_id,
                'plan_id' => (int) $r->plan_id,
                'plans_date_list_id' => (int) $r->plans_date_list_id,
                'attendance_id' => (int) $r->id,
                'event_at' => $r->changed_at,
                'summary' => ($type === 'present_after_rejected_excuse'
                        ? 'Changed to Present after the student\'s own excuse for this class (saying they were absent) was rejected'
                        : ($type === 'register_conflicts_with_excuse'
                            ? 'Register shows Present but the student\'s rejected excuse for this class says they were absent'
                            : 'Marked present although an excuse for this class was approved'))
                    .', last changed by '.$who.': '.$t->sessionLabel((int) $r->plans_date_list_id),
                'evidence' => [
                    'excuse_id' => (int) $r->excuse_id,
                    'excuse' => $rejected ? 'Rejected' : 'Approved',
                    'excuse_type' => $e->attendance_types ?? null,
                    'student_reason' => $e ? mb_substr((string) $e->reason, 0, 500) : null,
                    'reply' => $e ? mb_substr((string) $e->remarks, 0, 500) : null,
                    'decided_by' => $e ? $t->staffName($e->actioned_by ? (int) $e->actioned_by : null) : null,
                    'decided_at' => $e ? self::ukDateTime($e->actioned_at) : null,
                    'marked_present_by' => $who,
                    'marked_present_at' => self::ukDateTime($r->changed_at),
                ],
            ]);
        }

        return $out;
    }

    private function mitigating(TermScope $t): array
    {
        if (! Schema::hasTable('student_mitigating_circumstance_absences') || ! Schema::hasTable('student_mitigating_circumstances')) {
            return [];
        }
        $present = TermScope::in($t->present);
        $rows = DB::select("SELECT a.id, a.student_id, a.plans_date_list_id, d.plan_id, d.date, COALESCE(a.updated_by, a.created_by) AS editor,
                COALESCE(a.updated_at, a.created_at) AS changed_at, m.id AS claim_id, m.status AS claim_status, ab.absent_from, ab.absent_to
            FROM attendances a
            JOIN plans_date_lists d ON d.id = a.plans_date_list_id
            JOIN plans p ON p.id = d.plan_id
            JOIN student_mitigating_circumstances m ON m.student_id = a.student_id AND m.deleted_at IS NULL
            JOIN student_mitigating_circumstance_absences ab ON ab.request_id = m.id
                AND d.date BETWEEN ab.absent_from AND COALESCE(ab.absent_to, ab.absent_from)
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND a.attendance_feed_status_id IN ($present)", [$t->termId]);

        $out = [];
        foreach ($rows as $r) {
            $who = $t->staffName($r->editor ? (int) $r->editor : null);
            $out[] = $this->finding($t, [
                'type' => 'present_during_reported_absence',
                'basis' => 'fact',
                'weight' => $this->points('att_points_mitigating'),
                'event_key' => 'att:'.$r->id,
                'fp' => 'mitigating:'.$r->claim_id,
                'staff_user_id' => $r->editor ? (int) $r->editor : null,
                'student_id' => (int) $r->student_id,
                'plan_id' => (int) $r->plan_id,
                'plans_date_list_id' => (int) $r->plans_date_list_id,
                'attendance_id' => (int) $r->id,
                'event_at' => $r->changed_at,
                'summary' => 'Marked present on a day the student reported being absent (mitigating circumstances claim #'.$r->claim_id.'), last changed by '.$who.': '.$t->sessionLabel((int) $r->plans_date_list_id),
                'evidence' => ['claim_id' => (int) $r->claim_id, 'claim_status' => $r->claim_status, 'absent_from' => self::ukDate($r->absent_from), 'absent_to' => self::ukDate($r->absent_to ?: $r->absent_from), 'marked_present_by' => $who],
            ]);
        }

        return $out;
    }

    private function noStartRecord(TermScope $t): array
    {
        $present = TermScope::in($t->present);
        $rows = DB::select("SELECT a.plans_date_list_id AS session_id, d.plan_id, d.date, COUNT(*) AS present_n,
                SUBSTRING_INDEX(GROUP_CONCAT(a.created_by ORDER BY a.id), ',', 1) AS entered_by, MIN(a.created_at) AS first_at
            FROM attendances a JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND d.deleted_at IS NULL AND d.date >= ?
              AND a.attendance_feed_status_id IN ($present) AND COALESCE(d.status, '') <> 'Canceled' AND COALESCE(d.name, '') <> 'Visit'
              AND NOT EXISTS (SELECT 1 FROM attendance_informations ai WHERE ai.plans_date_list_id = d.id AND ai.deleted_at IS NULL)
            GROUP BY a.plans_date_list_id, d.plan_id, d.date", [$t->termId, AttendanceSettings::get('att_retro_from_date')]);

        $t->sessions(array_map(fn ($r) => $r->session_id, $rows));
        $out = [];
        foreach ($rows as $r) {
            $by = $r->entered_by ? (int) $r->entered_by : null;
            $out[] = $this->finding($t, [
                'type' => 'no_class_start_record',
                'basis' => 'anomaly',
                'weight' => $this->points('att_points_no_start'),
                'event_key' => 'ses:'.$r->session_id.':no-start',
                'staff_user_id' => $by,
                'plan_id' => (int) $r->plan_id,
                'plans_date_list_id' => (int) $r->session_id,
                'event_at' => $r->first_at,
                'rows_count' => (int) $r->present_n,
                'summary' => $r->present_n.' students marked present at a class with no class start record: '.$t->sessionLabel((int) $r->session_id),
                'evidence' => ['present' => (int) $r->present_n, 'first_entered_by' => $t->staffName($by), 'first_entered_at' => self::ukDateTime($r->first_at),
                    'note' => 'The tutor did not start this class in the system. It may still have been held.'],
            ]);
        }

        return $out;
    }
}
