<?php

namespace App\Http\Controllers\AttendanceIntegrity;

use App\Models\AttendanceIntegrity\AimFinding;
use App\Services\AttendanceIntegrity\AttendanceAudit;
use App\Services\AttendanceIntegrity\Detection\Scanner;
use App\Services\AttendanceIntegrity\Filters;
use App\Services\AttendanceIntegrity\IntegrityAccess;
use App\Services\AttendanceIntegrity\Lookup;
use App\Services\AttendanceIntegrity\Metrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The rule pages (Retrospective Attendance, Bulk Entry Detection, Staff–Student
 * Patterns, Timetable Conflicts, and every finding), Attendance Amendments with
 * the attendance history, and dismissing / reopening findings.
 */
class FindingController extends IntegrityBaseController
{
    /** page key => [rule, title, ability, lead] */
    public const PAGES = [
        'retrospective' => ['retrospective', 'Retrospective Attendance', 'view_dashboard',
            'Attendance first entered a long time after the class ended. The delay runs from the class\'s scheduled end time.'],
        'bulk' => ['bulk_entry', 'Bulk Entry Detection', 'view_dashboard',
            'Many separate entries by one person within seconds, across different class dates. A whole register saved at once counts as one entry.'],
        'patterns' => ['staff_student', 'Staff–Student Patterns', 'view_staff',
            'Staff changing attendance in classes they do not teach, the same students again and again, and lone Presents between absences.'],
        'timetable' => ['timetable', 'Timetable Conflicts', 'view_dashboard',
            'Present at cancelled or deleted class dates, at two campus classes at once, or at classes the student is not assigned to.'],
        'findings' => [null, 'All Findings', 'view_dashboard', 'Every finding of every rule.'],
    ];

    /** Readable finding types. */
    public const TYPES = [
        'late_entry' => 'Entered late',
        'absent_to_present' => 'Absent > Present (history)',
        'absent_to_present_notice' => 'Absent > Present (absence notice)',
        'absent_to_present_own_class' => 'Absent > Present by own tutor',
        'absent_to_excused' => 'Absent > Excused',
        'excused_to_present' => 'Excused > Present',
        'late_to_present' => 'Late > Present',
        'present_to_absent' => 'Present > Absent',
        'other_change' => 'Other status change',
        'late_correction' => 'Late correction',
        'repeated_change' => 'Changed repeatedly',
        'bulk_burst' => 'Bulk entry',
        'class_high_rate' => 'High class attendance',
        'student_sudden_rise' => 'Sudden rise',
        'other_classes' => 'Classes they do not teach',
        'other_classes_privileged' => 'Classes they do not teach (has Edit Attendance)',
        'recurring_pair' => 'Same student again and again',
        'lone_presents' => 'Lone Presents',
        'night' => 'At night',
        'non_working_day' => 'Non-working day',
        'on_leave' => 'During approved leave',
        'after_term' => 'After the term ended',
        'present_at_cancelled_class' => 'Present at a cancelled class',
        'deleted_class_date' => 'Deleted class date',
        'overlapping_classes' => 'Two classes at once',
        'not_assigned' => 'Not assigned to the class',
        'present_after_rejected_excuse' => 'Present after a rejected excuse',
        'register_conflicts_with_excuse' => 'Register conflicts with an excuse',
        'present_with_approved_excuse' => 'Present despite an approved excuse',
        'present_during_reported_absence' => 'Present during a reported absence',
        'no_class_start_record' => 'No class start record',
        'deleted_records' => 'Deleted records',
    ];

    public function page(Request $request, string $page)
    {
        abort_unless(isset(self::PAGES[$page]), 404);
        [$rule, $title, $ability, $lead] = self::PAGES[$page];
        IntegrityAccess::authorize($ability);
        $f = Filters::fromRequest($request);
        if ($rule) {
            $f->rule = $rule;
        }
        IntegrityAccess::log('view', 'findings', null, ['page' => $page] + $f->query());

        return $this->shell($request, 'pages.attendance-integrity.findings', $title, [
            'filters' => $f,
            'pageKey' => $page,
            'rule' => $rule,
            'lead' => $lead,
            'types' => $this->typesFor($rule, $f),
        ]);
    }

    /** Findings for a table (server-side pagination), or every match for an export. */
    public function data(Request $request)
    {
        $f = Filters::fromRequest($request);
        $ability = $f->rule === 'staff_student' ? 'view_staff' : 'view_dashboard';
        IntegrityAccess::authorize($ability);
        $size = min(max((int) $request->input('size', 50), 10), 200);
        $page = max(1, (int) $request->input('page', 1));

        $result = self::query($f, $page, $size, (string) $request->input('sort', 'points'));

        return response()->json($result);
    }

    /** [total, last_page, data] for findings under the filters. */
    public static function query(Filters $f, ?int $page, int $size = 50, string $sort = 'points'): array
    {
        [$join, $where, $b] = $f->findingWhere();
        $order = $sort === 'date' ? 'f.event_at DESC, f.id DESC' : 'f.weight DESC, f.event_at DESC, f.id DESC';
        $total = (int) DB::selectOne("SELECT COUNT(*) AS n FROM aim_findings f $join WHERE 1=1 $where", $b)->n;
        $sql = "SELECT f.* FROM aim_findings f $join WHERE 1=1 $where ORDER BY $order";
        $sql .= $page !== null ? ' LIMIT '.$size.' OFFSET '.($page - 1) * $size : ' LIMIT 20000';

        return ['total' => $total, 'last_page' => max(1, (int) ceil($total / $size)), 'data' => self::rows(DB::select($sql, $b))];
    }

    public static function rows(array $rows): array
    {
        $staff = Lookup::staff(array_map(fn ($r) => $r->staff_user_id, $rows));
        $students = Lookup::students(array_map(fn ($r) => $r->student_id, $rows));
        $labels = Scanner::ruleLabels();
        $terms = DB::table('term_declarations')->whereIn('id', array_unique(array_map(fn ($r) => $r->term_declaration_id, $rows)))->pluck('name', 'id');

        return array_map(fn ($r) => [
            'id' => (int) $r->id,
            'rule' => $r->rule,
            'rule_label' => $labels[$r->rule] ?? $r->rule,
            'type' => $r->type,
            'type_label' => self::TYPES[$r->type] ?? $r->type,
            'basis' => $r->basis,
            'basis_label' => AimFinding::BASIS[$r->basis] ?? $r->basis,
            'points' => (int) $r->weight,
            'event_at' => $r->event_at ? date('d/m/Y H:i', strtotime($r->event_at)) : '',
            'term' => $terms[$r->term_declaration_id] ?? '',
            'summary' => $r->summary,
            'staff_id' => $r->staff_user_id ? (int) $r->staff_user_id : null,
            'staff' => $r->staff_user_id ? ($staff[(int) $r->staff_user_id]['name'] ?? 'User #'.$r->staff_user_id) : '',
            'student_id' => $r->student_id ? (int) $r->student_id : null,
            'student' => $r->student_id ? trim(($students[(int) $r->student_id]['registration_no'] ?? '').' '.($students[(int) $r->student_id]['name'] ?? '')) : '',
            'session_id' => $r->plans_date_list_id ? (int) $r->plans_date_list_id : null,
            'attendance_id' => $r->attendance_id ? (int) $r->attendance_id : null,
            'records' => (int) $r->rows_count,
            'status' => $r->status,
            'case_id' => $r->case_id ? (int) $r->case_id : null,
            'dismiss_note' => $r->dismiss_note,
            'evidence' => json_decode((string) $r->evidence, true) ?: [],
            'first_detected' => $r->first_detected_at ? date('d/m/Y', strtotime($r->first_detected_at)) : '',
        ], $rows);
    }

    public function dismiss(Request $request, AimFinding $finding)
    {
        IntegrityAccess::authorize('manage_investigations');
        $note = trim((string) $request->input('note', ''));
        if (mb_strlen($note) < 5) {
            return response()->json(['message' => 'Please say why this finding is dismissed.'], 422);
        }
        if ($finding->status === 'case') {
            return response()->json(['message' => 'This finding is part of a case; resolve it there.'], 422);
        }
        $finding->forceFill(['status' => 'dismissed', 'dismissed_by' => auth()->id(), 'dismissed_at' => now('Europe/London')->format('Y-m-d H:i:s'),
            'dismiss_note' => mb_substr($note, 0, 500), 'updated_at' => now('Europe/London')->format('Y-m-d H:i:s')])->save();
        IntegrityAccess::log('case', 'finding', $finding->id, ['dismissed' => $note]);

        return response()->json(['message' => 'Finding dismissed.']);
    }

    public function reopen(AimFinding $finding)
    {
        IntegrityAccess::authorize('manage_investigations');
        if ($finding->status === 'dismissed') {
            $finding->forceFill(['status' => 'open', 'updated_at' => now('Europe/London')->format('Y-m-d H:i:s')])->save();
            IntegrityAccess::log('case', 'finding', $finding->id, ['reopened' => true]);
        }

        return response()->json(['message' => 'Finding reopened.']);
    }

    /** Attendance Amendments: the attendance history, and older changes known from the absence notice. */
    public function amendments(Request $request)
    {
        IntegrityAccess::authorize('view_audit_logs');
        $f = Filters::fromRequest($request);
        IntegrityAccess::log('view', 'amendments', null, $f->query());

        $counts = Metrics::uk(function () use ($f) {
            [$w, $b] = $f->attendanceWhere();
            $hasHistory = Schema::hasTable('attendance_audit_logs');
            $history = $hasHistory ? DB::selectOne("SELECT COUNT(*) AS changes, SUM(l.is_late_correction) AS late, SUM(l.change_reason IS NOT NULL AND l.is_late_correction) AS with_reason,
                    MIN(l.changed_at) AS since
                FROM attendance_audit_logs l JOIN attendances a ON a.id = l.attendance_id JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
                WHERE l.action = 'updated' AND l.old_status_id <> l.new_status_id $w", $b) : null;
            $unknown = DB::selectOne('SELECT COUNT(*) AS n '.Metrics::FROM.' WHERE a.deleted_at IS NULL AND a.updated_by IS NOT NULL'.($hasHistory ? ' AND NOT EXISTS (SELECT 1 FROM attendance_audit_logs l WHERE l.attendance_id = a.id)' : '').' '.$w, $b);

            return [
                'history_changes' => (int) ($history->changes ?? 0),
                'late' => (int) ($history->late ?? 0),
                'with_reason' => (int) ($history->with_reason ?? 0),
                'since' => DB::table('attendance_audit_logs')->min('changed_at'),
                'unknown' => (int) $unknown->n,
            ];
        });

        return $this->shell($request, 'pages.attendance-integrity.amendments', 'Attendance Amendments', [
            'filters' => $f,
            'counts' => $counts,
            'sources' => AttendanceAudit::SOURCES,
        ]);
    }

    /** The attendance history (status changes, deletions, restores), newest first. */
    public function history(Request $request)
    {
        IntegrityAccess::authorize('view_audit_logs');
        $f = Filters::fromRequest($request);
        $size = min(max((int) $request->input('size', 50), 10), 200);
        $page = $request->boolean('all') ? null : max(1, (int) $request->input('page', 1));

        return response()->json(self::historyQuery($f, $page, $size, (string) $request->input('kind', 'status')));
    }

    public static function historyQuery(Filters $f, ?int $page, int $size = 50, string $kind = 'status'): array
    {
        return Metrics::uk(function () use ($f, $page, $size, $kind) {
            if (! Schema::hasTable('attendance_audit_logs')) {
                return ['total' => 0, 'last_page' => 1, 'data' => []];
            }
            $staff = $f->staff;
            $f->staff = null;
            [$w, $b] = $f->attendanceWhere(false);
            $f->staff = $staff;
            $cond = $kind === 'all' ? '' : ($kind === 'late' ? ' AND l.is_late_correction = 1' : " AND ((l.action = 'updated' AND l.old_status_id <> l.new_status_id) OR l.action IN ('deleted', 'force_deleted', 'restored'))");
            if ($staff) {
                $cond .= ' AND l.changed_by = ?';
                $b[] = $staff;
            }
            $from = "FROM attendance_audit_logs l JOIN attendances a ON a.id = l.attendance_id JOIN plans_date_lists d ON d.id = a.plans_date_list_id JOIN plans p ON p.id = d.plan_id
                WHERE 1=1 $cond $w";
            $total = (int) DB::selectOne("SELECT COUNT(*) AS n $from", $b)->n;
            $sql = "SELECT l.*, d.date, p.start_time, p.end_time $from ORDER BY l.id DESC";
            $sql .= $page !== null ? ' LIMIT '.$size.' OFFSET '.($page - 1) * $size : ' LIMIT 50000';
            $rows = DB::select($sql, $b);

            $statuses = Lookup::feedStatusNames();
            $people = Lookup::staff(array_map(fn ($r) => $r->changed_by, $rows));
            $students = Lookup::students(array_map(fn ($r) => $r->student_id, $rows));
            $sessions = Metrics::sessionLabels(array_map(fn ($r) => $r->plans_date_list_id, $rows));
            $code = fn ($id) => $id === null ? '—' : ($statuses[(int) $id]['code'] ?? (string) $id);

            return ['total' => $total, 'last_page' => max(1, (int) ceil($total / $size)), 'data' => array_map(fn ($r) => [
                'id' => (int) $r->id,
                'attendance_id' => (int) $r->attendance_id,
                'action' => $r->action,
                'change' => $r->action === 'updated' ? $code($r->old_status_id).' > '.$code($r->new_status_id) : ucfirst(str_replace('_', ' ', $r->action)),
                'student_id' => (int) $r->student_id,
                'student' => trim(($students[(int) $r->student_id]['registration_no'] ?? '').' '.($students[(int) $r->student_id]['name'] ?? '')),
                'class' => trim(($sessions[(int) $r->plans_date_list_id]['module'] ?? '').' · '.($sessions[(int) $r->plans_date_list_id]['group'] ?? ''), ' ·'),
                'class_date' => $r->date ? date('d/m/Y', strtotime($r->date)).' '.substr((string) $r->start_time, 0, 5) : '',
                'changed_by' => $r->changed_by ? ($people[(int) $r->changed_by]['name'] ?? 'User #'.$r->changed_by) : 'not recorded',
                'changed_by_id' => $r->changed_by ? (int) $r->changed_by : null,
                'changed_at' => date('d/m/Y H:i', strtotime($r->changed_at)),
                'after_class' => $r->minutes_after_class !== null ? ($r->minutes_after_class > 0 ? \App\Services\AttendanceIntegrity\Detection\TermScope::duration((int) $r->minutes_after_class).' after' : 'before the end') : '',
                'late' => (bool) $r->is_late_correction,
                'reason' => (string) $r->change_reason,
                'screen' => AttendanceAudit::SOURCES[$r->source] ?? (string) $r->source,
                'ip' => (string) $r->ip_address,
            ], $rows)];
        });
    }

    /** Finding types that exist for this rule under the filters (the type picker). */
    private function typesFor(?string $rule, Filters $f): array
    {
        $type = $f->type;
        $f->type = null;
        [$join, $where, $b] = $f->findingWhere();
        $f->type = $type;
        $out = [];
        foreach (DB::select("SELECT f.type, COUNT(*) AS n FROM aim_findings f $join WHERE 1=1 $where GROUP BY f.type ORDER BY n DESC", $b) as $r) {
            $out[$r->type] = (self::TYPES[$r->type] ?? $r->type).' ('.number_format($r->n).')';
        }

        return $out;
    }
}
