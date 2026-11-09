<?php

namespace Tests\Feature;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentDocument;
use App\Models\StudentLetter;
use App\Models\StudentSms;
use App\Models\StudentTaskLog;
use App\Models\User;
use App\Services\ClassRoutine\ClassRoutineBuilder;
use App\Services\ClassRoutine\ClassRoutineGroupPdf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Course Management › Plan tree › "Send Class ICP" (user, 30 Oct 2026): the group's class routine
 * PDF emailed to each of its students, with no letter and no copy in their Uploads. Run against
 * LCC20260894's group (SEP26-HM-C, September 2026) as the dev database holds it.
 */
class PlanTreeClassIcpTest extends TestCase
{
    use DatabaseTransactions;

    private Student $student;
    /** The plan tree's coordinates for the student's group. */
    private array $coords;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake();
        config(['mail.redirect_to' => 'limon@lcc.ac.uk']);

        $student = Student::where('registration_no', 'LCC20260894')->first();
        $plan = $student ? DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->where('a.student_id', $student->id)->whereNull('a.deleted_at')->whereNull('p.deleted_at')
            ->where('p.class_type', 'Theory')->select('p.*')->first() : null;
        if (!$plan) {
            $this->markTestSkipped('LCC20260894 with class plans is not in this database.');
        }
        $this->student = $student;
        $this->coords = ['yearid' => $plan->academic_year_id, 'termid' => $plan->term_declaration_id, 'courseid' => $plan->course_id, 'groupid' => $plan->group_id];
        StudentContact::where('student_id', $student->id)->update(['institutional_email' => 'lcc20260894@lcc.ac.uk', 'personal_email' => 'costela@example.com']);
    }

    private function staff(): User
    {
        return User::find(7);
    }

    public function test_the_group_panel_has_the_button_after_assign_deassign(): void
    {
        $html = $this->actingAs($this->staff())->postJson(route('plans.tree.get.module'), [
            'courseId' => $this->coords['courseid'], 'attendancesemester' => $this->coords['termid'],
            'academicYearId' => $this->coords['yearid'], 'groupId' => $this->coords['groupid'],
        ])->assertOk()->json('htm');

        $assign = strpos($html, 'Assign / Deassign Students');
        $icp = strpos($html, 'Send Class ICP');
        $this->assertNotFalse($icp);
        $this->assertGreaterThan($assign, $icp);
        $this->assertStringContainsString('sendClassIcp" data-yearid="'.$this->coords['yearid'].'" data-attendanceSemester="'.$this->coords['termid'].'"', $html);
    }

    public function test_prepare_lists_the_groups_students_with_one_shared_pdf_and_a_preview(): void
    {
        $assigned = DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->where('p.group_id', $this->coords['groupid'])->where('p.term_declaration_id', $this->coords['termid'])
            ->whereNull('a.deleted_at')->whereNull('p.deleted_at')->distinct()->count('a.student_id');

        $response = $this->actingAs($this->staff())->postJson(route('plans.tree.class.icp.prepare'), $this->coords)->assertOk();

        $this->assertSame($assigned, $response->json('total'));
        $this->assertSame('limon@lcc.ac.uk', $response->json('redirect_to'));
        $main = $response->json('groups.0');
        $this->assertFalse($main['variant']);
        $this->assertContains('LCC20260894', array_column($main['students'], 'registration_no'));
        $this->assertSame($assigned, array_sum(array_column($response->json('groups'), 'count')) + count($response->json('skipped')));

        Storage::fake('local');
        $this->actingAs($this->staff())->get($main['preview_url'])->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_a_group_with_nobody_assigned_says_so(): void
    {
        $this->actingAs($this->staff())->postJson(route('plans.tree.class.icp.prepare'), ['yearid' => 0, 'termid' => 0, 'courseid' => 0, 'groupid' => 0])
            ->assertStatus(422)->assertJsonFragment(['message' => 'No students are assigned to this group\'s class plans yet, so there is nobody to send the class routine to.']);
    }

    public function test_send_emails_only_the_routine_pdf_with_no_letter_upload_text_or_task(): void
    {
        Storage::fake('local');
        Bus::fake([UserMailerJob::class]);
        $id = $this->student->id;
        $before = [StudentDocument::where('student_id', $id)->count(), StudentLetter::where('student_id', $id)->count(), StudentSms::where('student_id', $id)->count(), StudentTaskLog::count()];

        $this->actingAs($this->staff())->postJson(route('plans.tree.class.icp.send'), $this->coords + ['student_id' => $id])
            ->assertOk()
            ->assertJsonPath('emailed_to', ['lcc20260894@lcc.ac.uk', 'costela@example.com'])
            ->assertJsonPath('message', 'Emailed to lcc20260894@lcc.ac.uk, costela@example.com (this install delivers it to limon@lcc.ac.uk).');

        $this->assertSame($before, [StudentDocument::where('student_id', $id)->count(), StudentLetter::where('student_id', $id)->count(), StudentSms::where('student_id', $id)->count(), StudentTaskLog::count()]);

        $routine = app(ClassRoutineBuilder::class)->forStudent($id, (int) $this->coords['termid']);
        $path = 'class-routines/'.app(ClassRoutineGroupPdf::class)->key($routine).'.pdf';
        Storage::disk('local')->assertExists($path);
        Bus::assertDispatched(UserMailerJob::class, function ($job) use ($path) {
            $this->assertSame(['lcc20260894@lcc.ac.uk', 'costela@example.com'], $job->to);
            $this->assertInstanceOf(CommunicationSendMail::class, $job->mailable);
            $this->assertSame('Your class routine for September 2026', $job->mailable->subject);
            $this->assertSame([['pathinfo' => $path, 'nameinfo' => 'Class_Routine_SEP26-HM-C.pdf', 'mimeinfo' => 'application/pdf', 'disk' => 'local']], $job->mailable->attachmentList);
            $this->assertCount(1, $job->mailable->attachments()); // the file is there to attach
            $this->assertStringContainsString('Please find attached your class routine for <strong>September 2026</strong> (group <strong>SEP26-HM-C</strong>)', $job->mailable->content);
            $this->assertStringContainsString('Best Regards,', $job->mailable->content);

            return true;
        });
    }

    public function test_classmates_get_the_same_stored_pdf(): void
    {
        Storage::fake('local');
        Bus::fake([UserMailerJob::class]);
        $ids = $this->actingAs($this->staff())->postJson(route('plans.tree.class.icp.prepare'), $this->coords)->json('groups.0.students.*.id');
        $this->assertGreaterThan(1, count($ids));

        foreach (array_slice($ids, 0, 3) as $id) {
            $this->actingAs($this->staff())->postJson(route('plans.tree.class.icp.send'), $this->coords + ['student_id' => $id]);
        }

        $this->assertCount(1, Storage::disk('local')->files('class-routines'));
        $this->assertCount(1, Storage::disk('local')->files('class-routine-cache'));
        Bus::assertDispatchedTimes(UserMailerJob::class, count(array_slice($ids, 0, 3)) - count(array_filter(array_slice($ids, 0, 3), fn ($id) => !$this->hasAddress($id))));
    }

    private function hasAddress(int $studentId): bool
    {
        $contact = StudentContact::where('student_id', $studentId)->orderByDesc('id')->first();

        return $contact && ($contact->institutional_email || $contact->personal_email);
    }

    public function test_send_refuses_a_student_who_is_not_in_the_group(): void
    {
        Bus::fake([UserMailerJob::class]);
        $outsider = DB::table('students')->whereNull('deleted_at')
            ->whereNotExists(fn ($q) => $q->from('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')->whereColumn('a.student_id', 'students.id')
                ->where('p.group_id', $this->coords['groupid'])->whereNull('a.deleted_at'))
            ->value('id');

        $this->actingAs($this->staff())->postJson(route('plans.tree.class.icp.send'), $this->coords + ['student_id' => $outsider])
            ->assertStatus(422)->assertJsonFragment(['message' => 'This student is no longer assigned to this group\'s class plans.']);
        Bus::assertNotDispatched(UserMailerJob::class);
    }

    public function test_the_routine_is_built_for_the_trees_term_not_the_students_newest(): void
    {
        // a student with plans in two terms
        $row = DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->whereNull('a.deleted_at')->whereNull('p.deleted_at')->whereNotNull('p.term_declaration_id')
            ->groupBy('a.student_id')->havingRaw('COUNT(DISTINCT p.term_declaration_id) > 1')
            ->selectRaw('a.student_id, MIN(p.term_declaration_id) as one_term')->first();
        if (!$row) {
            $this->markTestSkipped('No student with class plans in two terms.');
        }
        $builder = app(ClassRoutineBuilder::class);
        $newest = $builder->forStudent((int) $row->student_id);
        $older = (int) DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')->join('term_declarations as t', 't.id', '=', 'p.term_declaration_id')
            ->where('a.student_id', $row->student_id)->whereNull('a.deleted_at')->whereNull('p.deleted_at')
            ->where('t.id', '!=', $newest['term']['id'])->value('t.id');

        $this->assertSame($older, $builder->forStudent((int) $row->student_id, $older)['term']['id']);
        $this->assertNull($builder->forStudent((int) $row->student_id, 999999)); // a term they have no plans in
    }
}
