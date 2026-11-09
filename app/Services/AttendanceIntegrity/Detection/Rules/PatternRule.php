<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Models\User;
use App\Services\AttendanceIntegrity\AttendanceGuard;
use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\TermScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rule E: staff and student patterns.
 *
 * - Entering or changing attendance in classes the person does not teach (not
 *   the tutor, personal tutor, cover tutor, theory-class tutor or the person
 *   who started the class). Scored once per person per term, and only when they
 *   do not hold Module Contents > Edit Attendance (checked against current
 *   privileges); privilege holders are listed without points.
 * - The same student's absence changed to Present by the same person again and
 *   again in a term ("absence changed to Present" = proven by the attendance
 *   history or by the absence notice, see AmendmentRule).
 * - Lone Presents: the student was Absent at the class date before and after.
 */
class PatternRule extends Rule
{
    private array $privCache = [];

    public function key(): string
    {
        return 'staff_student';
    }

    public function label(): string
    {
        return 'Staff and student patterns';
    }

    protected function setting(): string
    {
        return 'att_pattern_enabled';
    }

    public function detect(TermScope $t): array
    {
        return array_merge($this->outsideOwnClasses($t), $this->overturnedPatterns($t));
    }

    private function outsideOwnClasses(TermScope $t): array
    {
        $rows = DB::select('SELECT x.u, COUNT(*) AS rows_n, COUNT(DISTINCT x.s) AS sessions, COUNT(DISTINCT x.pl) AS classes,
                COUNT(DISTINCT x.st) AS students, MIN(x.ts) AS first_at, MAX(x.ts) AS last_at,
                SUM(x.kind = "entered") AS entered, SUM(x.kind = "changed") AS changed
            FROM (
                SELECT a.created_by AS u, a.plans_date_list_id AS s, d.plan_id AS pl, a.student_id AS st, a.created_at AS ts, "entered" AS kind
                '.TermScope::FROM.'
                WHERE p.term_declaration_id = ? AND a.created_by IS NOT NULL AND NOT '.TermScope::linked('a.created_by').'
                UNION ALL
                SELECT a.updated_by, a.plans_date_list_id, d.plan_id, a.student_id, a.updated_at, "changed"
                '.TermScope::FROM.'
                WHERE p.term_declaration_id = ? AND a.updated_by IS NOT NULL AND NOT (a.created_by <=> a.updated_by)
                  AND NOT '.TermScope::linked('a.updated_by').'
            ) x
            GROUP BY x.u', [$t->termId, $t->termId]);

        $t->staffNames(array_map(fn ($r) => $r->u, $rows));
        $out = [];
        foreach ($rows as $r) {
            $userId = (int) $r->u;
            $privileged = $this->hasEditAttendance($userId);
            $who = $t->staffName($userId);
            $out[] = $this->finding($t, [
                'type' => $privileged ? 'other_classes_privileged' : 'other_classes',
                'basis' => 'fact',
                'weight' => $privileged ? 0 : $this->points('att_points_non_linked'),
                'event_key' => 'u:'.$userId.':t:'.$t->termId.':other-classes',
                'staff_user_id' => $userId,
                'event_at' => $r->last_at,
                'rows_count' => (int) $r->rows_n,
                'summary' => $who.' entered or changed '.number_format($r->rows_n).' records in '.$r->classes.' '.((int) $r->classes === 1 ? 'class' : 'classes')
                    .' they do not teach'.($privileged ? ' (holds Edit Attendance)' : ''),
                'evidence' => [
                    'records' => (int) $r->rows_n,
                    'first_entered' => (int) $r->entered,
                    'last_changed' => (int) $r->changed,
                    'classes' => (int) $r->classes,
                    'class_dates' => (int) $r->sessions,
                    'students' => (int) $r->students,
                    'from' => self::ukDateTime($r->first_at),
                    'to' => self::ukDateTime($r->last_at),
                    'holds_edit_attendance' => $privileged,
                    'note' => 'Edit Attendance is checked against today\'s privileges. Each record remembers only its first and last editor.',
                ],
            ]);
        }

        return $out;
    }

    private function overturnedPatterns(TermScope $t): array
    {
        $neighbours = '(SELECT a2.attendance_feed_status_id FROM attendances a2 JOIN plans_date_lists d2 ON d2.id = a2.plans_date_list_id
                WHERE a2.student_id = a.student_id AND d2.plan_id = d.plan_id AND d2.date < d.date AND a2.deleted_at IS NULL ORDER BY d2.date DESC LIMIT 1) AS prev_status,
            (SELECT a2.attendance_feed_status_id FROM attendances a2 JOIN plans_date_lists d2 ON d2.id = a2.plans_date_list_id
                WHERE a2.student_id = a.student_id AND d2.plan_id = d.plan_id AND d2.date > d.date AND a2.deleted_at IS NULL ORDER BY d2.date ASC LIMIT 1) AS next_status';

        $set = [];
        foreach (DB::select('SELECT a.id, a.student_id, a.plans_date_list_id, d.plan_id, a.updated_by AS changer,
                CASE WHEN '.TermScope::linked('a.updated_by').' THEN 1 ELSE 0 END AS linked, '.$neighbours.'
            '.TermScope::FROM.'
            WHERE p.term_declaration_id = ? AND a.deleted_at IS NULL AND a.updated_by IS NOT NULL
              AND a.attendance_feed_status_id IN ('.TermScope::in($t->present).')
              AND a.notofication_date IS NOT NULL AND (a.sms_notification = 1 OR a.email_notification = 1)', [$t->termId]) as $r) {
            $set[(int) $r->id] = $r;
        }
        if (Schema::hasTable('attendance_audit_logs')) {
            foreach (DB::select('SELECT a.id, a.student_id, a.plans_date_list_id, d.plan_id, l.changed_by AS changer,
                    CASE WHEN '.TermScope::linked('l.changed_by').' THEN 1 ELSE 0 END AS linked, '.$neighbours.'
                FROM attendance_audit_logs l
                JOIN attendances a ON a.id = l.attendance_id
                JOIN plans_date_lists d ON d.id = a.plans_date_list_id
                JOIN plans p ON p.id = d.plan_id
                LEFT JOIN plans pa ON pa.id = a.plan_id
                LEFT JOIN plans pp ON pp.id = p.parent_id AND p.parent_id > 0
                WHERE p.term_declaration_id = ? AND l.action = "updated" AND l.changed_by IS NOT NULL
                  AND l.old_status_id NOT IN ('.TermScope::in($t->attended).') AND l.new_status_id IN ('.TermScope::in($t->present).')
                  AND COALESCE(l.source, "") <> "excuse_review"', [$t->termId]) as $r) {
                $set[(int) $r->id] = $r;
            }
        }

        $byChanger = [];
        foreach ($set as $r) {
            $byChanger[(int) $r->changer][] = $r;
        }
        $t->staffNames(array_keys($byChanger));
        $t->sessions(array_map(fn ($r) => $r->plans_date_list_id, $set));
        $students = $this->studentLabels(array_map(fn ($r) => $r->student_id, $set));

        $pairMin = AttendanceSettings::int('att_pair_min');
        $loneMin = AttendanceSettings::int('att_lone_min');
        $loneShare = AttendanceSettings::int('att_lone_share');

        $out = [];
        foreach ($byChanger as $userId => $list) {
            $who = $t->staffName($userId);
            $linkedShare = count(array_filter($list, fn ($r) => $r->linked)) / count($list);

            $perStudent = [];
            foreach ($list as $r) {
                $perStudent[(int) $r->student_id][] = $r;
            }
            foreach ($perStudent as $studentId => $rows) {
                if (count($rows) < $pairMin) {
                    continue;
                }
                $out[] = $this->finding($t, [
                    'type' => 'recurring_pair',
                    'basis' => 'fact',
                    'weight' => $this->points('att_points_pair'),
                    'event_key' => 'pair:u:'.$userId.':s:'.$studentId.':t:'.$t->termId,
                    'staff_user_id' => $userId,
                    'student_id' => $studentId,
                    'rows_count' => count($rows),
                    'summary' => $who.' changed '.($students[$studentId] ?? 'student #'.$studentId).'\'s absence to Present '.count($rows).' times this term',
                    'evidence' => [
                        'times' => count($rows),
                        'teaches_these_classes' => count(array_filter($rows, fn ($r) => $r->linked)).' of '.count($rows),
                        'class_dates' => array_slice(array_map(fn ($r) => ['class' => $t->sessionLabel((int) $r->plans_date_list_id), 'session_id' => (int) $r->plans_date_list_id, 'attendance_id' => (int) $r->id], $rows), 0, 25),
                    ],
                ]);
            }

            $known = array_filter($list, fn ($r) => $r->prev_status !== null && $r->next_status !== null);
            $lone = array_filter($known, fn ($r) => (int) $r->prev_status === TermScope::ABSENT && (int) $r->next_status === TermScope::ABSENT);
            $share = $known ? 100 * count($lone) / count($known) : 0;
            if (count($lone) >= $loneMin && $share >= $loneShare) {
                $loneStudents = array_count_values(array_map(fn ($r) => (int) $r->student_id, $lone));
                arsort($loneStudents);
                $out[] = $this->finding($t, [
                    'type' => 'lone_presents',
                    'basis' => 'fact',
                    'weight' => $this->points('att_points_lone'),
                    'event_key' => 'u:'.$userId.':t:'.$t->termId.':lone',
                    'staff_user_id' => $userId,
                    'rows_count' => count($lone),
                    'summary' => count($lone).' lone Presents between two absences by '.$who.' ('.round($share).'% of their absences changed to Present; about 4% when tutors fix their own register)',
                    'evidence' => [
                        'lone_presents' => count($lone),
                        'with_a_class_before_and_after' => count($known),
                        'share_percent' => round($share, 1),
                        'absences_changed_to_present' => count($list),
                        'in_classes_they_teach_percent' => round(100 * $linkedShare, 1),
                        'top_students' => array_slice(array_map(fn ($sid, $n) => ['student' => $students[$sid] ?? '#'.$sid, 'student_id' => $sid, 'lone' => $n], array_keys($loneStudents), $loneStudents), 0, 10),
                    ],
                ]);
            }
        }

        return $out;
    }

    private function hasEditAttendance(int $userId): bool
    {
        if (! array_key_exists($userId, $this->privCache)) {
            $user = User::find($userId);
            $this->privCache[$userId] = $user ? AttendanceGuard::priv($user, 'edit_attendance') : false;
        }

        return $this->privCache[$userId];
    }

    private function studentLabels(array $ids): array
    {
        $out = [];
        foreach (array_chunk(array_values(array_unique(array_map('intval', $ids))), 1000) as $chunk) {
            foreach (DB::select('SELECT id, registration_no, TRIM(CONCAT_WS(" ", first_name, last_name)) AS name FROM students WHERE id IN ('.TermScope::in($chunk).')') as $s) {
                $out[(int) $s->id] = trim($s->registration_no.' '.$s->name);
            }
        }

        return $out;
    }
}
