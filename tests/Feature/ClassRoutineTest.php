<?php

namespace Tests\Feature;

use App\Jobs\UserMailerJob;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentLetter;
use App\Models\StudentSms;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Models\TaskList;
use App\Models\User;
use App\Services\ClassRoutine\ClassRoutineBuilder;
use App\Services\ClassRoutine\ClassRoutineLink;
use App\Services\ClassRoutine\ClassRoutineSender;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Task Manager › "Send ICP & Mark as Complete" (user, 28 Oct 2026), checked on the student the
 * user named, LCC20260894 (group SEP26-HM-C, September 2026) as the dev database holds them.
 */
class ClassRoutineTest extends TestCase
{
    use DatabaseTransactions;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        // no SMS gateway or other outside call, ever (SendSmsTrait only stays quiet on local/development)
        Http::preventStrayRequests();
        Http::fake();
        $student = Student::where('registration_no', 'LCC20260894')->first();
        if (!$student) {
            $this->markTestSkipped('LCC20260894 is not in this database.');
        }
        $this->student = $student;
    }

    private function icpTask(): TaskList
    {
        $task = TaskList::where('class_routine', 'Yes')->orderBy('id')->first()
            ?? tap(TaskList::where('name', 'like', '%ICP%')->orderBy('id')->firstOrFail())->update(['class_routine' => 'Yes']);

        return $task;
    }

    public function test_the_routine_is_read_from_the_students_class_plans(): void
    {
        $r = app(ClassRoutineBuilder::class)->forStudent($this->student->id);

        $this->assertSame('September 2026', $r['term']['name']);
        $this->assertSame('Y1T1', $r['term']['code']);
        $this->assertSame('SEP26-HM-C', $r['group']['name']);
        $this->assertSame('Level 4', $r['level']);
        $this->assertSame('Pearson', $r['awarding_body']);
        $this->assertSame(['11:45', '14:00', '16:15'], array_column($r['slots'], 'start'));
        $this->assertSame(['Tuesday', 'Friday'], $r['support_days']);
        $this->assertSame('study', $r['days']['Saturday']['type']);

        // Monday: three online tutorials; Wednesday: two lectures from 14:00; Thursday: lecture, then the workshop
        $monday = array_merge(...$r['days']['Monday']['cells']);
        $this->assertSame(['Tutorial', 'Tutorial', 'Tutorial'], array_column($monday, 'kind'));
        $this->assertSame(['online', 'online', 'online'], array_column($monday, 'mode'));
        $this->assertSame([], $r['days']['Wednesday']['cells'][0]);
        $this->assertSame('Lecture', $r['days']['Wednesday']['cells'][1][0]['kind']);
        $this->assertSame('The Hospitality Business Toolkit', $r['days']['Wednesday']['cells'][1][0]['title']);
        $this->assertSame('Workshop', $r['days']['Thursday']['cells'][2][0]['kind']);
        $this->assertSame('All units', $r['days']['Thursday']['cells'][2][0]['title']);
        $this->assertSame('Sornaly Sorker', $r['days']['Thursday']['cells'][2][0]['person']);

        // 11 teaching weeks from 7 Sep: week 1 lectures only, week 11 is revision
        $this->assertCount(11, $r['weeks']);
        $this->assertSame('2026-09-07', $r['weeks'][0]['commencing']);
        $this->assertTrue($r['weeks'][0]['lecture']);
        $this->assertFalse($r['weeks'][0]['tutorial']);
        $this->assertTrue($r['weeks'][10]['revision']);
        $this->assertCount(3, $r['assessments']);
    }

    // the one-page routine (28 Oct 2026), replaced by the detailed term timetable on 30 Oct 2026:
    // public function test_the_pdf_is_one_a4_page(): void
    // {
    //     $r = app(ClassRoutineBuilder::class)->forStudent($this->student->id);
    //     $pdf = app(ClassRoutineSender::class)->pdf($r);
    //     $this->assertStringStartsWith('%PDF', $pdf);
    //     $this->assertSame(1, preg_match_all('#/Type\s*/Page[^s]#', $pdf));
    // }

    /** Another student with exactly the same class plans (same group), or skip. */
    private function classmate(): Student
    {
        $plans = \Illuminate\Support\Facades\DB::table('assigns')->where('student_id', $this->student->id)->whereNull('deleted_at')->pluck('plan_id')->sort()->values()->implode(',');
        $other = \Illuminate\Support\Facades\DB::table('assigns')->whereNull('deleted_at')->where('student_id', '!=', $this->student->id)
            ->groupBy('student_id')->havingRaw('GROUP_CONCAT(plan_id ORDER BY plan_id) = ?', [$plans])->value('student_id');
        if (!$other) {
            $this->markTestSkipped('No classmate with the same class plans.');
        }

        return Student::findOrFail($other);
    }

    public function test_the_pdf_is_the_term_timetable_with_a_page_per_week(): void
    {
        $r = app(ClassRoutineBuilder::class)->forStudent($this->student->id);
        $pdf = app(\App\Services\ClassRoutine\ClassRoutineGroupPdf::class)->render($r);

        $this->assertStringStartsWith('%PDF', $pdf);
        $weeks = count($r['calendar']);
        $this->assertSame(11, $weeks);
        // the overview, a page per week, and the deadlines on their own page (week 11 has classes)
        $this->assertSame(1 + $weeks + 1, preg_match_all('#/Type\s*/Page[^s]#', $pdf));

        $html = view('pages.students.class-routine.term-pdf', ['routine' => $r])->render();
        $this->assertStringContainsString('Week 11 of 11', $html);
        $this->assertStringContainsString('Revision week', $html);
        $this->assertStringContainsString('Week starting: Mon 7 Sep 2026', $html);
        $this->assertStringContainsString('meet.google.com/npf-kkma-ntv', $html);
        $this->assertStringContainsString('CANCELLED', $html); // the cancelled classes of 7 and 10 Sep
        $this->assertStringContainsString('Kings House 96, North Street, Barking, IG11 8FH', $html);
        $this->assertStringContainsString('Deadline: Friday 27 November 2026', $html);
        $this->assertStringNotContainsString($this->student->registration_no, $html); // nothing about one student: a group shares it
    }

    public function test_a_group_shares_one_pdf_and_any_change_makes_a_new_one(): void
    {
        Storage::fake('local');
        $group = app(\App\Services\ClassRoutine\ClassRoutineGroupPdf::class);
        $builder = app(ClassRoutineBuilder::class);
        $mate = $this->classmate();

        $mine = $builder->forStudent($this->student->id);
        $theirs = $builder->forStudent($mate->id);
        $this->assertSame($group->key($mine), $group->key($theirs));
        $group->bytes($mine);
        $group->bytes($theirs);
        $this->assertCount(1, Storage::disk('local')->files('class-routine-cache')); // rendered once

        // a class date cancelled: a new key, so a fresh PDF
        $planIds = \Illuminate\Support\Facades\DB::table('assigns')->where('student_id', $this->student->id)->whereNull('deleted_at')->pluck('plan_id');
        $date = \Illuminate\Support\Facades\DB::table('plans_date_lists')->whereIn('plan_id', $planIds)->whereNull('deleted_at')->where('date', '>=', '2026-10-01')->orderBy('date')->first();
        \Illuminate\Support\Facades\DB::table('plans_date_lists')->where('id', $date->id)->update(['status' => 'Canceled']);
        $this->assertNotSame($group->key($mine), $group->key($builder->forStudent($this->student->id)));
    }

    public function test_prepare_sorts_the_selection_into_groups_and_lists_who_is_left_out(): void
    {
        $this->icpTask();
        $mate = $this->classmate();
        $noPlan = Student::whereNotExists(fn ($q) => $q->from('assigns')->whereColumn('assigns.student_id', 'students.id')->whereNull('assigns.deleted_at'))->orderByDesc('id')->firstOrFail();

        $response = $this->actingAs(User::find(7))->postJson(route('task.manager.class.routine.prepare'), [
            'student_ids' => [$this->student->id, $mate->id, $noPlan->id], 'task_id' => $this->icpTask()->id,
        ])->assertOk();

        $response->assertJsonCount(1, 'groups')
            ->assertJsonPath('groups.0.name', 'SEP26-HM-C')
            ->assertJsonPath('groups.0.count', 2)
            ->assertJsonPath('groups.0.weeks', 11)
            ->assertJsonPath('skipped.0.id', $noPlan->id)
            ->assertJsonPath('skipped.0.reason', 'No class plan is assigned for this term.')
            ->assertJsonPath('total', 3);
        $this->assertNotEmpty($response->json('groups.0.warnings')); // the tutorials without a link of their own

        Storage::fake('local');
        $this->actingAs(User::find(7))->get($response->json('groups.0.preview_url'))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_sending_two_classmates_renders_once_and_files_a_copy_for_each(): void
    {
        Storage::fake('local');
        Bus::fake([UserMailerJob::class, \App\Jobs\SyncStudentClassCalendar::class]);
        $mate = $this->classmate();

        app(ClassRoutineSender::class)->send($this->student, null, 1);
        app(ClassRoutineSender::class)->send($mate, null, 1);

        $this->assertCount(1, Storage::disk('local')->files('class-routine-cache'));
        foreach ([$this->student, $mate] as $student) {
            $doc = StudentDocument::where('student_id', $student->id)->where('display_file_name', 'Class Routine')->latest('id')->firstOrFail();
            Storage::disk('local')->assertExists('public/students/'.$student->id.'/'.$doc->current_file_name);
        }
    }

    public function test_send_files_the_routine_issues_the_letter_emails_texts_and_completes_the_task(): void
    {
        Storage::fake('local');
        Bus::fake([UserMailerJob::class]);
        config(['class_routine.sms' => true]); // the gateway is faked (setUp); off by default outside production
        $list = $this->icpTask();
        $task = StudentTask::where('student_id', $this->student->id)->where('task_list_id', $list->id)->first()
            ?? StudentTask::create(['student_id' => $this->student->id, 'task_list_id' => $list->id, 'status' => 'Pending', 'created_by' => 1]);
        $task->update(['status' => 'Pending']);

        $this->actingAs(User::find(7))->post(route('task.manager.send.class.routine'), [
            'student_id' => $this->student->id, 'task_id' => $list->id,
        ], ['Accept' => 'application/json'])->assertOk()->assertJson(['status' => ClassRoutineSender::DONE]);

        // 1. Uploads › "Class Routine" (local disk here, S3 on the servers)
        $doc = StudentDocument::where('student_id', $this->student->id)->where('display_file_name', 'Class Routine')->latest('id')->first();
        $this->assertNotNull($doc);
        $this->assertSame('local', $doc->disk_type);
        Storage::disk('local')->assertExists('public/students/'.$this->student->id.'/'.$doc->current_file_name);

        // 2. the "LCC Class Routine" letter, signed by the Registrar, with its PDF in Letters
        //    (taken out 28 Oct, back 29 Oct 2026, user)
        $letter = StudentLetter::where('student_id', $this->student->id)->latest('id')->first();
        $this->assertSame((int) config('class_routine.letter_set_id'), (int) $letter->letter_set_id);
        $this->assertSame((int) config('class_routine.signatory_id'), (int) $letter->signatory_id);
        $letterDoc = \App\Models\StudentLettersDocument::where('student_letter_id', $letter->id)->first();
        $this->assertSame('local', $letterDoc->disk_type);
        Storage::disk('local')->assertExists('public/students/'.$this->student->id.'/'.$letterDoc->current_file_name);

        // 3. the email: the covering text other letters get (user, 29 Oct 2026), the letter PDF
        //    and the routine PDF attached
        $signatory = \App\Models\Signatory::find((int) config('class_routine.signatory_id'));
        Bus::assertDispatched(UserMailerJob::class, function ($job) use ($doc, $letterDoc, $signatory) {
            $to = $job->to;
            $mailable = $job->mailable;
            $this->assertContains('lcc20260894@lcc.ac.uk', $to);
            $this->assertFalse($job->direct); // MAIL_REDIRECT_TO still applies (limon@ locally)
            $this->assertStringContainsString('September 2026', $mailable->subject);
            $this->assertStringStartsWith('Dear '.e($this->student->first_name.' '.$this->student->last_name).', <br/><p>Please Find the letter attached herewith. </p>', $mailable->content);
            $this->assertStringContainsString('<strong>Best Regards,</strong><br/>'.e($signatory->signatory_name).'<br/>', $mailable->content);
            $this->assertStringNotContainsString('Your week at a glance', $mailable->content);
            $this->assertCount(2, $mailable->attachmentList);
            $this->assertSame('public/students/'.$letterDoc->student_id.'/'.$letterDoc->current_file_name, $mailable->attachmentList[0]['pathinfo']);
            $this->assertSame('public/students/'.$doc->student_id.'/'.$doc->current_file_name, $mailable->attachmentList[1]['pathinfo']);

            return true;
        });

        // 4. the text message, recorded on the student
        $sms = StudentSms::where('student_id', $this->student->id)->latest('id')->first();
        $this->assertNotNull($sms);
        $this->assertStringContainsString('/class-routine/'.$doc->id.'.', \App\Models\StudentSmsContent::find($sms->student_sms_content_id)->sms);

        // 5. the task
        $this->assertSame('Completed', $task->fresh()->status);
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $task->id)->where('actions', 'Class routine sent')->exists());
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $task->id)->where('actions', 'Status Changed')->where('current_field_value', 'Completed')->exists());
    }

    public function test_sms_is_off_outside_production_by_default(): void
    {
        Storage::fake('local');
        Bus::fake([UserMailerJob::class]);
        $before = StudentSms::where('student_id', $this->student->id)->count();

        $result = app(ClassRoutineSender::class)->send($this->student, null, 1);

        $this->assertFalse(ClassRoutineSender::smsEnabled());
        $this->assertSame('off', $result['sms']);
        $this->assertStringContainsString('SMS is off on this install', $result['message']);
        $this->assertSame($before, StudentSms::where('student_id', $this->student->id)->count());
        Http::assertNothingSent();
    }

    public function test_the_command_sends_to_limon_only_and_completes_the_icp_task(): void
    {
        Storage::fake('local');
        Bus::fake([UserMailerJob::class]);
        config(['mail.redirect_to' => 'limon@lcc.ac.uk']);
        $list = $this->icpTask();
        $task = StudentTask::where('student_id', $this->student->id)->where('task_list_id', $list->id)->first()
            ?? StudentTask::create(['student_id' => $this->student->id, 'task_list_id' => $list->id, 'status' => 'Pending', 'created_by' => 1]);
        $task->update(['status' => 'Pending']);

        $this->artisan('class-routine:send', ['student' => 'LCC20260894'])->expectsOutputToContain('Letters: letter ')->assertExitCode(0)->run();

        Bus::assertDispatchedSync(UserMailerJob::class, fn ($job) => $job->to === ['limon@lcc.ac.uk']);
        $this->assertSame('Completed', $task->fresh()->status);
        Http::assertNothingSent(); // no SMS off production

        // by id too, and --no-complete leaves the task alone
        $task->refresh()->update(['status' => 'Pending']);
        $this->artisan('class-routine:send', ['student' => (string) $this->student->id, '--no-complete' => true, '--to' => 'limon@lcc.ac.uk'])->assertExitCode(0)->run();
        $this->assertSame('Pending', $task->fresh()->status);
    }

    public function test_the_command_refuses_an_unknown_student_or_a_bad_address(): void
    {
        $this->artisan('class-routine:send', ['student' => 'LCC00000000'])->assertExitCode(1)->run();
        $this->artisan('class-routine:send', ['student' => 'LCC20260894', '--to' => 'not-an-email'])->assertExitCode(1)->run();
    }

    public function test_the_link_opens_the_pdf_and_nothing_else(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('public/students/'.$this->student->id.'/routine.pdf', '%PDF-1.4 test');
        $doc = StudentDocument::create(['student_id' => $this->student->id, 'document_setting_id' => 0, 'hard_copy_check' => 0, 'doc_type' => 'pdf', 'disk_type' => 'local', 'path' => '', 'display_file_name' => 'Class Routine', 'current_file_name' => 'routine.pdf', 'created_by' => 1]);
        $url = ClassRoutineLink::url($doc);
        $token = basename(parse_url($url, PHP_URL_PATH));

        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get(route('class.routine.download', ['token' => substr($token, 0, -1).(substr($token, -1) === 'a' ? 'b' : 'a')]))->assertNotFound();

        // only a "Class Routine" upload, and only until it expires
        $doc->update(['display_file_name' => 'Passport']);
        $this->get($url)->assertNotFound();
        $doc->update(['display_file_name' => 'Class Routine']);
        $this->travel((int) config('class_routine.link_days') + 1)->days();
        $this->get($url)->assertNotFound();
    }

    public function test_only_a_class_routine_task_offers_or_accepts_it(): void
    {
        $list = $this->icpTask();
        $other = TaskList::where('class_routine', '!=', 'Yes')->whereHas('processlist', fn ($q) => $q->where('phase', 'Live'))->orderBy('id')->firstOrFail();
        $staff = User::find(7);

        $this->actingAs($staff)->get(route('task.manager.show', $list->id))->assertOk()->assertSee('data-tkm-act="bulk-icp"', false)->assertSee('Send ICP &amp; Mark as Complete', false);
        $this->actingAs($staff)->get(route('task.manager.show', $other->id))->assertOk()->assertDontSee('bulk-icp', false);
        $this->actingAs($staff)->post(route('task.manager.send.class.routine'), ['student_id' => $this->student->id, 'task_id' => $other->id], ['Accept' => 'application/json'])->assertStatus(422);
    }

    public function test_a_student_without_class_plans_is_not_completed(): void
    {
        $list = $this->icpTask();
        $student = Student::whereDoesntHave('assign')->orderByDesc('id')->first();
        if (!$student) {
            $this->markTestSkipped('Every student has a class plan.');
        }
        $task = StudentTask::create(['student_id' => $student->id, 'task_list_id' => $list->id, 'status' => 'Pending', 'created_by' => 1]);

        $this->actingAs(User::find(7))->post(route('task.manager.send.class.routine'), ['student_id' => $student->id, 'task_id' => $list->id], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJson(['status' => ClassRoutineSender::FAILED]);
        $this->assertSame('Pending', $task->fresh()->status);
    }
}
