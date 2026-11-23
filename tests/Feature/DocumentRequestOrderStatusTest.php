<?php

namespace Tests\Feature;

use App\Models\StudentOrder;
use App\Models\StudentTask;
use App\Models\User;
use App\Services\DocumentRequestOrderStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Task Manager "Document Requests from Student portal" (task 20, user 23 Nov 2026): completing a
 * request's task leaves its student portal order Completed once none of the order's requests is
 * still open. Fixtures are copies of a real request form, made inside the rolled-back transaction.
 */
class DocumentRequestOrderStatusTest extends TestCase
{
    use DatabaseTransactions;

    private const TASK = 20;

    private object $template;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Mail::fake();
        $template = DB::table('student_document_request_forms')->whereNull('deleted_at')->whereNotNull('student_order_id')->orderByDesc('id')->first();
        if (!$template) {
            $this->markTestSkipped('No document request form in this database.');
        }
        $this->template = $template;
    }

    /** An In Progress order with one request (and task) per given task status. */
    private function order(array $taskStatuses): array
    {
        $order = (array) DB::table('student_orders')->where('id', $this->template->student_order_id)->first();
        unset($order['id']);
        $order['invoice_number'] = 'INV-TEST-'.uniqid();
        $order['idempotency_key'] = null;
        $order['status'] = 'In Progress';
        $orderId = DB::table('student_orders')->insertGetId($order);

        $tasks = [];
        foreach ($taskStatuses as $status) {
            $form = (array) $this->template;
            unset($form['id']);
            $form['student_order_id'] = $orderId;
            $form['status'] = $status === 'Canceled' ? 'Rejected' : 'Approved';
            // no letter generated yet: the older "every letter generated" rule must not complete it
            $form['letter_generated_count'] = 0;
            $formId = DB::table('student_document_request_forms')->insertGetId($form);
            $tasks[] = DB::table('student_tasks')->insertGetId([
                'student_id' => $this->template->student_id,
                'task_list_id' => self::TASK,
                'student_document_request_form_id' => $formId,
                'status' => $status,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$orderId, $tasks];
    }

    private function orderStatus(int $orderId): string
    {
        return (string) StudentOrder::find($orderId)->status;
    }

    private function complete(array $payload)
    {
        return $this->actingAs(User::find(7))->post(route('task.manager.update.task.status'), $payload + [
            'task_id' => self::TASK,
            'status' => 'Completed',
            'phase' => 'Live',
        ], ['Accept' => 'application/json']);
    }

    public function test_completing_the_only_request_completes_the_order(): void
    {
        [$orderId, [$taskId]] = $this->order(['Pending']);

        $this->complete(['student_ids' => [$this->template->student_id], 'student_task_id' => $taskId])->assertOk();

        $this->assertSame('Completed', StudentTask::find($taskId)->status);
        $this->assertSame('Completed', $this->orderStatus($orderId));
    }

    public function test_the_order_waits_for_every_request(): void
    {
        [$orderId, [$first, $second]] = $this->order(['Pending', 'Pending']);

        $this->complete(['student_ids' => [$this->template->student_id], 'student_task_id' => $first])->assertOk();
        $this->assertSame('In Progress', $this->orderStatus($orderId));

        $this->complete(['student_ids' => [$this->template->student_id], 'student_task_id' => $second])->assertOk();
        $this->assertSame('Completed', $this->orderStatus($orderId));
    }

    public function test_a_cancelled_request_does_not_hold_the_order_open(): void
    {
        [$orderId, [$open]] = $this->order(['Pending', 'Canceled']);

        $this->complete(['student_ids' => [$this->template->student_id], 'student_task_id' => $open])->assertOk();

        $this->assertSame('Completed', $this->orderStatus($orderId));
    }

    public function test_bulk_complete_changes_only_the_ticked_requests(): void
    {
        [$orderA, [$ticked]] = $this->order(['Pending']);
        [$orderB, [$other]] = $this->order(['Pending']);
        [$orderC, [$cancelled]] = $this->order(['Canceled']);

        $this->complete([
            'student_ids' => [$this->template->student_id],
            'student_task_id' => 0,
            'student_task_ids' => [$ticked],
        ])->assertOk();

        $this->assertSame('Completed', StudentTask::find($ticked)->status);
        $this->assertSame('Pending', StudentTask::find($other)->status);
        $this->assertSame('Canceled', StudentTask::find($cancelled)->status);
        $this->assertSame('Completed', $this->orderStatus($orderA));
        $this->assertSame('In Progress', $this->orderStatus($orderB));
        $this->assertSame('In Progress', $this->orderStatus($orderC));
    }

    public function test_completing_from_the_student_process_tab_completes_the_order(): void
    {
        [$orderId, [$taskId]] = $this->order(['Pending']);

        $this->actingAs(User::find(7))->post(route('student.completed.task'), [
            'student' => $this->template->student_id,
            'recordid' => $taskId,
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertSame('Completed', $this->orderStatus($orderId));
    }

    public function test_an_order_with_every_request_cancelled_is_left_alone(): void
    {
        [$orderId, $tasks] = $this->order(['Canceled', 'Canceled']);

        $this->assertSame([], DocumentRequestOrderStatus::syncForTasks($tasks));
        $this->assertSame('In Progress', $this->orderStatus($orderId));
    }

    public function test_the_catch_up_command_lists_by_default_and_writes_with_apply(): void
    {
        [$orderId] = $this->order(['Completed']);

        $this->artisan('document-requests:complete-orders')->assertSuccessful();
        $this->assertSame('In Progress', $this->orderStatus($orderId));

        $this->artisan('document-requests:complete-orders', ['--apply' => true])->assertSuccessful();
        $this->assertSame('Completed', $this->orderStatus($orderId));
    }
}
