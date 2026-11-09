<?php

namespace App\Services\AttendanceIntegrity;

use App\Models\Attendance;
use App\Models\AttendanceAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Writes the attendance history (attendance_audit_logs).
 *
 * Changes made through the Attendance model (create, save, delete, restore) are
 * recorded by App\Observers\AttendanceObserver. Changes made with one SQL
 * update (`Attendance::where(...)->update([...])`) skip model events, so those
 * call sites use AttendanceAudit::updateRows() instead, which records the same.
 *
 * The screen that made a change, and the reason given for a late correction,
 * reach the log through the request context set with AttendanceAudit::context().
 *
 * If the history table is missing (code deployed before `php artisan migrate`)
 * nothing is logged and attendance keeps saving; the gap is written to the log.
 */
class AttendanceAudit
{
    /** Where a change came from, for the history screens. */
    public const SOURCES = [
        'tutor_register' => 'Class register (tutor screen)',
        'office_register' => 'Class register (office screen)',
        'student_profile' => 'Student profile attendance edit',
        'profile_delete' => 'Deleted on the student profile',
        'restore' => 'Restored',
        'student_visit' => 'Student visit',
        'excuse_review' => 'Excuse review',
        'group_reassign' => 'Student moved to another group',
        'tutor_note' => 'Personal tutor follow-up',
        'plan_delete' => 'Class deleted',
        'system' => 'Other',
    ];

    /** Bookkeeping columns left out of "changed fields". */
    private const IGNORED = ['created_at', 'updated_at'];

    private static ?bool $enabled = null;

    private static array $context = ['source' => null, 'reason' => null, 'batch' => null];

    private static array $sessionEnds = [];

    /** Set the screen (and reason) for the changes this request is about to make. */
    public static function context(string $source, ?string $reason = null): void
    {
        self::$context = ['source' => $source, 'reason' => $reason !== null && trim($reason) !== '' ? trim($reason) : null, 'batch' => (string) Str::uuid()];
    }

    public static function clearContext(): void
    {
        self::$context = ['source' => null, 'reason' => null, 'batch' => null];
    }

    public static function enabled(): bool
    {
        if (self::$enabled === null) {
            try {
                self::$enabled = Schema::hasTable('attendance_audit_logs');
            } catch (\Throwable $e) {
                self::$enabled = false;
            }
            if (! self::$enabled) {
                Log::warning('Attendance history is not being recorded: run the attendance_audit_logs migration.');
            }
        }

        return self::$enabled;
    }

    /** For tests: forget the cached table check and the session end times. */
    public static function reset(): void
    {
        self::$enabled = null;
        self::$sessionEnds = [];
        self::clearContext();
    }

    /* ---------- Called by the observer ---------- */

    public static function created(Attendance $attendance): void
    {
        self::write($attendance->id, 'created', null, self::row($attendance->getAttributes()), $attendance->getAttributes());
    }

    public static function updated(Attendance $attendance): void
    {
        $changes = $attendance->getChanges();
        $fields = array_values(array_diff(array_keys($changes), self::IGNORED));
        // A restore saves deleted_at = null; restored() records that.
        if (! $fields || $fields === ['deleted_at']) {
            return;
        }
        $old = [];
        foreach ($fields as $field) {
            $old[$field] = $attendance->getOriginal($field);
        }
        $new = array_intersect_key($changes, array_flip($fields));
        self::write($attendance->id, 'updated', $old, $new, $attendance->getAttributes());
    }

    public static function deleted(Attendance $attendance): void
    {
        $action = method_exists($attendance, 'isForceDeleting') && $attendance->isForceDeleting() ? 'force_deleted' : 'deleted';
        self::write($attendance->id, $action, self::row($attendance->getOriginal()), null, $attendance->getOriginal());
    }

    public static function restored(Attendance $attendance): void
    {
        self::write($attendance->id, 'restored', ['deleted' => true], ['deleted' => false], $attendance->getAttributes());
    }

    /**
     * `Attendance::...->update($data)` with history: the same single SQL update,
     * with each affected row's before and after recorded.
     * Returns the number of rows updated, like the query builder.
     */
    public static function updateRows(Builder $query, array $data): int
    {
        if (! self::enabled()) {
            return $query->update($data);
        }

        $before = (clone $query)->get()->keyBy('id');
        if ($before->isEmpty()) {
            return 0;
        }

        $count = Attendance::whereIn('id', $before->keys()->all())->update($data);

        foreach ($before as $id => $row) {
            $old = [];
            $new = [];
            foreach ($data as $field => $value) {
                if (in_array($field, self::IGNORED, true)) {
                    continue;
                }
                if ((string) $row->getOriginal($field) !== (string) $value) {
                    $old[$field] = $row->getOriginal($field);
                    $new[$field] = $value;
                }
            }
            if ($new) {
                self::write((int) $id, 'updated', $old, $new, array_merge($row->getAttributes(), $data));
            }
        }

        return $count;
    }

    /* ---------- Lateness ---------- */

    /**
     * When the class (session) ended, UK clock time: its date plus the class's
     * scheduled end time. Null if the session or its times are missing.
     */
    public static function classEndsAt(?int $sessionId): ?\DateTimeImmutable
    {
        if (! $sessionId) {
            return null;
        }
        if (! array_key_exists($sessionId, self::$sessionEnds)) {
            $row = DB::table('plans_date_lists as d')->leftJoin('plans as p', 'p.id', '=', 'd.plan_id')
                ->where('d.id', $sessionId)->first(['d.date', 'p.end_time']);
            $end = null;
            if ($row && $row->date) {
                $time = $row->end_time ? substr((string) $row->end_time, 0, 8) : '23:59:59';
                $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', substr((string) $row->date, 0, 10).' '.(strlen($time) === 5 ? $time.':00' : $time), new \DateTimeZone('Europe/London')) ?: null;
            }
            self::$sessionEnds[$sessionId] = $end;
        }

        return self::$sessionEnds[$sessionId];
    }

    /** Minutes from the end of the class to now (negative before it ends). */
    public static function minutesAfterClass(?int $sessionId): ?int
    {
        $end = self::classEndsAt($sessionId);
        if (! $end) {
            return null;
        }
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/London'));

        return (int) floor(($now->getTimestamp() - $end->getTimestamp()) / 60);
    }

    /** A change now to this session's attendance is after the correction deadline. */
    public static function isLate(?int $sessionId): bool
    {
        $minutes = self::minutesAfterClass($sessionId);

        return $minutes !== null && $minutes > AttendanceSettings::int('att_correction_deadline_hours') * 60;
    }

    /* ---------- Writing ---------- */

    private static function write(int $attendanceId, string $action, ?array $old, ?array $new, array $row): void
    {
        if (! self::enabled()) {
            return;
        }

        $sessionId = isset($row['plans_date_list_id']) ? (int) $row['plans_date_list_id'] : null;
        $current = $row['attendance_feed_status_id'] ?? null;

        // The status columns always carry the status, changed or not, so a history
        // row can be read without opening the JSON.
        switch ($action) {
            case 'created':
                [$oldStatus, $newStatus] = [null, $current];
                break;
            case 'updated':
                $oldStatus = array_key_exists('attendance_feed_status_id', (array) $old) ? $old['attendance_feed_status_id'] : $current;
                $newStatus = $current;
                break;
            case 'restored':
                [$oldStatus, $newStatus] = [null, $current];
                break;
            default: // deleted, force_deleted
                [$oldStatus, $newStatus] = [$current, null];
        }

        $minutes = self::minutesAfterClass($sessionId);
        $statusChanged = $action === 'updated' && array_key_exists('attendance_feed_status_id', (array) $new);
        $isLate = ($statusChanged || in_array($action, ['deleted', 'force_deleted'], true))
            && $minutes !== null && $minutes > AttendanceSettings::int('att_correction_deadline_hours') * 60;

        $request = app()->runningInConsole() ? null : request();
        $now = now('Europe/London')->format('Y-m-d H:i:s');

        AttendanceAuditLog::create([
            'attendance_id' => $attendanceId,
            'student_id' => isset($row['student_id']) ? (int) $row['student_id'] : null,
            'plan_id' => isset($row['plan_id']) ? (int) $row['plan_id'] : null,
            'plans_date_list_id' => $sessionId,
            'action' => $action,
            'source' => self::$context['source'] ?? (app()->runningInConsole() ? 'system' : null),
            'old_status_id' => $oldStatus !== null ? (int) $oldStatus : null,
            'new_status_id' => $newStatus !== null ? (int) $newStatus : null,
            'old_values' => $old,
            'new_values' => $new,
            'changed_fields' => substr(implode(',', array_keys((array) ($new ?? $old))), 0, 500),
            'batch_id' => self::$context['batch'] ?? null,
            'changed_by' => auth()->id(),
            'changed_at' => $now,
            'class_ends_at' => ($end = self::classEndsAt($sessionId)) ? $end->format('Y-m-d H:i:s') : null,
            'minutes_after_class' => $minutes,
            'is_late_correction' => $isLate,
            'change_reason' => self::$context['reason'],
            'ip_address' => $request ? substr((string) $request->ip(), 0, 45) : null,
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
            'route' => $request && $request->route() ? substr((string) $request->route()->getName(), 0, 150) : null,
            'created_at' => $now,
        ]);
    }

    /** A row's columns, without timestamps, as stored values. */
    private static function row(array $attributes): array
    {
        return array_diff_key($attributes, array_flip(array_merge(['id'], self::IGNORED)));
    }
}
