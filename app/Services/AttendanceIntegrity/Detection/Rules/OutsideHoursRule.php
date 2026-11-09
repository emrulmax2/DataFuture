<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rule F: entries and changes made
 *   - at night (Attendance Settings, UK time),
 *   - on a non-working day: they neither teach a class that day nor have hours
 *     that weekday in their HR working pattern (or, with no pattern, it is not a
 *     working day in the settings). Teaching days always count as working days:
 *     HR patterns describe contracted hours, and tutors often teach outside them,
 *   - during approved leave (an active day of an approved leave),
 *   - more than N days after the term ended.
 * One finding per person, day and kind.
 */
class OutsideHoursRule extends Rule
{
    public function key(): string
    {
        return 'outside_hours';
    }

    public function label(): string
    {
        return 'Outside working hours';
    }

    protected function setting(): string
    {
        return 'att_hours_enabled';
    }

    public function detect(TermScope $t): array
    {
        $from = AttendanceSettings::get('att_retro_from_date').' 00:00:00';
        $nightStart = AttendanceSettings::get('att_night_start').':00';
        $nightEnd = AttendanceSettings::get('att_night_end').':00';
        $night = $nightStart > $nightEnd
            ? '(TIME(x.ts) >= ? OR TIME(x.ts) < ?)'
            : '(TIME(x.ts) >= ? AND TIME(x.ts) < ?)';

        $events = 'SELECT a.created_by AS u, a.created_at AS ts, a.id '.TermScope::FROM.'
                WHERE p.term_declaration_id = ? AND a.created_by IS NOT NULL AND a.created_at >= ?
            UNION
            SELECT a.updated_by, a.updated_at, a.id '.TermScope::FROM.'
                WHERE p.term_declaration_id = ? AND a.updated_by IS NOT NULL AND a.updated_at >= ?';
        $bindings = [$t->termId, $from, $t->termId, $from];
        if (Schema::hasTable('attendance_audit_logs')) {
            $events .= '
            UNION
            SELECT l.changed_by, l.changed_at, l.attendance_id FROM attendance_audit_logs l
                JOIN plans_date_lists d ON d.id = l.plans_date_list_id JOIN plans p ON p.id = d.plan_id
                WHERE p.term_declaration_id = ? AND l.changed_by IS NOT NULL';
            $bindings[] = $t->termId;
        }

        $days = DB::select("SELECT x.u, DATE(x.ts) AS day, COUNT(*) AS n, SUM($night) AS night_n,
                MIN(CASE WHEN $night THEN TIME(x.ts) END) AS night_first, MAX(CASE WHEN $night THEN TIME(x.ts) END) AS night_last,
                MIN(TIME(x.ts)) AS first_time, MAX(TIME(x.ts)) AS last_time
            FROM ($events) x GROUP BY x.u, DATE(x.ts)", array_merge([$nightStart, $nightEnd, $nightStart, $nightEnd, $nightStart, $nightEnd], $bindings));

        if (! $days) {
            return [];
        }

        $userIds = array_values(array_unique(array_map(fn ($d) => (int) $d->u, $days)));
        $t->staffNames($userIds);
        $employees = DB::table('employees')->whereIn('user_id', $userIds)->orderBy('id')->pluck('id', 'user_id')->all();
        [$patterns, $leaves] = $this->hr(array_values($employees));
        $teaching = $this->teachingDays($userIds, array_values(array_unique(array_map(fn ($d) => (string) $d->day, $days))));
        $usePatterns = AttendanceSettings::bool('att_use_working_patterns');
        $workingDays = AttendanceSettings::ids('att_working_days');
        $afterTerm = $t->end ? date('Y-m-d', strtotime($t->end.' +'.AttendanceSettings::int('att_after_term_days').' days')) : null;
        $points = $this->points('att_points_outside_hours');

        $out = [];
        foreach ($days as $d) {
            $userId = (int) $d->u;
            $who = $t->staffName($userId);
            $day = (string) $d->day;
            $label = date('D d/m/Y', strtotime($day));
            $employeeId = $employees[$userId] ?? null;
            $base = ['staff_user_id' => $userId, 'event_at' => $day.' '.$d->first_time, 'weight' => $points];

            if ((int) $d->night_n > 0) {
                $out[] = $this->finding($t, $base + [
                    'type' => 'night',
                    'event_key' => 'u:'.$userId.':d:'.$day.':night',
                    'event_at' => $day.' '.$d->night_first,
                    'rows_count' => (int) $d->night_n,
                    'summary' => $who.' entered or changed '.$d->night_n.' records at night ('.substr($d->night_first, 0, 5).'–'.substr($d->night_last, 0, 5).') on '.$label,
                    'evidence' => ['records' => (int) $d->night_n, 'from' => substr($d->night_first, 0, 5), 'to' => substr($d->night_last, 0, 5), 'night' => substr($nightStart, 0, 5).'–'.substr($nightEnd, 0, 5)],
                ]);
            }

            $working = isset($teaching[$userId][$day]) ? true : $this->isWorkingDay($employeeId, $day, $patterns, $usePatterns, $workingDays);
            if ($working === false) {
                $out[] = $this->finding($t, $base + [
                    'type' => 'non_working_day',
                    'event_key' => 'u:'.$userId.':d:'.$day.':non-working',
                    'rows_count' => (int) $d->n,
                    'summary' => $who.' entered or changed '.$d->n.' records on a non-working day ('.$label.')',
                    'evidence' => ['records' => (int) $d->n, 'from' => substr($d->first_time, 0, 5), 'to' => substr($d->last_time, 0, 5),
                        'based_on' => $employeeId && isset($patterns[$employeeId]) && $usePatterns ? 'HR working pattern' : 'working days in Attendance Settings'],
                ]);
            }

            if ($employeeId && isset($leaves[$employeeId][$day])) {
                $leave = $leaves[$employeeId][$day];
                $out[] = $this->finding($t, $base + [
                    'type' => 'on_leave',
                    'basis' => 'fact',
                    'event_key' => 'u:'.$userId.':d:'.$day.':leave',
                    'rows_count' => (int) $d->n,
                    'summary' => $who.' entered or changed '.$d->n.' records on a day of approved leave ('.$label.')',
                    'evidence' => ['records' => (int) $d->n, 'from' => substr($d->first_time, 0, 5), 'to' => substr($d->last_time, 0, 5), 'leave_hours' => $leave['hour'], 'part_day' => $leave['fraction']],
                ]);
            }

            if ($afterTerm && $day > $afterTerm) {
                $out[] = $this->finding($t, $base + [
                    'type' => 'after_term',
                    'basis' => 'fact',
                    'event_key' => 'u:'.$userId.':d:'.$day.':after-term',
                    'rows_count' => (int) $d->n,
                    'summary' => $who.' entered or changed '.$d->n.' records of '.$t->name.' on '.$label.', after the term ended ('.self::ukDate($t->end).')',
                    'evidence' => ['records' => (int) $d->n, 'term_ended' => self::ukDate($t->end)],
                ]);
            }
        }

        return $out;
    }

    /** true / false, or null when it cannot be told. */
    private function isWorkingDay(?int $employeeId, string $day, array $patterns, bool $usePatterns, array $workingDays): ?bool
    {
        $weekday = (int) date('N', strtotime($day));
        if ($usePatterns && $employeeId && isset($patterns[$employeeId])) {
            foreach ($patterns[$employeeId] as $p) {
                if ($p['from'] <= $day && ($p['to'] === null || $p['to'] >= $day)) {
                    return in_array($weekday, $p['days'], true);
                }
            }
        }

        return $workingDays ? in_array($weekday, $workingDays, true) : null;
    }

    /** user id => [date => true] for the days they teach a class (tutor, personal tutor or cover tutor). */
    private function teachingDays(array $userIds, array $dates): array
    {
        $out = [];
        if (! $userIds || ! $dates) {
            return $out;
        }
        $in = TermScope::in($userIds);
        $dateList = implode(',', array_fill(0, count($dates), '?'));
        foreach (DB::select("SELECT d.date, p.tutor_id, p.personal_tutor_id, d.proxy_tutor_id
                FROM plans_date_lists d JOIN plans p ON p.id = d.plan_id
                WHERE d.deleted_at IS NULL AND d.date IN ($dateList)
                  AND (p.tutor_id IN ($in) OR p.personal_tutor_id IN ($in) OR d.proxy_tutor_id IN ($in))", $dates) as $r) {
            foreach ([$r->tutor_id, $r->personal_tutor_id, $r->proxy_tutor_id] as $u) {
                if ($u) {
                    $out[(int) $u][substr((string) $r->date, 0, 10)] = true;
                }
            }
        }

        return $out;
    }

    /** Working patterns (employee => [from, to, ISO days with hours]) and approved leave days (employee => date => info). */
    private function hr(array $employeeIds): array
    {
        if (! $employeeIds) {
            return [[], []];
        }
        $patterns = [];
        foreach (DB::select('SELECT w.employee_id, w.effective_from, w.end_to, GROUP_CONCAT(dt.day) AS days
                FROM employee_working_patterns w
                LEFT JOIN employee_working_pattern_details dt ON dt.employee_working_pattern_id = w.id AND dt.deleted_at IS NULL
                    AND COALESCE(dt.start, "") <> "" AND COALESCE(dt.end, "") <> ""
                WHERE w.deleted_at IS NULL AND w.active = 1 AND w.employee_id IN ('.TermScope::in($employeeIds).')
                GROUP BY w.id, w.employee_id, w.effective_from, w.end_to
                ORDER BY w.effective_from DESC') as $w) {
            $patterns[(int) $w->employee_id][] = [
                'from' => substr((string) $w->effective_from, 0, 10),
                'to' => $w->end_to ? substr((string) $w->end_to, 0, 10) : null,
                'days' => array_values(array_unique(array_map('intval', array_filter(explode(',', (string) $w->days), 'strlen')))),
            ];
        }

        $leaves = [];
        foreach (DB::select('SELECT l.employee_id, ld.leave_date, ld.hour, ld.is_fraction
                FROM employee_leave_days ld JOIN employee_leaves l ON l.id = ld.employee_leave_id
                WHERE l.status = "Approved" AND ld.status = "Active" AND l.deleted_at IS NULL AND ld.deleted_at IS NULL
                  AND l.employee_id IN ('.TermScope::in($employeeIds).')') as $l) {
            $leaves[(int) $l->employee_id][substr((string) $l->leave_date, 0, 10)] = ['hour' => $l->hour, 'fraction' => (bool) $l->is_fraction];
        }

        return [$patterns, $leaves];
    }
}
