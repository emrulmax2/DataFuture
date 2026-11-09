<?php

namespace Tests\Feature;

use App\Jobs\TaskAssignment\SendInductionInvitationJob;
use App\Jobs\TaskAssignment\UpdateStudentStatusToSlcNewJob;
use App\Jobs\UserMailerJob;
use App\Mail\ConversionErrorReportMail;
use App\Mail\StudentInductionInvitation;
use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Models\TaskAssignmentRule;
use App\Services\OperationsGatherClient;
use App\Services\StudentEmail\InductionEventDetails;
use App\Services\StudentEmail\InductionInvitationSender;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The induction invitation action (user, 30 Oct 2026): course booking link → Gather event
 * in Operations → the user's email template to the institutional and personal addresses,
 * then the task completed; anything in the way is mailed to CONVERSION_ERROR_REPORT_TO.
 */
class StudentInductionInvitationTest extends TestCase
{
    use DatabaseTransactions;

    private Student $student;
    private StudentTask $task;
    /** What the faked Operations answers: [status, body]. */
    private array $operations = [404, ['message' => 'No event with that link.']];

    /** As Operations returns the live September 2026 form (event 14). */
    private function event(array $overrides = []): array
    {
        return array_merge([
            'id' => 14,
            'slug' => 'september-2026-induction-registration-form',
            'title' => 'September 2026 Induction Registration Form',
            'status' => 'live',
            'visibility' => 'private',
            'bookable' => true,
            'venue' => 'Thursday, 03 Sep 2026, Avicenna House, 258-262 Romford Rd, London E7 9HZ, 1st Induction: 09:30 am to 01:00 pm',
            'description_text' => "Dear Student,\n\nPlease complete this form to select your preferred induction session for the September 2026 intake.\n\n"
                ."Induction Day – Thursday, 03 September 2026\n\nVenue: Avicenna House, 258–262 Romford Road, London E7 9HZ\n\n"
                ."1st Induction – 9:30 AM to 1:00 PM\nHND in Business (Entrepreneurship and Small Business Management)\n\n"
                ."2nd Induction – 2:00 PM to 5:30 PM\nHND in Hospitality Management\nHND in Health and Social Care Practice (Social and Community Work)\n\n"
                ."Please note: If you are unable to attend the induction session indicated for your course, you may attend the other induction session on the same day.",
            'booking_url' => 'https://ops.test/book/september-2026-induction-registration-form',
            'sessions' => [
                ['id' => 23, 'date' => '2026-09-03', 'start_time' => '09:30', 'end_time' => '13:00', 'capacity' => 150, 'remaining' => 100, 'sold_out' => false, 'started' => false],
                ['id' => 24, 'date' => '2026-09-03', 'start_time' => '14:00', 'end_time' => '17:30', 'capacity' => 150, 'remaining' => 0, 'sold_out' => true, 'started' => false],
            ],
            'tickets' => [['id' => 21, 'name' => 'HND in Business']],
        ], $overrides);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(['ops.test/*' => fn () => Http::response($this->operations[1], $this->operations[0])]);
        Mail::fake();
        Bus::fake([UserMailerJob::class]);
        config([
            'services.operations.url' => 'https://ops.test',
            'services.operations.api_key' => 'test-key',
            'conversion_report.to' => 'limon@lcc.ac.uk',
            'student_email.ignore_mail_redirect' => false,
        ]);
        $this->travelTo(Carbon::parse('2026-08-20 10:00:00')); // before the induction day

        $student = Student::where('registration_no', 'LCC20260894')->first();
        if (!$student || !optional($student->activeCR)->creation) {
            $this->markTestSkipped('LCC20260894 with a course is not in this database.');
        }
        $this->student = $student;
        $this->student->activeCR->creation->update(['induction_booking_link' => 'https://lcc_operation_management.test/book/september-2026-induction-registration-form']);
        StudentContact::where('student_id', $student->id)->update(['institutional_email' => 'lcc20260894@lcc.ac.uk', 'personal_email' => 'costela@example.com']);

        TaskAssignmentRule::where('after_assign_job', UpdateStudentStatusToSlcNewJob::class)->update(['after_assign_job' => null]); // keep SLC New out of it
        $rule = TaskAssignmentRule::where('after_assign_job', SendInductionInvitationJob::class)->first();
        $this->task = StudentTask::create(['student_id' => $student->id, 'task_list_id' => $rule->task_list_id ?? 11, 'status' => 'Pending', 'rule_id' => $rule->id ?? null, 'created_by' => 1]);
    }

    private function fakeOperations(array $event, int $status = 200): void
    {
        $this->operations = [$status, $status === 200 ? ['data' => $event] : ['message' => 'No event with that link.']];
    }

    public function test_the_slug_is_read_from_every_kind_of_booking_link(): void
    {
        $this->assertSame('autumn-2026', OperationsGatherClient::slugFromLink('https://lcc_operation_management.test/book/autumn-2026'));
        $this->assertSame('autumn-2026', OperationsGatherClient::slugFromLink('https://lcc_operation_management.test/book/event/autumn-2026?x=1'));
        $this->assertSame('autumn-2026', OperationsGatherClient::slugFromLink('https://lcc_operation_management.test/d/gather/admin/events/autumn-2026'));
        $this->assertNull(OperationsGatherClient::slugFromLink('https://lcc_operation_management.test/book/ticket/ABC123'));
        $this->assertNull(OperationsGatherClient::slugFromLink('https://forms.gle/xyz'));
        $this->assertNull(OperationsGatherClient::slugFromLink(''));
    }

    public function test_the_event_gives_the_date_venue_sessions_and_each_sessions_courses(): void
    {
        $event = InductionEventDetails::fromEvent($this->event());

        $this->assertSame('Thursday, 3 September 2026', $event['date_label']);
        $this->assertTrue($event['same_day']);
        $this->assertSame(['Avicenna House', '258–262 Romford Road, London E7 9HZ'], [$event['venue_name'], $event['venue_address']]);
        $this->assertSame('9:30 am – 1:00 pm', $event['sessions'][0]['time']);
        $this->assertSame(['HND in Business (Entrepreneurship and Small Business Management)'], $event['sessions'][0]['courses']);
        $this->assertSame(['HND in Hospitality Management', 'HND in Health and Social Care Practice (Social and Community Work)'], $event['sessions'][1]['courses']);
        $this->assertTrue($event['sessions'][1]['sold_out']);

        // a session that has started is left out
        $this->travelTo(Carbon::parse('2026-09-03 12:00:00'));
        $this->assertCount(1, InductionEventDetails::fromEvent($this->event())['sessions']);
    }

    public function test_it_emails_the_template_with_the_booking_link_and_completes_the_task(): void
    {
        $this->fakeOperations($this->event());

        $result = app(InductionInvitationSender::class)->send($this->student, $this->task, 1);

        $this->assertSame(InductionInvitationSender::DONE, $result['status']);
        Http::assertSent(fn ($request) => $request->url() === 'https://ops.test/api/gather/events/september-2026-induction-registration-form'
            && $request->hasHeader('X-Operations-Key', 'test-key'));
        Bus::assertDispatched(UserMailerJob::class, function ($job) {
            $this->assertSame(['lcc20260894@lcc.ac.uk', 'costela@example.com'], $job->to);
            $this->assertInstanceOf(StudentInductionInvitation::class, $job->mailable);
            $this->assertSame('Action required: Confirm your LCC induction attendance', $job->mailable->envelope()->subject);
            $html = $job->mailable->render();
            $this->assertSame(2, substr_count($html, 'href="https://ops.test/book/september-2026-induction-registration-form"')); // both "Confirm" buttons
            $this->assertStringContainsString('Dear '.InductionInvitationSender::firstName($this->student).',', $html);
            $this->assertStringContainsString('SEPTEMBER 2026', $html); // the intake, top right
            $this->assertStringContainsString('Thursday, 3 September 2026', $html);
            $this->assertStringContainsString('<strong>Avicenna House</strong><br>258–262 Romford Road, London E7 9HZ', $html);
            $this->assertStringContainsString('9:30 am – 1:00 pm', $html);
            $this->assertStringContainsString('HND in Hospitality Management<br>HND in Health and Social Care Practice (Social and Community Work)', $html);
            $this->assertStringContainsString('FULLY BOOKED', $html);
            $this->assertStringContainsString('There are two sessions on the same day.', $html);
            $this->assertStringNotContainsString('[[', $html);

            return true;
        });
        $this->assertSame('Completed', $this->task->fresh()->status);
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $this->task->id)->where('actions', 'Induction invitation sent')->exists());
        Mail::assertNothingSent(); // no problem to report
    }

    public function test_no_link_a_closed_event_or_operations_down_leaves_the_task_open_and_is_reported(): void
    {
        $sender = app(InductionInvitationSender::class);

        $this->student->activeCR->creation->update(['induction_booking_link' => null]);
        $this->assertSame(InductionInvitationSender::WAITING, $sender->send($this->student->fresh(), $this->task, 1)['status']);

        $this->student->activeCR->creation->update(['induction_booking_link' => 'https://lcc_operation_management.test/book/september-2026-induction-registration-form']);
        $this->fakeOperations($this->event(['status' => 'draft', 'bookable' => false]));
        $this->assertSame(InductionInvitationSender::WAITING, $sender->send($this->student->fresh(), $this->task, 1)['status']);

        $this->operations = [500, ['message' => 'down']];
        $this->assertSame(InductionInvitationSender::FAILED, $sender->send($this->student->fresh(), $this->task, 1)['status']);

        Bus::assertNotDispatched(UserMailerJob::class);
        $this->assertSame('Pending', $this->task->fresh()->status);
        Mail::assertSent(ConversionErrorReportMail::class, 3);
        Mail::assertSent(ConversionErrorReportMail::class, fn ($mail) => $mail->hasTo('limon@lcc.ac.uk')
            && str_contains($mail->problem, 'No induction booking link')
            && $mail->info['student']->id === $this->student->id);
        Mail::assertSent(ConversionErrorReportMail::class, fn ($mail) => str_contains($mail->problem, 'is draft, so its booking page is closed'));
    }

    public function test_the_action_waits_for_the_student_email_account(): void
    {
        $this->fakeOperations($this->event());
        StudentContact::where('student_id', $this->student->id)->update(['institutional_email' => null]);

        app()->call([new SendInductionInvitationJob($this->student->id, $this->task->id, $this->task->rule_id), 'handle']);

        Bus::assertNotDispatched(UserMailerJob::class);
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $this->task->id)->where('actions', 'Induction invitation waiting')->exists());

        // the account exists now (CreateStudentEmailJob runs the action again)
        StudentContact::where('student_id', $this->student->id)->update(['institutional_email' => 'lcc20260894@lcc.ac.uk']);
        app()->call([new SendInductionInvitationJob($this->student->id, $this->task->id, $this->task->rule_id), 'handle']);

        Bus::assertDispatched(UserMailerJob::class);
        $this->assertSame('Completed', $this->task->fresh()->status);
    }

    public function test_the_test_command_sends_to_the_test_address(): void
    {
        $this->fakeOperations($this->event());
        config(['mail.redirect_to' => 'limon@lcc.ac.uk']);

        $this->artisan('student-induction:send', ['student' => 'LCC20260894', '--check' => true])
            ->expectsOutputToContain('Session 2: 2:00 pm – 5:30 pm FULL')
            ->assertExitCode(0)->run();
        Bus::assertNotDispatched(UserMailerJob::class);

        $this->artisan('student-induction:send', ['student' => 'LCC20260894'])->assertExitCode(0)->run();
        Bus::assertDispatchedSync(UserMailerJob::class, fn ($job) => $job->to === ['limon@lcc.ac.uk']);
    }
}
