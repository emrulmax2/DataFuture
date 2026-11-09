<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantTask;
use App\Models\User;
use App\Support\ApplicantTaskStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Applicant status driven by the process board: the first completed task moves
 * New → In Progress, and when no task is left open it moves to Awaiting Decision.
 */
class AdmissionTaskStatusTest extends TestCase
{
    use DatabaseTransactions;

    /** A submitted applicant with no tasks of its own, given $n fresh Pending tasks. */
    private function withTasks(int $n, int $status = 2): array
    {
        $id = DB::table('applicants')->whereNull('deleted_at')->where('status_id', '>', 1)->whereNotNull('submission_date')
            ->whereNotExists(fn ($q) => $q->from('applicant_tasks')->whereColumn('applicant_tasks.applicant_id', 'applicants.id'))
            ->orderByDesc('id')->value('id');
        $this->assertNotNull($id, 'needs a submitted applicant without tasks');
        DB::table('applicants')->where('id', $id)->update(['status_id' => $status]);
        $lists = DB::table('task_lists')->orderBy('id')->limit($n)->pluck('id');
        $tasks = $lists->map(fn ($list) => ApplicantTask::create(['applicant_id' => $id, 'task_list_id' => $list, 'status' => 'Pending', 'created_by' => 7]))->all();

        return [Applicant::find($id), $tasks];
    }

    private function complete(Applicant $a, ApplicantTask $t)
    {
        return $this->actingAs(User::find(7))->postJson(route('admission.completed.task'), ['applicant' => $a->id, 'recordid' => $t->id])->assertOk();
    }

    private function statusHistory(Applicant $a): array
    {
        return DB::table('applicant_archives')->where('applicant_id', $a->id)->where('field_name', 'status_id')
            ->orderBy('id')->get()->map(fn ($r) => $r->field_value.'→'.$r->field_new_value)->all();
    }

    public function test_first_completed_task_moves_new_to_in_progress_and_the_last_to_awaiting_decision(): void
    {
        [$a, $tasks] = $this->withTasks(3);

        $this->complete($a, $tasks[0])->assertJson(['applicant_status' => 'In Progress']);
        $this->assertSame(3, (int) $a->fresh()->status_id);

        $this->complete($a, $tasks[1])->assertJson(['applicant_status' => null]);
        $this->assertSame(3, (int) $a->fresh()->status_id);

        $this->complete($a, $tasks[2])->assertJson(['applicant_status' => 'Awaiting Decision']);
        $this->assertSame(4, (int) $a->fresh()->status_id);

        $this->assertSame(['2→3', '3→4'], $this->statusHistory($a));
    }

    public function test_one_completion_that_finishes_everything_goes_straight_to_awaiting_decision(): void
    {
        [$a, $tasks] = $this->withTasks(1);

        $this->complete($a, $tasks[0])->assertJson(['applicant_status' => 'Awaiting Decision']);
        $this->assertSame(['2→4'], $this->statusHistory($a));
    }

    public function test_archived_tasks_do_not_hold_it_back_and_later_statuses_are_left_alone(): void
    {
        [$a, $tasks] = $this->withTasks(3);
        $this->complete($a, $tasks[0]);
        $this->actingAs(User::find(7))->deleteJson(route('admission.destory.task'), ['applicant' => $a->id, 'recordid' => $tasks[1]->id])->assertOk();
        $this->complete($a, $tasks[2])->assertJson(['applicant_status' => 'Awaiting Decision']);
        $this->assertSame(4, (int) $a->fresh()->status_id);

        // completing a task once the application is Accepted does not move it
        [$b, $more] = $this->withTasks(2, 5);
        $this->complete($b, $more[0])->assertJson(['applicant_status' => null]);
        $this->complete($b, $more[1]);
        $this->assertSame(5, (int) $b->fresh()->status_id);
        $this->assertSame([], $this->statusHistory($b));
    }

    public function test_the_task_log_records_the_status_it_was_completed_from(): void
    {
        [$a, $tasks] = $this->withTasks(2);
        $tasks[0]->update(['status' => 'In Progress']);
        $this->complete($a, $tasks[0]);

        $log = DB::table('applicant_task_logs')->where('applicant_tasks_id', $tasks[0]->id)->where('actions', 'Status Changed')->orderByDesc('id')->first();
        $this->assertSame('In Progress', $log->prev_field_value);
        $this->assertSame('Completed', $log->current_field_value);
    }

    public function test_nothing_moves_while_no_task_is_completed(): void
    {
        [$a] = $this->withTasks(2);
        $this->assertNull(ApplicantTaskStatus::sync($a->id, 7));
        $this->assertSame(2, (int) $a->fresh()->status_id);
    }
}
