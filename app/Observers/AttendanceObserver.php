<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Services\AttendanceIntegrity\AttendanceAudit;

/**
 * Records every create / update / delete / restore made through the Attendance
 * model in the attendance history (see AttendanceAudit).
 */
class AttendanceObserver
{
    public function created(Attendance $attendance): void
    {
        AttendanceAudit::created($attendance);
    }

    public function updated(Attendance $attendance): void
    {
        AttendanceAudit::updated($attendance);
    }

    public function deleted(Attendance $attendance): void
    {
        AttendanceAudit::deleted($attendance);
    }

    public function restored(Attendance $attendance): void
    {
        AttendanceAudit::restored($attendance);
    }
}
