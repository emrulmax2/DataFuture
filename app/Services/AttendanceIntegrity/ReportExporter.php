<?php

namespace App\Services\AttendanceIntegrity;

use App\Http\Controllers\AttendanceIntegrity\FindingController;
use App\Models\AttendanceIntegrity\AimCase;
use App\Models\AttendanceIntegrity\AimFinding;
use App\Services\AttendanceIntegrity\Detection\Scanner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The ten Attendance Integrity reports (plus the drill-down lists), as
 * [title, notes, headings, rows]. Exported to Excel, CSV or PDF by
 * ReportController; every file starts with the filters, when and by whom it was
 * made, notes, and a Basis column wherever facts and anomalies are mixed.
 */
class ReportExporter
{
    public const REPORTS = [
        'term_summary' => ['Term Attendance Integrity Summary', 'One row per term: attendance, entries made late, changes, deletions, findings by rule, risk levels and cases.'],
        'staff' => ['Staff Attendance Anomaly Report', 'One row per member of staff: records entered and changed, late entries, Absent > Present changes, bulk entry, classes they do not teach, patterns and risk score.'],
        'students' => ['Student Attendance Anomaly Report', 'One row per student: recorded attendance, attendance %, late entries, changes, staff entering and correcting, excuses and risk score.'],
        'retrospective' => ['Retrospective Attendance Report', 'Class dates whose attendance was first entered long after the class ended, with the delay and who entered it.'],
        'amendments' => ['Attendance Amendment History', 'Status changes from the attendance history, and older Absent > Present changes known from the absence notice, with who, when and the reason.'],
        'bulk' => ['Suspicious Bulk Entry Report', 'Bursts of separate entries across different class dates within seconds.'],
        'patterns' => ['Staff–Student Pattern Report', 'Staff changing attendance in classes they do not teach, recurring staff and student pairs, and lone Presents.'],
        'timetable' => ['Timetable Conflict Report', 'Attendance at cancelled or deleted class dates, two campus classes at once, or classes the student is not assigned to.'],
        'deleted' => ['Deleted Attendance Report', 'Every deleted attendance record, with who deleted it where that was recorded.'],
        'cases' => ['Investigation Case Register', 'Every investigation case with its subject, status, investigator, score and outcome.'],
    ];

    public const BASIS_NOTE = 'Basis: "Confirmed fact" = read directly from the records; "Anomaly" = a pattern that needs an explanation, not proof of wrongdoing; "Evidence not available" = the information was never recorded.';

    public static function build(string $report, Filters $f, array $options = []): array
    {
        switch ($report) {
            case 'term_summary':
                return self::termSummary($f);
            case 'staff':
                return self::staff($f);
            case 'students':
                return self::students($f);
            case 'amendments':
                return self::amendments($f);
            case 'deleted':
                return self::deleted($f);
            case 'cases':
                return self::cases($f);
            case 'records':
                return self::records($f, (string) ($options['metric'] ?? 'recorded'));
            case 'findings':
                return self::findings($f, 'All Findings', 'Every finding under the filters.');
            default:
                $rule = ['retrospective' => 'retrospective', 'bulk' => 'bulk_entry', 'patterns' => 'staff_student', 'timetable' => 'timetable'][$report] ?? null;
                abort_unless($rule, 404);
                $f->rule = $rule;

                return self::findings($f, self::REPORTS[$report][0], self::REPORTS[$report][1]);
        }
    }

    private static function termSummary(Filters $f): array
    {
        $terms = $f->terms ?: Filters::defaultTerms();
        $labels = Scanner::ruleLabels();
        $rows = [];
        foreach (DB::table('term_declarations')->whereIn('id', $terms)->orderBy('start_date')->get(['id', 'name']) as $term) {
            $tf = clone $f;
            $tf->terms = [(int) $term->id];
            $d = Metrics::dashboard($tf);
            $byRule = collect($d['by_rule'])->keyBy('rule');
            $c = $d['cards'];
            $rows[] = array_merge([
                $term->name, $c['opportunities'], $c['recorded'], $c['rate'], $c['late_status'], $c['retrospective'], $c['modified'], $c['deleted'],
                $c['absent_to_present'], $c['bulk'],
            ], array_map(fn ($k) => $byRule[$k]['findings'] ?? 0, array_keys($labels)), [
                $d['staff_bands']['high'], $d['staff_bands']['medium'], $d['student_bands']['high'], $d['student_bands']['medium'],
                $d['cases']['open'], $d['cases']['substantiated'], $d['cases']['no_issue'],
            ]);
        }

        return [
            'title' => self::REPORTS['term_summary'][0],
            'notes' => [
                'Attendance % counts the statuses set in Attendance Settings > Attendance %.',
                '"Changed after entry" counts records whose last editor is recorded; before the attendance history was switched on the previous value is unknown.',
                self::BASIS_NOTE,
            ],
            'headings' => array_merge(['Term', 'Scheduled (class dates x students)', 'Recorded', 'Attendance %', 'Late status', 'Entered >24h late', 'Changed after entry', 'Deleted',
                'Absent > Present (evidence held)', 'Bulk entry bursts'], array_values($labels), ['Staff High', 'Staff Medium', 'Students High', 'Students Medium', 'Open cases', 'Substantiated', 'No issue / process error']),
            'rows' => $rows,
        ];
    }

    private static function staff(Filters $f): array
    {
        $rows = array_map(fn ($r) => [
            $r['name'], $r['job_title'], $r['department'], $r['created'], $r['changed'], $r['late_entered'], $r['positive'], $r['negative'], $r['deleted'],
            $r['students'], $r['classes'], $r['bulk'], $r['unauthorised'], $r['other_classes_privileged'], $r['pairs'], $r['lone'], $r['rejected_excuse'],
            $r['changes_per_100'], $r['peer_changes_per_100'], $r['score'], $r['band'] ? ucfirst($r['band']) : '',
        ], Metrics::staffRows($f));

        return [
            'title' => self::REPORTS['staff'][0],
            'notes' => [
                'Entered / changed are facts from the records; each record keeps only its first and last editor, so counts are minimums.',
                'Absent > Present uses the attendance history and, before it, the absence notice the register records. Classes they do not teach are scored only without the Edit Attendance privilege (today\'s privileges).',
                'Changes per 100 = records changed per 100 records handled; peer = the median of everyone listed. Scores rank what to look at first; they are not proof.',
            ],
            'headings' => ['Staff', 'Job title', 'Department', 'Records entered', 'Records changed', 'Entered >24h late', 'Absent > Present', 'Present > Absent', 'Deleted (recorded)',
                'Students', 'Classes', 'Bulk bursts', 'Records in classes not taught (no privilege)', 'Records in classes not taught (has Edit Attendance)', 'Recurring student pairs',
                'Lone Presents findings', 'Present after rejected excuse', 'Changes per 100', 'Peer median', 'Risk score', 'Risk level'],
            'rows' => $rows,
        ];
    }

    private static function students(Filters $f): array
    {
        $rows = array_map(fn ($r) => [
            $r['registration_no'], $r['name'], $r['scheduled'], $r['recorded'], $r['present'], $r['absent'], $r['late'], $r['excused'], $r['rate'],
            $r['late_entered'], $r['positive'], $r['negative'], $r['staff_entering'], $r['staff_correcting'], $r['excuses'], $r['flagged'], $r['score'],
            $r['band'] ? ucfirst($r['band']) : '',
        ], Metrics::studentRows($f));

        return [
            'title' => self::REPORTS['students'][0],
            'notes' => [
                'Scheduled = class dates held so far for the classes the student is assigned to. There is no card swipe or QR check-in; a missing record is never treated as proof of absence.',
                'Scores rank what to look at first; they are not proof.',
            ],
            'headings' => ['Registration no', 'Student', 'Scheduled', 'Recorded', 'Present (P/O)', 'Absent', 'Late', 'Excused (E/M/H)', 'Attendance %', 'Entered >24h late',
                'Absent > Present', 'Present > Absent', 'Staff entering', 'Staff correcting', 'Excuses', 'Findings', 'Risk score', 'Risk level'],
            'rows' => $rows,
        ];
    }

    private static function findings(Filters $f, string $title, string $about): array
    {
        $data = FindingController::query($f, null)['data'];
        $rows = array_map(fn ($r) => [
            $r['event_at'], $r['term'], $r['rule_label'], $r['type_label'], $r['basis_label'], $r['points'], $r['summary'], $r['staff'], $r['student'],
            $r['records'], AimFinding::STATUSES[$r['status']] ?? $r['status'], $r['dismiss_note'], self::evidenceText($r['evidence']),
        ], $data);

        return [
            'title' => $title,
            'notes' => [$about, 'Points are investigative priorities, not proof of misconduct.', self::BASIS_NOTE],
            'headings' => ['When', 'Term', 'Rule', 'Finding', 'Basis', 'Points', 'Summary', 'Staff', 'Student', 'Records', 'Status', 'Dismissed because', 'Evidence'],
            'rows' => $rows,
        ];
    }

    private static function amendments(Filters $f): array
    {
        $rows = [];
        foreach (\App\Http\Controllers\AttendanceIntegrity\FindingController::historyQuery($f, null, 50, 'status')['data'] as $h) {
            $rows[] = ['Attendance history', $h['changed_at'], $h['student'], $h['class'], $h['class_date'], $h['change'], $h['changed_by'], $h['after_class'],
                $h['late'] ? 'Yes' : '', $h['reason'], $h['screen'], 'Confirmed fact'];
        }
        $f->rule = 'amendment';
        foreach (FindingController::query($f, null)['data'] as $r) {
            if (! in_array($r['type'], ['absent_to_present_notice', 'absent_to_present_own_class'], true) || ! empty($r['evidence']['history_id'])) {
                continue;
            }
            $rows[] = ['Absence notice (before the history)', $r['evidence']['changed_at'] ?? $r['event_at'], $r['student'], '', '', 'Absent > Present',
                $r['staff'], '', '', '', '', 'Confirmed fact (previous status from the absence notice)'];
        }

        $since = Schema::hasTable('attendance_audit_logs') ? DB::table('attendance_audit_logs')->min('changed_at') : null;

        return [
            'title' => self::REPORTS['amendments'][0],
            'notes' => [
                'The attendance history holds every change with its before and after from '.($since ? date('d/m/Y H:i', strtotime($since)) : 'the day it is switched on').' (UK time). Nothing before that is reconstructed.',
                'Older Absent > Present changes are listed only where the record still carries the absence notice the class register writes when it saves a student Absent.',
                'Other older edits are not listed: the previous value was not recorded.',
            ],
            'headings' => ['Source', 'Changed (UK)', 'Student', 'Class', 'Class date', 'Change', 'Changed by', 'After the class', 'Late correction', 'Reason given', 'Screen', 'Basis'],
            'rows' => $rows,
        ];
    }

    private static function deleted(Filters $f): array
    {
        $data = Metrics::records($f, 'deleted', null)['data'];
        $ids = array_map(fn ($r) => $r['attendance_id'], $data);
        $by = [];
        if ($ids && Schema::hasTable('attendance_audit_logs')) {
            foreach (array_chunk($ids, 1000) as $chunk) {
                foreach (DB::select("SELECT attendance_id, changed_by, change_reason FROM attendance_audit_logs WHERE action IN ('deleted', 'force_deleted') AND attendance_id IN (".Filters::in($chunk).') ORDER BY id') as $l) {
                    $by[(int) $l->attendance_id] = [Lookup::staffName($l->changed_by ? (int) $l->changed_by : null) ?: 'not recorded', (string) $l->change_reason];
                }
            }
        }
        foreach (array_chunk($ids, 1000) as $chunk) {
            foreach (DB::select('SELECT attendance_id, attendance_deleted_by FROM student_visits WHERE attendance_id IN ('.Filters::in($chunk).') AND attendance_deleted_by IS NOT NULL') as $v) {
                $by[(int) $v->attendance_id] ??= [Lookup::staffName((int) $v->attendance_deleted_by), 'Student visit deleted'];
            }
        }
        $rows = array_map(fn ($r) => [
            $r['class_date'], $r['class_time'], $r['module'], $r['group'], $r['registration_no'], $r['student_name'], $r['status'], $r['first_entered_by'], $r['first_entered_at'],
            $r['deleted_at'], $by[$r['attendance_id']][0] ?? 'not recorded', $by[$r['attendance_id']][1] ?? '', isset($by[$r['attendance_id']]) ? 'Confirmed fact' : 'Evidence not available (who deleted it)',
        ], $data);

        return [
            'title' => self::REPORTS['deleted'][0],
            'notes' => ['Who deleted a record is known only from the attendance history (from the day it was switched on) or from a student visit record; otherwise "not recorded".', self::BASIS_NOTE],
            'headings' => ['Class date', 'Time', 'Module', 'Group', 'Registration no', 'Student', 'Status when deleted', 'First entered by', 'First entered (UK)', 'Deleted (UK)', 'Deleted by', 'Reason', 'Basis'],
            'rows' => $rows,
        ];
    }

    private static function cases(Filters $f): array
    {
        $q = AimCase::query()->orderBy('id');
        if ($f->caseStatus) {
            $q->where('investigation_status', $f->caseStatus);
        }
        $cases = $q->get();
        $people = Lookup::staff(array_merge($cases->pluck('assigned_investigator')->all(), $cases->pluck('reviewed_by')->all(), $cases->pluck('created_by')->all()));
        $terms = DB::table('term_declarations')->whereIn('id', $cases->pluck('term_declaration_id')->filter())->pluck('name', 'id');
        $rows = $cases->map(fn ($c) => [
            $c->case_number, date('d/m/Y', strtotime($c->created_at)), $terms[$c->term_declaration_id] ?? '', AimCase::SUBJECT_TYPES[$c->subject_type] ?? $c->subject_type,
            $c->subject_label, $c->risk_score, $c->summary, $c->investigation_status, $people[$c->assigned_investigator]['name'] ?? '',
            $people[$c->created_by]['name'] ?? '', $people[$c->reviewed_by]['name'] ?? '', $c->reviewed_at ? date('d/m/Y H:i', strtotime($c->reviewed_at)) : '', (string) $c->outcome,
        ])->all();

        return [
            'title' => self::REPORTS['cases'][0],
            'notes' => ['Opening a case records a concern to investigate; it is not a finding of misconduct. Outcomes are recorded by the investigator.'],
            'headings' => ['Case', 'Opened', 'Term', 'Subject type', 'Subject', 'Score', 'Summary', 'Status', 'Investigator', 'Opened by', 'Reviewed by', 'Reviewed (UK)', 'Outcome'],
            'rows' => $rows,
        ];
    }

    private static function records(Filters $f, string $metric): array
    {
        $metric = array_key_exists($metric, Metrics::RECORD_METRICS) ? $metric : 'recorded';
        $rows = array_map(fn ($r) => [
            $r['class_date'], $r['class_day'], $r['class_time'], $r['module'], $r['group'], $r['class_type'], $r['teacher'], $r['registration_no'], $r['student_name'],
            $r['status'], $r['first_entered_by'], $r['first_entered_at'], $r['entered_after'], $r['last_changed_by'], $r['last_changed_at'],
            $r['changer_teaches_class'] === null ? '' : ($r['changer_teaches_class'] ? 'Yes' : 'No'), $r['deleted_at'], $r['note'],
        ], Metrics::records($f, $metric, null)['data']);

        return [
            'title' => Metrics::RECORD_METRICS[$metric],
            'notes' => ['Each record keeps only its first and last editor; earlier changes appear in the attendance history from the day it was switched on.', 'Times are UK time.'],
            'headings' => ['Class date', 'Day', 'Time', 'Module', 'Group', 'Class type', 'Class teacher', 'Registration no', 'Student', 'Status', 'First entered by', 'First entered (UK)',
                'Entered after the class', 'Last changed by', 'Last changed (UK)', 'Changer teaches the class', 'Deleted (UK)', 'Note'],
            'rows' => $rows,
        ];
    }

    /** Evidence JSON as "key: value; ..." text for a spreadsheet cell. */
    public static function evidenceText(array $evidence): string
    {
        $parts = [];
        foreach ($evidence as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_map(fn ($v) => is_array($v) ? implode(' / ', array_map(fn ($x) => is_scalar($x) ? (string) $x : '', $v)) : (string) $v, array_slice($value, 0, 10)));
            } elseif (is_bool($value)) {
                $value = $value ? 'yes' : 'no';
            }
            if ($value === null || $value === '') {
                continue;
            }
            $parts[] = str_replace('_', ' ', $key).': '.$value;
        }

        return mb_substr(implode('; ', $parts), 0, 2000);
    }
}
