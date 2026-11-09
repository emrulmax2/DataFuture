<?php

namespace App\Services\AttendanceIntegrity\Detection;

use App\Models\AttendanceIntegrity\AimScanRun;
use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\Rules\AmendmentRule;
use App\Services\AttendanceIntegrity\Detection\Rules\BulkEntryRule;
use App\Services\AttendanceIntegrity\Detection\Rules\DeletionRule;
use App\Services\AttendanceIntegrity\Detection\Rules\EvidenceRule;
use App\Services\AttendanceIntegrity\Detection\Rules\HighAttendanceRule;
use App\Services\AttendanceIntegrity\Detection\Rules\OutsideHoursRule;
use App\Services\AttendanceIntegrity\Detection\Rules\PatternRule;
use App\Services\AttendanceIntegrity\Detection\Rules\RetrospectiveRule;
use App\Services\AttendanceIntegrity\Detection\Rules\Rule;
use App\Services\AttendanceIntegrity\Detection\Rules\TimetableRule;
use Illuminate\Support\Facades\DB;

/**
 * Runs the detection rules for one attendance term and stores what they find in
 * aim_findings.
 *
 * - One row per fingerprint (rule + type + event + detail): a re-scan updates
 *   the row (points, summary, evidence, last seen) and never duplicates it. Its
 *   status (open / dismissed / in a case) and first-seen date are kept.
 * - A finding the rules no longer produce (the data changed, or the rule was
 *   switched off) is marked cleared, never deleted, so cases keep their evidence.
 * - Reads attendance at session time zone +00:00 so TIMESTAMP columns come back
 *   as the UK clock time live wrote.
 *
 * Reads only; the only writes are to aim_findings and aim_scan_runs.
 */
class Scanner
{
    /** @return Rule[] keyed by rule key, in the order the screens list them */
    public static function rules(): array
    {
        $rules = [];
        foreach ([RetrospectiveRule::class, AmendmentRule::class, BulkEntryRule::class, HighAttendanceRule::class, PatternRule::class,
            OutsideHoursRule::class, TimetableRule::class, EvidenceRule::class, DeletionRule::class] as $class) {
            $rule = new $class();
            $rules[$rule->key()] = $rule;
        }

        return $rules;
    }

    /** Rule key => label (letters as in the specification). */
    public static function ruleLabels(): array
    {
        return [
            'retrospective' => 'A · Late entry',
            'amendment' => 'B · Changes to attendance',
            'bulk_entry' => 'C · Bulk entry',
            'high_attendance' => 'D · Unusually high attendance',
            'staff_student' => 'E · Staff and student patterns',
            'outside_hours' => 'F · Outside working hours',
            'timetable' => 'G · Timetable conflicts',
            'evidence' => 'H · Other evidence',
            'deletion' => 'J · Deleted attendance',
        ];
    }

    /**
     * Scan one term now. Returns counts per rule plus new / updated / cleared.
     * Records the run in aim_scan_runs (the given one, or a new one).
     */
    public function scanTerm(int $termId, ?AimScanRun $run = null, string $scope = 'term', ?int $userId = null): array
    {
        $now = fn () => now('Europe/London')->format('Y-m-d H:i:s');
        $run = $run ?? AimScanRun::create(['scope' => $scope, 'term_declaration_id' => $termId, 'status' => 'pending',
            'requested_by' => $userId, 'requested_at' => $now(), 'created_at' => $now(), 'updated_at' => $now()]);
        $run->forceFill(['status' => 'running', 'started_at' => $now(), 'error' => null, 'updated_at' => $now()])->save();

        AttendanceSettings::flush();
        $previous = DB::selectOne('SELECT @@session.time_zone AS tz')->tz;
        DB::statement("SET time_zone = '+00:00'");
        $started = microtime(true);

        try {
            $scope = new TermScope($termId);
            $rows = [];
            $perRule = [];
            $ruleKeys = [];
            foreach (self::rules() as $key => $rule) {
                $ruleKeys[] = $key;
                if (! $rule->enabled()) {
                    $perRule[$key] = 'off';
                    continue;
                }
                $t0 = microtime(true);
                $found = $rule->detect($scope);
                foreach ($found as $f) {
                    $fingerprint = sha1($f['rule'].'|'.$f['type'].'|'.$f['event_key'].'|'.$f['fp'].'|'.$termId);
                    $rows[$fingerprint] = $this->row($f, $fingerprint, $run->id, $now());
                }
                $perRule[$key] = ['found' => count($found), 'seconds' => round(microtime(true) - $t0, 2)];
            }

            $existing = [];
            foreach (array_chunk(array_keys($rows), 1000) as $chunk) {
                foreach (DB::table('aim_findings')->whereIn('fingerprint', $chunk)->pluck('fingerprint') as $fp) {
                    $existing[$fp] = true;
                }
            }

            $update = ['rule', 'type', 'basis', 'weight', 'event_key', 'staff_user_id', 'student_id', 'plan_id', 'plans_date_list_id',
                'attendance_id', 'event_at', 'rows_count', 'summary', 'evidence', 'last_detected_at', 'cleared_at', 'scan_run_id', 'updated_at'];
            $cleared = 0;
            DB::transaction(function () use ($rows, $update, $termId, $ruleKeys, $run, $now, &$cleared) {
                foreach (array_chunk(array_values($rows), 300) as $chunk) {
                    DB::table('aim_findings')->upsert($chunk, ['fingerprint'], $update);
                }
                // No longer produced by the rules: mark cleared (kept for cases and history).
                $cleared = DB::table('aim_findings')->where('term_declaration_id', $termId)->whereIn('rule', $ruleKeys)
                    ->whereNull('cleared_at')->where(function ($q) use ($run) {
                        $q->whereNull('scan_run_id')->orWhere('scan_run_id', '<>', $run->id);
                    })->update(['cleared_at' => $now(), 'updated_at' => $now()]);
            });

            $stats = ['rules' => $perRule, 'seconds' => round(microtime(true) - $started, 1), 'term' => $scope->name];
            $run->forceFill([
                'status' => 'completed', 'finished_at' => $now(), 'updated_at' => $now(),
                'findings_new' => count($rows) - count($existing), 'findings_updated' => count($existing), 'findings_cleared' => (int) $cleared,
                'stats' => $stats,
            ])->save();

            return $stats + ['new' => count($rows) - count($existing), 'updated' => count($existing), 'cleared' => (int) $cleared, 'run_id' => $run->id];
        } catch (\Throwable $e) {
            $run->forceFill(['status' => 'failed', 'finished_at' => $now(), 'updated_at' => $now(), 'error' => mb_substr($e->getMessage(), 0, 2000)])->save();
            report($e);
            throw $e;
        } finally {
            DB::statement('SET time_zone = '.DB::getPdo()->quote($previous));
        }
    }

    /**
     * Terms the daily scan covers: running today, or ended within the last N
     * days (Attendance Settings), and with classes.
     *
     * @return int[]
     */
    public static function activeTerms(): array
    {
        $today = now('Europe/London')->toDateString();
        $since = now('Europe/London')->subDays(AttendanceSettings::int('att_scan_recent_days'))->toDateString();

        return DB::table('term_declarations as td')
            ->whereNull('td.deleted_at')
            ->where('td.start_date', '<=', $today)->where('td.end_date', '>=', $since)
            ->whereExists(fn ($q) => $q->selectRaw(1)->from('plans as p')->whereColumn('p.term_declaration_id', 'td.id'))
            ->orderBy('td.start_date')->pluck('td.id')->map(fn ($v) => (int) $v)->all();
    }

    private function row(array $f, string $fingerprint, int $runId, string $now): array
    {
        return [
            'rule' => $f['rule'],
            'type' => mb_substr($f['type'], 0, 60),
            'basis' => $f['basis'],
            'weight' => max(0, (int) $f['weight']),
            'event_key' => mb_substr($f['event_key'], 0, 120),
            'fingerprint' => $fingerprint,
            'term_declaration_id' => $f['term_declaration_id'],
            'staff_user_id' => $f['staff_user_id'] ?: null,
            'student_id' => $f['student_id'] ?: null,
            'plan_id' => $f['plan_id'] ?: null,
            'plans_date_list_id' => $f['plans_date_list_id'] ?: null,
            'attendance_id' => $f['attendance_id'] ?: null,
            'event_at' => $f['event_at'] ? substr((string) $f['event_at'], 0, 19) : null,
            'rows_count' => max(1, (int) $f['rows_count']),
            'summary' => mb_substr($f['summary'], 0, 500),
            'evidence' => json_encode($f['evidence'], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
            'status' => 'open',
            'first_detected_at' => $now,
            'last_detected_at' => $now,
            'cleared_at' => null,
            'scan_run_id' => $runId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
