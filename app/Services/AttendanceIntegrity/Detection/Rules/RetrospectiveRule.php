<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;

/**
 * Rule A: attendance first entered a long time after the class ended.
 * Delay = first-entry time (created_at, UK) minus the class date's scheduled end
 * (its date + the class end time), never midnight of the date. One finding per
 * class date and member of staff who entered it; points from the highest delay
 * tier reached (Attendance Settings). Student visits are left out: they are
 * recorded after the event by design.
 */
class RetrospectiveRule extends Rule
{
    public function key(): string
    {
        return 'retrospective';
    }

    public function label(): string
    {
        return 'Late entry';
    }

    protected function setting(): string
    {
        return 'att_retro_enabled';
    }

    public function detect(TermScope $t): array
    {
        $tiers = AttendanceSettings::get('att_retro_tiers') ?: [];
        if (! $tiers) {
            return [];
        }
        $minMinutes = (int) min(array_map(fn ($r) => $r[0], $tiers)) * 60;
        $delay = "TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, COALESCE(p.end_time, '23:59:59')), a.created_at)";

        $rows = DB::select("SELECT a.plans_date_list_id AS session_id, a.created_by, d.plan_id,
                COUNT(*) AS rows_n, MIN(a.created_at) AS first_at, MAX($delay) AS max_delay, MIN($delay) AS min_delay,
                MAX(CASE WHEN ".TermScope::linked('a.created_by')." THEN 1 ELSE 0 END) AS linked
            ".TermScope::FROM."
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND a.created_by IS NOT NULL
              AND a.created_at >= ? AND COALESCE(d.name, '') <> 'Visit'
              AND $delay > ?
            GROUP BY a.plans_date_list_id, a.created_by, d.plan_id", [$t->termId, AttendanceSettings::get('att_retro_from_date').' 00:00:00', $minMinutes]);

        $t->sessions(array_map(fn ($r) => $r->session_id, $rows));
        $t->staffNames(array_map(fn ($r) => $r->created_by, $rows));

        $out = [];
        foreach ($rows as $r) {
            $points = 0;
            $tierHours = 0;
            foreach ($tiers as [$hours, $tierPoints]) {
                if ((int) $r->max_delay > $hours * 60) {
                    [$points, $tierHours] = [$tierPoints, $hours];
                }
            }
            $who = $t->staffName((int) $r->created_by);
            $out[] = $this->finding($t, [
                'type' => 'late_entry',
                'basis' => 'fact',
                'weight' => $points,
                'event_key' => 'ses:'.$r->session_id.':u:'.$r->created_by.':entry',
                'staff_user_id' => (int) $r->created_by,
                'plan_id' => (int) $r->plan_id,
                'plans_date_list_id' => (int) $r->session_id,
                'event_at' => $r->first_at,
                'rows_count' => (int) $r->rows_n,
                'summary' => 'Attendance first entered '.TermScope::duration((int) $r->max_delay).' after the class ended: '
                    .$t->sessionLabel((int) $r->session_id).' ('.$r->rows_n.' '.((int) $r->rows_n === 1 ? 'record' : 'records').', by '.$who.')',
                'evidence' => [
                    'first_entered_at' => self::ukDateTime($r->first_at),
                    'longest_delay' => TermScope::duration((int) $r->max_delay).' after the class ended',
                    'shortest_delay' => TermScope::duration((int) $r->min_delay).' after the class ended',
                    'delay_band' => 'over '.($tierHours % 24 === 0 ? ($tierHours / 24).' '.($tierHours === 24 ? 'day' : 'days') : $tierHours.' hours'),
                    'delay_minutes' => (int) $r->max_delay,
                    'entered_by' => $who,
                    'entered_by_teaches_class' => (bool) $r->linked,
                ],
            ]);
        }

        return $out;
    }
}
