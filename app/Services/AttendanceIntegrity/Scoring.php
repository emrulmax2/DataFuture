<?php

namespace App\Services\AttendanceIntegrity;

use Illuminate\Support\Facades\DB;

/**
 * Risk scores. A score ranks what to look at first; it is not proof.
 *
 * Scoring policy (written down so overlapping rules never count one event twice):
 *   findings that describe the same event share an event key (one attendance
 *   record, one class date for one person, or one pattern per person / pair /
 *   class per term); a subject's score is the sum, over its events, of the
 *   highest points among that event's findings. Dismissed and cleared findings
 *   do not count. Bands: Attendance Settings, separately for staff and students
 *   (Low below Medium, High from High), scores per the filtered period.
 */
class Scoring
{
    /** Risk level for a staff ('staff') or student ('student') score (Attendance Settings). */
    public static function band(int $score, string $kind = 'staff'): string
    {
        $kind = $kind === 'student' ? 'student' : 'staff';
        if ($score >= AttendanceSettings::int("att_band_{$kind}_high")) {
            return 'high';
        }

        return $score >= AttendanceSettings::int("att_band_{$kind}_medium") ? 'medium' : 'low';
    }

    /**
     * Scores of every staff member ('staff') or student ('student') with findings
     * under the filters: id => ['score', 'events', 'findings', 'band', 'rules' => [rule => points]].
     */
    public static function subjects(string $kind, Filters $f): array
    {
        $col = $kind === 'student' ? 'f.student_id' : 'f.staff_user_id';
        [$join, $where, $b] = $f->findingWhere(false);

        $rows = DB::select("SELECT x.subject, x.rule, SUM(x.w) AS score, COUNT(*) AS events, SUM(x.n) AS findings FROM (
                SELECT $col AS subject, f.event_key, SUBSTRING_INDEX(GROUP_CONCAT(f.rule ORDER BY f.weight DESC), ',', 1) AS rule,
                    MAX(f.weight) AS w, COUNT(*) AS n
                FROM aim_findings f $join
                WHERE $col IS NOT NULL AND f.status <> 'dismissed' $where
                GROUP BY $col, f.event_key
            ) x GROUP BY x.subject, x.rule", $b);

        $out = [];
        foreach ($rows as $r) {
            $id = (int) $r->subject;
            $out[$id] ??= ['score' => 0, 'events' => 0, 'findings' => 0, 'rules' => []];
            $out[$id]['score'] += (int) $r->score;
            $out[$id]['events'] += (int) $r->events;
            $out[$id]['findings'] += (int) $r->findings;
            $out[$id]['rules'][$r->rule] = ($out[$id]['rules'][$r->rule] ?? 0) + (int) $r->score;
        }
        foreach ($out as $id => $s) {
            $out[$id]['band'] = self::band($s['score'], $kind);
        }
        if ($f->risk) {
            $out = array_filter($out, fn ($s) => $s['band'] === $f->risk);
        }

        return $out;
    }

    /** ['high' => n, 'medium' => n, 'low' => n] */
    public static function bandCounts(array $subjects): array
    {
        $counts = ['high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($subjects as $s) {
            $counts[$s['band']]++;
        }

        return $counts;
    }
}
