<?php

namespace App\Services\AttendanceIntegrity;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Who may use Attendance Integrity. A user needs the parent "Attendance
 * Integrity" switch and the key for the area. Nobody gets these by default
 * except the super-admin accounts (which receive every key automatically).
 *
 * Also writes the access log (UK GDPR): every page, record and export opened.
 */
class IntegrityAccess
{
    public const PARENT = 'attendance_integrity';

    /** ability => legacy privilege name (what priv() returns) */
    public const KEYS = [
        'view_dashboard' => 'ai_view_dashboard',
        'view_staff' => 'ai_view_staff',
        'view_students' => 'ai_view_students',
        'view_audit_logs' => 'ai_view_audit_logs',
        'view_investigations' => 'ai_view_investigations',
        'manage_investigations' => 'ai_manage_investigations',
        'export' => 'ai_export',
        'manage_settings' => 'ai_manage_settings',
    ];

    public const LABELS = [
        'view_dashboard' => 'View dashboard',
        'view_staff' => 'View staff analysis',
        'view_students' => 'View student analysis',
        'view_audit_logs' => 'View attendance history',
        'view_investigations' => 'View investigations',
        'manage_investigations' => 'Manage investigations',
        'export' => 'Export',
        'manage_settings' => 'Manage settings',
    ];

    public static function can(string $ability, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user || ! isset(self::KEYS[$ability])) {
            return false;
        }
        $priv = $user->priv();

        return ! empty($priv[self::PARENT]) && (string) $priv[self::PARENT] === '1'
            && ! empty($priv[self::KEYS[$ability]]) && (string) $priv[self::KEYS[$ability]] === '1';
    }

    /** Any area at all (to show the menu entry). */
    public static function any(?User $user = null): bool
    {
        foreach (array_keys(self::KEYS) as $ability) {
            if (self::can($ability, $user)) {
                return true;
            }
        }

        return false;
    }

    public static function authorize(string $ability): void
    {
        abort_unless(self::can($ability), 403, 'You do not have access to this part of Attendance Integrity.');
    }

    /** The first page this user may open (the module's landing page). */
    public static function home(): ?string
    {
        $pages = [
            'view_dashboard' => 'attendance.integrity.dashboard',
            'view_staff' => 'attendance.integrity.staff',
            'view_students' => 'attendance.integrity.students',
            'view_audit_logs' => 'attendance.integrity.amendments',
            'view_investigations' => 'attendance.integrity.cases',
        ];
        foreach ($pages as $ability => $route) {
            if (self::can($ability) && \Illuminate\Support\Facades\Route::has($route)) {
                return route($route);
            }
        }

        return null;
    }

    /**
     * Record who looked at or exported what. Never lets a logging problem stop
     * the page.
     */
    public static function log(string $action, ?string $subjectType = null, ?int $subjectId = null, array $details = []): void
    {
        try {
            $request = request();
            DB::table('aim_access_logs')->insert([
                'user_id' => auth()->id(),
                'action' => substr($action, 0, 20),
                'route' => $request->route() ? substr((string) $request->route()->getName(), 0, 150) : null,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'details' => $details ? json_encode($details) : null,
                'ip_address' => substr((string) $request->ip(), 0, 45),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'created_at' => now('Europe/London')->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
