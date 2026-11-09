<?php

namespace App\Services\AttendanceIntegrity;

use App\Models\Option;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance Integrity > Settings: every configurable attendance rule in one
 * place, stored in `options` under the ATTENDANCE_SETTINGS category (the same
 * store Site Settings and Library Settings use).
 *
 * A setting nobody has saved yet reads as its default, so the code works the day
 * it is deployed without any data being written first.
 *
 * Field types:
 *   int       whole number (min/max)        bool      on / off
 *   time      HH:MM (UK)                    date      Y-m-d
 *   statuses  attendance_feed_statuses ids  weekdays  ISO days 1 (Mon) .. 7 (Sun)
 *   tiers     [[threshold, points], ...] rows the page edits as a small table
 */
class AttendanceSettings
{
    public const CATEGORY = 'ATTENDANCE_SETTINGS';

    private static ?array $values = null;

    /** The settings page, card by card. */
    public static function groups(): array
    {
        return [
            'corrections' => [
                'title' => 'Corrections',
                'icon' => 'pencil-line',
                'lead' => 'Changing attendance after a class has ended.',
                'fields' => [
                    'att_correction_deadline_hours' => ['type' => 'int', 'default' => 48, 'min' => 1, 'max' => 2160, 'label' => 'Correction deadline', 'suffix' => 'hours after class end',
                        'hint' => 'Changing a student\'s status, or deleting a record, later than this counts as a late correction.'],
                    'att_require_late_reason' => ['type' => 'bool', 'default' => 1, 'label' => 'Ask for a reason for late corrections',
                        'hint' => 'On: the screen asks for a reason before a late correction is saved. The reason is kept in the attendance history and shown in the Attendance Integrity reports.'],
                    'att_reason_min_length' => ['type' => 'int', 'default' => 10, 'min' => 1, 'max' => 500, 'label' => 'Shortest reason accepted', 'suffix' => 'characters',
                        'hint' => 'Stops one-word reasons such as "ok".'],
                ],
            ],
            'ownership' => [
                'title' => 'Who can record attendance',
                'icon' => 'user-check',
                'lead' => 'Saving a class register.',
                'fields' => [
                    'att_enforce_class_ownership' => ['type' => 'bool', 'default' => 1, 'label' => 'Only the class\'s own tutors',
                        'hint' => 'On: only the class\'s tutor, personal tutor or cover tutor (or the tutor of its theory class) can save its register. Staff with the Module Contents > Edit Attendance privilege can still save any register. Turn off only if staff are blocked from work they should do.'],
                ],
            ],
            'rates' => [
                'title' => 'Attendance %',
                'icon' => 'percent',
                'lead' => 'How the Attendance Integrity screens count attendance. Existing reports keep their own rules and are not affected.',
                'fields' => [
                    'att_attended_statuses' => ['type' => 'statuses', 'default' => null, 'label' => 'Counted as attended',
                        'hint' => 'Statuses that count towards the attendance %. Default: the statuses marked "counts as attended" below.'],
                    'att_present_statuses' => ['type' => 'statuses', 'default' => [1, 2, 5], 'label' => 'Counted as present in class',
                        'hint' => 'Statuses that mean the student was in the class (not excused). Used to tell an Absent > Present change from an Absent > Excused one.'],
                ],
            ],
            'retrospective' => [
                'title' => 'Rule A: Late entry',
                'icon' => 'alarm-clock',
                'lead' => 'Attendance first entered a long time after the class ended.',
                'fields' => [
                    'att_retro_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check late entries'],
                    'att_retro_tiers' => ['type' => 'tiers', 'default' => [[24, 5], [48, 8], [72, 12], [168, 20], [336, 25]], 'label' => 'Delay and points',
                        'columns' => ['More than (hours after class end)', 'Points'], 'hint' => 'The highest delay reached gives the points. 168 hours = 7 days, 336 = 14 days.'],
                    'att_retro_from_date' => ['type' => 'date', 'default' => '2025-01-01', 'label' => 'Check entries from',
                        'hint' => 'The first-entry time is reliable from 2025; older rows were migrated or have no time.'],
                ],
            ],
            'amendments' => [
                'title' => 'Rule B: Changes to attendance',
                'icon' => 'history',
                'lead' => 'Status changes. Full before/after history exists only from the day the attendance history was switched on.',
                'fields' => [
                    'att_amend_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check changes'],
                    'att_points_abs_present_audit' => ['type' => 'int', 'default' => 25, 'min' => 0, 'max' => 100, 'label' => 'Absent > Present (recorded in the history)', 'suffix' => 'points'],
                    'att_points_abs_present_notice' => ['type' => 'int', 'default' => 20, 'min' => 0, 'max' => 100, 'label' => 'Absent > Present (absence notice still on the record)', 'suffix' => 'points',
                        'hint' => 'Older changes: the register saved the student Absent (an absence notice was recorded) and someone later made it Present on its own.'],
                    'att_points_abs_present_own' => ['type' => 'int', 'default' => 5, 'min' => 0, 'max' => 100, 'label' => 'Absent > Present by the class\'s own tutor', 'suffix' => 'points',
                        'hint' => 'Used instead of the two above when the change was made by the class\'s tutor, personal tutor or cover tutor, which is usually a normal correction.'],
                    'att_points_late_correction' => ['type' => 'int', 'default' => 10, 'min' => 0, 'max' => 100, 'label' => 'Late correction', 'suffix' => 'points'],
                    'att_repeated_min' => ['type' => 'int', 'default' => 3, 'min' => 2, 'max' => 50, 'label' => 'Repeated changes: same record changed at least', 'suffix' => 'times'],
                    'att_points_repeated' => ['type' => 'int', 'default' => 10, 'min' => 0, 'max' => 100, 'label' => 'Repeated changes', 'suffix' => 'points'],
                    'att_points_present_absent' => ['type' => 'int', 'default' => 0, 'min' => 0, 'max' => 100, 'label' => 'Present > Absent', 'suffix' => 'points', 'hint' => 'Shown for completeness; 0 = listed but not scored.'],
                ],
            ],
            'bulk' => [
                'title' => 'Rule C: Bulk entry',
                'icon' => 'layers',
                'lead' => 'Many separate entries across different classes within seconds. A whole class register saved at once counts as one entry.',
                'fields' => [
                    'att_bulk_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check bulk entry'],
                    'att_bulk_windows' => ['type' => 'tiers', 'default' => [[10, 30], [20, 60], [30, 120]], 'label' => 'Entries within seconds',
                        'columns' => ['Entries', 'Within (seconds)'], 'hint' => 'Any one of these counts. One entry = one class date saved by one person in the same second.'],
                    'att_bulk_min_sessions' => ['type' => 'int', 'default' => 2, 'min' => 2, 'max' => 50, 'label' => 'Different class dates at least', 'suffix' => 'classes'],
                    'att_points_bulk' => ['type' => 'int', 'default' => 15, 'min' => 0, 'max' => 100, 'label' => 'Bulk entry', 'suffix' => 'points'],
                ],
            ],
            'high' => [
                'title' => 'Rule D: Unusually high attendance',
                'icon' => 'trending-up',
                'lead' => 'High attendance alone is not evidence of anything; these only point to classes and students worth a look.',
                'fields' => [
                    'att_high_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check high attendance'],
                    'att_high_class_rate' => ['type' => 'int', 'default' => 95, 'min' => 50, 'max' => 100, 'label' => 'Class attendance at least', 'suffix' => '%'],
                    'att_high_peer_gap' => ['type' => 'int', 'default' => 20, 'min' => 1, 'max' => 100, 'label' => 'And above similar classes by at least', 'suffix' => 'points'],
                    'att_high_min_sessions' => ['type' => 'int', 'default' => 6, 'min' => 1, 'max' => 100, 'label' => 'Class dates at least', 'suffix' => 'dates'],
                    'att_high_min_students' => ['type' => 'int', 'default' => 5, 'min' => 1, 'max' => 500, 'label' => 'Students at least', 'suffix' => 'students'],
                    'att_points_high_class' => ['type' => 'int', 'default' => 5, 'min' => 0, 'max' => 100, 'label' => 'High class attendance', 'suffix' => 'points'],
                    'att_student_jump' => ['type' => 'int', 'default' => 30, 'min' => 5, 'max' => 100, 'label' => 'Student attendance rose by at least', 'suffix' => 'points on the previous term'],
                    'att_points_student_jump' => ['type' => 'int', 'default' => 5, 'min' => 0, 'max' => 100, 'label' => 'Sudden rise', 'suffix' => 'points'],
                ],
            ],
            'patterns' => [
                'title' => 'Rule E: Staff and student patterns',
                'icon' => 'users',
                'lead' => 'Staff changing attendance in classes they do not teach, the same students again and again, and lone Presents between absences.',
                'fields' => [
                    'att_pattern_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check patterns'],
                    'att_points_non_linked' => ['type' => 'int', 'default' => 25, 'min' => 0, 'max' => 100, 'label' => 'Changes in classes they do not teach (once per person per term)', 'suffix' => 'points'],
                    'att_pair_min' => ['type' => 'int', 'default' => 3, 'min' => 2, 'max' => 100, 'label' => 'Same student changed to Present at least', 'suffix' => 'times in a term'],
                    'att_points_pair' => ['type' => 'int', 'default' => 15, 'min' => 0, 'max' => 100, 'label' => 'Recurring staff and student', 'suffix' => 'points'],
                    'att_lone_min' => ['type' => 'int', 'default' => 10, 'min' => 1, 'max' => 1000, 'label' => 'Lone Presents at least', 'suffix' => 'per person per term'],
                    'att_lone_share' => ['type' => 'int', 'default' => 15, 'min' => 1, 'max' => 100, 'label' => 'And at least', 'suffix' => '% of their Absent > Present changes',
                        'hint' => 'A lone Present: the student was Absent at the class date before and after. When tutors correct their own register this is about 4%.'],
                    'att_points_lone' => ['type' => 'int', 'default' => 15, 'min' => 0, 'max' => 100, 'label' => 'Lone Presents', 'suffix' => 'points'],
                ],
            ],
            'hours' => [
                'title' => 'Rule F: Outside working hours',
                'icon' => 'moon',
                'lead' => 'Entries and changes at night, on non-working days, during approved leave, or after the term ended.',
                'fields' => [
                    'att_hours_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check working hours'],
                    'att_night_start' => ['type' => 'time', 'default' => '22:00', 'label' => 'Night starts'],
                    'att_night_end' => ['type' => 'time', 'default' => '06:00', 'label' => 'Night ends'],
                    'att_working_days' => ['type' => 'weekdays', 'default' => [1, 2, 3, 4, 5, 6], 'label' => 'Working days',
                        'hint' => 'Used when the member of staff has no working pattern in HR for that day.'],
                    'att_use_working_patterns' => ['type' => 'bool', 'default' => 1, 'label' => 'Use HR working patterns',
                        'hint' => 'On: a day is a working day when the person\'s HR working pattern has hours that day.'],
                    'att_after_term_days' => ['type' => 'int', 'default' => 14, 'min' => 0, 'max' => 365, 'label' => 'After term end, allow', 'suffix' => 'days'],
                    'att_points_outside_hours' => ['type' => 'int', 'default' => 10, 'min' => 0, 'max' => 100, 'label' => 'Outside working hours', 'suffix' => 'points'],
                ],
            ],
            'timetable' => [
                'title' => 'Rule G: Timetable conflicts',
                'icon' => 'calendar-x',
                'lead' => 'Attendance that does not fit the timetable.',
                'fields' => [
                    'att_timetable_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check timetable conflicts'],
                    'att_points_cancelled' => ['type' => 'int', 'default' => 15, 'min' => 0, 'max' => 100, 'label' => 'Present at a cancelled class', 'suffix' => 'points'],
                    'att_points_overlap' => ['type' => 'int', 'default' => 15, 'min' => 0, 'max' => 100, 'label' => 'Present at two classes at the same time on campus', 'suffix' => 'points',
                        'hint' => 'Online classes are left out, so a student can be online in one and on campus in another.'],
                    'att_points_not_assigned' => ['type' => 'int', 'default' => 10, 'min' => 0, 'max' => 100, 'label' => 'Present at a class the student is not assigned to', 'suffix' => 'points'],
                    'att_points_deleted_session' => ['type' => 'int', 'default' => 10, 'min' => 0, 'max' => 100, 'label' => 'Attendance on a deleted class date', 'suffix' => 'points'],
                ],
            ],
            'evidence' => [
                'title' => 'Rule H: Other evidence',
                'icon' => 'file-search',
                'lead' => 'There is no card swipe or QR check-in. Missing evidence is never treated as proof of absence; these use what the system does hold.',
                'fields' => [
                    'att_evidence_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check evidence'],
                    'att_points_rejected_excuse' => ['type' => 'int', 'default' => 30, 'min' => 0, 'max' => 100, 'label' => 'Changed to Present after the student\'s own excuse for that class was rejected', 'suffix' => 'points'],
                    'att_points_excuse_conflict' => ['type' => 'int', 'default' => 10, 'min' => 0, 'max' => 100, 'label' => 'Register shows Present but the student\'s rejected excuse says absent', 'suffix' => 'points',
                        'hint' => 'The register was taken as Present before the excuse was decided: the student and the register disagree.'],
                    'att_points_mitigating' => ['type' => 'int', 'default' => 30, 'min' => 0, 'max' => 100, 'label' => 'Present during a period the student reported being absent (mitigating circumstances)', 'suffix' => 'points'],
                    'att_points_approved_excuse' => ['type' => 'int', 'default' => 10, 'min' => 0, 'max' => 100, 'label' => 'Present although an excuse for that class was approved', 'suffix' => 'points'],
                    'att_points_no_start' => ['type' => 'int', 'default' => 10, 'min' => 0, 'max' => 100, 'label' => 'Present at a class with no class start record', 'suffix' => 'points'],
                ],
            ],
            'deletions' => [
                'title' => 'Rule J: Deleted attendance',
                'icon' => 'trash-2',
                'lead' => 'Who deleted a record is known only from the attendance history (from the day it was switched on).',
                'fields' => [
                    'att_deletion_enabled' => ['type' => 'bool', 'default' => 1, 'label' => 'Check deletions'],
                    'att_points_deletion' => ['type' => 'int', 'default' => 5, 'min' => 0, 'max' => 100, 'label' => 'Deleted attendance', 'suffix' => 'points per class date'],
                ],
            ],
            'scoring' => [
                'title' => 'Risk levels',
                'icon' => 'gauge',
                'lead' => 'Scores rank what to look at first; they are not proof of misconduct. When several rules flag the same record, only the highest points count. Staff and students score on different scales, so each has its own levels. Defaults come from the 2026 terms: about the top 10% High, the next 15% Medium.',
                'fields' => [
                    'att_band_staff_medium' => ['type' => 'int', 'default' => 600, 'min' => 1, 'max' => 1000000, 'label' => 'Staff: Medium from', 'suffix' => 'points per term'],
                    'att_band_staff_high' => ['type' => 'int', 'default' => 1500, 'min' => 1, 'max' => 1000000, 'label' => 'Staff: High from', 'suffix' => 'points per term'],
                    'att_band_student_medium' => ['type' => 'int', 'default' => 100, 'min' => 1, 'max' => 1000000, 'label' => 'Students: Medium from', 'suffix' => 'points per term'],
                    'att_band_student_high' => ['type' => 'int', 'default' => 200, 'min' => 1, 'max' => 1000000, 'label' => 'Students: High from', 'suffix' => 'points per term'],
                ],
            ],
            'scans' => [
                'title' => 'Scans',
                'icon' => 'scan-search',
                'lead' => 'When the detection rules run.',
                'fields' => [
                    'att_scan_daily' => ['type' => 'bool', 'default' => 1, 'label' => 'Daily scan',
                        'hint' => 'Runs each night for the terms that are running or ended in the last few weeks.'],
                    'att_scan_time' => ['type' => 'time', 'default' => '02:30', 'label' => 'Daily scan time (UK)'],
                    'att_scan_recent_days' => ['type' => 'int', 'default' => 30, 'min' => 0, 'max' => 365, 'label' => 'Keep scanning a term after it ends for', 'suffix' => 'days'],
                ],
            ],
        ];
    }

    /** name => definition, across every group. */
    public static function definitions(): array
    {
        $out = [];
        foreach (self::groups() as $group) {
            $out += $group['fields'];
        }

        return $out;
    }

    /** The typed value of one setting (its default when never saved). */
    public static function get(string $name)
    {
        $all = self::all();

        return array_key_exists($name, $all) ? $all[$name] : null;
    }

    public static function int(string $name): int
    {
        return (int) self::get($name);
    }

    public static function bool(string $name): bool
    {
        return (bool) self::get($name);
    }

    /** @return int[] */
    public static function ids(string $name): array
    {
        return array_values(array_map('intval', (array) self::get($name)));
    }

    /** Every setting, typed, with defaults filled in. */
    public static function all(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        $stored = [];
        try {
            $stored = Option::where('category', self::CATEGORY)->pluck('value', 'name')->toArray();
        } catch (\Throwable $e) {
            // No options table / no connection: run on defaults rather than fail a save.
            report($e);
        }

        $values = [];
        foreach (self::definitions() as $name => $def) {
            $values[$name] = array_key_exists($name, $stored)
                ? self::decode($def, $stored[$name])
                : self::defaultFor($name, $def);
        }

        return self::$values = $values;
    }

    /** Forget the values read in this request (after a save, in tests). */
    public static function flush(): void
    {
        self::$values = null;
    }

    /**
     * Validate and store the posted settings. Returns name => [old, new] for the
     * ones that changed, so the caller can log them.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function save(array $input, int $userId): array
    {
        $errors = [];
        $clean = [];
        foreach (self::definitions() as $name => $def) {
            try {
                $clean[$name] = self::validate($def, $input[$name] ?? null);
            } catch (\InvalidArgumentException $e) {
                $errors[$name] = $e->getMessage();
            }
        }

        foreach (['staff', 'student'] as $kind) {
            if (($clean["att_band_{$kind}_high"] ?? 0) <= ($clean["att_band_{$kind}_medium"] ?? 0)) {
                $errors["att_band_{$kind}_high"] = 'High must start above Medium.';
            }
        }

        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        $before = self::all();
        $changes = [];
        DB::transaction(function () use ($clean, $before, $userId, &$changes) {
            foreach ($clean as $name => $value) {
                if (self::encode($value) === self::encode($before[$name])) {
                    continue;
                }
                $changes[$name] = [$before[$name], $value];
                Option::updateOrCreate(
                    ['category' => self::CATEGORY, 'name' => $name],
                    ['category' => self::CATEGORY, 'name' => $name, 'value' => self::encode($value), 'created_by' => $userId, 'updated_by' => $userId]
                );
            }
        });

        self::flush();

        return $changes;
    }

    /** The feed statuses (id, code, name, flags) for the status pickers and the read-only table. */
    public static function feedStatuses(): array
    {
        try {
            return DB::table('attendance_feed_statuses')->orderBy('id')
                ->get(['id', 'code', 'name', 'active', 'tutor_availability', 'attendance_count'])
                ->map(fn ($r) => (array) $r)->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function encode($value): string
    {
        return is_array($value) ? json_encode(array_values($value)) : (string) $value;
    }

    private static function decode(array $def, $raw)
    {
        switch ($def['type']) {
            case 'int':
                return (int) $raw;
            case 'bool':
                return (int) ((string) $raw === '1');
            case 'statuses':
            case 'weekdays':
                $list = json_decode((string) $raw, true);

                return is_array($list) ? array_values(array_map('intval', $list)) : [];
            case 'tiers':
                $list = json_decode((string) $raw, true);

                return is_array($list) ? array_values(array_map(fn ($r) => [(int) ($r[0] ?? 0), (int) ($r[1] ?? 0)], $list)) : [];
            default:
                return (string) $raw;
        }
    }

    private static function defaultFor(string $name, array $def)
    {
        if ($name === 'att_attended_statuses') {
            // The statuses the college already marks as counting towards attendance.
            $ids = [];
            try {
                $ids = DB::table('attendance_feed_statuses')->where('attendance_count', 1)->orderBy('id')->pluck('id')->map(fn ($v) => (int) $v)->all();
            } catch (\Throwable $e) {
            }

            return $ids ?: [1, 2, 5, 6, 7, 8];
        }

        return $def['default'];
    }

    /** @throws \InvalidArgumentException with a message for the field */
    private static function validate(array $def, $value)
    {
        $label = $def['label'];
        switch ($def['type']) {
            case 'int':
                if ($value === null || $value === '' || ! preg_match('/^-?\d+$/', trim((string) $value))) {
                    throw new \InvalidArgumentException("$label must be a whole number.");
                }
                $value = (int) $value;
                if (isset($def['min']) && $value < $def['min'] || isset($def['max']) && $value > $def['max']) {
                    throw new \InvalidArgumentException("$label must be between {$def['min']} and {$def['max']}.");
                }

                return $value;
            case 'bool':
                return in_array((string) $value, ['1', 'on', 'true'], true) ? 1 : 0;
            case 'time':
                if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $value)) {
                    throw new \InvalidArgumentException("$label must be a time such as 22:00.");
                }

                return (string) $value;
            case 'date':
                $date = \DateTime::createFromFormat('!Y-m-d', (string) $value);
                if (! $date || $date->format('Y-m-d') !== (string) $value) {
                    throw new \InvalidArgumentException("$label must be a date.");
                }

                return (string) $value;
            case 'statuses':
                $known = array_map(fn ($s) => (int) $s['id'], self::feedStatuses());
                $list = array_values(array_unique(array_map('intval', (array) $value)));
                $list = array_values(array_filter($list, fn ($id) => in_array($id, $known, true)));
                if (! $list) {
                    throw new \InvalidArgumentException("Choose at least one status for \"$label\".");
                }
                sort($list);

                return $list;
            case 'weekdays':
                $list = array_values(array_unique(array_filter(array_map('intval', (array) $value), fn ($d) => $d >= 1 && $d <= 7)));
                sort($list);

                return $list;
            case 'tiers':
                $rows = [];
                foreach ((array) $value as $row) {
                    $a = trim((string) ($row[0] ?? ''));
                    $b = trim((string) ($row[1] ?? ''));
                    if ($a === '' && $b === '') {
                        continue;
                    }
                    if (! ctype_digit($a) || ! ctype_digit($b) || (int) $a < 1) {
                        throw new \InvalidArgumentException("Every row of \"$label\" needs two whole numbers.");
                    }
                    $rows[] = [(int) $a, (int) $b];
                }
                if (! $rows) {
                    throw new \InvalidArgumentException("\"$label\" needs at least one row.");
                }
                usort($rows, fn ($x, $y) => $x[0] <=> $y[0]);

                return $rows;
            default:
                return (string) $value;
        }
    }
}
