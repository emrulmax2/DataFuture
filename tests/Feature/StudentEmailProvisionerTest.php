<?php

namespace Tests\Feature;

use App\Jobs\TaskAssignment\CreateStudentEmailJob;
use App\Models\User;
use App\Services\StudentEmail\WorkspaceDirectory;
use Tests\Fakes\FakeWorkspace;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Mail\StudentWorkspaceAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * A new student's @lcc.ac.uk account (CreateStudentEmailJob → StudentEmailProvisioner):
 * Google account, institutional + login email, personal email kept, task completed and
 * the welcome email queued — or, without Google, the address set and the task left for staff.
 */
class StudentEmailProvisionerTest extends TestCase
{
    use DatabaseTransactions;

    private FakeWorkspace $google;

    protected function setUp(): void
    {
        parent::setUp();
        config(['student_email.initial_password' => 'Fallback-2026', 'student_email.change_at_first_login' => true, 'student_email.google.org_unit' => '/Students', 'student_email.address_prefix' => '']);
        Storage::fake('s3');
        Storage::fake('local');                 // the welcome letter PDF, when it runs
        \Illuminate\Support\Facades\Queue::fake(); // and its email
        Mail::fake();
        $this->google = new FakeWorkspace();
        $this->app->instance(WorkspaceDirectory::class, $this->google);
    }

    /** A freshly converted student: login = personal address, no institutional email, the email task Pending. */
    private function student(array $contact = []): array
    {
        $reg = 'LCCT'.random_int(100000, 999999);
        $login = DB::table('student_users')->insertGetId(['name' => 'Test', 'email' => strtolower($reg).'.home@example.com', 'password' => 'x', 'gender' => 'Male', 'active' => 1, 'created_at' => now()]);
        $student = DB::table('students')->insertGetId(['student_user_id' => $login, 'registration_no' => $reg, 'first_name' => 'Sara', 'last_name' => 'Test', 'date_of_birth' => array_key_exists('dob', $contact) ? $contact['dob'] : '1986-11-26', 'created_by' => 1, 'created_at' => now()]);
        DB::table('student_contacts')->insert(['student_id' => $student, 'mobile' => '07000000000', 'personal_email' => array_key_exists('personal_email', $contact) ? $contact['personal_email'] : strtolower($reg).'.home@example.com', 'created_by' => 1, 'created_at' => now()]);
        $task = DB::table('student_tasks')->insertGetId(['student_id' => $student, 'task_list_id' => 10, 'status' => 'Pending', 'created_by' => 1, 'created_at' => now()]);

        return [$student, $task, $login, strtolower($reg).'@lcc.ac.uk', strtolower($reg).'.home@example.com'];
    }

    private function provision(int $student, int $task): void
    {
        app()->call([new CreateStudentEmailJob($student, $task, 202), 'handle']);
    }

    public function test_with_google_it_creates_the_account_emails_the_details_and_completes_the_task(): void
    {
        $this->google->configured = true;
        [$student, $task, $login, $email, $home] = $this->student(['personal_email' => null]);

        $this->provision($student, $task);

        // the password is the date of birth as DDMMYYYY (26-11-1986 → 26111986)
        $this->assertSame([[$email, 'Sara', 'Test', '26111986', true, '/Students']], $this->google->created);
        $this->assertSame([$email], $this->google->licensed);
        $contact = DB::table('student_contacts')->where('student_id', $student)->first();
        $this->assertSame($email, $contact->institutional_email);
        $this->assertSame(1, (int) $contact->institutional_email_verification);
        $this->assertSame($home, $contact->personal_email, 'the old login email is kept as the personal email');
        $this->assertSame($email, DB::table('student_users')->where('id', $login)->value('email'));
        $this->assertSame('x', DB::table('student_users')->where('id', $login)->value('password'), 'the portal password is not changed');
        $this->assertSame('Completed', DB::table('student_tasks')->where('id', $task)->value('status'));
        Mail::assertSent(StudentWorkspaceAccount::class, fn ($m) => $m->hasTo($home) && $m->email === $email && $m->password === '26111986');
        // no task whose rule has the welcome-letter action, so no letter (StudentWelcomeLetterTest covers it)
        $this->assertFalse(DB::table('student_letters')->where('student_id', $student)->where('letter_set_id', 116)->exists());
        $this->assertTrue(DB::table('student_archives')->where('student_id', $student)->where('field_name', 'institutional_email')->where('field_new_value', $email)->exists());
        $this->assertTrue(DB::table('student_archives')->where('student_id', $student)->where('table', 'google_workspace')->where('field_new_value', $email)->exists(), 'marked as created by us');
    }

    /** A student on a 2027 January course (their intake). */
    private function onIntake(int $student): string
    {
        $creation = DB::table('course_creations as cc')->join('semesters as s', 's.id', '=', 'cc.semester_id')->whereNull('cc.deleted_at')
            ->orderByDesc('s.id')->first(['cc.id', 's.name']);
        if (!$creation) {
            $this->markTestSkipped('No course creation with a semester.');
        }
        DB::table('student_course_relations')->insert(['student_id' => $student, 'course_creation_id' => $creation->id, 'active' => 1, 'created_by' => 1, 'created_at' => now()]);

        return $creation->name;
    }

    /** User / IT manager, 13 Nov 2026: the account goes in /Students/<intake> (made when missing) and joins student@lcc.ac.uk. */
    public function test_the_account_goes_in_the_intake_ou_and_joins_the_student_group(): void
    {
        config(['student_email.google.intake_ou_parent' => '/Students', 'student_email.google.student_group' => 'student@lcc.ac.uk']);
        $this->google->configured = true;
        [$student, $task, , $email] = $this->student(['personal_email' => 'own@example.com']);
        $intake = $this->onIntake($student);

        $this->provision($student, $task);

        $ou = '/Students/'.$intake;
        $this->assertSame([$ou], $this->google->ensured);
        $this->assertContains($ou, $this->google->orgUnits, 'made when it was missing');
        $this->assertSame($ou, $this->google->created[0][5], 'the account is created in it');
        $this->assertSame([$email], $this->google->groups['student@lcc.ac.uk']);
        $this->assertSame('Completed', DB::table('student_tasks')->where('id', $task)->value('status'));
        $log = (string) DB::table('student_task_log')->where('student_tasks_id', $task)->where('actions', 'Student email created')->value('current_field_value');
        $this->assertStringContainsString('OU '.$ou.' (new)', $log);
        $this->assertStringContainsString('added to student@lcc.ac.uk', $log);
        Mail::assertNotSent(\App\Mail\ConversionErrorReportMail::class);

        // a second student on the same intake: the OU is already there
        [$student2, $task2] = $this->student(['personal_email' => 'own2@example.com']);
        $this->onIntake($student2);
        $this->provision($student2, $task2);
        $this->assertStringContainsString('OU '.$ou.' ·', (string) DB::table('student_task_log')->where('student_tasks_id', $task2)->where('actions', 'Student email created')->value('current_field_value'));
    }

    public function test_if_google_refuses_the_ou_or_group_the_account_still_goes_ahead_and_it_is_reported(): void
    {
        config(['student_email.google.intake_ou_parent' => '/Students', 'student_email.google.student_group' => 'student@lcc.ac.uk']);
        $this->google->configured = true;
        $this->google->ouFail = 'unauthorized_client';
        $this->google->groupFail = 'unauthorized_client';
        [$student, $task] = $this->student(['personal_email' => 'own@example.com']);
        $this->onIntake($student);

        $this->provision($student, $task);

        $this->assertSame('/Students', $this->google->created[0][5], 'the usual OU (GOOGLE_WORKSPACE_STUDENT_OU)');
        $this->assertSame([], $this->google->groups);
        $this->assertSame('Completed', DB::table('student_tasks')->where('id', $task)->value('status'));
        $problem = (string) DB::table('student_task_log')->where('student_tasks_id', $task)->where('actions', 'Student email: OU / group')->value('current_field_value');
        $this->assertStringContainsString('not used', $problem);
        $this->assertStringContainsString('not added to student@lcc.ac.uk', $problem);
        Mail::assertSent(\App\Mail\ConversionErrorReportMail::class, fn ($m) => $m->step === 'Action: Create student email (OU / group)');
    }

    public function test_test_addresses_never_join_the_student_group_and_no_intake_keeps_the_usual_ou(): void
    {
        config(['student_email.address_prefix' => 'test.', 'student_email.google.intake_ou_parent' => '/Students', 'student_email.google.student_group' => 'student@lcc.ac.uk']);
        $this->google->configured = true;
        [$student, $task] = $this->student(['personal_email' => 'own@example.com']); // no course: no intake

        $this->provision($student, $task);

        $this->assertSame([], $this->google->ensured);
        $this->assertSame('/Students', $this->google->created[0][5]);
        $this->assertSame([], $this->google->groups);
        $this->assertStringContainsString('not added to student@lcc.ac.uk (test address)', (string) DB::table('student_task_log')->where('student_tasks_id', $task)->where('actions', 'Student email created')->value('current_field_value'));
    }

    public function test_without_google_it_sets_the_address_and_leaves_the_task_for_staff(): void
    {
        [$student, $task, $login, $email] = $this->student(['personal_email' => 'own@example.com']);

        $this->provision($student, $task);

        $this->assertSame([], $this->google->created);
        $this->assertSame($email, DB::table('student_contacts')->where('student_id', $student)->value('institutional_email'));
        $this->assertSame('own@example.com', DB::table('student_contacts')->where('student_id', $student)->value('personal_email'), 'an existing personal email stays');
        $this->assertSame($email, DB::table('student_users')->where('id', $login)->value('email'));
        $this->assertSame('Pending', DB::table('student_tasks')->where('id', $task)->value('status'));
        $this->assertFalse(DB::table('student_letters_documents')->where('student_id', $student)->exists());
        Mail::assertNotSent(StudentWorkspaceAccount::class); // no password mailed
        // the task is left for staff, so the problem is reported (CONVERSION_ERROR_REPORT_TO, 30 Oct 2026)
        Mail::assertSent(\App\Mail\ConversionErrorReportMail::class, fn ($m) => $m->step === 'Action: Create student email');
        $this->assertStringContainsString('Google not connected', (string) DB::table('student_task_log')->where('student_tasks_id', $task)->latest('id')->value('current_field_value'));
    }

    public function test_when_google_refuses_nothing_changes(): void
    {
        $this->google->configured = true;
        $this->google->fail = 'Google refused to create it: quota';
        [$student, $task, $login, $email, $home] = $this->student();

        $this->provision($student, $task);

        $this->assertNull(DB::table('student_contacts')->where('student_id', $student)->value('institutional_email'));
        $this->assertSame($home, DB::table('student_users')->where('id', $login)->value('email'));
        $this->assertSame('Pending', DB::table('student_tasks')->where('id', $task)->value('status'));
        $this->assertSame('Student email failed', DB::table('student_task_log')->where('student_tasks_id', $task)->latest('id')->value('actions'));
    }

    public function test_without_a_date_of_birth_or_fallback_it_does_not_call_google(): void
    {
        config(['student_email.initial_password' => '']);
        $this->google->configured = true;
        [$student, $task] = $this->student(['dob' => null]);

        $this->provision($student, $task);

        $this->assertSame([], $this->google->created);
        $this->assertSame('Pending', DB::table('student_tasks')->where('id', $task)->value('status'));
    }

    public function test_an_address_that_already_has_a_login_is_linked_instead_of_duplicated(): void
    {
        [$student, $task, $login, $email] = $this->student();
        $existing = DB::table('student_users')->insertGetId(['name' => 'Old', 'email' => $email, 'password' => 'y', 'gender' => 'Male', 'active' => 1, 'created_at' => now()]);

        $this->provision($student, $task);

        $this->assertSame($existing, (int) DB::table('students')->where('id', $student)->value('student_user_id'));
        $this->assertSame(1, DB::table('student_users')->where('email', $email)->count());
    }

    public function test_an_address_google_already_had_is_left_alone_and_no_password_is_sent(): void
    {
        $this->google->configured = true;
        $this->google->exists = true;
        [$student, $task] = $this->student();

        $this->provision($student, $task);

        Mail::assertNotSent(StudentWorkspaceAccount::class); // no password mailed
        // the task is left for staff, so the problem is reported (CONVERSION_ERROR_REPORT_TO, 30 Oct 2026)
        Mail::assertSent(\App\Mail\ConversionErrorReportMail::class, fn ($m) => $m->step === 'Action: Create student email');
        $this->assertSame('Pending', DB::table('student_tasks')->where('id', $task)->value('status'));
        $this->assertFalse(DB::table('student_archives')->where('student_id', $student)->where('table', 'google_workspace')->exists(), 'not ours, never deleted by a reset');
    }

    public function test_the_local_prefix_keeps_test_addresses_apart(): void
    {
        config(['student_email.address_prefix' => 'test.']);
        [$student, $task, , $email] = $this->student();

        $this->provision($student, $task);

        $this->assertSame('test.'.$email, DB::table('student_contacts')->where('student_id', $student)->value('institutional_email'));
    }

    public function test_the_password_only_goes_to_the_personal_email_never_a_college_address(): void
    {
        $this->google->configured = true;
        [$student, $task, , $email, $home] = $this->student(['personal_email' => null]);

        $this->provision($student, $task);
        Mail::assertSent(StudentWorkspaceAccount::class, fn ($m) => $m->hasTo($home) && !$m->hasTo($email) && count($m->to) === 1 && empty($m->cc) && empty($m->bcc));

        // a personal email that is really a college address: no password sent
        Mail::fake();
        [$other, $otherTask] = $this->student(['personal_email' => 'someone@lcc.ac.uk']);
        $this->provision($other, $otherTask);
        Mail::assertNotSent(StudentWorkspaceAccount::class); // no password mailed
        // the task is left for staff, so the problem is reported (CONVERSION_ERROR_REPORT_TO, 30 Oct 2026)
        Mail::assertSent(\App\Mail\ConversionErrorReportMail::class, fn ($m) => $m->step === 'Action: Create student email');
        $this->assertSame('Pending', DB::table('student_tasks')->where('id', $otherTask)->value('status'));
    }

    public function test_the_job_can_be_chosen_on_a_student_rule(): void
    {
        $this->assertArrayHasKey(CreateStudentEmailJob::class, config('tasks.student_assignment_jobs'));
        $html = $this->actingAs(User::find(7))->get(route('taskrules', ['subject' => 'student']))->assertOk()->getContent();
        $this->assertStringContainsString('Create the student @lcc.ac.uk email and complete the task', $html);
    }
}
