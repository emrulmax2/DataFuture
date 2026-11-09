<?php

namespace App\Services\AttendanceIntegrity\Detection;

use App\Services\AttendanceIntegrity\AttendanceSettings;
use Illuminate\Support\Facades\DB;

/**
 * What every detection rule needs for one attendance term: the term, the status
 * sets from Attendance Settings, the shared FROM clause and the "linked to the
 * class" test, plus look-ups for the readable summaries.
 *
 * A class date belongs to the term of its class (plans.term_declaration_id),
 * the same way every attendance report groups it.
 *
 * Runs with the session time zone at +00:00 (see Scanner), so TIMESTAMP columns
 * read back as the UK clock time live wrote; created_at is a DATETIME already.
 */
class TermScope
{
    public int $termId;

    public string $name = '';

    public ?string $start = null;

    public ?string $end = null;

    /** @var int[] counted as attended */
    public array $attended;

    /** @var int[] in class (not excused) */
    public array $present;

    /** @var int[] attended but not present (excused) */
    public array $excused;

    public const ABSENT = 4;

    /**
     * Attendance rows of this term, with the class date (d), its class (p), the
     * row's own class (pa: students moved to another group keep the old class
     * date) and the theory class of a tutorial or seminar (pp).
     */
    public const FROM = 'FROM attendances a
        JOIN plans_date_lists d ON d.id = a.plans_date_list_id
        JOIN plans p ON p.id = d.plan_id
        LEFT JOIN plans pa ON pa.id = a.plan_id
        LEFT JOIN plans pp ON pp.id = p.parent_id AND p.parent_id > 0';

    private array $sessionCache = [];

    private array $staffCache = [];

    public function __construct(int $termId)
    {
        $this->termId = $termId;
        $term = DB::table('term_declarations')->where('id', $termId)->first(['name', 'start_date', 'end_date']);
        if ($term) {
            $this->name = (string) $term->name;
            $this->start = $term->start_date ? substr((string) $term->start_date, 0, 10) : null;
            $this->end = $term->end_date ? substr((string) $term->end_date, 0, 10) : null;
        }
        $this->attended = AttendanceSettings::ids('att_attended_statuses') ?: [1, 2, 5, 6, 7, 8];
        $this->present = AttendanceSettings::ids('att_present_statuses') ?: [1, 2, 5];
        $this->excused = array_values(array_diff($this->attended, $this->present));
    }

    /** "1,2,5" for an IN (...) list of ids (always at least one value). */
    public static function in(array $ids): string
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        return $ids ? implode(',', $ids) : '0';
    }

    /**
     * SQL that is true when the user in $column teaches the class date: tutor or
     * personal tutor of the class, of the row's own class or of the theory class,
     * the cover tutor of that date, or whoever started the class.
     */
    public static function linked(string $column): string
    {
        return "($column IN (COALESCE(p.tutor_id, 0), COALESCE(p.personal_tutor_id, 0), COALESCE(d.proxy_tutor_id, 0),
                COALESCE(pa.tutor_id, 0), COALESCE(pa.personal_tutor_id, 0), COALESCE(pp.tutor_id, 0), COALESCE(pp.personal_tutor_id, 0))
            OR EXISTS (SELECT 1 FROM attendance_informations ai WHERE ai.plans_date_list_id = d.id AND ai.tutor_id = $column AND ai.deleted_at IS NULL))";
    }

    /** Module, group, date and times of class dates, for summaries. */
    public function sessions(array $ids): array
    {
        $missing = array_values(array_diff(array_unique(array_map('intval', $ids)), array_keys($this->sessionCache)));
        foreach (array_chunk($missing, 1000) as $chunk) {
            $rows = DB::select('SELECT d.id, d.date, d.status, d.name AS kind, p.id AS plan_id, p.start_time, p.end_time, p.class_type,
                    p.tutor_id, p.personal_tutor_id, TRIM(COALESCE(p.virtual_room, "")) <> "" AS online,
                    mc.module_name, mc.code AS module_code, g.name AS group_name
                FROM plans_date_lists d
                JOIN plans p ON p.id = d.plan_id
                LEFT JOIN module_creations mc ON mc.id = p.module_creation_id
                LEFT JOIN `groups` g ON g.id = p.group_id
                WHERE d.id IN ('.self::in($chunk).')');
            foreach ($rows as $r) {
                $this->sessionCache[(int) $r->id] = [
                    'date' => substr((string) $r->date, 0, 10),
                    'status' => (string) $r->status,
                    'kind' => (string) $r->kind,
                    'plan_id' => (int) $r->plan_id,
                    'time' => trim(substr((string) $r->start_time, 0, 5).'-'.substr((string) $r->end_time, 0, 5), '-'),
                    'class_type' => (string) $r->class_type,
                    'online' => (bool) $r->online,
                    'teacher_id' => (int) ($r->tutor_id ?: $r->personal_tutor_id),
                    'module' => trim((string) $r->module_name.($r->module_code ? ' ('.$r->module_code.')' : '')),
                    'group' => (string) $r->group_name,
                ];
            }
        }

        return array_intersect_key($this->sessionCache, array_flip(array_map('intval', $ids)));
    }

    /** "Business Law (A/123) · GROUP-A · 14/10/2026 09:30-11:30" */
    public function sessionLabel(int $sessionId): string
    {
        $s = $this->sessions([$sessionId])[$sessionId] ?? null;
        if (! $s) {
            return 'class date #'.$sessionId;
        }

        return trim($s['module'].' · '.$s['group'].' · '.date('d/m/Y', strtotime($s['date'])).' '.$s['time'], ' ·');
    }

    /** user id => display name (employee name, else user name). */
    public function staffNames(array $userIds): array
    {
        $missing = array_values(array_diff(array_filter(array_unique(array_map('intval', $userIds))), array_keys($this->staffCache)));
        foreach (array_chunk($missing, 1000) as $chunk) {
            foreach (DB::select('SELECT u.id, u.name, TRIM(CONCAT_WS(" ", e.first_name, e.last_name)) AS employee_name
                    FROM users u LEFT JOIN employees e ON e.id = (SELECT MAX(e2.id) FROM employees e2 WHERE e2.user_id = u.id)
                    WHERE u.id IN ('.self::in($chunk).')') as $r) {
                $this->staffCache[(int) $r->id] = $r->employee_name ?: (string) $r->name;
            }
        }

        return array_intersect_key($this->staffCache, array_flip(array_map('intval', $userIds)));
    }

    public function staffName(?int $userId): string
    {
        if (! $userId) {
            return 'unknown';
        }

        return $this->staffNames([$userId])[$userId] ?? 'User #'.$userId;
    }

    /** "3d 4h" / "45m" */
    public static function duration(int $minutes): string
    {
        $minutes = max(0, $minutes);
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);

        return $days ? $days.'d '.$hours.'h' : ($hours ? $hours.'h '.($minutes % 60).'m' : ($minutes % 60).'m');
    }
}
