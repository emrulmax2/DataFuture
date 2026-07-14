<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Summarises what PrivilegeGuard would have blocked, so enforcement is switched
 * on from evidence rather than from hope.
 */
class PrivilegeAuditReport extends Command
{
    protected $signature = 'privileges:audit-report
        {--days=7 : How many days of log files to read.}
        {--route= : Only show entries for routes matching this substring.}';

    protected $description = 'Summarise the privilege guard log: who would be blocked, and from what.';

    public function handle(): int
    {
        $entries = $this->readLog((int) $this->option('days'));

        if (empty($entries)) {
            $this->warn('No privilege decisions logged yet.');
            $this->line('The guard only logs when it would deny a request. No entries means');
            $this->line('either no traffic yet, or nothing would have been blocked.');
            return self::SUCCESS;
        }

        $filter = $this->option('route');

        if ($filter) {
            $entries = array_filter($entries, fn($e) => str_contains($e['route'] ?? '', $filter));
        }

        $blocked = array_filter($entries, fn($e) => ($e['_event'] ?? '') === 'blocked');

        $this->info(sprintf('%d decision(s) logged (%d actually blocked, %d would-have-blocked).',
            count($entries), count($blocked), count($entries) - count($blocked)));

        // 1. Unmapped routes: these are the gap in the route map. Anything here
        //    that real staff actually use MUST be mapped before enforcing, or
        //    turning enforcement on will lock them out of it.
        $unmapped = [];
        foreach ($entries as $e) {
            if (($e['reason'] ?? '') === 'unmapped_route') {
                $key = $e['route'] ?? '(unnamed)';
                $unmapped[$key]['hits'] = ($unmapped[$key]['hits'] ?? 0) + 1;
                $unmapped[$key]['users'][$e['user_id'] ?? '?'] = true;
                $unmapped[$key]['uri'] = $e['uri'] ?? '';
            }
        }

        if (!empty($unmapped)) {
            uasort($unmapped, fn($a, $b) => $b['hits'] <=> $a['hits']);

            $this->newLine();
            $this->error('UNMAPPED ROUTES - real staff are using these, and they have no permission mapped.');
            $this->line('Add each to the `routes` map in config/privileges.php before enforcing,');
            $this->line('or enabling deny_unmapped will lock these users out.');
            $this->newLine();
            $this->table(
                ['Route', 'Example URI', 'Hits', 'Distinct users'],
                array_map(fn($route, $d) => [$route, $d['uri'], $d['hits'], count($d['users'])],
                    array_keys($unmapped), $unmapped)
            );
        }

        // 2. Missing permissions: the guard would deny these on purpose. Check the
        //    list is who you expect - a familiar name here means a real lockout.
        $missing = [];
        foreach ($entries as $e) {
            if (str_starts_with($e['reason'] ?? '', 'missing_permission')) {
                $key = ($e['route'] ?? '?').' | '.$e['reason'];
                $missing[$key]['hits'] = ($missing[$key]['hits'] ?? 0) + 1;
                $missing[$key]['users'][($e['user_id'] ?? '?').':'.($e['user'] ?? '')] = true;
            }
        }

        if (!empty($missing)) {
            uasort($missing, fn($a, $b) => $b['hits'] <=> $a['hits']);
            $this->newLine();
            $this->warn('WOULD DENY - user lacks the mapped permission (this is the guard working as intended):');
            $this->newLine();
            $this->table(
                ['Route | required', 'Hits', 'Users'],
                array_map(fn($k, $d) => [
                    $k,
                    $d['hits'],
                    implode(', ', array_slice(array_keys($d['users']), 0, 3)).(count($d['users']) > 3 ? ' +'.(count($d['users']) - 3).' more' : ''),
                ], array_keys($missing), $missing)
            );
        }

        // 3. Remote access: the riskiest switch. Anyone here loses the portal
        //    entirely, so read this list carefully before enabling remote_enforce.
        $remote = [];
        foreach ($entries as $e) {
            if (str_starts_with($e['reason'] ?? '', 'remote_access')) {
                $key = ($e['user_id'] ?? '?').':'.($e['user'] ?? '?');
                $remote[$key]['hits'] = ($remote[$key]['hits'] ?? 0) + 1;
                $remote[$key]['reason'] = $e['reason'];
                $remote[$key]['login_ip'] = $e['login_ip'] ?? '-';
                $remote[$key]['request_ip'] = $e['request_ip'] ?? '-';
            }
        }

        if (!empty($remote)) {
            uasort($remote, fn($a, $b) => $b['hits'] <=> $a['hits']);
            $this->newLine();
            $this->error('WOULD LOSE THE PORTAL ENTIRELY (remote access) - review before enabling remote_enforce:');
            $this->newLine();
            $this->table(
                ['User', 'Reason', 'Login IP', 'Request IP', 'Hits'],
                array_map(fn($u, $d) => [$u, $d['reason'], $d['login_ip'], $d['request_ip'], $d['hits']],
                    array_keys($remote), $remote)
            );
        }

        $this->newLine();

        if (empty($unmapped) && empty($remote)) {
            $this->info('No unmapped routes and no remote-access lockouts. Safe to set PRIVILEGE_ENFORCE=true.');
        } else {
            $this->warn('Resolve the sections above before switching enforcement on.');
        }

        return self::SUCCESS;
    }

    /**
     * The guard writes one JSON context blob per line via the daily log channel.
     */
    private function readLog(int $days): array
    {
        $entries = [];

        for ($i = 0; $i < max(1, $days); $i++) {
            $file = storage_path('logs/privileges-'.date('Y-m-d', strtotime("-{$i} days")).'.log');

            if (!file_exists($file)) {
                continue;
            }

            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (!preg_match('/(would_block|blocked)\s+(\{.*\})\s*$/', $line, $m)) {
                    continue;
                }

                $context = json_decode($m[2], true);

                if (is_array($context)) {
                    $context['_event'] = $m[1];
                    $entries[] = $context;
                }
            }
        }

        return $entries;
    }
}
