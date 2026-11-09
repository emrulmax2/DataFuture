<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\ApplicantTask;
use App\Models\TaskAssignmentRule;
use App\Models\TaskList;
use App\Services\TaskAssignment\TaskAssigner;
use Illuminate\Console\Command;

/**
 * Score the current rules against what staff actually assigned.
 *
 * For each past applicant the engine plans as if they had just submitted,
 * and the plan is compared with the tasks staff gave them. Only tasks that
 * have at least one rule are scored - a task nobody has written a rule for
 * is not a miss. Reads only; writes nothing.
 */
class TasksReplay extends Command
{
    protected $signature = 'tasks:replay {--trigger=application_submitted} {--since= : only applicants submitted on/after this date} {--limit=500} {--show-misses=10 : how many disagreeing applicants to list}';
    protected $description = 'Replay the task assignment rules over past applicants and report how closely they match staff decisions';

    public function handle(TaskAssigner $assigner): int
    {
        $ruledTasks = TaskAssignmentRule::active()->forTrigger($this->option('trigger'))->pluck('task_list_id')->unique()->map(fn($v) => (int) $v)->values();
        if ($ruledTasks->isEmpty()) {
            $this->warn('No active rules for this trigger - nothing to score.');
            return self::SUCCESS;
        }
        $names = TaskList::whereIn('id', $ruledTasks)->pluck('name', 'id');

        $q = Applicant::whereNotNull('submission_date')->where('status_id', '>=', 3)->orderByDesc('id');
        if ($this->option('since')) {
            $q->where('submission_date', '>=', $this->option('since'));
        }
        $applicants = $q->limit((int) $this->option('limit'))->get();

        $per = [];
        foreach ($ruledTasks as $t) {
            $per[$t] = ['hit' => 0, 'over' => 0, 'under' => 0, 'none' => 0];
        }
        $exact = 0;
        $misses = [];

        $bar = $this->output->createProgressBar($applicants->count());
        foreach ($applicants as $a) {
            $plan = $assigner->plan($a, $this->option('trigger'));
            /* the engine would create these, or they are there already */
            $predicted = collect($plan['evaluations'])
                ->whereIn('outcome', [TaskAssigner::ASSIGN, TaskAssigner::EXISTS])
                ->pluck('task_list_id')->unique()->map(fn($v) => (int) $v)->values();
            $actual = ApplicantTask::where('applicant_id', $a->id)->whereIn('task_list_id', $ruledTasks)->pluck('task_list_id')->map(fn($v) => (int) $v)->unique()->values();

            $same = true;
            foreach ($ruledTasks as $t) {
                $p = $predicted->contains($t); $x = $actual->contains($t);
                if ($p && $x) $per[$t]['hit']++;
                elseif ($p && !$x) { $per[$t]['over']++; $same = false; }
                elseif (!$p && $x) { $per[$t]['under']++; $same = false; }
                else $per[$t]['none']++;
            }
            if ($same) $exact++;
            elseif (count($misses) < (int) $this->option('show-misses')) {
                $misses[] = [$a->id, $predicted->map(fn($t) => $names[$t] ?? $t)->implode(', ') ?: '-', $actual->map(fn($t) => $names[$t] ?? $t)->implode(', ') ?: '-'];
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        $rows = [];
        foreach ($per as $t => $c) {
            $decided = $c['hit'] + $c['over'] + $c['under'] + $c['none'];
            $agree = $decided ? round(100 * ($c['hit'] + $c['none']) / $decided, 1) : 0;
            $rows[] = [$t, $names[$t] ?? '?', $c['hit'], $c['over'], $c['under'], $agree . '%'];
        }
        $this->table(['Task', 'Name', 'Both', 'Rule only', 'Staff only', 'Agreement'], $rows);
        $this->info(sprintf('%d applicants replayed; %d (%.1f%%) match staff exactly on the ruled tasks.', $applicants->count(), $exact, $applicants->count() ? 100 * $exact / $applicants->count() : 0));

        if ($misses) {
            $this->newLine();
            $this->line('Disagreements (first ' . count($misses) . '):');
            $this->table(['Applicant', 'Rules would give', 'Staff gave'], $misses);
        }

        return self::SUCCESS;
    }
}
