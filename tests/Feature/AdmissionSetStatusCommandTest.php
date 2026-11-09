<?php

namespace Tests\Feature;

use App\Models\Applicant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Services\StudentEmail\WorkspaceDirectory;
use Tests\Fakes\FakeWorkspace;
use Tests\TestCase;

/** admission:set-status — a quiet status change with a history row, for local testing. */
class AdmissionSetStatusCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_sets_the_status_and_writes_history_without_sending_anything(): void
    {
        Queue::fake();
        Http::preventStrayRequests();
        $a = Applicant::whereNotNull('submission_date')->orderByDesc('id')->firstOrFail();
        DB::table('applicants')->where('id', $a->id)->update(['status_id' => 6]);
        $letters = DB::table('applicant_letters')->where('applicant_id', $a->id)->count();

        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 5, '--keep-students' => true])
            ->expectsOutputToContain('6 Offer Placed → 5 Accepted')
            ->assertSuccessful();

        $this->assertSame(5, (int) $a->fresh()->status_id);
        $row = DB::table('applicant_archives')->where('applicant_id', $a->id)->where('field_name', 'status_id')->orderByDesc('id')->first();
        $this->assertSame(['6', '5', 7], [(string) $row->field_value, (string) $row->field_new_value, (int) $row->created_by]);
        $this->assertSame($letters, DB::table('applicant_letters')->where('applicant_id', $a->id)->count(), 'no letter');
        Queue::assertNothingPushed();
    }

    /** An applicant with no conversions yet, given one fake conversion (inside the test transaction). */
    private function converted(): array
    {
        $a = Applicant::whereNotNull('submission_date')
            ->whereNotExists(fn ($q) => $q->from('student_conversion_logs')->whereColumn('student_conversion_logs.applicant_id', 'applicants.id'))
            ->orderByDesc('id')->firstOrFail();
        DB::table('applicants')->where('id', $a->id)->update(['status_id' => 7]);
        // an old student that only shares the applicant id: must survive
        $legacy = DB::table('students')->insertGetId(['applicant_id' => $a->id, 'student_user_id' => 1, 'registration_no' => 'LEGACY'.$a->id, 'created_by' => 1, 'created_at' => '2020-01-01 00:00:00']);
        $login = DB::table('student_users')->insertGetId(['name' => 'Test', 'email' => 'reset-'.$a->id.'@example.test', 'password' => 'x', 'gender' => 'Male', 'active' => 1, 'created_at' => now()]);
        $batch = 'test-'.uniqid();
        DB::table('student_conversion_logs')->insert(['applicant_id' => $a->id, 'batch_id' => $batch, 'job_class' => 'App\\Jobs\\ProcessStudents', 'job_name' => 'Create Student Record', 'status' => 'completed', 'created_at' => now()]);
        $student = DB::table('students')->insertGetId(['applicant_id' => $a->id, 'student_user_id' => $login, 'registration_no' => 'LCCTEST'.$a->id, 'created_by' => 1, 'created_at' => now()]);
        DB::table('student_conversion_logs')->where('batch_id', $batch)->update(['student_id' => $student]);
        DB::table('student_contacts')->insert(['student_id' => $student, 'mobile' => '07000000000', 'created_by' => 1, 'created_at' => now()]);
        Storage::fake('local');
        Storage::disk('local')->put('public/students/'.$student.'/file.pdf', '%PDF');

        return [$a, $student, $legacy, $login];
    }

    public function test_it_removes_the_students_this_applicants_conversions_created(): void
    {
        [$a, $student, $legacy, $login] = $this->converted();

        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 5, '--force' => true])
            ->expectsOutputToContain('student #'.$student)
            ->assertSuccessful();

        $this->assertSame(5, (int) $a->fresh()->status_id);
        $this->assertFalse(DB::table('students')->where('id', $student)->exists());
        $this->assertSame(0, DB::table('student_contacts')->where('student_id', $student)->count());
        $this->assertSame(0, DB::table('student_conversion_logs')->where('applicant_id', $a->id)->count());
        $this->assertFalse(DB::table('student_users')->where('id', $login)->exists(), 'the login the conversion made, used by nothing else');
        $this->assertTrue(DB::table('students')->where('id', $legacy)->exists(), 'an older student sharing the applicant id stays');
        Storage::disk('local')->assertMissing('public/students/'.$student.'/file.pdf');
    }

    public function test_it_asks_first_and_a_dry_run_or_no_changes_nothing(): void
    {
        [$a, $student] = $this->converted();

        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 5, '--dry-run' => true])->expectsOutputToContain('Dry run')->assertSuccessful();
        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 5])
            ->expectsConfirmation('Delete the rows above and set the status?', 'no')
            ->expectsOutputToContain('Nothing changed')
            ->assertSuccessful();

        $this->assertSame(7, (int) $a->fresh()->status_id);
        $this->assertTrue(DB::table('students')->where('id', $student)->exists());
    }

    /** The converted student's Google account, as StudentEmailProvisioner leaves it. */
    private function withGoogle(int $student, string $email, ?string $googleCreated = null): FakeWorkspace
    {
        $google = new FakeWorkspace();
        $google->configured = true;
        $google->accounts[$email] = $googleCreated ?? now()->toIso8601String();
        $this->app->instance(WorkspaceDirectory::class, $google);
        DB::table('student_archives')->insert(['student_id' => $student, 'table' => 'google_workspace', 'field_name' => 'account_created', 'field_new_value' => $email, 'created_by' => 1, 'created_at' => now()]);
        DB::table('student_contacts')->where('student_id', $student)->update(['institutional_email' => $email]);

        return $google;
    }

    public function test_the_google_account_it_created_is_deleted_with_the_student(): void
    {
        [$a, $student] = $this->converted();
        $google = $this->withGoogle($student, 'test.lccx'.$student.'@lcc.ac.uk');

        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 5, '--force' => true])
            ->expectsOutputToContain('will be deleted')
            ->assertSuccessful();

        $this->assertSame(['test.lccx'.$student.'@lcc.ac.uk'], $google->deleted);
        $this->assertFalse(DB::table('students')->where('id', $student)->exists());
    }

    public function test_google_accounts_it_did_not_create_or_still_in_use_are_kept(): void
    {
        [$a, $student] = $this->converted();
        // Google says the account is a year older than the student: not one we made for them
        $google = $this->withGoogle($student, 'old'.$student.'@lcc.ac.uk', now()->subYear()->toIso8601String());

        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 5, '--force' => true])
            ->expectsOutputToContain('older than this student')
            ->assertSuccessful();
        $this->assertSame([], $google->deleted);

        // --keep-google leaves it alone too
        [$b, $other] = $this->converted();
        $google = $this->withGoogle($other, 'keep'.$other.'@lcc.ac.uk');
        $this->artisan('admission:set-status', ['applicant' => $b->id, 'status' => 5, '--force' => true, '--keep-google' => true])->assertSuccessful();
        $this->assertSame([], $google->deleted);
    }

    public function test_a_failed_google_deletion_changes_nothing_else(): void
    {
        [$a, $student] = $this->converted();
        $google = new class extends FakeWorkspace {
            public function deleteUser(string $email): void
            {
                throw new \RuntimeException('Google could not delete: forbidden');
            }
        };
        $google->configured = true;
        $google->accounts['gone'.$student.'@lcc.ac.uk'] = now()->toIso8601String();
        $this->app->instance(WorkspaceDirectory::class, $google);
        DB::table('student_archives')->insert(['student_id' => $student, 'table' => 'google_workspace', 'field_name' => 'account_created', 'field_new_value' => 'gone'.$student.'@lcc.ac.uk', 'created_by' => 1, 'created_at' => now()]);

        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 5, '--force' => true])
            ->expectsOutputToContain('Nothing else changed')
            ->assertFailed();

        $this->assertSame(7, (int) $a->fresh()->status_id);
        $this->assertTrue(DB::table('students')->where('id', $student)->exists());
    }

    public function test_it_refuses_unknown_statuses_and_leaves_an_unchanged_status_alone(): void
    {
        $a = Applicant::whereNotNull('submission_date')->orderByDesc('id')->firstOrFail();
        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 9999])->assertFailed();

        DB::table('applicants')->where('id', $a->id)->update(['status_id' => 5]);
        $before = DB::table('applicant_archives')->where('applicant_id', $a->id)->count();
        $this->artisan('admission:set-status', ['applicant' => $a->id, 'status' => 5, '--keep-students' => true])->expectsOutputToContain('Nothing to change')->assertSuccessful();
        $this->assertSame($before, DB::table('applicant_archives')->where('applicant_id', $a->id)->count());
    }
}
