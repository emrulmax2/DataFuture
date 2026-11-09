<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One change to an `attendances` row (Attendance Integrity history).
 * Written only by App\Services\AttendanceIntegrity\AttendanceAudit.
 *
 * Append-only: updating or deleting a log row through the application throws.
 */
class AttendanceAuditLog extends Model
{
    protected $table = 'attendance_audit_logs';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'is_late_correction' => 'boolean',
    ];

    protected static function booted()
    {
        static::updating(function () {
            throw new \LogicException('Attendance history rows cannot be changed.');
        });
        static::deleting(function () {
            throw new \LogicException('Attendance history rows cannot be deleted.');
        });
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
