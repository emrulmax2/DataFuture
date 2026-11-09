<?php

namespace Tests\Feature;

use App\Jobs\ProcessStudents;
use App\Jobs\TaskAssignment\SendInductionInvitationJob;
use App\Listeners\StudentActionFailureSubscriber;
use App\Listeners\StudentConversionLogSubscriber;
use App\Mail\ConversionErrorReportMail;
use App\Models\Applicant;
use App\Models\Student;
use App\Models\StudentConversionLog;
use App\Services\Conversion\ConversionErrorReport;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

/**
 * Conversion & Task Automation problems are emailed to CONVERSION_ERROR_REPORT_TO
 * (user, 30 Oct 2026).
 */
class ConversionErrorReportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Cache::flush();
        config(['conversion_report.to' => 'limon@lcc.ac.uk, registry@lcc.ac.uk', 'conversion_report.repeat_minutes' => 30]);
    }

    /** A queue job whose payload carries $command, as the worker hands it to the listeners. */
    private function queueJob(object $command): Job
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('payload')->andReturn(['data' => ['commandName' => get_class($command), 'command' => serialize($command)]]);

        return $job;
    }

    public function test_a_report_names_the_student_and_goes_to_every_address_once_per_window(): void
    {
        $student = Student::whereNotNull('registration_no')->orderByDesc('id')->firstOrFail();

        ConversionErrorReport::report('Action: Welcome letter', 'Welcome letter set #116 not found.', ['student_id' => $student->id, 'task_id' => 5]);
        ConversionErrorReport::report('Action: Welcome letter', 'Welcome letter set #116 not found.', ['student_id' => $student->id, 'task_id' => 5]); // a retry

        Mail::assertSent(ConversionErrorReportMail::class, 1);
        Mail::assertSent(ConversionErrorReportMail::class, function ($mail) use ($student) {
            $this->assertTrue($mail->hasTo('limon@lcc.ac.uk') && $mail->hasTo('registry@lcc.ac.uk'));
            $this->assertSame('[Conversion problem] Action: Welcome letter – '.$student->registration_no, $mail->envelope()->subject);
            $html = $mail->render();
            $this->assertStringContainsString('Welcome letter set #116 not found.', $html);
            $this->assertStringContainsString($student->registration_no, $html);
            $this->assertStringContainsString('/student/process/'.$student->id, $html);

            return true;
        });

        // another problem, or the same one after the window, is sent
        ConversionErrorReport::report('Action: Welcome letter', 'No institutional or personal email.', ['student_id' => $student->id]);
        $this->travel(31)->minutes();
        ConversionErrorReport::report('Action: Welcome letter', 'Welcome letter set #116 not found.', ['student_id' => $student->id, 'task_id' => 5]);
        Mail::assertSent(ConversionErrorReportMail::class, 3);
    }

    public function test_a_conversion_step_that_halts_the_chain_is_reported(): void
    {
        $applicant = Applicant::orderByDesc('id')->firstOrFail();
        $command = (new ProcessStudents($applicant))->withBatchId('test-batch-'.uniqid());
        $failure = new \RuntimeException('S3 refused the upload');

        app(StudentConversionLogSubscriber::class)->handleJobFailed(new JobFailed('sync', $this->queueJob($command), $failure));

        $this->assertSame(StudentConversionLog::STATUS_FAILED, StudentConversionLog::where('batch_id', $command->batchId)->value('status'));
        Mail::assertSent(ConversionErrorReportMail::class, fn ($mail) => $mail->step === 'Conversion: Create Student Record'
            && $mail->problem === 'The conversion stopped: S3 refused the upload'
            && $mail->info['applicant']->id === $applicant->id);
    }

    public function test_one_failed_attempt_that_may_still_be_retried_is_not_reported(): void
    {
        $applicant = Applicant::orderByDesc('id')->firstOrFail();
        $command = (new ProcessStudents($applicant))->withBatchId('test-batch-'.uniqid());

        app(StudentConversionLogSubscriber::class)->handleJobExceptionOccurred(new \Illuminate\Queue\Events\JobExceptionOccurred('sync', $this->queueJob($command), new \RuntimeException('blip')));

        Mail::assertNothingSent();
    }

    public function test_a_queued_student_action_that_fails_is_reported(): void
    {
        $student = Student::orderByDesc('id')->firstOrFail();
        $command = new SendInductionInvitationJob($student->id, 77, 198);

        app(StudentActionFailureSubscriber::class)->handleJobFailed(new JobFailed('database', $this->queueJob($command), new \RuntimeException('Operations timed out')));

        Mail::assertSent(ConversionErrorReportMail::class, fn ($mail) => str_starts_with($mail->step, 'Action: Send the induction invitation')
            && $mail->problem === 'The action failed on the queue: Operations timed out'
            && $mail->info['task_id'] === 77 && $mail->info['rule_id'] === 198);
    }

    public function test_an_automation_action_that_throws_while_assigning_is_reported(): void
    {
        \App\Models\TaskAssignmentRule::where('trigger', 'student_created')->update(['active' => 0]); // only this test's rule
        $list = \App\Models\TaskList::whereHas('processlist', fn ($q) => $q->where('phase', 'Live'))->orderBy('id')->firstOrFail();
        $student = Student::orderByDesc('id')->firstOrFail();
        \App\Models\StudentTask::withTrashed()->where('student_id', $student->id)->where('task_list_id', $list->id)->forceDelete(); // rolled back
        $rule = \App\Models\TaskAssignmentRule::create(['task_list_id' => $list->id, 'trigger' => 'student_created', 'priority' => 1, 'active' => 1, 'after_assign_job' => ExplodingTestAction::class, 'created_by' => 1]);
        config(['tasks.student_assignment_jobs' => [ExplodingTestAction::class => 'Exploding test action'], 'tasks.jobs_run' => 'sync']);

        app(\App\Services\TaskAssignment\TaskAssigner::class)->applyStudent($student, \App\Services\TaskAssignment\TaskAssigner::TRIGGER_STUDENT_CREATED, [], true);

        Mail::assertSent(ConversionErrorReportMail::class, fn ($mail) => $mail->step === 'Action: Exploding test action'
            && $mail->problem === 'The action failed: boom'
            && $mail->info['student']->id === $student->id
            && $mail->info['rule_id'] === $rule->id);
    }

    public function test_reporting_never_breaks_the_caller(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        ConversionErrorReport::report('Conversion: Copy Emails', 'Something broke.', []);

        $this->assertTrue(true); // no exception escaped
    }
}

/** A student action that always fails, for the report test. */
class ExplodingTestAction
{
    public function __construct(public int $studentId, public int $studentTaskId, public ?int $ruleId = null)
    {
    }

    public function handle(): void
    {
        throw new \RuntimeException('boom');
    }
}
