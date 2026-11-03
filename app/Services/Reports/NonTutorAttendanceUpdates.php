<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Reports > Non-Tutor Attendance Updates: attendance rows that are now Present
 * (attendance_feed_status_id 1) and were last changed by a member of staff with no
 * link to the class (user, 3 Nov 2026).
 *
 * "Linked to the class" = the class tutor or personal tutor of the session's plan or
 * of the attendance's own plan (a student moved to another group keeps the old
 * plan's session), the tutor or personal tutor of the parent theory class, the cover
 * (proxy) tutor of that session, or whoever started that class.
 *
 * attendances.updated_by holds only the LAST person to change a row, so a change by
 * someone else is hidden once the tutor saves the row again: every count is a minimum.
 *
 * Times are UK clock time. Live writes UK clock digits into the TIMESTAMP column
 * updated_at as if they were UTC, so it is read with the session time zone at +00:00
 * (see onUkClock); created_at is a DATETIME holding the same UK digits.
 */
class NonTutorAttendanceUpdates
{
    /**
     * How the last change was made, worked out from attendance_captured_at: saving a
     * class register writes the save date into it, a single change on the student's
     * attendance page leaves it alone. A change on the day of the first entry
     * cannot be told apart.
     */
    public const HOW = [
        'register' => 'Register saved again',
        'single' => 'Single change',
        'same_day' => 'Same day as first entry',
        'unknown' => 'Not known',
    ];

    public const PRESENT = 1;

    private const ABSENT = 4;

    /**
     * "Needs review" (a red row): patterns that need an explanation, not proof.
     * A person is flagged when, among the absences they changed to Present (see base(),
     * "overturned"), a student was marked Present after their own excuse for that class
     * was rejected, or at least REVIEW_LONE_MIN of them are a lone Present between two
     * absences and that is REVIEW_LONE_SHARE or more of them (when class tutors fix
     * their own register it is about 4%, 2026 figures).
     */
    public const REVIEW_LONE_MIN = 10;

    public const REVIEW_LONE_SHARE = 0.15;

    public const REVIEW_RULE = 'Red = needs review, not proof. Flagged when a student was marked Present after their own excuse for that class was rejected, '
        .'or when at least 10 (and 15% or more) of the absences this person changed to Present are a lone Present between two absences of the same class '
        .'(about 4% when class tutors correct their own register). "Absence overturned" = the class register saved the student Absent (an absence notice was recorded) '
        .'and this person later changed it to Present on its own.';

    private const EXCUSE_STATUS = [0 => 'Pending', 1 => 'Rejected', 2 => 'Approved'];

    private const WEEK_MINUTES = 10080;

    /** What the date range can apply to. */
    public const DATE_ON = [
        'class' => 'Class date',
        'changed' => 'Date changed',
    ];

    /**
     * @param int[] $termIds term_declarations ids (may be empty when a date range is given)
     * @param array{on: string, from: string, to: string}|null $dates Y-m-d, both ends included;
     *        "class" = the session date, "changed" = the UK date of the last change
     */
    public function __construct(private array $termIds, private ?array $dates = null)
    {
        $this->termIds = array_values(array_unique(array_map('intval', $termIds)));
    }

    /** One row per member of staff, most rows first. */
    public function summary(): array
    {
        return $this->onUkClock(function () {
            [$base, $bindings] = $this->base();

            $rows = DB::select("SELECT x.updated_by AS user_id,
                    COUNT(*) AS rows_updated,
                    COUNT(DISTINCT x.plan_id) AS classes,
                    COUNT(DISTINCT x.plans_date_list_id) AS sessions,
                    COUNT(DISTINCT x.student_id) AS students,
                    SUM(x.how = 'single') AS single_changes,
                    SUM(x.how = 'register') AS register_saves,
                    SUM(x.how = 'same_day') AS same_day,
                    SUM(x.minutes_after_class <= 60) AS within_hour,
                    SUM(x.minutes_after_class > 60 AND x.minutes_after_class <= ".self::WEEK_MINUTES.") AS within_week,
                    SUM(x.minutes_after_class > ".self::WEEK_MINUTES.") AS after_week,
                    MIN(x.updated_at) AS first_update,
                    MAX(x.updated_at) AS last_update
                FROM ($base) x
                GROUP BY x.updated_by
                ORDER BY rows_updated DESC, x.updated_by", $bindings);

            $staff = $this->staff(array_map(fn ($r) => (int) $r->user_id, $rows));
            $flags = $this->reviewFlags();

            return array_map(function ($r) use ($staff, $flags) {
                $person = $staff[(int) $r->user_id] ?? null;

                return ($flags[(int) $r->user_id] ?? self::reviewFlag([])) + [
                    'user_id' => (int) $r->user_id,
                    'name' => $person['name'] ?? 'User #'.$r->user_id,
                    'job_title' => $person['job_title'] ?? '',
                    'department' => $person['department'] ?? '',
                    'profile_url' => $person['profile_url'] ?? null,
                    'rows_updated' => (int) $r->rows_updated,
                    'classes' => (int) $r->classes,
                    'sessions' => (int) $r->sessions,
                    'students' => (int) $r->students,
                    'single_changes' => (int) $r->single_changes,
                    'register_saves' => (int) $r->register_saves,
                    'same_day' => (int) $r->same_day,
                    'within_hour' => (int) $r->within_hour,
                    'within_week' => (int) $r->within_week,
                    'after_week' => (int) $r->after_week,
                    'first_update' => self::ukDateTime($r->first_update),
                    'last_update' => self::ukDateTime($r->last_update),
                ];
            }, $rows);
        });
    }

    /**
     * One member of staff's changes grouped by student: one row per student with how
     * many of their attendance rows were changed, most first.
     */
    public function students(int $userId, string $search = ''): array
    {
        return $this->onUkClock(function () use ($userId, $search) {
            [$from, $bindings] = $this->detailFrom($userId, $search);

            $rows = DB::select("SELECT x.student_id,
                    MAX(st.registration_no) AS registration_no,
                    MAX(TRIM(CONCAT_WS(' ', st.first_name, st.last_name))) AS student_name,
                    COUNT(*) AS rows_changed,
                    COUNT(DISTINCT x.plans_date_list_id) AS sessions,
                    COUNT(DISTINCT x.plan_id) AS classes,
                    SUM(x.how = 'single') AS single_changes,
                    SUM(x.how = 'register') AS register_saves,
                    SUM(x.minutes_after_class > ".self::WEEK_MINUTES.") AS after_week,
                    MIN(x.updated_at) AS first_update,
                    MAX(x.updated_at) AS last_update
                $from
                GROUP BY x.student_id
                ORDER BY rows_changed DESC, registration_no", $bindings);

            return array_map(fn ($r) => [
                'student_id' => (int) $r->student_id,
                'registration_no' => (string) $r->registration_no,
                'student_name' => (string) $r->student_name,
                'student_url' => $r->student_id ? route('student.show', $r->student_id) : null,
                'rows_changed' => (int) $r->rows_changed,
                'sessions' => (int) $r->sessions,
                'classes' => (int) $r->classes,
                'single_changes' => (int) $r->single_changes,
                'register_saves' => (int) $r->register_saves,
                'after_week' => (int) $r->after_week,
                'first_update' => self::ukDateTime($r->first_update),
                'last_update' => self::ukDateTime($r->last_update),
            ], $rows);
        });
    }

    /**
     * One member of staff's changes grouped by who teaches the class: its tutor, or
     * its personal tutor when the class has no tutor (theory classes always have a
     * tutor, tutorials only a personal tutor). Most changes first.
     */
    public function tutors(int $userId, string $search): array
    {
        return $this->onUkClock(function () use ($userId, $search) {
            [$from, $bindings] = $this->detailFrom($userId, $search);
            $tutor = self::tutorColumn();

            $rows = DB::select("SELECT $tutor AS tutor_id,
                    COUNT(*) AS rows_changed,
                    COUNT(DISTINCT x.plans_date_list_id) AS sessions,
                    COUNT(DISTINCT x.plan_id) AS classes,
                    COUNT(DISTINCT x.student_id) AS students,
                    SUM(x.how = 'single') AS single_changes,
                    SUM(x.how = 'register') AS register_saves,
                    SUM(x.minutes_after_class > ".self::WEEK_MINUTES.") AS after_week,
                    COUNT(DISTINCT CASE WHEN COALESCE(p.tutor_id, 0) > 0 THEN x.plan_id END) AS tutor_classes,
                    COUNT(DISTINCT CASE WHEN COALESCE(p.tutor_id, 0) = 0 THEN x.plan_id END) AS pt_classes,
                    MIN(x.updated_at) AS first_update,
                    MAX(x.updated_at) AS last_update
                $from
                GROUP BY $tutor
                ORDER BY rows_changed DESC, tutor_id", $bindings);

            $staff = $this->staff(array_map(fn ($r) => (int) $r->tutor_id, $rows));
            $none = 'No tutor or personal tutor on the class';

            return array_map(function ($r) use ($staff, $none) {
                $person = $staff[(int) $r->tutor_id] ?? null;

                return [
                    'tutor_id' => (int) $r->tutor_id,
                    'name' => (int) $r->tutor_id ? ($person['name'] ?? 'User #'.$r->tutor_id) : $none,
                    'job_title' => $person['job_title'] ?? '',
                    'department' => $person['department'] ?? '',
                    'profile_url' => $person['profile_url'] ?? null,
                    'rows_changed' => (int) $r->rows_changed,
                    'sessions' => (int) $r->sessions,
                    'classes' => (int) $r->classes,
                    'students' => (int) $r->students,
                    'single_changes' => (int) $r->single_changes,
                    'register_saves' => (int) $r->register_saves,
                    'after_week' => (int) $r->after_week,
                    'tutor_classes' => (int) $r->tutor_classes,
                    'pt_classes' => (int) $r->pt_classes,
                    'first_update' => self::ukDateTime($r->first_update),
                    'last_update' => self::ukDateTime($r->last_update),
                ];
            }, $rows);
        });
    }

    /**
     * The class dates (sessions) of one class teacher (tutor, else personal tutor) in
     * which this member of staff changed attendance, newest class first, with what the
     * register shows about the class itself.
     */
    public function sessions(int $userId, string $search, int $tutorId): array
    {
        return $this->onUkClock(function () use ($userId, $search, $tutorId) {
            [$from, $bindings] = $this->detailFrom($userId, $search, ['tutor' => $tutorId]);

            // Who started the class (the tutor's class start record), its times, and how
            // many rows the class register has in total.
            $info = fn ($column) => "(SELECT ai.$column FROM attendance_informations ai
                WHERE ai.plans_date_list_id = x.plans_date_list_id AND ai.deleted_at IS NULL ORDER BY ai.id LIMIT 1)";

            $rows = DB::select("SELECT x.plans_date_list_id AS session_id,
                    MAX(x.session_date) AS session_date, MAX(p.start_time) AS start_time, MAX(p.end_time) AS end_time,
                    MAX(mc.module_name) AS module_name, MAX(mc.code) AS module_code, MAX(g.name) AS group_name,
                    MAX(p.class_type) AS class_type, MAX(td.name) AS term_name, MAX(d.status) AS session_status,
                    MAX(TRIM(COALESCE(p.virtual_room, '')) <> '') AS is_online,
                    MAX(p.tutor_id) AS tutor_id, MAX(p.personal_tutor_id) AS personal_tutor_id, MAX(d.proxy_tutor_id) AS proxy_tutor_id,
                    COUNT(*) AS rows_changed,
                    SUM(x.how = 'single') AS single_changes,
                    SUM(x.how = 'register') AS register_saves,
                    SUM(x.how = 'same_day') AS same_day,
                    SUM(x.minutes_after_class > ".self::WEEK_MINUTES.") AS after_week,
                    MIN(x.updated_at) AS first_update,
                    MAX(x.updated_at) AS last_update,
                    (SELECT COUNT(*) FROM attendances a2 WHERE a2.plans_date_list_id = x.plans_date_list_id AND a2.deleted_at IS NULL) AS register_size,
                    {$info('tutor_id')} AS started_by,
                    {$info('start_time')} AS started_at,
                    {$info('end_time')} AS ended_at
                $from
                GROUP BY x.plans_date_list_id
                ORDER BY session_date DESC, start_time DESC, session_id DESC", $bindings);

            $people = [];
            foreach ($rows as $r) {
                array_push($people, (int) $r->tutor_id, (int) $r->personal_tutor_id, (int) $r->proxy_tutor_id, (int) $r->started_by);
            }
            $staff = $this->staff($people);
            $name = fn ($id) => $id ? ($staff[(int) $id]['name'] ?? 'User #'.$id) : '';

            return array_map(fn ($r) => [
                'session_id' => (int) $r->session_id,
                'session_date' => $r->session_date ? date('d/m/Y', strtotime($r->session_date)) : '',
                'session_day' => $r->session_date ? date('D', strtotime($r->session_date)) : '',
                'class_time' => trim(substr((string) $r->start_time, 0, 5).' - '.substr((string) $r->end_time, 0, 5), ' -'),
                'module' => trim((string) $r->module_name.($r->module_code ? ' ('.$r->module_code.')' : '')),
                'group' => (string) $r->group_name,
                'class_type' => (string) $r->class_type,
                'class_mode' => self::classMode((int) $r->is_online),
                'term' => (string) $r->term_name,
                'session_status' => (string) $r->session_status,
                'teacher_role' => (int) $r->tutor_id ? 'Tutor' : 'Personal tutor',
                'class_tutor' => $name($r->tutor_id),
                'personal_tutor' => $name($r->personal_tutor_id),
                'cover_tutor' => $name($r->proxy_tutor_id),
                'started_by' => $name($r->started_by),
                'started_at' => $r->started_at ? substr((string) $r->started_at, 0, 5) : '',
                'ended_at' => $r->ended_at ? substr((string) $r->ended_at, 0, 5) : '',
                'rows_changed' => (int) $r->rows_changed,
                'register_size' => (int) $r->register_size,
                'single_changes' => (int) $r->single_changes,
                'register_saves' => (int) $r->register_saves,
                'same_day' => (int) $r->same_day,
                'after_week' => (int) $r->after_week,
                'first_update' => self::ukDateTime($r->first_update),
                'last_update' => self::ukDateTime($r->last_update),
            ], $rows);
        });
    }

    /**
     * The rows one member of staff changed, newest change first. $filters narrows them
     * to one student (student_id) or one class date (session_id).
     *
     * @return array{total: int, rows: array}
     */
    public function details(int $userId, string $search = '', ?int $page = null, int $size = 50, array $filters = []): array
    {
        return $this->onUkClock(function () use ($userId, $search, $page, $size, $filters) {
            [$from, $bindings] = $this->detailFrom($userId, $search, $filters);

            $total = (int) DB::selectOne("SELECT COUNT(*) AS n $from", $bindings)->n;

            $sql = "SELECT x.*, st.registration_no, st.first_name AS student_first_name, st.last_name AS student_last_name,
                    mc.module_name, mc.code AS module_code, g.name AS group_name, c.name AS course_name, td.name AS term_name,
                    p.class_type, p.tutor_id, p.personal_tutor_id, d.proxy_tutor_id,
                    TRIM(COALESCE(p.virtual_room, '')) <> '' AS is_online,
                    fs.code AS status_code, fs.name AS status_name
                $from
                ORDER BY x.updated_at DESC, x.id DESC";

            if ($page !== null) {
                $sql .= ' LIMIT '.max(1, $size).' OFFSET '.(max(1, $page) - 1) * max(1, $size);
            }

            $rows = DB::select($sql, $bindings);

            $people = [];
            foreach ($rows as $r) {
                array_push($people, (int) $r->updated_by, (int) $r->created_by, (int) $r->tutor_id, (int) $r->personal_tutor_id, (int) $r->proxy_tutor_id);
            }
            $staff = $this->staff(array_values(array_filter(array_unique($people))));
            $name = fn ($id) => $id ? ($staff[(int) $id]['name'] ?? 'User #'.$id) : '';

            return [
                'total' => $total,
                'rows' => array_map(function ($r) use ($name) {
                    return [
                        'attendance_id' => (int) $r->id,
                        'session_date' => $r->session_date ? date('d/m/Y', strtotime($r->session_date)) : '',
                        'session_day' => $r->session_date ? date('D', strtotime($r->session_date)) : '',
                        'session_iso' => (string) $r->session_date,
                        'class_time' => trim(substr((string) $r->start_time, 0, 5).' - '.substr((string) $r->end_time, 0, 5), ' -'),
                        'term' => (string) $r->term_name,
                        'course' => (string) $r->course_name,
                        'module' => trim((string) $r->module_name.($r->module_code ? ' ('.$r->module_code.')' : '')),
                        'group' => (string) $r->group_name,
                        'class_type' => (string) $r->class_type,
                        'class_mode' => self::classMode((int) $r->is_online),
                        'class_tutor' => $name($r->tutor_id),
                        'personal_tutor' => $name($r->personal_tutor_id),
                        'cover_tutor' => $name($r->proxy_tutor_id),
                        'student_id' => (int) $r->student_id,
                        'registration_no' => (string) $r->registration_no,
                        'student_name' => trim($r->student_first_name.' '.$r->student_last_name),
                        'student_url' => $r->student_id ? route('student.show', $r->student_id) : null,
                        'status' => trim(($r->status_code ? $r->status_code.' - ' : '').$r->status_name),
                        'how' => self::HOW[$r->how] ?? $r->how,
                        'first_entered_by' => $name($r->created_by),
                        'first_entered_at' => self::ukDateTime($r->created_at),
                        'changed_by' => $name($r->updated_by),
                        'updated_at' => self::ukDateTime($r->updated_at),
                        'minutes_after_class' => $r->minutes_after_class === null ? null : (int) $r->minutes_after_class,
                        'after_class' => self::afterClass($r->minutes_after_class),
                        'note' => (string) $r->note,
                    ];
                }, $rows),
            ];
        });
    }

    /**
     * FROM ... WHERE for one member of staff's rows, joined to what the details list
     * shows and searches (student, module, group). $filters: student_id, session_id,
     * tutor => users.id of who teaches the class (see tutorColumn), 0 for nobody.
     */
    private function detailFrom(int $userId, string $search, array $filters = []): array
    {
        [$base, $bindings] = $this->base($userId);

        $from = "FROM ($base) x
            JOIN plans p ON p.id = x.plan_id
            JOIN plans_date_lists d ON d.id = x.plans_date_list_id
            LEFT JOIN students st ON st.id = x.student_id
            LEFT JOIN module_creations mc ON mc.id = p.module_creation_id
            LEFT JOIN `groups` g ON g.id = p.group_id
            LEFT JOIN courses c ON c.id = p.course_id
            LEFT JOIN term_declarations td ON td.id = p.term_declaration_id
            LEFT JOIN attendance_feed_statuses fs ON fs.id = x.status_id";

        $where = [];

        if (isset($filters['student_id'])) {
            $where[] = 'x.student_id = ?';
            $bindings[] = (int) $filters['student_id'];
        }

        if (isset($filters['session_id'])) {
            $where[] = 'x.plans_date_list_id = ?';
            $bindings[] = (int) $filters['session_id'];
        }

        if (isset($filters['tutor'])) {
            $where[] = self::tutorColumn().' = ?';
            $bindings[] = (int) $filters['tutor'];
        }

        if (! empty($filters['overturned'])) {
            $where[] = 'x.overturned = 1';
        }

        $search = trim($search);
        if ($search !== '') {
            $where[] = "(st.registration_no LIKE ? OR CONCAT_WS(' ', st.first_name, st.last_name) LIKE ?
                OR mc.module_name LIKE ? OR mc.code LIKE ? OR g.name LIKE ?)";
            $like = '%'.$search.'%';
            array_push($bindings, $like, $like, $like, $like, $like);
        }

        if ($where) {
            $from .= ' WHERE '.implode(' AND ', $where);
        }

        return [$from, $bindings];
    }

    /**
     * Every attendance row in the chosen terms and date range that is now Present and
     * was last changed by someone with no link to the class.
     */
    private function base(?int $userId = null): array
    {
        $bindings = [];
        $scope = '';
        if ($this->termIds) {
            $scope .= ' AND p.term_declaration_id IN ('.implode(',', array_fill(0, count($this->termIds), '?')).')';
            array_push($bindings, ...$this->termIds);
        }
        if ($this->dates && $this->dates['on'] === 'class') {
            $scope .= ' AND d.date BETWEEN ? AND ?';
            array_push($bindings, $this->dates['from'], $this->dates['to']);
        } elseif ($this->dates) {
            // updated_at holds UK clock digits and is read at +00:00 (onUkClock).
            $scope .= ' AND a.updated_at >= ? AND a.updated_at < ?';
            array_push($bindings, $this->dates['from'].' 00:00:00', date('Y-m-d', strtotime($this->dates['to'].' +1 day')).' 00:00:00');
        }

        $sql = "SELECT a.id, a.updated_by, a.created_by, a.student_id, a.plans_date_list_id, d.plan_id,
                d.date AS session_date, p.start_time, p.end_time, a.attendance_feed_status_id AS status_id,
                a.note, a.created_at, a.updated_at,
                (a.notofication_date IS NOT NULL AND (a.sms_notification = 1 OR a.email_notification = 1)) AS overturned,
                CASE
                    WHEN a.attendance_captured_at IS NULL OR a.created_at IS NULL THEN 'unknown'
                    WHEN a.attendance_captured_at = DATE(a.updated_at)
                        THEN IF(DATE(a.created_at) = DATE(a.updated_at), 'same_day', 'register')
                    ELSE 'single'
                END AS how,
                TIMESTAMPDIFF(MINUTE, TIMESTAMP(d.date, p.end_time), a.updated_at) AS minutes_after_class
            FROM attendances a
            JOIN plans_date_lists d ON d.id = a.plans_date_list_id
            JOIN plans p ON p.id = d.plan_id
            LEFT JOIN plans pa ON pa.id = a.plan_id
            LEFT JOIN plans pp ON pp.id = p.parent_id AND p.parent_id > 0
            WHERE a.deleted_at IS NULL
              AND a.updated_by IS NOT NULL
              AND a.attendance_feed_status_id = ".self::PRESENT."
              $scope
              AND a.updated_by NOT IN (
                    COALESCE(p.tutor_id, 0), COALESCE(p.personal_tutor_id, 0), COALESCE(d.proxy_tutor_id, 0),
                    COALESCE(pa.tutor_id, 0), COALESCE(pa.personal_tutor_id, 0),
                    COALESCE(pp.tutor_id, 0), COALESCE(pp.personal_tutor_id, 0))
              AND NOT EXISTS (SELECT 1 FROM attendance_informations ai
                    WHERE ai.plans_date_list_id = d.id AND ai.tutor_id = a.updated_by AND ai.deleted_at IS NULL)";

        if ($userId !== null) {
            $sql .= ' AND a.updated_by = ?';
            $bindings[] = $userId;
        }

        return [$sql, $bindings];
    }

    /**
     * "Needs review" tab: one line per student whose absence this person changed to
     * Present, with the lone Presents, the Presents after a rejected excuse, and the
     * student's attendance in those classes (whole class) now and without these changes.
     */
    public function reviewStudents(int $userId, string $search = ''): array
    {
        return $this->onUkClock(function () use ($userId, $search) {
            $rows = $this->overturnedRows($userId, $search);
            if (! $rows) {
                return [];
            }

            $by = [];
            foreach ($rows as $r) {
                $sid = (int) $r->student_id;
                $s = $by[$sid] ?? ['overturned' => 0, 'lone' => 0, 'after_rejected' => 0, 'plans' => [], 'first' => null, 'last' => null];
                $s['overturned']++;
                $s['lone'] += self::isLone($r) ? 1 : 0;
                $s['after_rejected'] += (int) $r->excuse_status === 1 ? 1 : 0;
                $s['plans'][(int) $r->plan_id] = ($s['plans'][(int) $r->plan_id] ?? 0) + 1;
                $s['first'] = $s['first'] === null || $r->updated_at < $s['first'] ? $r->updated_at : $s['first'];
                $s['last'] = $s['last'] === null || $r->updated_at > $s['last'] ? $r->updated_at : $s['last'];
                $by[$sid] = $s;
            }

            $attendance = $this->classAttendance(array_keys($by), array_values(array_unique(array_map(fn ($r) => (int) $r->plan_id, $rows))));
            $students = $this->studentInfo(array_keys($by));

            $out = [];
            foreach ($by as $sid => $s) {
                [$total, $attended] = [0, 0];
                foreach (array_keys($s['plans']) as $planId) {
                    $total += $attendance[$sid][$planId]['total'] ?? 0;
                    $attended += $attendance[$sid][$planId]['attended'] ?? 0;
                }
                $out[] = [
                    'student_id' => $sid,
                    'registration_no' => $students[$sid]['registration_no'] ?? '',
                    'student_name' => $students[$sid]['name'] ?? '',
                    'student_url' => route('student.show', $sid),
                    'overturned' => $s['overturned'],
                    'lone' => $s['lone'],
                    'after_rejected' => $s['after_rejected'],
                    'classes' => count($s['plans']),
                    'class_dates' => $total,
                    'attended' => $attended,
                    'rate_now' => $total ? round(100 * $attended / $total, 1) : null,
                    'rate_without' => $total ? round(100 * ($attended - $s['overturned']) / $total, 1) : null,
                    'first_update' => self::ukDateTime($s['first']),
                    'last_update' => self::ukDateTime($s['last']),
                ];
            }

            usort($out, fn ($a, $b) => [$b['after_rejected'], $b['lone'], $b['overturned'], $a['registration_no']]
                <=> [$a['after_rejected'], $a['lone'], $a['overturned'], $b['registration_no']]);

            return $out;
        });
    }

    /**
     * "Needs review" tab, a student opened: every class date of the classes in which this
     * person changed the student's absence to Present (the whole class, not only the
     * changed dates), oldest first, so a lone Present between absences shows. Without a
     * student id: every such student (the Excel file).
     */
    public function reviewTimeline(int $userId, ?int $studentId = null, string $search = ''): array
    {
        return $this->onUkClock(function () use ($userId, $studentId, $search) {
            $rows = $this->overturnedRows($userId, $search, $studentId ? ['student_id' => $studentId] : []);
            if (! $rows) {
                return [];
            }

            $mine = [];
            $pairs = [];
            foreach ($rows as $r) {
                $mine[(int) $r->id] = $r;
                $pairs[(int) $r->student_id][(int) $r->plan_id] = true;
            }
            $studentIds = array_keys($pairs);
            $planIds = array_values(array_unique(array_map(fn ($r) => (int) $r->plan_id, $rows)));

            $inS = implode(',', array_fill(0, count($studentIds), '?'));
            $inP = implode(',', array_fill(0, count($planIds), '?'));
            $list = DB::select("SELECT a.id, a.student_id, a.created_by, a.created_at, a.updated_by, a.updated_at, a.note,
                    (a.notofication_date IS NOT NULL AND (a.sms_notification = 1 OR a.email_notification = 1)) AS absence_notice,
                    d.id AS session_id, d.date AS session_date, d.status AS session_status, d.plan_id,
                    p.start_time, p.end_time, p.class_type, p.tutor_id, p.personal_tutor_id,
                    TRIM(COALESCE(p.virtual_room, '')) <> '' AS is_online,
                    mc.module_name, mc.code AS module_code, g.name AS group_name, td.name AS term_name,
                    fs.code AS status_code, fs.name AS status_name, fs.attendance_count, a.attendance_feed_status_id AS status_id
                FROM attendances a
                JOIN plans_date_lists d ON d.id = a.plans_date_list_id
                JOIN plans p ON p.id = d.plan_id
                LEFT JOIN module_creations mc ON mc.id = p.module_creation_id
                LEFT JOIN `groups` g ON g.id = p.group_id
                LEFT JOIN term_declarations td ON td.id = p.term_declaration_id
                LEFT JOIN attendance_feed_statuses fs ON fs.id = a.attendance_feed_status_id
                WHERE a.deleted_at IS NULL AND a.student_id IN ($inS) AND d.plan_id IN ($inP)
                ORDER BY a.student_id, d.plan_id, d.date, p.start_time, a.id", array_merge($studentIds, $planIds));
            $list = array_values(array_filter($list, fn ($r) => isset($pairs[(int) $r->student_id][(int) $r->plan_id])));

            $excuses = $this->excuses($studentIds, array_values(array_unique(array_map(fn ($r) => (int) $r->session_id, $list))));
            $people = [];
            foreach ($list as $r) {
                array_push($people, (int) $r->created_by, (int) $r->updated_by, (int) $r->tutor_id, (int) $r->personal_tutor_id);
            }
            foreach ($excuses as $e) {
                $people[] = (int) $e['actioned_by'];
            }
            $staff = $this->staff(array_values(array_filter(array_unique($people))));
            $name = fn ($id) => $id ? ($staff[(int) $id]['name'] ?? 'User #'.$id) : '';
            $students = $this->studentInfo($studentIds);

            return array_map(function ($r) use ($mine, $excuses, $name, $students, $userId) {
                $own = $mine[(int) $r->id] ?? null;
                $excuse = $excuses[$r->student_id.'-'.$r->session_id] ?? null;

                return [
                    'attendance_id' => (int) $r->id,
                    'student_id' => (int) $r->student_id,
                    'registration_no' => $students[(int) $r->student_id]['registration_no'] ?? '',
                    'student_name' => $students[(int) $r->student_id]['name'] ?? '',
                    'plan_id' => (int) $r->plan_id,
                    'module' => trim((string) $r->module_name.($r->module_code ? ' ('.$r->module_code.')' : '')),
                    'group' => (string) $r->group_name,
                    'class_type' => (string) $r->class_type,
                    'class_mode' => self::classMode((int) $r->is_online),
                    'term' => (string) $r->term_name,
                    'class_tutor' => $name($r->tutor_id),
                    'personal_tutor' => $name($r->personal_tutor_id),
                    'session_id' => (int) $r->session_id,
                    'session_iso' => (string) $r->session_date,
                    'session_date' => $r->session_date ? date('d/m/Y', strtotime($r->session_date)) : '',
                    'session_day' => $r->session_date ? date('D', strtotime($r->session_date)) : '',
                    'class_time' => trim(substr((string) $r->start_time, 0, 5).' - '.substr((string) $r->end_time, 0, 5), ' -'),
                    'session_status' => (string) $r->session_status,
                    'status' => trim(($r->status_code ? $r->status_code.' - ' : '').$r->status_name),
                    'status_code' => (string) $r->status_code,
                    'attended' => (int) $r->attendance_count === 1,
                    'absent' => (int) $r->status_id === self::ABSENT,
                    'first_entered_by' => $name($r->created_by),
                    'first_entered_at' => self::ukDateTime($r->created_at),
                    'changed_by' => $name($r->updated_by),
                    'changed_at' => $r->updated_by ? self::ukDateTime($r->updated_at) : '',
                    'note' => (string) $r->note,
                    // This person changed the student's absence to Present.
                    'overturned_here' => $own !== null,
                    // Someone else did (same proof: absence notice still on a Present row).
                    'overturned_by_other' => $own === null && (int) $r->absence_notice === 1 && (int) $r->status_id === self::PRESENT
                        && (int) $r->updated_by && (int) $r->updated_by !== $userId,
                    'lone' => $own !== null && self::isLone($own),
                    'excuse' => $excuse ? [
                        'status' => self::EXCUSE_STATUS[$excuse['status']] ?? 'Unknown',
                        'type' => $excuse['type'],
                        'reason' => $excuse['reason'],
                        'remarks' => $excuse['remarks'],
                        'actioned_by' => $name($excuse['actioned_by']),
                        'actioned_at' => self::ukDateTime($excuse['actioned_at']),
                    ] : null,
                ];
            }, $list);
        });
    }

    /** Review numbers and the red flag for every member of staff in the search. */
    private function reviewFlags(): array
    {
        $by = [];
        foreach ($this->overturnedRows() as $r) {
            $by[(int) $r->updated_by][] = $r;
        }

        return array_map(fn ($rows) => self::reviewFlag($rows), $by);
    }

    /** @param object[] $rows one person's overturned rows from overturnedRows() */
    private static function reviewFlag(array $rows): array
    {
        $lone = 0;
        $known = 0;
        $rejected = 0;
        $perStudent = [];
        foreach ($rows as $r) {
            $known += ($r->prev_status !== null && $r->next_status !== null) ? 1 : 0;
            $lone += self::isLone($r) ? 1 : 0;
            $rejected += (int) $r->excuse_status === 1 ? 1 : 0;
            $perStudent[(int) $r->student_id] = ($perStudent[(int) $r->student_id] ?? 0) + 1;
        }
        $share = $known ? $lone / $known : 0;
        $repeat = count(array_filter($perStudent, fn ($n) => $n >= 3));

        $reasons = [];
        if ($rejected) {
            $reasons[] = $rejected.' marked Present after the student\'s own excuse for that class was rejected';
        }
        if ($lone >= self::REVIEW_LONE_MIN && $share >= self::REVIEW_LONE_SHARE) {
            $reasons[] = $lone.' lone Presents between two absences ('.round(100 * $share).'% of the absences overturned; about 4% when tutors fix their own register)';
        }

        return [
            'overturned' => count($rows),
            'lone' => $lone,
            'lone_share' => round(100 * $share, 1),
            'after_rejected' => $rejected,
            'repeat_students' => $repeat,
            'review' => (bool) $reasons,
            'review_reasons' => $reasons,
        ];
    }

    private static function isLone(object $r): bool
    {
        return (int) $r->prev_status === self::ABSENT && (int) $r->next_status === self::ABSENT;
    }

    /**
     * Absences this person (or, without a user id, anyone in the search) changed to
     * Present, with the student's status in the class date before and after (same
     * class) and the best status of any excuse the student sent for that class date
     * (0 pending, 1 rejected, 2 approved; null = none).
     */
    private function overturnedRows(?int $userId = null, string $search = '', array $filters = []): array
    {
        if ($userId === null) {
            [$base, $bindings] = $this->base();
            $from = "FROM ($base) x WHERE x.overturned = 1";
        } else {
            [$from, $bindings] = $this->detailFrom($userId, $search, $filters + ['overturned' => true]);
        }

        // The student's status in the same class's date before / after this one.
        $neighbour = fn ($op, $dir) => "(SELECT a2.attendance_feed_status_id FROM attendances a2
                JOIN plans_date_lists d2 ON d2.id = a2.plans_date_list_id
                WHERE a2.student_id = x.student_id AND d2.plan_id = x.plan_id AND d2.date $op x.session_date AND a2.deleted_at IS NULL
                ORDER BY d2.date $dir LIMIT 1)";
        $prev = $neighbour('<', 'DESC');
        $next = $neighbour('>', 'ASC');

        return DB::select("SELECT x.id, x.updated_by, x.student_id, x.plan_id, x.plans_date_list_id, x.session_date, x.updated_at,
                $prev AS prev_status,
                $next AS next_status,
                (SELECT MAX(e.status) FROM attendance_excuses e
                    JOIN attendance_excuse_days ed ON ed.attendance_excuse_id = e.id
                    WHERE e.student_id = x.student_id AND ed.plans_date_list_id = x.plans_date_list_id
                      AND e.deleted_at IS NULL AND ed.deleted_at IS NULL) AS excuse_status
            $from", $bindings);
    }

    /** [student_id][plan_id] => ['total' => class dates, 'attended' => counted as attended]. */
    private function classAttendance(array $studentIds, array $planIds): array
    {
        if (! $studentIds || ! $planIds) {
            return [];
        }
        $inS = implode(',', array_fill(0, count($studentIds), '?'));
        $inP = implode(',', array_fill(0, count($planIds), '?'));
        $out = [];
        foreach (DB::select("SELECT a.student_id, d.plan_id, COUNT(*) AS total, SUM(fs.attendance_count = 1) AS attended
                FROM attendances a
                JOIN plans_date_lists d ON d.id = a.plans_date_list_id
                LEFT JOIN attendance_feed_statuses fs ON fs.id = a.attendance_feed_status_id
                WHERE a.deleted_at IS NULL AND a.student_id IN ($inS) AND d.plan_id IN ($inP)
                GROUP BY a.student_id, d.plan_id", array_merge($studentIds, $planIds)) as $r) {
            $out[(int) $r->student_id][(int) $r->plan_id] = ['total' => (int) $r->total, 'attended' => (int) $r->attended];
        }

        return $out;
    }

    /** The latest excuse each student sent for each class date: "student-session" => fields. */
    private function excuses(array $studentIds, array $sessionIds): array
    {
        if (! $studentIds || ! $sessionIds) {
            return [];
        }
        $inS = implode(',', array_fill(0, count($studentIds), '?'));
        $inD = implode(',', array_fill(0, count($sessionIds), '?'));
        $out = [];
        foreach (DB::select("SELECT e.id, e.student_id, ed.plans_date_list_id, e.status, e.attendance_types, e.reason, e.remarks, e.actioned_by, e.actioned_at
                FROM attendance_excuses e
                JOIN attendance_excuse_days ed ON ed.attendance_excuse_id = e.id
                WHERE e.student_id IN ($inS) AND ed.plans_date_list_id IN ($inD) AND e.deleted_at IS NULL AND ed.deleted_at IS NULL
                ORDER BY e.id", array_merge($studentIds, $sessionIds)) as $r) {
            $out[$r->student_id.'-'.$r->plans_date_list_id] = [
                'status' => (int) $r->status,
                'type' => (string) $r->attendance_types,
                'reason' => trim((string) $r->reason),
                'remarks' => trim((string) $r->remarks),
                'actioned_by' => (int) $r->actioned_by,
                'actioned_at' => $r->actioned_at,
            ];
        }

        return $out;
    }

    /** student_id => registration number and name. */
    private function studentInfo(array $studentIds): array
    {
        if (! $studentIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($studentIds), '?'));
        $out = [];
        foreach (DB::select("SELECT id, registration_no, TRIM(CONCAT_WS(' ', first_name, last_name)) AS name FROM students WHERE id IN ($in)", $studentIds) as $r) {
            $out[(int) $r->id] = ['registration_no' => (string) $r->registration_no, 'name' => (string) $r->name];
        }

        return $out;
    }

    /** A class with a virtual room (plans.virtual_room, a meeting link) is online. */
    private static function classMode(int $isOnline): string
    {
        return $isOnline ? 'Online' : 'Physical';
    }

    /** Name, job title, department and HR profile link for each user id. */
    private function staff(array $userIds): array
    {
        $userIds = array_values(array_filter(array_unique(array_map('intval', $userIds))));
        if (! $userIds) {
            return [];
        }

        $in = implode(',', array_fill(0, count($userIds), '?'));
        $rows = DB::select("SELECT u.id AS user_id, u.name AS user_name, e.id AS employee_id,
                TRIM(CONCAT_WS(' ', e.first_name, e.last_name)) AS employee_name,
                jt.name AS job_title, dp.name AS department
            FROM users u
            LEFT JOIN employees e ON e.id = (SELECT MAX(e2.id) FROM employees e2 WHERE e2.user_id = u.id)
            LEFT JOIN employments em ON em.id = (SELECT MAX(em2.id) FROM employments em2 WHERE em2.employee_id = e.id)
            LEFT JOIN employee_job_titles jt ON jt.id = em.employee_job_title_id
            LEFT JOIN departments dp ON dp.id = em.department_id
            WHERE u.id IN ($in)", $userIds);

        $staff = [];
        foreach ($rows as $r) {
            $staff[(int) $r->user_id] = [
                'name' => $r->employee_name ?: (string) $r->user_name,
                'job_title' => (string) $r->job_title,
                'department' => (string) $r->department,
                'profile_url' => $r->employee_id ? route('profile.employee.view', $r->employee_id) : null,
            ];
        }

        return $staff;
    }

    /** Who teaches the class in detailFrom()'s plan p: its tutor, else its personal tutor (users.id, 0 when none). */
    private static function tutorColumn(): string
    {
        return 'COALESCE(NULLIF(p.tutor_id, 0), p.personal_tutor_id, 0)';
    }

    /**
     * Runs the queries with the session time zone at +00:00 so the TIMESTAMP columns
     * come back as the UK clock time live wrote (this dev database's own zone is +06),
     * then puts the connection's zone back.
     */
    private function onUkClock(callable $run)
    {
        $previous = DB::selectOne('SELECT @@session.time_zone AS tz')->tz;
        DB::statement("SET time_zone = '+00:00'");

        try {
            return $run();
        } finally {
            DB::statement('SET time_zone = '.DB::getPdo()->quote($previous));
        }
    }

    /** Reformats the UK clock digits as they are, with no time-zone maths (no BST shifts). */
    private static function ukDateTime($value): string
    {
        $date = $value ? \DateTime::createFromFormat('Y-m-d H:i:s', substr((string) $value, 0, 19), new \DateTimeZone('UTC')) : false;

        return $date ? $date->format('d/m/Y H:i') : '';
    }

    /** "3d 4h after", "45m after", "Before class end" */
    private static function afterClass($minutes): string
    {
        if ($minutes === null) {
            return '';
        }

        $minutes = (int) $minutes;
        if ($minutes < 0) {
            return 'Before class end';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $mins = $minutes % 60;

        $text = $days ? $days.'d '.$hours.'h' : ($hours ? $hours.'h '.$mins.'m' : $mins.'m');

        return $text.' after';
    }
}
