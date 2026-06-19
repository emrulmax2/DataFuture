<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ApplicantInterviewDocumentSyncController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Verifies the receiver endpoint that the LCC Operations app calls to attach an
 * interview-outcome PDF and complete the applicant's interview task.
 *
 * Runs against an in-memory sqlite connection (force with
 * DB_CONNECTION=sqlite DB_DATABASE=:memory:) and hand-rolls only the few tables
 * the controller touches — so it never runs the full migration set and never
 * touches the production DataFuture database. The controller is invoked directly
 * to bypass the Passport client-credentials middleware.
 */
class InterviewDocumentSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Run with DB_CONNECTION=sqlite DB_DATABASE=:memory: to keep this test off the real database.');
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('applicant_tasks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('applicant_id');
            $t->unsignedBigInteger('task_list_id');
            $t->string('status')->nullable();
            $t->unsignedBigInteger('task_status_id')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('applicant_documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('applicant_id');
            $t->unsignedBigInteger('document_setting_id')->nullable();
            $t->string('hard_copy_check')->nullable();
            $t->string('doc_type')->nullable();
            $t->string('disk_type')->nullable();
            $t->string('path')->nullable();
            $t->string('display_file_name')->nullable();
            $t->string('current_file_name')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('applicant_task_documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('applicant_task_id');
            $t->unsignedBigInteger('applicant_document_id');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('applicant_task_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('applicant_tasks_id');
            $t->string('actions')->nullable();
            $t->string('field_name')->nullable();
            $t->string('prev_field_value')->nullable();
            $t->string('current_field_value')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        config()->set('services.sms_sync.created_by', 1);
        config()->set('services.sms_sync.interview_task_list_id', 7);
    }

    private function seedInterviewTask(int $applicantId = 999): int
    {
        return DB::table('applicant_tasks')->insertGetId([
            'applicant_id' => $applicantId,
            'task_list_id' => 7,
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function invoke(int $applicant, array $payload)
    {
        $request = Request::create("/api/applicants/{$applicant}/interview-document", 'POST', $payload);
        $request->headers->set('Accept', 'application/json');

        return (new ApplicantInterviewDocumentSyncController())->store($request, $applicant);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'path' => 'https://smschurchill.s3.eu-west-2.amazonaws.com/public/applicants/999/12_interview-APP123.pdf',
            'current_file_name' => '12_interview-APP123.pdf',
            'display_file_name' => 'College Interview Outcome',
            'doc_type' => 'pdf',
            'outcome' => 'Pass',
        ], $overrides);
    }

    public function test_attaches_document_completes_task_and_resolves_user_by_email(): void
    {
        $taskId = $this->seedInterviewTask(999);
        $userId = DB::table('users')->insertGetId(['name' => 'Jane', 'email' => 'jane@lcc.ac.uk']);

        $response = $this->invoke(999, $this->payload(['created_by_email' => 'jane@lcc.ac.uk']));
        $body = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());

        // Document recorded, attributed to the resolved DataFuture user (not the fallback).
        $this->assertDatabaseHas('applicant_documents', [
            'id' => $body['document_id'],
            'applicant_id' => 999,
            'doc_type' => 'pdf',
            'current_file_name' => '12_interview-APP123.pdf',
            'created_by' => $userId,
        ]);

        // Linked to the task.
        $this->assertDatabaseHas('applicant_task_documents', [
            'applicant_task_id' => $taskId,
            'applicant_document_id' => $body['document_id'],
            'created_by' => $userId,
        ]);

        // Task completed (Pass -> status 1), attributed to the same user.
        $this->assertDatabaseHas('applicant_tasks', [
            'id' => $taskId,
            'status' => 'Completed',
            'task_status_id' => 1,
            'updated_by' => $userId,
        ]);

        // Audit log written.
        $this->assertDatabaseHas('applicant_task_logs', [
            'applicant_tasks_id' => $taskId,
            'actions' => 'Document',
            'current_field_value' => $body['document_id'],
            'created_by' => $userId,
        ]);
    }

    public function test_falls_back_to_system_user_when_email_unknown(): void
    {
        config()->set('services.sms_sync.created_by', 7);
        $this->seedInterviewTask(999);

        $response = $this->invoke(999, $this->payload(['created_by_email' => 'nobody@example.com']));
        $body = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertDatabaseHas('applicant_documents', [
            'id' => $body['document_id'],
            'created_by' => 7,
        ]);
    }

    public function test_fail_outcome_sets_task_status_two(): void
    {
        $taskId = $this->seedInterviewTask(999);

        $this->invoke(999, $this->payload(['outcome' => 'Fail']));

        $this->assertDatabaseHas('applicant_tasks', [
            'id' => $taskId,
            'status' => 'Completed',
            'task_status_id' => 2,
        ]);
    }

    public function test_is_idempotent_on_retry(): void
    {
        $this->seedInterviewTask(999);

        $first = $this->invoke(999, $this->payload(['created_by_email' => 'jane@lcc.ac.uk']))->getData(true);
        $second = $this->invoke(999, $this->payload(['created_by_email' => 'jane@lcc.ac.uk']))->getData(true);

        $this->assertTrue($second['idempotent'] ?? false);
        $this->assertSame($first['document_id'], $second['document_id']);
        $this->assertSame(1, DB::table('applicant_documents')->where('current_file_name', '12_interview-APP123.pdf')->count());
    }

    public function test_returns_404_when_applicant_has_no_interview_task(): void
    {
        // No task seeded for applicant 1000.
        $response = $this->invoke(1000, $this->payload());

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(0, DB::table('applicant_documents')->count());
    }
}
