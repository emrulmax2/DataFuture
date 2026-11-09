<?php

namespace App\Services\AttendanceIntegrity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The Attendance Integrity filter bar, read from a request, with the SQL that
 * applies it. Every screen and export uses the same filters.
 *
 * Attendance figures filter on the class date; findings filter on when the
 * event happened (or, for findings about a class, its class date).
 *
 * Default term: the terms running today (else the latest term with classes).
 */
class Filters
{
    public array $terms = [];

    public ?int $academicYear = null;

    public ?int $venue = null;

    public ?int $course = null;

    public ?int $group = null;

    public ?int $module = null;

    public ?int $plan = null;

    public ?int $session = null;

    public ?int $status = null;

    public ?int $staff = null;

    public ?int $student = null;

    public ?string $from = null;

    public ?string $to = null;

    public ?string $risk = null;

    public ?string $caseStatus = null;

    public ?string $rule = null;

    public ?string $type = null;

    public ?string $basis = null;

    /** open | dismissed | case | all */
    public string $findingStatus = 'open';

    public string $search = '';

    public static function fromRequest(Request $request): self
    {
        $f = new self();
        $ids = $request->input('terms', []);
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        $f->terms = array_values(array_filter(array_map('intval', $ids)));
        foreach (['academic_year' => 'academicYear', 'venue' => 'venue', 'course' => 'course', 'group' => 'group', 'module' => 'module',
            'plan' => 'plan', 'session' => 'session', 'status' => 'status', 'staff' => 'staff', 'student' => 'student'] as $key => $prop) {
            $value = (int) $request->input($key);
            $f->$prop = $value > 0 ? $value : null;
        }
        $f->from = self::date($request->input('date_from'));
        $f->to = self::date($request->input('date_to'));
        if ($f->from && $f->to && $f->from > $f->to) {
            [$f->from, $f->to] = [$f->to, $f->from];
        }
        $f->risk = in_array($request->input('risk'), ['high', 'medium', 'low'], true) ? $request->input('risk') : null;
        $f->caseStatus = $request->filled('case_status') ? (string) $request->input('case_status') : null;
        $f->rule = $request->filled('rule') ? preg_replace('/[^a-z_]/', '', (string) $request->input('rule')) : null;
        $f->type = $request->filled('type') ? preg_replace('/[^a-z_]/', '', (string) $request->input('type')) : null;
        $f->basis = in_array($request->input('basis'), ['fact', 'anomaly', 'unavailable'], true) ? $request->input('basis') : null;
        $f->findingStatus = in_array($request->input('finding_status'), ['open', 'dismissed', 'case', 'all'], true) ? $request->input('finding_status') : 'open';
        $f->search = trim(mb_substr((string) $request->input('search', ''), 0, 100));

        if ($f->academicYear && ! $f->terms) {
            $f->terms = DB::table('term_declarations')->where('academic_year_id', $f->academicYear)->whereNull('deleted_at')->pluck('id')->map(fn ($v) => (int) $v)->all();
        }
        if (! $f->terms && ! $f->from && ! $f->to) {
            $f->terms = self::defaultTerms();
        }

        return $f;
    }

    /** Terms running today; else the latest term with classes. */
    public static function defaultTerms(): array
    {
        $today = now('Europe/London')->toDateString();
        $running = DB::table('term_declarations as td')->whereNull('td.deleted_at')
            ->where('td.start_date', '<=', $today)->where('td.end_date', '>=', $today)
            ->whereExists(fn ($q) => $q->selectRaw(1)->from('plans as p')->whereColumn('p.term_declaration_id', 'td.id'))
            ->pluck('td.id')->map(fn ($v) => (int) $v)->all();
        if ($running) {
            return $running;
        }
        $latest = DB::table('term_declarations as td')->whereNull('td.deleted_at')->where('td.start_date', '<=', $today)
            ->whereExists(fn ($q) => $q->selectRaw(1)->from('plans as p')->whereColumn('p.term_declaration_id', 'td.id'))
            ->orderByDesc('td.start_date')->value('td.id');

        return $latest ? [(int) $latest] : [];
    }

    /**
     * Conditions on attendance rows (aliases a, d = plans_date_lists, p = plans).
     * Returns [sql fragment starting with " AND", bindings].
     */
    public function attendanceWhere(bool $withStatus = true): array
    {
        $sql = '';
        $b = [];
        if ($this->terms) {
            $sql .= ' AND p.term_declaration_id IN ('.self::in($this->terms).')';
        }
        if ($this->from) {
            $sql .= ' AND d.date >= ?';
            $b[] = $this->from;
        }
        if ($this->to) {
            $sql .= ' AND d.date <= ?';
            $b[] = $this->to;
        }
        foreach (['venue' => 'p.venue_id', 'course' => 'p.course_id', 'group' => 'p.group_id', 'module' => 'p.module_creation_id', 'plan' => 'p.id', 'session' => 'd.id', 'student' => 'a.student_id'] as $prop => $col) {
            if ($this->$prop) {
                $sql .= " AND $col = ?";
                $b[] = $this->$prop;
            }
        }
        if ($withStatus && $this->status) {
            $sql .= ' AND a.attendance_feed_status_id = ?';
            $b[] = $this->status;
        }
        if ($this->staff) {
            $sql .= ' AND (a.created_by = ? OR a.updated_by = ?)';
            array_push($b, $this->staff, $this->staff);
        }

        return [$sql, $b];
    }

    /**
     * Conditions on findings (alias f; class filters join plans as fp).
     * Returns [extra joins, where fragment starting with " AND", bindings].
     */
    public function findingWhere(bool $withStatus = true): array
    {
        $join = '';
        $sql = ' AND f.cleared_at IS NULL';
        $b = [];
        if ($this->terms) {
            $sql .= ' AND f.term_declaration_id IN ('.self::in($this->terms).')';
        }
        if ($this->from) {
            $sql .= ' AND f.event_at >= ?';
            $b[] = $this->from.' 00:00:00';
        }
        if ($this->to) {
            $sql .= ' AND f.event_at <= ?';
            $b[] = $this->to.' 23:59:59';
        }
        if ($this->venue || $this->course || $this->group || $this->module) {
            $join .= ' JOIN plans fp ON fp.id = f.plan_id';
            foreach (['venue' => 'fp.venue_id', 'course' => 'fp.course_id', 'group' => 'fp.group_id', 'module' => 'fp.module_creation_id'] as $prop => $col) {
                if ($this->$prop) {
                    $sql .= " AND $col = ?";
                    $b[] = $this->$prop;
                }
            }
        }
        foreach (['plan' => 'f.plan_id', 'session' => 'f.plans_date_list_id', 'staff' => 'f.staff_user_id', 'student' => 'f.student_id'] as $prop => $col) {
            if ($this->$prop) {
                $sql .= " AND $col = ?";
                $b[] = $this->$prop;
            }
        }
        if ($this->rule) {
            $sql .= ' AND f.rule = ?';
            $b[] = $this->rule;
        }
        if ($this->type) {
            $sql .= ' AND f.type = ?';
            $b[] = $this->type;
        }
        if ($this->basis) {
            $sql .= ' AND f.basis = ?';
            $b[] = $this->basis;
        }
        if ($withStatus && $this->findingStatus !== 'all') {
            $sql .= ' AND f.status = ?';
            $b[] = $this->findingStatus;
        }
        if ($this->search !== '') {
            $sql .= ' AND f.summary LIKE ?';
            $b[] = '%'.$this->search.'%';
        }

        return [$join, $sql, $b];
    }

    /** What the filters are, in words, for exports and page headers. */
    public function describe(): array
    {
        $out = [];
        $out['Terms'] = $this->terms ? DB::table('term_declarations')->whereIn('id', $this->terms)->orderByDesc('start_date')->pluck('name')->implode(', ') : 'All';
        if ($this->academicYear) {
            $out['Academic year'] = (string) DB::table('academic_years')->where('id', $this->academicYear)->value('name');
        }
        if ($this->from || $this->to) {
            $out['Date range'] = ($this->from ? date('d/m/Y', strtotime($this->from)) : '…').' – '.($this->to ? date('d/m/Y', strtotime($this->to)) : '…');
        }
        $lookups = [
            'venue' => ['Campus', 'venues', 'name'], 'course' => ['Course', 'courses', 'name'], 'group' => ['Group', 'groups', 'name'],
            'module' => ['Module', 'module_creations', 'module_name'], 'status' => ['Attendance status', 'attendance_feed_statuses', 'name'],
        ];
        foreach ($lookups as $prop => [$label, $table, $col]) {
            if ($this->$prop) {
                $out[$label] = (string) DB::table($table)->where('id', $this->$prop)->value($col);
            }
        }
        if ($this->plan) {
            $out['Class'] = '#'.$this->plan;
        }
        if ($this->session) {
            $out['Class date'] = '#'.$this->session;
        }
        if ($this->staff) {
            $out['Staff'] = Lookup::staffName($this->staff);
        }
        if ($this->student) {
            $out['Student'] = Lookup::studentLabel($this->student);
        }
        if ($this->risk) {
            $out['Risk level'] = ucfirst($this->risk);
        }
        if ($this->caseStatus) {
            $out['Investigation status'] = $this->caseStatus;
        }
        if ($this->rule) {
            $out['Rule'] = \App\Services\AttendanceIntegrity\Detection\Scanner::ruleLabels()[$this->rule] ?? $this->rule;
        }
        if ($this->basis) {
            $out['Basis'] = \App\Models\AttendanceIntegrity\AimFinding::BASIS[$this->basis] ?? $this->basis;
        }
        if ($this->findingStatus !== 'open') {
            $out['Findings'] = $this->findingStatus === 'all' ? 'All' : ucfirst($this->findingStatus);
        }
        if ($this->search !== '') {
            $out['Search'] = $this->search;
        }

        return $out;
    }

    /** The filters as query parameters (for links and exports). */
    public function query(): array
    {
        return array_filter([
            'terms' => $this->terms ? implode(',', $this->terms) : null,
            'academic_year' => $this->academicYear, 'venue' => $this->venue, 'course' => $this->course, 'group' => $this->group,
            'module' => $this->module, 'plan' => $this->plan, 'session' => $this->session, 'status' => $this->status,
            'staff' => $this->staff, 'student' => $this->student, 'date_from' => $this->from, 'date_to' => $this->to,
            'risk' => $this->risk, 'case_status' => $this->caseStatus, 'rule' => $this->rule, 'type' => $this->type, 'basis' => $this->basis,
            'finding_status' => $this->findingStatus !== 'open' ? $this->findingStatus : null, 'search' => $this->search !== '' ? $this->search : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    public static function in(array $ids): string
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        return $ids ? implode(',', $ids) : '0';
    }

    private static function date($value): ?string
    {
        $value = (string) $value;
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $value, $m)) {
            $value = $m[3].'-'.$m[2].'-'.$m[1];
        }
        $d = \DateTime::createFromFormat('!Y-m-d', $value);

        return $d && $d->format('Y-m-d') === $value ? $value : null;
    }
}
