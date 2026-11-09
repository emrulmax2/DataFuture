<?php

namespace App\Console\Commands;

use App\Models\AttendanceIntegrity\AimScanRun;
use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\Scanner;
use Illuminate\Console\Command;

/**
 * Attendance Integrity detection scans.
 *
 *   --pending   run the term scans queued from the screens (Settings > Scans);
 *               scheduled every five minutes
 *   --daily     the nightly scan of running and recently ended terms (when on in
 *               Attendance Settings); scheduled daily
 *   --term=ID   scan these terms now (historical analysis from the command line)
 *
 * Reads attendance only; writes aim_findings and aim_scan_runs.
 */
class AttendanceIntegrityScan extends Command
{
    protected $signature = 'attendance-integrity:scan {--pending} {--daily} {--term=* : term_declarations id}';

    protected $description = 'Attendance Integrity: run the detection rules (queued, nightly or for given terms)';

    public function handle(): int
    {
        $scanner = new Scanner();
        $now = fn () => now('Europe/London')->format('Y-m-d H:i:s');
        $failed = 0;

        if ($this->option('pending')) {
            // A run left "running" for over two hours died with its process: try again.
            AimScanRun::where('status', 'running')->where('started_at', '<', now('Europe/London')->subHours(2)->format('Y-m-d H:i:s'))
                ->update(['status' => 'pending', 'updated_at' => $now()]);
            foreach (AimScanRun::where('status', 'pending')->orderBy('id')->limit(5)->get() as $run) {
                $failed += $this->scan($scanner, $run, (int) $run->term_declaration_id);
            }
        }

        if ($this->option('daily')) {
            AttendanceSettings::flush();
            if (! AttendanceSettings::bool('att_scan_daily')) {
                $this->info('Daily scan is switched off in Attendance Settings.');
            } else {
                foreach (Scanner::activeTerms() as $termId) {
                    $run = AimScanRun::create(['scope' => 'daily', 'term_declaration_id' => $termId, 'status' => 'pending', 'requested_at' => $now(), 'created_at' => $now(), 'updated_at' => $now()]);
                    $failed += $this->scan($scanner, $run, $termId);
                }
            }
        }

        foreach ((array) $this->option('term') as $termId) {
            $run = AimScanRun::create(['scope' => 'term', 'term_declaration_id' => (int) $termId, 'status' => 'pending', 'requested_at' => $now(), 'created_at' => $now(), 'updated_at' => $now()]);
            $failed += $this->scan($scanner, $run, (int) $termId);
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function scan(Scanner $scanner, AimScanRun $run, int $termId): int
    {
        try {
            $r = $scanner->scanTerm($termId, $run);
            $this->info("Term $termId ({$r['term']}): {$r['new']} new, {$r['updated']} updated, {$r['cleared']} cleared in {$r['seconds']}s");

            return 0;
        } catch (\Throwable $e) {
            $this->error("Term $termId failed: ".$e->getMessage());

            return 1;
        }
    }
}
