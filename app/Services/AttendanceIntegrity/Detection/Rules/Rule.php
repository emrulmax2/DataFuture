<?php

namespace App\Services\AttendanceIntegrity\Detection\Rules;

use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\TermScope;

/**
 * A detection rule. detect() returns finding rows for one term; the Scanner
 * stores them (one row per fingerprint, so re-scans never duplicate).
 *
 * Event keys (scoring counts only the highest points per event):
 *   att:{attendance id}            one attendance record
 *   ses:{class date}:u:{user}:...  one class date for one member of staff
 *   other keys                     patterns (per person / pair / class per term)
 */
abstract class Rule
{
    /** Short key stored in aim_findings.rule. */
    abstract public function key(): string;

    abstract public function label(): string;

    /** The Attendance Settings switch for this rule. */
    abstract protected function setting(): string;

    /** @return array[] finding rows */
    abstract public function detect(TermScope $t): array;

    public function enabled(): bool
    {
        return AttendanceSettings::bool($this->setting());
    }

    protected function points(string $name): int
    {
        return AttendanceSettings::int($name);
    }

    /**
     * A finding with the defaults filled in. `fp` (optional) is any extra text
     * that makes the fingerprint unique when type + event key are not enough.
     */
    protected function finding(TermScope $t, array $f): array
    {
        return array_merge([
            'rule' => $this->key(),
            'type' => 'general',
            'basis' => 'anomaly',
            'weight' => 0,
            'event_key' => '',
            'fp' => '',
            'term_declaration_id' => $t->termId,
            'staff_user_id' => null,
            'student_id' => null,
            'plan_id' => null,
            'plans_date_list_id' => null,
            'attendance_id' => null,
            'event_at' => null,
            'rows_count' => 1,
            'summary' => '',
            'evidence' => [],
        ], $f);
    }

    protected static function ukDate(?string $value): string
    {
        return $value ? date('d/m/Y', strtotime($value)) : '';
    }

    protected static function ukDateTime(?string $value): string
    {
        return $value ? date('d/m/Y H:i', strtotime(substr($value, 0, 19))) : '';
    }
}
