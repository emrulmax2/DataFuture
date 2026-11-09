<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tamper evidence for the attendance history.
 *
 * Each night the new history rows are chained into one hash (each row's hash
 * includes the previous one, starting from the last seal) and the result is
 * stored in attendance_audit_seals. `--verify` recomputes every seal from the
 * rows: a row changed or removed directly in the database breaks the chain.
 */
class AttendanceAuditSeal extends Command
{
    protected $signature = 'attendance-integrity:seal {--verify : check every seal instead of adding one}';

    protected $description = 'Attendance Integrity: seal (or verify) the attendance history with a hash chain';

    private const COLUMNS = ['id', 'attendance_id', 'student_id', 'plan_id', 'plans_date_list_id', 'action', 'source', 'old_status_id', 'new_status_id',
        'old_values', 'new_values', 'changed_fields', 'batch_id', 'changed_by', 'changed_at', 'class_ends_at', 'minutes_after_class',
        'is_late_correction', 'change_reason', 'ip_address', 'user_agent', 'route', 'created_at'];

    public function handle(): int
    {
        if (! Schema::hasTable('attendance_audit_logs') || ! Schema::hasTable('attendance_audit_seals')) {
            $this->error('The attendance history tables are missing: run the migrations.');

            return self::FAILURE;
        }

        return $this->option('verify') ? $this->verify() : $this->seal();
    }

    private function seal(): int
    {
        $last = DB::table('attendance_audit_seals')->orderByDesc('id')->first();
        $fromId = $last ? (int) $last->to_log_id + 1 : (int) (DB::table('attendance_audit_logs')->min('id') ?? 0);
        $toId = (int) DB::table('attendance_audit_logs')->max('id');
        if (! $toId || $fromId > $toId) {
            $this->info('Nothing new to seal.');

            return self::SUCCESS;
        }

        [$hash, $count] = $this->chain($fromId, $toId, $last->chain_hash ?? null);
        DB::table('attendance_audit_seals')->insert([
            'from_log_id' => $fromId, 'to_log_id' => $toId, 'row_count' => $count,
            'previous_hash' => $last->chain_hash ?? null, 'chain_hash' => $hash,
            'sealed_at' => now('Europe/London')->format('Y-m-d H:i:s'),
        ]);
        $this->info("Sealed history rows $fromId-$toId ($count rows).");

        return self::SUCCESS;
    }

    private function verify(): int
    {
        $broken = 0;
        $previous = null;
        foreach (DB::table('attendance_audit_seals')->orderBy('id')->get() as $seal) {
            [$hash, $count] = $this->chain((int) $seal->from_log_id, (int) $seal->to_log_id, $previous);
            if ($hash !== $seal->chain_hash || $count !== (int) $seal->row_count || $seal->previous_hash !== $previous) {
                $broken++;
                $this->error("Seal #{$seal->id} (rows {$seal->from_log_id}-{$seal->to_log_id}) does not match: history rows were changed or removed.");
            }
            $previous = $seal->chain_hash;
        }
        $broken ? $this->error("$broken seal(s) broken.") : $this->info('Every seal matches the history.');

        return $broken ? self::FAILURE : self::SUCCESS;
    }

    /** [chain hash, rows] over history ids $from..$to, continuing from $previous. */
    private function chain(int $from, int $to, ?string $previous): array
    {
        $hash = $previous ?? str_repeat('0', 64);
        $count = 0;
        DB::table('attendance_audit_logs')->whereBetween('id', [$from, $to])->orderBy('id')->select(self::COLUMNS)
            ->chunk(2000, function ($rows) use (&$hash, &$count) {
                foreach ($rows as $row) {
                    $hash = hash('sha256', $hash.'|'.json_encode(array_values((array) $row)));
                    $count++;
                }
            });

        return [$hash, $count];
    }
}
