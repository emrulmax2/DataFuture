<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rule C: many separate entries by one person within seconds, across different
 * class dates. Saving a class register writes all its rows in the same second;
 * that is the normal way attendance is taken, so a person's rows for one class
 * date in the same second count as ONE entry. A burst needs N entries within the
 * window (Attendance Settings) from at least M different class dates.
 *
 * Events: first entries (created_at), last changes (updated_at, the only trace
 * before the attendance history) and every change in the history; the same
 * record at the same second is counted once. Times come back from MySQL (session
 * at +00:00) as UK clock digits read as UTC, so they are formatted with gmdate().
 */
class BulkEntryRule extends Rule
{
    public function key(): string
    {
        return 'bulk_entry';
    }

    public function label(): string
    {
        return 'Bulk entry';
    }

    protected function setting(): string
    {
        return 'att_bulk_enabled';
    }

    public function detect(TermScope $t): array
    {
        $windows = AttendanceSettings::get('att_bulk_windows') ?: [];
        if (! $windows) {
            return [];
        }
        $minSessions = AttendanceSettings::int('att_bulk_min_sessions');
        $from = AttendanceSettings::get('att_retro_from_date').' 00:00:00';

        $sql = 'SELECT a.created_by AS u, UNIX_TIMESTAMP(a.created_at) AS ts, a.plans_date_list_id AS s, a.id, a.student_id
                '.TermScope::FROM.' WHERE p.term_declaration_id = ? AND a.created_by IS NOT NULL AND a.created_at >= ?
            UNION ALL
            SELECT a.updated_by, UNIX_TIMESTAMP(a.updated_at), a.plans_date_list_id, a.id, a.student_id
                '.TermScope::FROM.' WHERE p.term_declaration_id = ? AND a.updated_by IS NOT NULL AND a.updated_at >= ?';
        $bindings = [$t->termId, $from, $t->termId, $from];
        if (Schema::hasTable('attendance_audit_logs')) {
            $sql .= '
            UNION ALL
            SELECT l.changed_by, UNIX_TIMESTAMP(l.changed_at), l.plans_date_list_id, l.attendance_id, l.student_id
                FROM attendance_audit_logs l JOIN plans_date_lists d ON d.id = l.plans_date_list_id JOIN plans p ON p.id = d.plan_id
                WHERE p.term_declaration_id = ? AND l.changed_by IS NOT NULL';
            $bindings[] = $t->termId;
        }

        // One entry = one person, one class date, one second (a register save is one entry).
        $byUser = [];
        $seen = [];
        foreach (DB::cursor($sql, $bindings) as $e) {
            $u = (int) $e->u;
            if (isset($seen[$u][$e->id.':'.$e->ts])) {
                continue;
            }
            $seen[$u][$e->id.':'.$e->ts] = true;
            $key = $e->s.':'.$e->ts;
            if (! isset($byUser[$u][$key])) {
                $byUser[$u][$key] = [(int) $e->ts, (int) $e->s, [], 0];
            }
            $byUser[$u][$key][2][(int) $e->student_id] = true;
            $byUser[$u][$key][3]++;
        }
        unset($seen);

        $out = [];
        $maxWindow = max(array_map(fn ($w) => (int) $w[1], $windows));
        foreach ($byUser as $userId => $events) {
            $events = array_values($events);
            usort($events, fn ($x, $y) => $x[0] <=> $y[0]);
            $n = count($events);
            $flag = array_fill(0, $n, false);

            foreach ($windows as [$needRows, $seconds]) {
                $sessionCount = [];
                $i = 0;
                for ($j = 0; $j < $n; $j++) {
                    $sessionCount[$events[$j][1]] = ($sessionCount[$events[$j][1]] ?? 0) + 1;
                    while ($events[$j][0] - $events[$i][0] > $seconds) {
                        if (--$sessionCount[$events[$i][1]] === 0) {
                            unset($sessionCount[$events[$i][1]]);
                        }
                        $i++;
                    }
                    if ($j - $i + 1 >= $needRows && count($sessionCount) >= $minSessions) {
                        for ($k = $i; $k <= $j; $k++) {
                            $flag[$k] = true;
                        }
                    }
                }
            }

            // Flagged events close together form one burst.
            $burst = [];
            $flush = function () use (&$burst, &$out, $t, $userId) {
                if (! $burst) {
                    return;
                }
                $sessions = [];
                $students = [];
                $records = 0;
                foreach ($burst as $e) {
                    $sessions[$e[1]] = ($sessions[$e[1]] ?? 0) + $e[3];
                    $students += $e[2];
                    $records += $e[3];
                }
                $students = count($students);
                $start = $burst[0][0];
                $end = end($burst)[0];
                arsort($sessions);
                $t->sessions(array_keys($sessions));
                $who = $t->staffName($userId);
                $out[] = $this->finding($t, [
                    'type' => 'bulk_burst',
                    'weight' => $this->points('att_points_bulk'),
                    'event_key' => 'burst:u:'.$userId.':'.$start,
                    'staff_user_id' => $userId,
                    'event_at' => gmdate('Y-m-d H:i:s', $start),
                    'rows_count' => $records,
                    'summary' => count($burst).' separate entries ('.$records.' records) across '.count($sessions).' class dates in '.max(1, $end - $start).' seconds by '.$who
                        .' ('.gmdate('d/m/Y H:i:s', $start).')',
                    'evidence' => [
                        'from' => gmdate('d/m/Y H:i:s', $start),
                        'to' => gmdate('d/m/Y H:i:s', $end),
                        'seconds' => $end - $start,
                        'entries' => count($burst),
                        'records' => $records,
                        'students' => $students,
                        'class_dates' => array_slice(array_map(fn ($s, $c) => ['class' => $t->sessionLabel($s), 'records' => $c, 'session_id' => $s], array_keys($sessions), $sessions), 0, 15),
                    ],
                ]);
                $burst = [];
            };
            for ($k = 0; $k < $n; $k++) {
                if (! $flag[$k]) {
                    continue;
                }
                if ($burst && $events[$k][0] - end($burst)[0] > $maxWindow) {
                    $flush();
                }
                $burst[] = $events[$k];
            }
            $flush();
        }

        return $out;
    }
}
