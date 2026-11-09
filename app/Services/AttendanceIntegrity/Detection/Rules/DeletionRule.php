<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rule J: deleted attendance records, one finding per class date. Who deleted a
 * record is known only from the attendance history (from the day it was switched
 * on) or, for student visits, the visit record; otherwise it says "not recorded"
 * and no one is named. Records removed with a deleted class date are left out.
 */
class DeletionRule extends Rule
{
    public function key(): string
    {
        return 'deletion';
    }

    public function label(): string
    {
        return 'Deleted attendance';
    }

    protected function setting(): string
    {
        return 'att_deletion_enabled';
    }

    public function detect(TermScope $t): array
    {
        $history = Schema::hasTable('attendance_audit_logs')
            ? '(SELECT l.changed_by FROM attendance_audit_logs l WHERE l.attendance_id = a.id AND l.action IN ("deleted", "force_deleted") ORDER BY l.id DESC LIMIT 1)'
            : 'NULL';
        $rows = DB::select("SELECT a.id, a.plans_date_list_id AS session_id, d.plan_id, a.student_id, a.deleted_at, a.attendance_feed_status_id AS status_id,
                COALESCE($history, (SELECT v.attendance_deleted_by FROM student_visits v WHERE v.attendance_id = a.id LIMIT 1)) AS deleted_by
            FROM attendances a JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NOT NULL AND d.deleted_at IS NULL", [$t->termId]);

        $bySession = [];
        foreach ($rows as $r) {
            $bySession[(int) $r->session_id][] = $r;
        }
        $t->sessions(array_keys($bySession));
        $t->staffNames(array_map(fn ($r) => $r->deleted_by, $rows));

        $out = [];
        foreach ($bySession as $sessionId => $list) {
            $deleters = array_values(array_unique(array_filter(array_map(fn ($r) => $r->deleted_by ? (int) $r->deleted_by : null, $list))));
            $known = count(array_filter($list, fn ($r) => $r->deleted_by));
            $last = max(array_map(fn ($r) => (string) $r->deleted_at, $list));
            $out[] = $this->finding($t, [
                'type' => 'deleted_records',
                'basis' => 'fact',
                'weight' => $this->points('att_points_deletion'),
                'event_key' => 'ses:'.$sessionId.':deleted',
                'staff_user_id' => count($deleters) === 1 ? $deleters[0] : null,
                'plan_id' => (int) $list[0]->plan_id,
                'plans_date_list_id' => $sessionId,
                'event_at' => $last,
                'rows_count' => count($list),
                'summary' => count($list).' attendance '.(count($list) === 1 ? 'record' : 'records').' deleted: '.$t->sessionLabel($sessionId)
                    .($deleters ? ' (by '.implode(', ', array_map(fn ($u) => $t->staffName($u), $deleters)).')' : ' (who deleted them was not recorded)'),
                'evidence' => [
                    'records' => count($list),
                    'deleted_by_known' => $known,
                    'deleted_by' => $deleters ? array_map(fn ($u) => $t->staffName($u), $deleters) : 'not recorded',
                    'last_deleted_at' => self::ukDateTime($last),
                    'attendance_ids' => array_slice(array_map(fn ($r) => (int) $r->id, $list), 0, 50),
                ],
            ]);
        }

        return $out;
    }
}
