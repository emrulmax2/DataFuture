<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Services\TaskAssignment\TaskAssigner;
use Illuminate\Console\Command;

/**
 * tasks:assign 3559            - show what the rules would do (dry run)
 * tasks:assign 3559 --live     - do it
 */
class TasksAssign extends Command
{
    protected $signature = 'tasks:assign {applicant : applicants.id} {--trigger=application_submitted} {--live : create the tasks instead of only showing the plan}';
    protected $description = 'Run the task assignment rules for one applicant (dry run unless --live)';

    public function handle(TaskAssigner $assigner): int
    {
        $applicant = Applicant::find($this->argument('applicant'));
        if (!$applicant) {
            $this->error('No applicant with id ' . $this->argument('applicant'));
            return self::FAILURE;
        }

        $plan = $assigner->plan($applicant, $this->option('trigger'));

        $this->info(sprintf('Applicant %d (%s %s) - status %s - trigger %s', $applicant->id, $applicant->first_name, $applicant->last_name, $applicant->status_id, $plan['trigger']));
        $this->line('Facts:');
        foreach ($plan['facts'] as $k => $v) {
            $this->line(sprintf('  %-22s %s', $k, is_bool($v) ? ($v ? 'yes' : 'no') : (is_null($v) ? '-' : (string) $v)));
        }

        $rows = [];
        foreach ($plan['evaluations'] as $e) {
            $rows[] = [
                $e['rule_id'],
                $e['task'],
                $e['group'] ?: '-',
                $e['priority'],
                $e['conditions'] ? implode(' AND ', array_map(fn($c) => ($c['ok'] ? '✓ ' : '✗ ') . $c['text'], $e['conditions'])) : 'always',
                $e['outcome'],
                $e['job_label'] ?? '-',
            ];
        }
        $this->table(['Rule', 'Task', 'Group', 'Prio', 'Conditions', 'Outcome', 'Then'], $rows);
        $this->line('Would assign: ' . ($plan['assign'] ? implode(', ', array_keys($plan['assign'])) : 'nothing'));

        if ($this->option('live')) {
            $run = $assigner->apply($applicant, $this->option('trigger'), true);
            $this->info(sprintf('Run #%d: %d task(s) created (%s).', $run->id, $run->tasks_created, implode(', ', $run->created_task_ids ?: [])));
            foreach ($run->evaluations as $e) {
                if (!empty($e['job_status'])) {
                    $this->line(sprintf('  follow-up for rule %d (%s): %s', $e['rule_id'], $e['job_label'], $e['job_status']));
                }
            }
        } else {
            $this->comment('Dry run - nothing written. Add --live to create the tasks.');
        }

        return self::SUCCESS;
    }
}
