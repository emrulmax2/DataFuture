<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeePermission;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Support\LegacyPrivilegeMap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Copies privileges from the legacy user_privileges table into
 * employee_permissions.
 *
 * Keyed by USER, not by employee, because User::priv() is
 * hasMany(UserPrivilege, 'user_id') - it reads by user_id and ignores
 * employee_id entirely. Copying per-employee would miss the legacy rows whose
 * user_id no longer matches the employee's current account: those rows are
 * invisible on the privilege screen but they still grant access today, and
 * dropping them would silently strip access from real staff at cutover.
 */
class CopyLegacyPrivileges extends Command
{
    protected $signature = 'privileges:copy-legacy
        {--employee=* : Employee id(s) to copy. Repeatable.}
        {--user=* : User id(s) to copy. Repeatable.}
        {--all : Copy every user that has legacy privileges.}
        {--dry-run : Report what would change without writing anything.}
        {--force : Skip the confirmation prompt.}';

    protected $description = 'Copy privileges from the legacy user_privileges table into employee_permissions.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $userIds = $this->resolveTargets();

        if ($userIds === null) {
            return self::FAILURE;
        }

        if (empty($userIds)) {
            $this->warn('No users with legacy privileges found. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%s privileges for %d user(s).',
            $dryRun ? 'Previewing' : 'Copying',
            count($userIds)
        ));

        $unmapped = [];
        $plans = [];
        $skipped = [];

        foreach ($userIds as $userId) {
            $plan = $this->planFor((int) $userId, $unmapped);

            if (isset($plan['error'])) {
                $skipped[] = sprintf('  user #%-5s %s', $userId, $plan['error']);
                continue;
            }

            $plans[] = $plan;
        }

        if (!empty($skipped)) {
            $this->newLine();
            $this->warn('Skipped (these grant nothing, because no such user can log in):');
            $this->line(implode(PHP_EOL, $skipped));
        }

        if (empty($plans)) {
            $this->error('Nothing to copy.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['User', 'Name', 'Employee', 'Legacy rows', 'Will write', 'Existing (replaced)'],
            array_map(fn($p) => [
                $p['user_id'],
                $p['name'],
                $p['employee_id'] ?: '-',
                $p['legacy_count'],
                count($p['rows']),
                $p['existing_count'] ?: '-',
            ], $plans)
        );

        if (!empty($unmapped)) {
            $this->newLine();
            $this->warn('Legacy keys with no equivalent in the new system (not copied):');
            foreach ($unmapped as $key => $count) {
                $this->line("  {$key} ({$count} row(s))");
            }
        }

        $willWrite = array_sum(array_map(fn($p) => count($p['rows']), $plans));
        $willReplace = array_sum(array_map(fn($p) => $p['existing_count'], $plans));

        if ($dryRun) {
            $this->newLine();
            $this->info("Dry run: {$willWrite} permission row(s) would be written. Nothing was changed.");
            $this->line('Then confirm with: php artisan privileges:verify');
            return self::SUCCESS;
        }

        if ($willReplace > 0) {
            $this->newLine();
            $this->warn("{$willReplace} existing employee_permissions row(s) will be deleted and replaced.");
        }

        if (!$this->option('force') && !$this->confirm("Write {$willWrite} permission row(s)?", false)) {
            $this->info('Aborted. Nothing was changed.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($plans) {
            foreach ($plans as $plan) {
                EmployeePermission::where('user_id', $plan['user_id'])->delete();

                if (!empty($plan['rows'])) {
                    EmployeePermission::insert($plan['rows']);
                }
            }
        });

        $this->newLine();
        $this->info(sprintf('Done. Wrote %d permission row(s) for %d user(s).', $willWrite, count($plans)));
        $this->line('Now run: php artisan privileges:verify');

        return self::SUCCESS;
    }

    /**
     * @return array<int>|null  null on a bad invocation
     */
    private function resolveTargets(): ?array
    {
        $employees = $this->option('employee');
        $users = $this->option('user');
        $all = $this->option('all');

        if (!$all && empty($employees) && empty($users)) {
            $this->error('Pass --all, --user=ID, or --employee=ID (both repeatable).');
            return null;
        }

        if ($all && (!empty($employees) || !empty($users))) {
            $this->error('Use --all on its own.');
            return null;
        }

        if ($all) {
            // Every user_id that holds legacy privileges - this is exactly the set
            // priv() can answer for today.
            return UserPrivilege::distinct()->orderBy('user_id')->pluck('user_id')->all();
        }

        $ids = array_map('intval', $users);

        foreach ($employees as $employeeId) {
            $employee = Employee::find((int) $employeeId);

            if (!$employee || empty($employee->user_id)) {
                $this->warn("Employee #{$employeeId} not found, or has no linked user account. Skipped.");
                continue;
            }

            $ids[] = (int) $employee->user_id;

            // Warn about legacy rows filed against this employee under a different
            // user_id: they belong to that other account, not this one.
            $others = UserPrivilege::where('employee_id', $employee->id)
                ->where('user_id', '!=', $employee->user_id)
                ->distinct()
                ->pluck('user_id');

            foreach ($others as $otherUserId) {
                $this->warn(sprintf(
                    'Employee #%d also has legacy privileges under user #%d (its current user is #%d). '
                    .'Those belong to that user; copy them with --user=%d.',
                    $employee->id,
                    $otherUserId,
                    $employee->user_id,
                    $otherUserId
                ));
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Builds the rows for one user without touching the database.
     */
    private function planFor(int $userId, array &$unmapped): array
    {
        // employee_permissions.user_id is a foreign key, so a legacy row pointing
        // at a user that no longer exists cannot be copied. It also grants nothing,
        // because nobody can log in as that user.
        $user = User::find($userId);

        if (!$user) {
            return ['error' => 'no such user (orphan legacy rows)'];
        }

        $legacy = UserPrivilege::where('user_id', $userId)->get();

        if ($legacy->isEmpty()) {
            return ['error' => 'no legacy privileges'];
        }

        $now = now();
        $rows = [];

        foreach ($legacy as $priv) {
            $key = LegacyPrivilegeMap::resolve($priv->category, $priv->name);

            if ($key === null) {
                $label = $priv->category.'.'.$priv->name;
                $unmapped[$label] = ($unmapped[$label] ?? 0) + 1;
                continue;
            }

            $valued = in_array($key, LegacyPrivilegeMap::VALUED_KEYS, true);

            // An off switch is simply absent in the new table, exactly as an
            // unticked box posts nothing from the form.
            if (!$valued && empty($priv->access)) {
                continue;
            }

            if ($valued && ($priv->access === null || $priv->access === '')) {
                continue;
            }

            $rows[$key] = [
                'user_id' => $userId,
                // Null means "set directly": a legacy privilege never came from a
                // department template.
                'department_id' => null,
                'key' => $key,
                'value' => $valued ? $priv->access : '1',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $employee = Employee::where('user_id', $userId)->first();

        return [
            'user_id' => $userId,
            'employee_id' => $employee->id ?? null,
            'name' => $user->name ?: '-',
            'legacy_count' => $legacy->count(),
            'existing_count' => EmployeePermission::where('user_id', $userId)->count(),
            'rows' => array_values($rows),
        ];
    }
}
