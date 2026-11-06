<?php

namespace App\Services\TaskAssignment;

use App\Events\StudentStatusChanged;
use App\Jobs\TaskAssignment\UpdateStudentStatusToSlcNewJob;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Models\TaskAssignmentRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The "Change student status to SLC New" action (user, 28 Oct 2026). The student's task whose
 * Task Automation rule runs UpdateStudentStatusToSlcNewJob stays open until every other task the
 * student has in the same process (New Student) is done: Completed or Canceled, and tasks staff
 * removed don't count. Then the student status becomes SLC New, recorded as the status dialog
 * records it (archive row, term status row, the "Student status changed" rules), and the task is
 * completed.
 *
 * The task is found by its rule's action, never by a task id: the rule that assigned it
 * (student_tasks.rule_id), or an active student rule with the action for the same task, so a task
 * staff added by hand works too. The process comes from that task, and the status is looked up by
 * name (config tasks.slc_new_status).
 *
 * check() is safe to call any number of times. It runs after every student task update on the task
 * manager and the student process page, and after any task saved through the model
 * (StudentTaskObserver), e.g. the automatic email and welcome letter tasks.
 */
class StudentProcessWrapUp
{
    public const WAITING = 'waiting';
    public const DONE = 'done';
    public const NO_STATUS = 'no_status';

    private const OPEN = ['Pending', 'In Progress'];

    /** Completing the task saves it through the model, which would call check() again. */
    private static bool $running = false;

    /** @param int|int[] $studentIds */
    public static function checkMany($studentIds): void
    {
        foreach (array_unique(array_map('intval', (array) $studentIds)) as $studentId) {
            if ($studentId > 0) {
                app(self::class)->check($studentId);
            }
        }
    }

    /**
     * @return array{status:string,message:string,open:int}|null null when the student has no open task
     *                                                             with this action (or it is already running)
     */
    public function check(int $studentId): ?array
    {
        if (self::$running || !$this->task($studentId)) {
            return null;
        }

        self::$running = true;
        try {
            // one run per student when two updates land together; the loser has nothing left to do
            $result = Cache::lock('student-process-wrap-up-'.$studentId, 30)->get(fn () => $this->settle($studentId));

            return is_array($result) ? $result : null;
        } catch (Throwable $e) {
            Log::error('[Task Automation] SLC New status action failed', ['student_id' => $studentId, 'error' => $e->getMessage()]);

            return null;
        } finally {
            self::$running = false;
        }
    }

    /** The student's open task whose rule runs the action, oldest first. */
    public function task(int $studentId): ?StudentTask
    {
        [$ruleIds, $listIds] = $this->rules();
        if (!$ruleIds) {
            return null;
        }

        return StudentTask::with('task.processlist')
            ->where('student_id', $studentId)
            ->whereIn('status', self::OPEN)
            ->where(fn ($q) => $q->whereIn('rule_id', $ruleIds)->orWhereIn('task_list_id', $listIds ?: [0]))
            ->orderBy('id')
            ->first();
    }

    /** Whether this student task is one the action completes, found the way task() finds it. */
    public function isWrapUp(StudentTask $task): bool
    {
        [$ruleIds, $listIds] = $this->rules();

        return $ruleIds && (in_array((int) $task->rule_id, $ruleIds, true) || in_array((int) $task->task_list_id, $listIds, true));
    }

    /** The student's other tasks in the same process that are still open. */
    public function openSiblings(StudentTask $task): int
    {
        return $this->siblingsQuery($task)->count();
    }

    /** The same open tasks with their task names, for staff to see. */
    public function openSiblingTasks(StudentTask $task): Collection
    {
        return $this->siblingsQuery($task)->with('task:id,name')->orderBy('id')->get();
    }

    /**
     * Mark as Complete on the student process page (user, 29 Oct 2026): what completing this task
     * by hand means, or null when it is not an open task of this action. With other tasks of the
     * process still open staff are asked first, and completing it then leaves the student status
     * alone. With none open it completes as the action does, status change included.
     *
     * @return array{process:string,status:string,open:array<int,array{id:int,name:string,status:string}>}|null
     */
    public function manualCompletion(StudentTask $task): ?array
    {
        if (!in_array($task->status, self::OPEN, true) || !$this->isWrapUp($task)) {
            return null;
        }
        $task->loadMissing('task.processlist');

        return [
            'process' => optional(optional($task->task)->processlist)->name ?: 'process',
            'status' => (string) config('tasks.slc_new_status', 'SLC New'),
            'open' => $this->openSiblingTasks($task)
                ->map(fn (StudentTask $open) => ['id' => (int) $open->id, 'name' => (string) (optional($open->task)->name ?: 'Task #'.$open->task_list_id), 'status' => (string) $open->status])
                ->values()
                ->all(),
        ];
    }

    /** @return array{0:int[],1:int[]} ids of every student rule with the action, and the task lists of the active ones */
    private function rules(): array
    {
        $rules = TaskAssignmentRule::forSubject(TaskAssignmentRule::SUBJECT_STUDENT)->where('after_assign_job', UpdateStudentStatusToSlcNewJob::class);

        return [
            (clone $rules)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            (clone $rules)->where('active', 1)->pluck('task_list_id')->map(fn ($id) => (int) $id)->all(),
        ];
    }

    private function siblingsQuery(StudentTask $task): Builder
    {
        $processId = (int) optional($task->task)->process_list_id;

        return StudentTask::where('student_id', $task->student_id)
            ->where('id', '!=', $task->id)
            ->whereIn('status', self::OPEN)
            ->whereHas('task', fn ($q) => $q->where('process_list_id', $processId));
    }

    private function settle(int $studentId): ?array
    {
        $task = $this->task($studentId);
        $processId = (int) optional(optional($task)->task)->process_list_id;
        if (!$task || $processId <= 0) {
            return null;
        }
        $process = optional($task->task->processlist)->name ?: 'process';

        $open = $this->openSiblings($task);
        if ($open > 0) {
            return [
                'status' => self::WAITING,
                'open' => $open,
                'message' => 'Waiting for '.$open.' other '.$process.' '.($open === 1 ? 'task' : 'tasks').'; the status changes once they are done.',
            ];
        }

        $statusName = (string) config('tasks.slc_new_status', 'SLC New');
        $status = Status::where('type', 'Student')->where('name', $statusName)->first();
        if (!$status) {
            Log::warning('[Task Automation] SLC New status action: no student status named "'.$statusName.'"', ['student_id' => $studentId, 'student_task_id' => $task->id]);

            \App\Services\Conversion\ConversionErrorReport::report('Action: Change student status to SLC New', 'No student status named "'.$statusName.'", so the task was left open.', ['student_id' => $studentId, 'task_id' => $task->id, 'rule_id' => $task->rule_id]);

            return ['status' => self::NO_STATUS, 'open' => 0, 'message' => 'No student status named "'.$statusName.'", so the task was left open.'];
        }
        $student = Student::find($studentId);
        if (!$student) {
            return null;
        }

        $actor = (int) config('tasks.system_user_id', 1);
        $previousId = $student->status_id !== null ? (int) $student->status_id : null;
        $changed = $previousId !== (int) $status->id;
        $previousName = $previousId ? (string) optional(Status::find($previousId))->name : '';

        DB::transaction(function () use ($student, $task, $status, $actor, $previousId, $previousName, $changed, $process) {
            if ($changed) {
                $student->status_id = $status->id;
                $student->save();
                StudentArchive::create([
                    'student_id' => $student->id,
                    'table' => 'students',
                    'field_name' => 'status_id',
                    'field_value' => $previousId,
                    'field_new_value' => $status->id,
                    'created_by' => $actor,
                ]);
                // the status history row the status dialog adds, on the student's latest term
                $lastTerm = StudentAttendanceTermStatus::where('student_id', $student->id)->orderByDesc('id')->first();
                StudentAttendanceTermStatus::create([
                    'student_id' => $student->id,
                    'term_declaration_id' => $lastTerm->term_declaration_id ?? null,
                    'status_id' => $status->id,
                    'status_change_reason' => 'Changed automatically: every other '.$process.' task is done.',
                    'status_change_date' => now()->format('Y-m-d H:i:s'),
                    'created_by' => $actor,
                ]);
            }

            $previousTaskStatus = $task->status ?: 'Pending';
            $task->update(['status' => 'Completed', 'updated_by' => $actor]);
            StudentTaskLog::create([
                'student_tasks_id' => $task->id, 'actions' => 'Status Changed', 'field_name' => 'status',
                'prev_field_value' => $previousTaskStatus, 'current_field_value' => 'Completed', 'created_by' => $actor,
            ]);
            StudentTaskLog::create([
                'student_tasks_id' => $task->id, 'actions' => 'Student Status', 'field_name' => 'status_id',
                'prev_field_value' => $previousName,
                'current_field_value' => $changed ? $status->name : $status->name.' (already set)',
                'created_by' => $actor,
            ]);
        });

        if ($changed) {
            // "Student status changed" rules, as for a change made in the status dialog
            event(new StudentStatusChanged((int) $student->id, $previousId, (int) $status->id));
        }
        Log::info('[Task Automation] SLC New status action completed', ['student_id' => $student->id, 'student_task_id' => $task->id, 'status_changed' => $changed]);

        return [
            'status' => self::DONE,
            'open' => 0,
            'message' => $changed ? 'Student status changed to '.$status->name.' and the task completed.' : 'The student was already '.$status->name.'; the task was completed.',
        ];
    }
}
