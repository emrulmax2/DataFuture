<?php

namespace App\Services\AttendanceIntegrity;

use Illuminate\Support\Facades\DB;

/** Names, labels and filter options for the Attendance Integrity screens. */
class Lookup
{
    private static array $staff = [];

    private static array $students = [];

    /** user id => ['name', 'job_title', 'department', 'employee_id'] */
    public static function staff(array $userIds): array
    {
        $missing = array_values(array_diff(array_filter(array_unique(array_map('intval', $userIds))), array_keys(self::$staff)));
        foreach (array_chunk($missing, 1000) as $chunk) {
            foreach (DB::select('SELECT u.id, u.name AS user_name, e.id AS employee_id, TRIM(CONCAT_WS(" ", e.first_name, e.last_name)) AS employee_name,
                    jt.name AS job_title, dp.name AS department
                FROM users u
                LEFT JOIN employees e ON e.id = (SELECT MAX(e2.id) FROM employees e2 WHERE e2.user_id = u.id)
                LEFT JOIN employments em ON em.id = (SELECT MAX(em2.id) FROM employments em2 WHERE em2.employee_id = e.id)
                LEFT JOIN employee_job_titles jt ON jt.id = em.employee_job_title_id
                LEFT JOIN departments dp ON dp.id = em.department_id
                WHERE u.id IN ('.Filters::in($chunk).')') as $r) {
                self::$staff[(int) $r->id] = [
                    'name' => $r->employee_name ?: (string) $r->user_name,
                    'job_title' => (string) $r->job_title,
                    'department' => (string) $r->department,
                    'employee_id' => $r->employee_id ? (int) $r->employee_id : null,
                ];
            }
        }

        return array_intersect_key(self::$staff, array_flip(array_map('intval', $userIds)));
    }

    public static function staffName(?int $userId): string
    {
        if (! $userId) {
            return '';
        }

        return self::staff([$userId])[$userId]['name'] ?? 'User #'.$userId;
    }

    /** student id => ['registration_no', 'name'] */
    public static function students(array $ids): array
    {
        $missing = array_values(array_diff(array_filter(array_unique(array_map('intval', $ids))), array_keys(self::$students)));
        foreach (array_chunk($missing, 1000) as $chunk) {
            foreach (DB::select('SELECT id, registration_no, TRIM(CONCAT_WS(" ", first_name, last_name)) AS name FROM students WHERE id IN ('.Filters::in($chunk).')') as $s) {
                self::$students[(int) $s->id] = ['registration_no' => (string) $s->registration_no, 'name' => (string) $s->name];
            }
        }

        return array_intersect_key(self::$students, array_flip(array_map('intval', $ids)));
    }

    public static function studentLabel(?int $id): string
    {
        if (! $id) {
            return '';
        }
        $s = self::students([$id])[$id] ?? null;

        return $s ? trim($s['registration_no'].' '.$s['name']) : 'Student #'.$id;
    }

    /** Options for the filter bar. */
    public static function filterOptions(): array
    {
        return [
            'terms' => DB::table('term_declarations as td')->whereNull('td.deleted_at')
                ->whereExists(fn ($q) => $q->selectRaw(1)->from('plans as p')->whereColumn('p.term_declaration_id', 'td.id'))
                ->orderByDesc('td.start_date')->get(['td.id', 'td.name'])->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name])->all(),
            'years' => DB::table('academic_years')->orderByDesc('from_date')->get(['id', 'name'])->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name])->all(),
            'venues' => DB::table('venues')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name'])->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name])->all(),
            'courses' => DB::table('courses')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name'])->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name])->all(),
            'statuses' => DB::table('attendance_feed_statuses')->orderBy('id')->get(['id', 'code', 'name'])->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->code.' - '.$r->name])->all(),
        ];
    }

    /** Groups and modules of the chosen terms (they depend on the term). */
    public static function termOptions(array $termIds): array
    {
        if (! $termIds) {
            return ['groups' => [], 'modules' => []];
        }
        $in = Filters::in($termIds);

        return [
            'groups' => DB::select("SELECT DISTINCT g.id, g.name FROM plans p JOIN `groups` g ON g.id = p.group_id WHERE p.term_declaration_id IN ($in) ORDER BY g.name"),
            'modules' => DB::select("SELECT DISTINCT mc.id, CONCAT(mc.module_name, IF(mc.code IS NULL OR mc.code = '', '', CONCAT(' (', mc.code, ')'))) AS name
                FROM plans p JOIN module_creations mc ON mc.id = p.module_creation_id WHERE p.term_declaration_id IN ($in) ORDER BY name"),
        ];
    }

    /** Staff search for the filter bar (name, email). */
    public static function searchStaff(string $q): array
    {
        $like = '%'.trim($q).'%';

        return DB::select('SELECT u.id, TRIM(COALESCE(NULLIF(CONCAT_WS(" ", e.first_name, e.last_name), ""), u.name)) AS name
            FROM users u LEFT JOIN employees e ON e.id = (SELECT MAX(e2.id) FROM employees e2 WHERE e2.user_id = u.id)
            WHERE (u.name LIKE ? OR u.email LIKE ? OR CONCAT_WS(" ", e.first_name, e.last_name) LIKE ?)
            ORDER BY name LIMIT 20', [$like, $like, $like]);
    }

    /** Student search for the filter bar (registration number, name). */
    public static function searchStudents(string $q): array
    {
        $like = '%'.trim($q).'%';

        return DB::select('SELECT id, CONCAT(registration_no, " ", TRIM(CONCAT_WS(" ", first_name, last_name))) AS name
            FROM students WHERE registration_no LIKE ? OR CONCAT_WS(" ", first_name, last_name) LIKE ? ORDER BY registration_no LIMIT 20', [$like, $like]);
    }

    public static function feedStatusNames(): array
    {
        static $names = null;

        return $names ??= DB::table('attendance_feed_statuses')->get(['id', 'code', 'name'])
            ->mapWithKeys(fn ($r) => [(int) $r->id => ['code' => (string) $r->code, 'name' => (string) $r->name]])->all();
    }
}
