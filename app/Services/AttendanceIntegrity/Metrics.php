<?php

namespace App\Services\AttendanceIntegrity;

use App\Models\AttendanceIntegrity\AimCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Figures for the Attendance Integrity screens: the dashboard, the drill-down
 * record lists, staff analysis and student analysis. Facts come straight from
 * attendance (and its history); anomalies come from aim_findings.
 *
 * Reads run with the session time zone at +00:00 so TIMESTAMP columns come back
 * as the UK clock time live wrote (see uk()).
 */
class Metrics
{
    public const FROM = 'FROM attendances a JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id';

    /** Drill-down lists behind the dashboard cards. */
    public const RECORD_METRICS = [
        'recorded' => 'Recorded attendance',
        'late_status' => 'Recorded as Late',
        'retrospective' => 'First entered more than 24 hours after the class',
        'modified' => 'Changed after first entry',
        'deleted' => 'Deleted',
        'restored' => 'Restored after deletion',
        'absent_to_present' => 'Absent changed to Present (evidence held)',
        'not_linked' => 'Last changed by someone who does not teach the class',
    ];

    public static function uk(callable $run)
    {
        $previous = DB::selectOne('SELECT @@session.time_zone AS tz')->tz;
        DB::statement("SET time_zone = '+00:00'");
        try {
            return $run();
        } finally {
            DB::statement('SET time_zone = '.DB::getPdo()->quote($previous));
        }
    }

    /* ---------------------------------------------------------------- Dashboard */

    public static function dashboard(Filters $f): array
    {
        return self::uk(function () use ($f) {
            [$w, $b] = $f->attendanceWhere();
            $attended = Filters::in(AttendanceSettings::ids('att_attended_statuses'));
            $retroFrom = AttendanceSettings::get('att_retro_from_date').' 00:00:00';
            $today = now('Europe/London')->toDateString();

            $facts = DB::selectOne("SELECT
                    SUM(a.deleted_at IS NULL) AS recorded,
                    SUM(a.deleted_at IS NULL AND a.attendance_feed_status_id IN ($attended)) AS attended,
                    SUM(a.deleted_at IS NULL AND a.attendance_feed_status_id = 5) AS late_status,
                    SUM(a.deleted_at IS NULL AND a.created_at >= ? AND TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, COALESCE(p.end_time, '23:59:59')), a.created_at) > 1440) AS retrospective,
                    SUM(a.deleted_at IS NULL AND a.updated_by IS NOT NULL) AS modified,
                    SUM(a.deleted_at IS NOT NULL) AS deleted
                ".self::FROM." WHERE COALESCE(d.name, '') <> 'Visit' $w", array_merge([$retroFrom], $b));

            $opportunities = self::opportunities($f, $today);

            $restored = 0;
            $historyRows = 0;
            if (Schema::hasTable('attendance_audit_logs')) {
                $h = DB::selectOne("SELECT SUM(l.action = 'restored') AS restored, COUNT(DISTINCT l.attendance_id) AS records
                    FROM attendance_audit_logs l JOIN attendances a ON a.id = l.attendance_id JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
                    WHERE 1=1 $w", $b);
                [$restored, $historyRows] = [(int) $h->restored, (int) $h->records];
            }

            [$fj, $fw, $fb] = $f->findingWhere(false);
            $find = DB::selectOne("SELECT
                    COUNT(DISTINCT CASE WHEN f.type IN ('absent_to_present', 'absent_to_present_notice', 'absent_to_present_own_class', 'present_after_rejected_excuse') THEN f.attendance_id END) AS absent_to_present,
                    SUM(f.type = 'bulk_burst') AS bulk,
                    COUNT(DISTINCT CASE WHEN f.student_id IS NOT NULL AND f.weight > 0 THEN f.student_id END) AS students_flagged,
                    SUM(f.status = 'open') AS open_findings
                FROM aim_findings f $fj WHERE f.status <> 'dismissed' $fw", $fb);

            $staffScores = Scoring::subjects('staff', $f);
            $studentScores = Scoring::subjects('student', $f);
            $highAmenders = self::highAmendmentStaff($f);

            $byTerm = DB::select("SELECT p.term_declaration_id AS term_id, td.name, COUNT(*) AS rows_n, SUM(a.attendance_feed_status_id IN ($attended)) AS attended_n
                ".self::FROM." JOIN term_declarations td ON td.id = p.term_declaration_id
                WHERE a.deleted_at IS NULL AND COALESCE(d.name, '') <> 'Visit' $w
                GROUP BY p.term_declaration_id, td.name, td.start_date ORDER BY td.start_date", $b);

            $cases = self::caseCounts($f);

            return [
                'cards' => [
                    'opportunities' => $opportunities,
                    'recorded' => (int) $facts->recorded,
                    'rate' => $facts->recorded ? round(100 * $facts->attended / $facts->recorded, 1) : null,
                    'late_status' => (int) $facts->late_status,
                    'retrospective' => (int) $facts->retrospective,
                    'modified' => (int) $facts->modified,
                    'with_history' => $historyRows,
                    'deleted' => (int) $facts->deleted,
                    'restored' => $restored,
                    'absent_to_present' => (int) $find->absent_to_present,
                    'bulk' => (int) $find->bulk,
                    'students_flagged' => (int) $find->students_flagged,
                    'high_amendment_staff' => count($highAmenders),
                    'open_findings' => (int) $find->open_findings,
                ],
                'staff_bands' => Scoring::bandCounts($staffScores),
                'student_bands' => Scoring::bandCounts($studentScores),
                'cases' => $cases,
                'by_term' => array_map(fn ($r) => ['term' => $r->name, 'term_id' => (int) $r->term_id, 'records' => (int) $r->rows_n,
                    'rate' => $r->rows_n ? round(100 * $r->attended_n / $r->rows_n, 1) : null], $byTerm),
                'trend' => self::trend($f),
                'by_rule' => self::findingsByRule($f),
                'top_staff' => self::topSubjects('staff', $staffScores, 8),
                'top_students' => self::topSubjects('student', $studentScores, 8),
            ];
        });
    }

    /** Class dates held (not cancelled, not in the future) x students assigned to the class. */
    private static function opportunities(Filters $f, string $today): int
    {
        $sql = "SELECT COUNT(*) AS n FROM plans_date_lists d JOIN plans p ON p.id = d.plan_id
            JOIN assigns s ON s.plan_id = p.id AND s.deleted_at IS NULL AND (s.attendance IS NULL OR s.attendance = 1)
            WHERE d.deleted_at IS NULL AND COALESCE(d.status, '') <> 'Canceled' AND COALESCE(d.name, '') <> 'Visit' AND d.date <= ?";
        $b = [$today];
        if ($f->terms) {
            $sql .= ' AND p.term_declaration_id IN ('.Filters::in($f->terms).')';
        }
        foreach (['from' => ['d.date >= ?'], 'to' => ['d.date <= ?']] as $prop => [$cond]) {
            if ($f->$prop) {
                $sql .= ' AND '.$cond;
                $b[] = $f->$prop;
            }
        }
        foreach (['venue' => 'p.venue_id', 'course' => 'p.course_id', 'group' => 'p.group_id', 'module' => 'p.module_creation_id', 'plan' => 'p.id', 'session' => 'd.id', 'student' => 's.student_id'] as $prop => $col) {
            if ($f->$prop) {
                $sql .= " AND $col = ?";
                $b[] = $f->$prop;
            }
        }

        return (int) DB::selectOne($sql, $b)->n;
    }

    /** Weekly series by class week: records, entered late, changed, findings. */
    private static function trend(Filters $f): array
    {
        [$w, $b] = $f->attendanceWhere();
        $retroFrom = AttendanceSettings::get('att_retro_from_date').' 00:00:00';
        $rows = DB::select("SELECT YEARWEEK(d.date, 3) AS wk, MIN(d.date) AS week_start, COUNT(*) AS records,
                SUM(a.created_at >= ? AND TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, COALESCE(p.end_time, '23:59:59')), a.created_at) > 1440) AS late_entered,
                SUM(a.updated_by IS NOT NULL) AS changed
            ".self::FROM." WHERE a.deleted_at IS NULL AND COALESCE(d.name, '') <> 'Visit' $w
            GROUP BY YEARWEEK(d.date, 3) ORDER BY wk", array_merge([$retroFrom], $b));

        [$fj, $fw, $fb] = $f->findingWhere(false);
        $findings = [];
        foreach (DB::select("SELECT YEARWEEK(f.event_at, 3) AS wk, COUNT(*) AS n FROM aim_findings f $fj
                WHERE f.status <> 'dismissed' AND f.weight > 0 AND f.event_at IS NOT NULL $fw GROUP BY YEARWEEK(f.event_at, 3)", $fb) as $r) {
            $findings[(int) $r->wk] = (int) $r->n;
        }

        return array_map(fn ($r) => [
            'week' => date('d/m', strtotime($r->week_start)),
            'records' => (int) $r->records,
            'late_entered' => (int) $r->late_entered,
            'changed' => (int) $r->changed,
            'findings' => $findings[(int) $r->wk] ?? 0,
        ], $rows);
    }

    public static function findingsByRule(Filters $f): array
    {
        [$fj, $fw, $fb] = $f->findingWhere(false);
        $labels = Detection\Scanner::ruleLabels();
        $out = [];
        foreach (DB::select("SELECT f.rule, COUNT(*) AS n, SUM(f.weight > 0) AS scored, SUM(f.status = 'open') AS open_n
                FROM aim_findings f $fj WHERE f.status <> 'dismissed' $fw GROUP BY f.rule", $fb) as $r) {
            $out[$r->rule] = ['rule' => $r->rule, 'label' => $labels[$r->rule] ?? $r->rule, 'findings' => (int) $r->n, 'scored' => (int) $r->scored, 'open' => (int) $r->open_n];
        }

        // In rule order (A, B, C, ...).
        $ordered = [];
        foreach (array_keys($labels) as $key) {
            if (isset($out[$key])) {
                $ordered[] = $out[$key];
            }
        }

        return $ordered;
    }

    private static function topSubjects(string $kind, array $scores, int $limit): array
    {
        uasort($scores, fn ($x, $y) => $y['score'] <=> $x['score']);
        $scores = array_slice($scores, 0, $limit, true);
        $out = [];
        if ($kind === 'staff') {
            $people = Lookup::staff(array_keys($scores));
            foreach ($scores as $id => $s) {
                $out[] = ['id' => $id, 'name' => $people[$id]['name'] ?? 'User #'.$id, 'meta' => trim(($people[$id]['job_title'] ?? '').' · '.($people[$id]['department'] ?? ''), ' ·'),
                    'score' => $s['score'], 'band' => $s['band'], 'findings' => $s['findings']];
            }
        } else {
            $students = Lookup::students(array_keys($scores));
            foreach ($scores as $id => $s) {
                $out[] = ['id' => $id, 'name' => $students[$id]['registration_no'] ?? '#'.$id, 'meta' => $students[$id]['name'] ?? '',
                    'score' => $s['score'], 'band' => $s['band'], 'findings' => $s['findings']];
            }
        }

        return $out;
    }

    private static function caseCounts(Filters $f): array
    {
        $q = DB::table('aim_cases');
        if ($f->terms) {
            $q->where(function ($q) use ($f) {
                $q->whereIn('term_declaration_id', $f->terms)->orWhereNull('term_declaration_id');
            });
        }
        $rows = $q->groupBy('investigation_status')->select('investigation_status', DB::raw('COUNT(*) AS n'))->pluck('n', 'investigation_status')->all();

        return [
            'open' => array_sum(array_intersect_key($rows, array_flip(AimCase::OPEN_STATUSES))),
            'substantiated' => (int) ($rows['Resolved – Substantiated'] ?? 0),
            'no_issue' => (int) ($rows['Resolved – No Issue'] ?? 0) + (int) ($rows['Resolved – Process Error'] ?? 0),
            'closed' => (int) ($rows['Closed'] ?? 0),
            'by_status' => $rows,
        ];
    }

    /**
     * Staff whose changes per 100 records they handled are at least twice the
     * median of all staff in scope (and at least 20 changes).
     */
    private static function highAmendmentStaff(Filters $f): array
    {
        $rows = self::staffRows($f);
        $rates = array_values(array_filter(array_map(fn ($r) => $r['changes_per_100'], $rows), fn ($v) => $v !== null));
        if (! $rates) {
            return [];
        }
        sort($rates);
        $median = $rates[intdiv(count($rates), 2)];

        return array_filter($rows, fn ($r) => $r['changed'] >= 20 && $median > 0 && $r['changes_per_100'] >= 2 * $median);
    }

    /* ---------------------------------------------------------------- Records (drill-down) */

    public static function records(Filters $f, string $metric, ?int $page, int $size = 50): array
    {
        return self::uk(function () use ($f, $metric, $page, $size) {
            [$w, $b] = $f->attendanceWhere();
            $cond = self::metricCondition($metric);
            $from = self::FROM." LEFT JOIN plans pa ON pa.id = a.plan_id LEFT JOIN plans pp ON pp.id = p.parent_id AND p.parent_id > 0
                WHERE COALESCE(d.name, '') <> 'Visit' {$cond['sql']} $w";
            $bindings = array_merge($cond['bindings'], $b);

            $total = (int) DB::selectOne("SELECT COUNT(*) AS n $from", $bindings)->n;
            $sql = "SELECT a.id, a.student_id, a.plans_date_list_id, d.date, p.start_time, p.end_time, p.id AS plan_id, p.class_type,
                    a.attendance_feed_status_id AS status_id, a.created_by, a.created_at, a.updated_by, a.updated_at, a.deleted_at, a.note,
                    TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, COALESCE(p.end_time, '23:59:59')), a.created_at) AS entered_after,
                    CASE WHEN a.updated_by IS NULL THEN NULL WHEN ".Detection\TermScope::linked('a.updated_by')." THEN 1 ELSE 0 END AS changer_linked
                $from ORDER BY d.date DESC, p.start_time, a.id";
            if ($page !== null) {
                $sql .= ' LIMIT '.max(1, $size).' OFFSET '.(max(1, $page) - 1) * max(1, $size);
            } else {
                $sql .= ' LIMIT 50000';
            }
            $rows = DB::select($sql, $bindings);

            return ['total' => $total, 'last_page' => max(1, (int) ceil($total / max(1, $size))), 'data' => self::recordRows($rows)];
        });
    }

    private static function metricCondition(string $metric): array
    {
        $retroFrom = AttendanceSettings::get('att_retro_from_date').' 00:00:00';
        switch ($metric) {
            case 'late_status':
                return ['sql' => ' AND a.deleted_at IS NULL AND a.attendance_feed_status_id = 5', 'bindings' => []];
            case 'retrospective':
                return ['sql' => " AND a.deleted_at IS NULL AND a.created_at >= ? AND TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, COALESCE(p.end_time, '23:59:59')), a.created_at) > 1440", 'bindings' => [$retroFrom]];
            case 'modified':
                return ['sql' => ' AND a.deleted_at IS NULL AND a.updated_by IS NOT NULL', 'bindings' => []];
            case 'deleted':
                return ['sql' => ' AND a.deleted_at IS NOT NULL', 'bindings' => []];
            case 'restored':
                return ['sql' => Schema::hasTable('attendance_audit_logs') ? " AND EXISTS (SELECT 1 FROM attendance_audit_logs l WHERE l.attendance_id = a.id AND l.action = 'restored')" : ' AND 1=0', 'bindings' => []];
            case 'absent_to_present':
                return ['sql' => " AND EXISTS (SELECT 1 FROM aim_findings f WHERE f.attendance_id = a.id AND f.cleared_at IS NULL AND f.status <> 'dismissed'
                    AND f.type IN ('absent_to_present', 'absent_to_present_notice', 'absent_to_present_own_class', 'present_after_rejected_excuse'))", 'bindings' => []];
            case 'not_linked':
                return ['sql' => ' AND a.deleted_at IS NULL AND a.updated_by IS NOT NULL AND NOT '.Detection\TermScope::linked('a.updated_by'), 'bindings' => []];
            default:
                return ['sql' => ' AND a.deleted_at IS NULL', 'bindings' => []];
        }
    }

    /** Readable attendance rows (records lists, exports, timelines). */
    public static function recordRows(array $rows): array
    {
        $sessions = self::sessionLabels(array_map(fn ($r) => $r->plans_date_list_id, $rows));
        $people = Lookup::staff(array_merge(array_map(fn ($r) => $r->created_by, $rows), array_map(fn ($r) => $r->updated_by, $rows)));
        $students = Lookup::students(array_map(fn ($r) => $r->student_id, $rows));
        $statuses = Lookup::feedStatusNames();

        return array_map(function ($r) use ($sessions, $people, $students, $statuses) {
            $s = $sessions[(int) $r->plans_date_list_id] ?? [];
            $st = $students[(int) $r->student_id] ?? null;

            return [
                'attendance_id' => (int) $r->id,
                'student_id' => (int) $r->student_id,
                'registration_no' => $st['registration_no'] ?? '',
                'student_name' => $st['name'] ?? '',
                'student_url' => $r->student_id ? route('student.show', $r->student_id) : null,
                'session_id' => (int) $r->plans_date_list_id,
                'class_date' => $r->date ? date('d/m/Y', strtotime($r->date)) : '',
                'class_day' => $r->date ? date('D', strtotime($r->date)) : '',
                'class_time' => trim(substr((string) $r->start_time, 0, 5).'-'.substr((string) $r->end_time, 0, 5), '-'),
                'module' => $s['module'] ?? '',
                'group' => $s['group'] ?? '',
                'class_type' => (string) ($r->class_type ?? ''),
                'teacher' => $s['teacher'] ?? '',
                'status' => isset($statuses[(int) $r->status_id]) ? $statuses[(int) $r->status_id]['code'].' - '.$statuses[(int) $r->status_id]['name'] : (string) $r->status_id,
                'first_entered_by' => $r->created_by ? ($people[(int) $r->created_by]['name'] ?? 'User #'.$r->created_by) : '',
                'first_entered_at' => $r->created_at ? date('d/m/Y H:i', strtotime($r->created_at)) : '',
                'entered_after' => isset($r->entered_after) && $r->entered_after !== null ? ((int) $r->entered_after > 0 ? Detection\TermScope::duration((int) $r->entered_after).' after' : 'during or before') : '',
                'last_changed_by' => $r->updated_by ? ($people[(int) $r->updated_by]['name'] ?? 'User #'.$r->updated_by) : '',
                'last_changed_at' => $r->updated_by && $r->updated_at ? date('d/m/Y H:i', strtotime($r->updated_at)) : '',
                'changer_teaches_class' => isset($r->changer_linked) && $r->changer_linked !== null ? (bool) $r->changer_linked : null,
                'deleted_at' => $r->deleted_at ? date('d/m/Y H:i', strtotime($r->deleted_at)) : '',
                'note' => (string) ($r->note ?? ''),
            ];
        }, $rows);
    }

    /** class date id => module, group, teacher. */
    public static function sessionLabels(array $ids): array
    {
        $out = [];
        foreach (array_chunk(array_values(array_unique(array_map('intval', $ids))), 1000) as $chunk) {
            $rows = DB::select('SELECT d.id, mc.module_name, mc.code, g.name AS group_name, COALESCE(NULLIF(p.tutor_id, 0), p.personal_tutor_id) AS teacher_id
                FROM plans_date_lists d JOIN plans p ON p.id = d.plan_id
                LEFT JOIN module_creations mc ON mc.id = p.module_creation_id LEFT JOIN `groups` g ON g.id = p.group_id
                WHERE d.id IN ('.Filters::in($chunk).')');
            $teachers = Lookup::staff(array_map(fn ($r) => $r->teacher_id, $rows));
            foreach ($rows as $r) {
                $out[(int) $r->id] = [
                    'module' => trim((string) $r->module_name.($r->code ? ' ('.$r->code.')' : '')),
                    'group' => (string) $r->group_name,
                    'teacher' => $r->teacher_id ? ($teachers[(int) $r->teacher_id]['name'] ?? '') : '',
                ];
            }
        }

        return $out;
    }

    /* ---------------------------------------------------------------- Staff analysis */

    /**
     * One row per member of staff who entered or changed attendance in scope.
     * Every count is a minimum where the history is not recorded.
     */
    public static function staffRows(Filters $f): array
    {
        return self::uk(function () use ($f) {
            $staffFilter = $f->staff;
            $f->staff = null;
            [$w, $b] = $f->attendanceWhere();
            $f->staff = $staffFilter;
            $retroFrom = AttendanceSettings::get('att_retro_from_date').' 00:00:00';

            $created = DB::select("SELECT a.created_by AS u, COUNT(*) AS created, COUNT(DISTINCT a.student_id) AS students, COUNT(DISTINCT p.id) AS classes,
                    SUM(a.created_at >= ? AND TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, COALESCE(p.end_time, '23:59:59')), a.created_at) > 1440) AS late_entered
                ".self::FROM." WHERE a.deleted_at IS NULL AND a.created_by IS NOT NULL $w GROUP BY a.created_by", array_merge([$retroFrom], $b));
            $changed = DB::select("SELECT a.updated_by AS u, COUNT(*) AS changed, COUNT(DISTINCT a.student_id) AS students, COUNT(DISTINCT p.id) AS classes
                ".self::FROM." WHERE a.deleted_at IS NULL AND a.updated_by IS NOT NULL $w GROUP BY a.updated_by", $b);

            $rows = [];
            foreach ($created as $r) {
                $rows[(int) $r->u] = ['created' => (int) $r->created, 'late_entered' => (int) $r->late_entered, 'changed' => 0, 'students' => (int) $r->students, 'classes' => (int) $r->classes];
            }
            foreach ($changed as $r) {
                $rows[(int) $r->u] ??= ['created' => 0, 'late_entered' => 0, 'changed' => 0, 'students' => 0, 'classes' => 0];
                $rows[(int) $r->u]['changed'] = (int) $r->changed;
                $rows[(int) $r->u]['students'] = max($rows[(int) $r->u]['students'], (int) $r->students);
                $rows[(int) $r->u]['classes'] = max($rows[(int) $r->u]['classes'], (int) $r->classes);
            }

            [$fj, $fw, $fb] = $f->findingWhere(false);
            $fwNoStaff = $fw;
            $find = [];
            foreach (DB::select("SELECT f.staff_user_id AS u,
                    COUNT(DISTINCT CASE WHEN f.type IN ('absent_to_present', 'absent_to_present_notice', 'absent_to_present_own_class') THEN f.attendance_id END) AS positive,
                    SUM(f.type = 'present_to_absent') AS negative,
                    SUM(CASE WHEN f.type = 'deleted_records' THEN f.rows_count ELSE 0 END) AS deleted,
                    SUM(f.type = 'bulk_burst') AS bulk,
                    SUM(CASE WHEN f.type = 'other_classes' THEN f.rows_count ELSE 0 END) AS unauthorised,
                    SUM(CASE WHEN f.type = 'other_classes_privileged' THEN f.rows_count ELSE 0 END) AS other_classes_privileged,
                    SUM(f.type = 'recurring_pair') AS pairs,
                    SUM(f.type = 'lone_presents') AS lone,
                    SUM(f.type = 'present_after_rejected_excuse') AS rejected_excuse
                FROM aim_findings f $fj WHERE f.staff_user_id IS NOT NULL AND f.status <> 'dismissed' $fwNoStaff GROUP BY f.staff_user_id", $fb) as $r) {
                $find[(int) $r->u] = (array) $r;
            }

            $scores = Scoring::subjects('staff', $f);
            $ids = array_values(array_unique(array_merge(array_keys($rows), array_keys($scores))));
            if ($f->staff) {
                $ids = array_values(array_intersect($ids, [$f->staff]));
            }
            $people = Lookup::staff($ids);

            $out = [];
            foreach ($ids as $id) {
                $r = $rows[$id] ?? ['created' => 0, 'late_entered' => 0, 'changed' => 0, 'students' => 0, 'classes' => 0];
                $x = $find[$id] ?? [];
                $s = $scores[$id] ?? null;
                if ($f->risk && (! $s || $s['band'] !== $f->risk)) {
                    continue;
                }
                $handled = $r['created'] + $r['changed'];
                $out[] = [
                    'user_id' => $id,
                    'name' => $people[$id]['name'] ?? 'User #'.$id,
                    'job_title' => $people[$id]['job_title'] ?? '',
                    'department' => $people[$id]['department'] ?? '',
                    'created' => $r['created'],
                    'changed' => $r['changed'],
                    'late_entered' => $r['late_entered'],
                    'positive' => (int) ($x['positive'] ?? 0),
                    'negative' => (int) ($x['negative'] ?? 0),
                    'deleted' => (int) ($x['deleted'] ?? 0),
                    'students' => $r['students'],
                    'classes' => $r['classes'],
                    'bulk' => (int) ($x['bulk'] ?? 0),
                    'unauthorised' => (int) ($x['unauthorised'] ?? 0),
                    'other_classes_privileged' => (int) ($x['other_classes_privileged'] ?? 0),
                    'pairs' => (int) ($x['pairs'] ?? 0),
                    'lone' => (int) ($x['lone'] ?? 0),
                    'rejected_excuse' => (int) ($x['rejected_excuse'] ?? 0),
                    'changes_per_100' => $handled ? round(100 * $r['changed'] / $handled, 1) : null,
                    'positive_per_100' => $handled ? round(100 * (int) ($x['positive'] ?? 0) / $handled, 2) : null,
                    'score' => $s['score'] ?? 0,
                    'band' => $s['band'] ?? null,
                    'rules' => $s['rules'] ?? [],
                ];
            }

            // Peer comparison: each person against the median of everyone listed.
            $med = function (string $key) use ($out) {
                $v = array_values(array_filter(array_map(fn ($r) => $r[$key], $out), fn ($x) => $x !== null));
                if (! $v) {
                    return null;
                }
                sort($v);

                return $v[intdiv(count($v), 2)];
            };
            $medChanges = $med('changes_per_100');
            $medPositive = $med('positive_per_100');
            foreach ($out as $i => $r) {
                $out[$i]['peer_changes_per_100'] = $medChanges;
                $out[$i]['peer_positive_per_100'] = $medPositive;
            }
            usort($out, fn ($x, $y) => [$y['score'], $y['changed']] <=> [$x['score'], $x['changed']]);

            return $out;
        });
    }

    /* ---------------------------------------------------------------- Student analysis */

    public static function studentRows(Filters $f): array
    {
        return self::uk(function () use ($f) {
            $statusFilter = $f->status;
            $f->status = null;
            [$w, $b] = $f->attendanceWhere();
            $f->status = $statusFilter;
            $attended = Filters::in(AttendanceSettings::ids('att_attended_statuses'));
            $retroFrom = AttendanceSettings::get('att_retro_from_date').' 00:00:00';

            $rows = DB::select("SELECT a.student_id, COUNT(*) AS recorded,
                    SUM(a.attendance_feed_status_id IN (1, 2)) AS present_n,
                    SUM(a.attendance_feed_status_id = 4) AS absent_n,
                    SUM(a.attendance_feed_status_id = 5) AS late_n,
                    SUM(a.attendance_feed_status_id IN (6, 7, 8)) AS excused_n,
                    SUM(a.attendance_feed_status_id IN ($attended)) AS attended_n,
                    SUM(a.created_at >= ? AND TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, COALESCE(p.end_time, '23:59:59')), a.created_at) > 1440) AS late_entered,
                    COUNT(DISTINCT a.created_by) AS staff_entering,
                    COUNT(DISTINCT CASE WHEN a.updated_by IS NOT NULL AND NOT (a.updated_by <=> a.created_by) THEN a.updated_by END) AS staff_correcting
                ".self::FROM." WHERE a.deleted_at IS NULL AND COALESCE(d.name, '') <> 'Visit' $w
                GROUP BY a.student_id", array_merge([$retroFrom], $b));

            [$fj, $fw, $fb] = $f->findingWhere(false);
            $find = [];
            foreach (DB::select("SELECT f.student_id AS s,
                    COUNT(DISTINCT CASE WHEN f.type IN ('absent_to_present', 'absent_to_present_notice', 'absent_to_present_own_class') THEN f.attendance_id END) AS positive,
                    SUM(f.type = 'present_to_absent') AS negative,
                    SUM(f.weight > 0) AS flagged
                FROM aim_findings f $fj WHERE f.student_id IS NOT NULL AND f.status <> 'dismissed' $fw GROUP BY f.student_id", $fb) as $r) {
                $find[(int) $r->s] = $r;
            }
            $scores = Scoring::subjects('student', $f);
            $excuses = [];
            $ids = array_map(fn ($r) => (int) $r->student_id, $rows);
            foreach (array_chunk($ids, 1000) as $chunk) {
                foreach (DB::select('SELECT student_id, COUNT(*) AS n, SUM(status = 2) AS approved, SUM(status = 1) AS rejected FROM attendance_excuses
                    WHERE deleted_at IS NULL AND student_id IN ('.Filters::in($chunk).') GROUP BY student_id') as $e) {
                    $excuses[(int) $e->student_id] = $e;
                }
            }
            $students = Lookup::students($ids);
            $opportunities = self::studentOpportunities($f, $ids);

            $out = [];
            foreach ($rows as $r) {
                $id = (int) $r->student_id;
                $s = $scores[$id] ?? null;
                if ($f->risk && (! $s || $s['band'] !== $f->risk)) {
                    continue;
                }
                $x = $find[$id] ?? null;
                $e = $excuses[$id] ?? null;
                $out[] = [
                    'student_id' => $id,
                    'registration_no' => $students[$id]['registration_no'] ?? '',
                    'name' => $students[$id]['name'] ?? '',
                    'student_url' => route('student.show', $id),
                    'scheduled' => $opportunities[$id] ?? (int) $r->recorded,
                    'recorded' => (int) $r->recorded,
                    'present' => (int) $r->present_n,
                    'absent' => (int) $r->absent_n,
                    'late' => (int) $r->late_n,
                    'excused' => (int) $r->excused_n,
                    'rate' => $r->recorded ? round(100 * $r->attended_n / $r->recorded, 1) : null,
                    'late_entered' => (int) $r->late_entered,
                    'positive' => (int) ($x->positive ?? 0),
                    'negative' => (int) ($x->negative ?? 0),
                    'staff_entering' => (int) $r->staff_entering,
                    'staff_correcting' => (int) $r->staff_correcting,
                    'flagged' => (int) ($x->flagged ?? 0),
                    'excuses' => $e ? ((int) $e->n).' ('.((int) $e->approved).' approved, '.((int) $e->rejected).' rejected)' : '',
                    'excuses_n' => $e ? (int) $e->n : 0,
                    'excuses_detail' => $e ? ((int) $e->approved).' approved, '.((int) $e->rejected).' rejected' : '',
                    'score' => $s['score'] ?? 0,
                    'band' => $s['band'] ?? null,
                ];
            }
            usort($out, fn ($x, $y) => [$y['score'], $y['positive'], $x['registration_no']] <=> [$x['score'], $x['positive'], $y['registration_no']]);

            return $out;
        });
    }

    private static function studentOpportunities(Filters $f, array $ids): array
    {
        if (! $ids) {
            return [];
        }
        $today = now('Europe/London')->toDateString();
        $out = [];
        foreach (array_chunk($ids, 1000) as $chunk) {
            $sql = "SELECT s.student_id, COUNT(*) AS n FROM assigns s JOIN plans p ON p.id = s.plan_id JOIN plans_date_lists d ON d.plan_id = p.id
                WHERE s.deleted_at IS NULL AND (s.attendance IS NULL OR s.attendance = 1) AND d.deleted_at IS NULL AND COALESCE(d.status, '') <> 'Canceled'
                  AND COALESCE(d.name, '') <> 'Visit' AND d.date <= ? AND s.student_id IN (".Filters::in($chunk).')';
            $b = [$today];
            if ($f->terms) {
                $sql .= ' AND p.term_declaration_id IN ('.Filters::in($f->terms).')';
            }
            if ($f->from) {
                $sql .= ' AND d.date >= ?';
                $b[] = $f->from;
            }
            if ($f->to) {
                $sql .= ' AND d.date <= ?';
                $b[] = $f->to;
            }
            foreach (DB::select($sql.' GROUP BY s.student_id', $b) as $r) {
                $out[(int) $r->student_id] = (int) $r->n;
            }
        }

        return $out;
    }

    /**
     * A student's chronological timeline: every class date in scope with what is
     * recorded, the history (when recorded), excuses and findings.
     */
    public static function studentTimeline(Filters $f, int $studentId): array
    {
        return self::uk(function () use ($f, $studentId) {
            $f->student = $studentId;
            [$w, $b] = $f->attendanceWhere(false);
            $rows = DB::select("SELECT a.id, a.student_id, a.plans_date_list_id, d.date, p.start_time, p.end_time, p.id AS plan_id, p.class_type,
                    a.attendance_feed_status_id AS status_id, a.created_by, a.created_at, a.updated_by, a.updated_at, a.deleted_at, a.note,
                    a.notofication_date, a.sms_notification, d.status AS session_status,
                    TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, COALESCE(p.end_time, '23:59:59')), a.created_at) AS entered_after
                ".self::FROM." WHERE 1=1 $w ORDER BY d.date, p.start_time, a.id", $b);

            $ids = array_map(fn ($r) => (int) $r->id, $rows);
            $history = [];
            if ($ids && Schema::hasTable('attendance_audit_logs')) {
                foreach (array_chunk($ids, 1000) as $chunk) {
                    foreach (DB::select('SELECT * FROM attendance_audit_logs WHERE attendance_id IN ('.Filters::in($chunk).') ORDER BY id') as $l) {
                        $history[(int) $l->attendance_id][] = $l;
                    }
                }
            }
            $findings = [];
            foreach (array_chunk($ids, 1000) as $chunk) {
                foreach (DB::select('SELECT id, attendance_id, rule, type, weight, basis, summary, status FROM aim_findings
                    WHERE cleared_at IS NULL AND attendance_id IN ('.Filters::in($chunk).')') as $x) {
                    $findings[(int) $x->attendance_id][] = $x;
                }
            }
            $excuses = [];
            foreach (DB::select('SELECT ed.plans_date_list_id, e.id, e.status, e.attendance_types, e.reason, e.remarks, e.actioned_by, e.actioned_at
                    FROM attendance_excuses e JOIN attendance_excuse_days ed ON ed.attendance_excuse_id = e.id
                    WHERE e.student_id = ? AND e.deleted_at IS NULL AND ed.deleted_at IS NULL ORDER BY e.id', [$studentId]) as $e) {
                $excuses[(int) $e->plans_date_list_id] = $e;
            }

            $base = self::recordRows($rows);
            $statuses = Lookup::feedStatusNames();
            $peopleIds = array_map(fn ($e) => $e->actioned_by, $excuses);
            foreach ($history as $list) {
                foreach ($list as $l) {
                    $peopleIds[] = $l->changed_by;
                }
            }
            $people = Lookup::staff($peopleIds);
            $name = fn ($id) => $id ? ($people[(int) $id]['name'] ?? 'User #'.$id) : 'not recorded';
            $code = fn ($id) => $id === null ? '—' : ($statuses[(int) $id]['code'] ?? (string) $id);

            foreach ($rows as $i => $r) {
                $base[$i]['session_status'] = (string) $r->session_status;
                $base[$i]['absence_notice'] = $r->notofication_date ? date('d/m/Y', strtotime($r->notofication_date)) : '';
                $base[$i]['history'] = array_map(fn ($l) => [
                    'action' => $l->action,
                    'change' => $code($l->old_status_id).' > '.$code($l->new_status_id),
                    'by' => $name($l->changed_by),
                    'at' => date('d/m/Y H:i', strtotime($l->changed_at)),
                    'late' => (bool) $l->is_late_correction,
                    'reason' => $l->change_reason,
                    'screen' => AttendanceAudit::SOURCES[$l->source] ?? $l->source,
                ], $history[(int) $r->id] ?? []);
                $e = $excuses[(int) $r->plans_date_list_id] ?? null;
                $base[$i]['excuse'] = $e ? [
                    'status' => [0 => 'Pending', 1 => 'Rejected', 2 => 'Approved'][(int) $e->status] ?? 'Unknown',
                    'type' => $e->attendance_types, 'reason' => mb_substr((string) $e->reason, 0, 300), 'reply' => mb_substr((string) $e->remarks, 0, 300),
                    'by' => $name($e->actioned_by), 'at' => $e->actioned_at ? date('d/m/Y H:i', strtotime($e->actioned_at)) : '',
                ] : null;
                $base[$i]['findings'] = array_map(fn ($x) => ['id' => (int) $x->id, 'summary' => $x->summary, 'weight' => (int) $x->weight, 'basis' => $x->basis, 'status' => $x->status], $findings[(int) $r->id] ?? []);
            }

            return $base;
        });
    }
}
