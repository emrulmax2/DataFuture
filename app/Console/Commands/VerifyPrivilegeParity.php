<?php

namespace App\Console\Commands;

use App\Models\EmployeePermission;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Support\LegacyPrivilegeMap;
use Illuminate\Console\Command;

/**
 * Proves that switching config('privileges.source') to "new" would give every
 * user exactly the privileges they have today.
 *
 * This is the gate for the cutover: it must report zero differences before
 * PRIVILEGE_SOURCE is flipped. It only reads.
 */
class VerifyPrivilegeParity extends Command
{
    protected $signature = 'privileges:verify
        {--user= : Check a single user id.}
        {--show-all : List every difference rather than the first few per user.}';

    protected $description = 'Compare the privileges a user would get from user_privileges vs employee_permissions.';

    public function handle(): int
    {
        $userIds = $this->option('user')
            ? [(int) $this->option('user')]
            : UserPrivilege::distinct()->orderBy('user_id')->pluck('user_id')->all();

        // Users who have new-system permissions but no legacy ones would silently
        // gain access on cutover, so they have to be checked too.
        $extra = EmployeePermission::distinct()->pluck('user_id')->diff($userIds)->all();
        $userIds = array_values(array_unique(array_merge($userIds, $extra)));

        if (empty($userIds)) {
            $this->warn('No users with privileges. Nothing to verify.');
            return self::SUCCESS;
        }

        // Only these actually gate anything today; a difference in one of them is
        // a real access change, anything else is noise.
        $readKeys = $this->keysTheApplicationReads();

        $this->info(sprintf('Comparing legacy vs new privileges for %d user(s)...', count($userIds)));
        $this->newLine();

        $mismatched = [];
        $totalDiffs = 0;
        $criticalDiffs = 0;
        $ghosts = [];

        foreach ($userIds as $userId) {
            // A user_id with no row in `users` cannot authenticate, so whatever
            // legacy privileges it holds grant nothing and can never be exercised.
            // (employee_permissions.user_id is a foreign key, so these cannot be
            // copied across either.) Report them, but do not let them block the
            // cutover forever.
            if (!User::withTrashed()->find($userId)) {
                $ghosts[$userId] = count($this->legacyPriv($userId));
                continue;
            }

            $legacy = $this->legacyPriv($userId);
            $new = $this->newPriv($userId);

            $diffs = [];

            foreach (array_unique(array_merge(array_keys($legacy), array_keys($new))) as $key) {
                $l = $legacy[$key] ?? null;
                $n = $new[$key] ?? null;

                if ((string) $l !== (string) $n) {
                    $critical = in_array($key, $readKeys, true);
                    $diffs[] = [
                        'key' => $key,
                        'legacy' => $l ?? '(absent)',
                        'new' => $n ?? '(absent)',
                        'critical' => $critical,
                    ];

                    $totalDiffs++;

                    if ($critical) {
                        $criticalDiffs++;
                    }
                }
            }

            if (!empty($diffs)) {
                $mismatched[$userId] = $diffs;
            }
        }

        if (!empty($ghosts)) {
            $this->line(sprintf(
                'Ignored %d user id(s) with no user account - they cannot log in, so their %d legacy row(s) grant nothing: %s',
                count($ghosts),
                array_sum($ghosts),
                implode(', ', array_map(fn($id) => '#'.$id, array_keys($ghosts)))
            ));
            $this->newLine();
        }

        if (empty($mismatched)) {
            $this->info('PASS - every user who can log in gets identical privileges from both sources.');
            $this->line('It is safe to set PRIVILEGE_SOURCE=new.');
            return self::SUCCESS;
        }

        foreach ($mismatched as $userId => $diffs) {
            $user = User::find($userId);
            $this->warn(sprintf('user #%d (%s) - %d difference(s)', $userId, $user->name ?? '?', count($diffs)));

            $show = $this->option('show-all') ? $diffs : array_slice($diffs, 0, 6);

            foreach ($show as $d) {
                $this->line(sprintf(
                    '   %s %-34s legacy=%-12s new=%s',
                    $d['critical'] ? '[GATES ACCESS]' : '              ',
                    $d['key'],
                    $d['legacy'],
                    $d['new']
                ));
            }

            if (count($diffs) > count($show)) {
                $this->line(sprintf('   ... and %d more (use --show-all)', count($diffs) - count($show)));
            }
        }

        $this->newLine();

        // Only a difference in a key the application actually gates on can change
        // what a user is able to do. Anything else is dead data (a legacy key no
        // code reads), which is worth reporting but must not block the cutover.
        if ($criticalDiffs === 0) {
            $this->info(sprintf(
                'PASS - %d difference(s) across %d user(s), but NONE of them gate access.',
                $totalDiffs,
                count($mismatched)
            ));
            $this->line('These are legacy keys no code reads. No user gains or loses anything.');
            $this->line('It is safe to set PRIVILEGE_SOURCE=new.');

            return self::SUCCESS;
        }

        $this->error(sprintf(
            'FAIL - %d difference(s) across %d user(s); %d of them GATE REAL ACCESS.',
            $totalDiffs,
            count($mismatched),
            $criticalDiffs
        ));

        $this->newLine();
        $this->line('A difference means the two sources disagree. There are two reasons for that,');
        $this->line('and they need OPPOSITE fixes - read the rows above before doing either:');
        $this->newLine();
        $this->line('  1. NOT YET SYNCED - legacy changed (or was never copied). The new system');
        $this->line('     needs to catch up:');
        $this->line('         php artisan privileges:copy-legacy --user=<id>');
        $this->newLine();
        $this->line('  2. A DELIBERATE EDIT on the new privilege screen that has simply not taken');
        $this->line('     effect yet, because PRIVILEGE_SOURCE is still `legacy`. Nothing to fix:');
        $this->line('     the difference IS the pending change, and it goes live at cutover.');
        $this->newLine();
        $this->warn('DANGER: `privileges:copy-legacy --all` overwrites the new system FROM legacy.');
        $this->warn('If a difference is case 2, that command will silently destroy the edit.');

        return self::FAILURE;
    }

    /**
     * The legacy priv() map, read straight from the table.
     */
    private function legacyPriv(int $userId): array
    {
        return UserPrivilege::where('user_id', $userId)
            ->select('access', 'name')
            ->pluck('access', 'name')
            ->toArray();
    }

    /**
     * What priv() would return once it reads employee_permissions.
     */
    private function newPriv(int $userId): array
    {
        $priv = [];

        foreach (EmployeePermission::where('user_id', $userId)->pluck('value', 'key') as $key => $value) {
            $legacyName = LegacyPrivilegeMap::toLegacyName($key);

            if ($legacyName !== null) {
                $priv[$legacyName] = $value;
            }
        }

        return $priv;
    }

    /**
     * Scrapes the keys the codebase actually gates on, so the report can tell a
     * cosmetic difference apart from one that changes what a user can do.
     */
    private function keysTheApplicationReads(): array
    {
        $keys = [];
        $paths = [base_path('app'), base_path('resources/views')];

        foreach ($paths as $path) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($files as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                if (preg_match_all('/priv\(\)\[[\'"]([a-z_0-9]+)[\'"]\]/', file_get_contents($file->getPathname()), $m)) {
                    $keys = array_merge($keys, $m[1]);
                }
            }
        }

        return array_values(array_unique($keys));
    }
}
