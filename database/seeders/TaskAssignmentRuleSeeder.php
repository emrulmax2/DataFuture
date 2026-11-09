<?php

namespace Database\Seeders;

use App\Models\TaskAssignmentRule;
use App\Services\TaskAssignment\TaskAssigner;
use Illuminate\Database\Seeder;

/**
 * The rule set the admission task analysis recommended (docs/admission-task-analysis.md §9):
 * the universal spine, the current English task, and Academic vs Mature entry
 * from the qualification record. Idempotent - a task that already has a rule
 * for the trigger is skipped. Not run automatically: `php artisan db:seed --class=TaskAssignmentRuleSeeder`.
 */
class TaskAssignmentRuleSeeder extends Seeder
{
    public function run(): void
    {
        $spine = [
            1 => 'Signed Application form',
            2 => 'Proof of ID',
            3 => 'Proof of Address',
            4 => 'Proof of Residency',
            7 => 'Academic Interview',
            24 => 'English Proficiency Certificate (replaces task 8)',
            25 => 'Signed Application form Audit',
        ];
        foreach ($spine as $task => $note) {
            $this->once($task, [], null, 100, 'Universal: every applicant receives this. ' . $note);
        }
        $this->once(5, [['has_qualification', 'is', '1']], 'entry_documentation', 10, 'Academic entry when a qualification is recorded (92.5% of staff decisions).');
        $this->once(6, [['has_qualification', 'is', '0']], 'entry_documentation', 20, 'Mature entry when no qualification is recorded (99.8% of staff decisions).');
    }

    private function once(int $task, array $conds, ?string $group, int $priority, string $notes): void
    {
        if (TaskAssignmentRule::forTrigger(TaskAssigner::TRIGGER_SUBMITTED)->where('task_list_id', $task)->exists()) {
            return;
        }
        $r = TaskAssignmentRule::create(['task_list_id' => $task, 'trigger' => TaskAssigner::TRIGGER_SUBMITTED, 'exclusive_group' => $group, 'priority' => $priority, 'active' => 1, 'notes' => $notes, 'created_by' => config('tasks.system_user_id', 1)]);
        foreach ($conds as $i => [$f, $o, $v]) {
            $r->conditions()->create(['field' => $f, 'operator' => $o, 'value' => $v, 'sort_order' => $i]);
        }
    }
}
