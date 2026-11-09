<?php

namespace Tests\Feature;

use App\Jobs\TaskAssignment\CreateStudentEmailJob;
use App\Jobs\TaskAssignment\SendStudentWelcomeLetterJob;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Services\StudentEmail\StudentEmailProvisioner;
use App\Services\StudentEmail\WelcomeLetterSender;
use App\Services\StudentEmail\WorkspaceDirectory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakeWorkspace;
use Tests\TestCase;

/**
 * After the @lcc.ac.uk account exists, the welcome letter ("Welcome Message to Students")
 * goes to the institutional and personal addresses the way staff send it, and "Send
 * welcome email, information pack & text message" is completed; student-email:create
 * retries both steps.
 */
class StudentWelcomeLetterTest extends TestCase
{
    use DatabaseTransactions;

    private FakeWorkspace $google;

    protected function setUp(): void
    {
        parent::setUp();
        config(['student_email.address_prefix' => '', 'student_email.google.org_unit' => '/']);
        Storage::fake('local');
        Storage::fake('s3');
        Queue::fake();
        Mail::fake();
        $this->google = new FakeWorkspace();
        $this->app->instance(WorkspaceDirectory::class, $this->google);
    }

    /** An inactive test rule with this action, on a task list id that is deliberately not 10 / 13. */
    private function rule(string $job, int $taskList): int
    {
        return DB::table('task_assignment_rules')->insertGetId(['task_list_id' => $taskList, 'trigger' => 'student_created', 'priority' => 50, 'active' => 0, 'after_assign_job' => $job, 'created_by' => 1, 'created_at' => now()]);
    }

    /** A real converted student (so the letter's tags have data), given fresh Pending email + welcome tasks. */
    private function student(): array
    {
        $student = DB::table('students')->whereNotNull('registration_no')->whereNotNull('date_of_birth')
            ->whereExists(fn ($q) => $q->from('student_contacts')->whereColumn('student_contacts.student_id', 'students.id'))
            ->whereExists(fn ($q) => $q->from('student_course_relations')->whereColumn('student_course_relations.student_id', 'students.id'))
            ->orderByDesc('id')->first();
        $this->assertNotNull($student);
        $reg = 'LCCW'.random_int(100000, 999999);
        DB::table('students')->where('id', $student->id)->update(['registration_no' => $reg]);
        DB::table('student_contacts')->where('student_id', $student->id)->update(['institutional_email' => null, 'personal_email' => 'own'.$student->id.'@example.com']);
        // the student's real tasks from rules with these actions (e.g. local rules 199 / 202) would be found too
        DB::table('student_tasks')->where('student_id', $student->id)
            ->whereIn('rule_id', DB::table('task_assignment_rules')->whereIn('after_assign_job', [CreateStudentEmailJob::class, SendStudentWelcomeLetterJob::class])->pluck('id'))
            ->update(['rule_id' => null]);
        // the tasks are found by their rule's action, so any task list ids do
        $lists = DB::table('task_lists')->orderByDesc('id')->limit(2)->pluck('id');
        $email = DB::table('student_tasks')->insertGetId(['student_id' => $student->id, 'task_list_id' => $lists[0], 'status' => 'Pending', 'assigned_via' => 'auto', 'rule_id' => $this->rule(CreateStudentEmailJob::class, $lists[0]), 'created_by' => 1, 'created_at' => now()]);
        $welcome = DB::table('student_tasks')->insertGetId(['student_id' => $student->id, 'task_list_id' => $lists[1], 'status' => 'Pending', 'assigned_via' => 'auto', 'rule_id' => $this->rule(SendStudentWelcomeLetterJob::class, $lists[1]), 'created_by' => 1, 'created_at' => now()]);

        return [(int) $student->id, $email, $welcome, strtolower($reg).'@lcc.ac.uk', 'own'.$student->id.'@example.com'];
    }

    public function test_after_the_account_the_welcome_letter_goes_to_both_addresses_and_both_tasks_complete(): void
    {
        $this->google->configured = true;
        [$student, $emailTask, $welcomeTask, $address, $personal] = $this->student();

        app()->call([new CreateStudentEmailJob($student, $emailTask, 202), 'handle']);

        $this->assertSame('Completed', DB::table('student_tasks')->where('id', $emailTask)->value('status'));
        $this->assertSame('Completed', DB::table('student_tasks')->where('id', $welcomeTask)->value('status'));
        $doc = DB::table('student_letters_documents')->where('student_id', $student)->orderByDesc('id')->first();
        $this->assertSame('Welcome Message to Students', $doc->display_file_name);
        $this->assertSame('local', $doc->disk_type);
        Storage::disk('local')->assertExists('public/students/'.$student.'/'.$doc->current_file_name);
        Queue::assertPushed(UserMailerJob::class, function ($job) use ($address, $personal) {
            return $job->to === [$address, $personal]
                && $job->mailable instanceof CommunicationSendMail;
        });
    }

    public function test_no_welcome_letter_while_the_address_is_not_made(): void
    {
        [$student, $emailTask, $welcomeTask] = $this->student(); // Google not connected

        app()->call([new CreateStudentEmailJob($student, $emailTask, 202), 'handle']);

        $this->assertSame('Pending', DB::table('student_tasks')->where('id', $welcomeTask)->value('status'));
        Queue::assertNotPushed(UserMailerJob::class);
    }

    public function test_either_rule_may_run_first_and_the_letter_goes_once(): void
    {
        $this->google->configured = true;
        [$student, $emailTask, $welcomeTask] = $this->student();

        // the welcome rule's action first: no address yet, so it only waits
        app()->call([new SendStudentWelcomeLetterJob($student, $welcomeTask), 'handle']);
        $this->assertSame('Pending', DB::table('student_tasks')->where('id', $welcomeTask)->value('status'));
        $this->assertSame('Welcome letter waiting', DB::table('student_task_log')->where('student_tasks_id', $welcomeTask)->latest('id')->value('actions'));
        Queue::assertNotPushed(UserMailerJob::class);

        // then the email rule's action: the account, then the letter
        app()->call([new CreateStudentEmailJob($student, $emailTask), 'handle']);
        $this->assertSame('Completed', DB::table('student_tasks')->where('id', $welcomeTask)->value('status'));

        // a late run of the welcome action does nothing more
        app()->call([new SendStudentWelcomeLetterJob($student, $welcomeTask), 'handle']);
        Queue::assertPushed(UserMailerJob::class, 1);
    }

    public function test_the_retry_command_finishes_both_steps_and_recognises_its_own_account(): void
    {
        $this->google->configured = true;
        [$student, $emailTask, $welcomeTask, $address] = $this->student();
        // an earlier run created the account, then stopped before the rest
        $this->google->accounts[$address] = now()->toIso8601String();
        $this->google->exists = true;
        DB::table('student_archives')->insert(['student_id' => $student, 'table' => 'google_workspace', 'field_name' => 'account_created', 'field_new_value' => $address, 'created_by' => 1, 'created_at' => now()]);

        $this->artisan('student-email:create', ['student' => $student])
            ->expectsOutputToContain('Welcome letter: Welcome letter sent')
            ->assertSuccessful();

        $this->assertSame('Completed', DB::table('student_tasks')->where('id', $emailTask)->value('status'));
        $this->assertSame('Completed', DB::table('student_tasks')->where('id', $welcomeTask)->value('status'));
        $this->assertSame($address, DB::table('student_contacts')->where('student_id', $student)->value('institutional_email'));

        // run again: both already done
        $this->artisan('student-email:create', ['student' => $student])
            ->expectsOutputToContain('already done')
            ->expectsOutputToContain('already sent')
            ->assertSuccessful();
    }

    public function test_the_retry_command_needs_a_task_with_the_welcome_action(): void
    {
        $this->google->configured = true;
        [$student, $emailTask, $welcomeTask] = $this->student();
        DB::table('student_tasks')->where('id', $welcomeTask)->update(['rule_id' => null]); // added by hand, no rule

        $this->artisan('student-email:create', ['student' => $student])
            ->expectsOutputToContain('no task whose rule runs the welcome-letter action')
            ->assertSuccessful();
        Queue::assertNotPushed(UserMailerJob::class);
    }

    public function test_the_status_command_shows_our_records_and_google(): void
    {
        $this->google->configured = true;
        [$student, $emailTask, , $address] = $this->student();
        app()->call([new CreateStudentEmailJob($student, $emailTask), 'handle']);

        $this->artisan('student-email:status', ['who' => (string) $student])
            ->expectsOutputToContain('Google: '.$address.' exists.')
            ->assertSuccessful();
        $this->artisan('student-email:status', ['who' => $address])
            ->expectsOutputToContain('Student #'.$student)
            ->assertSuccessful();
        $this->artisan('student-email:status', ['who' => 'nobody'.$student.'@lcc.ac.uk'])
            ->expectsOutputToContain('does NOT exist')
            ->assertSuccessful();
    }

    public function test_welcome_only_needs_the_address_first(): void
    {
        [$student] = $this->student();

        $this->artisan('student-email:create', ['student' => $student, '--welcome-only' => true])
            ->expectsOutputToContain('no institutional email yet')
            ->assertFailed();
    }

    public function test_with_the_switch_on_both_student_emails_skip_the_local_redirect(): void
    {
        config(['student_email.ignore_mail_redirect' => true, 'mail.redirect_to' => 'redirect@example.com']);
        $this->google->configured = true;
        [$student, $emailTask, , $address, $personal] = $this->student();

        app()->call([new CreateStudentEmailJob($student, $emailTask), 'handle']);

        // account details: sent on the separate "student_direct" mailer, which the redirect does not reach
        Mail::mailer('student_direct')->assertSent(\App\Mail\StudentWorkspaceAccount::class, fn ($m) => $m->hasTo($personal));
        // welcome letter: the SMTP job is told to skip the redirect
        Queue::assertPushed(UserMailerJob::class, fn ($job) => $job->direct === true && $job->to === [$address, $personal]);

        $spy = new class {
            public array $calls = [];
            public function __call($name, $args) { $this->calls[] = $name; return $this; }
        };
        $this->app->bind('user.mailer', fn () => $spy);
        (new UserMailerJob([], [$personal], new CommunicationSendMail('Hi', '<p>x</p>', []), [], true))->handle();
        $this->assertNotContains('alwaysTo', $spy->calls);
    }

    public function test_letters_sent_through_the_smtp_job_follow_the_local_redirect(): void
    {
        config(['mail.redirect_to' => 'redirect@example.com']);
        $spy = new class {
            public array $calls = [];
            public function __call($name, $args) { $this->calls[] = [$name, $args]; return $this; }
        };
        $this->app->bind('user.mailer', fn () => $spy);

        (new UserMailerJob([], ['student@example.com'], new CommunicationSendMail('Hello', '<p>Hi</p>', [])))->handle();

        $this->assertContains(['alwaysTo', ['redirect@example.com']], $spy->calls);
    }
}
