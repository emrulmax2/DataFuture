<?php

namespace App\Services\AttendanceIntegrity;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Server-side checks for the screens that write attendance (they used to rely on
 * the page hiding buttons):
 *   - who may save a class register (the class's own tutors, or Edit Attendance);
 *   - a register that is locked once the class has ended and been fed;
 *   - a reason for late corrections (Attendance Integrity > Settings).
 */
class AttendanceGuard
{
    /** Header the screens send back with the reason once the member of staff has typed it. */
    public const REASON_FIELD = 'change_reason';

    public static function priv(?User $user, string $key): bool
    {
        if (! $user) {
            return false;
        }
        $priv = $user->priv();

        return isset($priv[$key]) && (string) $priv[$key] === '1';
    }

    /**
     * The user teaches this class date: the class's tutor or personal tutor, the
     * cover (proxy) tutor of that date, the tutor or personal tutor of its theory
     * class (for tutorials and seminars), or whoever started the class.
     */
    public static function isLinked(int $userId, int $sessionId): bool
    {
        if ($userId < 1 || $sessionId < 1) {
            return false;
        }

        $row = DB::table('plans_date_lists as d')
            ->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->leftJoin('plans as pp', function ($join) {
                $join->on('pp.id', '=', 'p.parent_id')->where('p.parent_id', '>', 0);
            })
            ->where('d.id', $sessionId)
            ->first(['p.tutor_id', 'p.personal_tutor_id', 'd.proxy_tutor_id', 'pp.tutor_id as parent_tutor_id', 'pp.personal_tutor_id as parent_personal_tutor_id']);

        if ($row && in_array($userId, array_map('intval', [$row->tutor_id, $row->personal_tutor_id, $row->proxy_tutor_id, $row->parent_tutor_id, $row->parent_personal_tutor_id]), true)) {
            return true;
        }

        return DB::table('attendance_informations')->where('plans_date_list_id', $sessionId)
            ->where('tutor_id', $userId)->whereNull('deleted_at')->exists();
    }

    /** May this user save the register of this class date? */
    public static function canRecordFor(?User $user, int $sessionId): bool
    {
        if (! $user) {
            return false;
        }
        if (! AttendanceSettings::bool('att_enforce_class_ownership')) {
            return true;
        }

        return self::priv($user, 'edit_attendance') || self::isLinked((int) $user->id, $sessionId);
    }

    /**
     * Statuses changing on rows that already exist, after the correction deadline.
     *
     * @param array $changes [['session_id' => int, 'old' => ?int, 'new' => ?int], ...]
     *                       old null = new row (not a correction); new null = deletion
     * @return array the late ones
     */
    public static function lateChanges(array $changes): array
    {
        return array_values(array_filter($changes, function ($c) {
            if ($c['old'] === null) {
                return false;
            }
            if ($c['new'] !== null && (int) $c['old'] === (int) $c['new']) {
                return false;
            }

            return AttendanceAudit::isLate($c['session_id'] ?? null);
        }));
    }

    /**
     * When there are late corrections and the setting asks for a reason, the
     * response telling the screen to ask for one (null = go ahead).
     * The screen re-sends the same request with `change_reason`.
     */
    public static function reasonResponse(Request $request, array $late): ?JsonResponse
    {
        if (! $late || ! AttendanceSettings::bool('att_require_late_reason')) {
            return null;
        }

        $reason = trim((string) $request->input(self::REASON_FIELD, ''));
        $min = AttendanceSettings::int('att_reason_min_length');
        if (mb_strlen($reason) >= $min) {
            return null;
        }

        $hours = AttendanceSettings::int('att_correction_deadline_hours');
        $count = count($late);
        $end = AttendanceAudit::classEndsAt($late[0]['session_id'] ?? null);
        $message = $count === 1
            ? 'This changes attendance more than '.$hours.' hours after the class ended'.($end ? ' ('.$end->format('d/m/Y H:i').')' : '').'. Please give the reason for the change.'
            : 'This changes '.$count.' attendance records more than '.$hours.' hours after their class ended. Please give the reason for the change.';

        return response()->json([
            'message' => $message,
            'requires_reason' => true,
            'min_length' => $min,
            'late_count' => $count,
            'errors' => [self::REASON_FIELD => [$reason === '' ? 'Please give a reason.' : 'Please give a reason of at least '.$min.' characters.']],
        ], 422);
    }

    public static function reason(Request $request): ?string
    {
        $reason = trim((string) $request->input(self::REASON_FIELD, ''));

        return $reason === '' ? null : mb_substr($reason, 0, 2000);
    }

    public static function deny(string $message, int $status = 403): JsonResponse
    {
        return response()->json(['message' => $message, 'errors' => ['permission' => [$message]]], $status);
    }
}
