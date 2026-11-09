<?php

namespace Tests\Feature\AttendanceIntegrity;

use App\Models\Attendance;
use App\Models\AttendanceIntegrity\AimCase;
use App\Models\AttendanceIntegrity\AimCaseActivity;
use App\Models\AttendanceIntegrity\AimFinding;
use App\Models\User;
use App\Services\AttendanceIntegrity\AttendanceAudit;
use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Detection\Scanner;
use App\Services\AttendanceIntegrity\Filters;
use App\Services\AttendanceIntegrity\Metrics;
use App\Services\AttendanceIntegrity\Scoring;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Attendance Integrity monitoring (user, 3 Nov 2026): detection rules, scoring,
 * filters, pages, access, cases, exports and the history seal. Runs on the dev
 * database inside a rolled-back transaction; no SMS or email can leave.
 */
class AttendanceIntegrityModuleTest extends TestCase
{
    use DatabaseTransactions;

    private int $term;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Http::preventStrayRequests();
        Bus::fake();
        AttendanceSettings::flush();
        AttendanceAudit::reset();

        // The most recent term with at least 20 class dates since the late-entry cut-off.
        $term = DB::table('plans_date_lists as d')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->where('d.date', '>=', '2025-06-01')->where('d.date', '<', now()->subDays(5)->toDateString())->whereNull('d.deleted_at')
            ->groupBy('p.term_declaration_id')->havingRaw('COUNT(*) >= 20')->orderByDesc(DB::raw('MAX(d.date)'))->value('p.term_declaration_id');
        if (! $term) {
            $this->markTestSkipped('Needs a term with class dates.');
        }
        $this->term = (int) $term;
    }

    /** Physical (no virtual room) class dates of the term with a start and end time. */
    private function sessions(int $count): array
    {
        return DB::table('plans_date_lists as d')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->where('p.term_declaration_id', $this->term)->whereNull('d.deleted_at')->where('d.date', '>=', '2025-06-01')
            ->whereNotNull('p.start_time')->whereNotNull('p.end_time')->where(DB::raw('COALESCE(d.name, "")'), '<>', 'Visit')
            ->orderByDesc('d.id')->limit($count)->get(['d.id', 'd.plan_id', 'd.date', 'p.start_time', 'p.end_time'])->all();
    }

    /** A staff user with no attendance in this term (so their findings come only from the test rows). */
    private function freshStaff(): User
    {
        $used = DB::table('attendances as a')->join('plans_date_lists as d', 'd.id', '=', 'a.plans_date_list_id')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->where('p.term_declaration_id', $this->term)->selectRaw('DISTINCT a.created_by AS u')->pluck('u')
            ->merge(DB::table('attendances as a')->join('plans_date_lists as d', 'd.id', '=', 'a.plans_date_list_id')->join('plans as p', 'p.id', '=', 'd.plan_id')
                ->where('p.term_declaration_id', $this->term)->selectRaw('DISTINCT a.updated_by AS u')->pluck('u'))->filter()->all();
        $user = User::whereNotIn('id', $used)->whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderByDesc('id')->first();
        $this->assertNotNull($user);

        return $user;
    }

    private function student(int $offset = 0): int
    {
        return (int) DB::table('students')->orderByDesc('id')->skip($offset)->value('id');
    }

    private function insertRow(object $s, int $userId, string $createdAt, int $status = 1, int $studentOffset = 0): int
    {
        return (int) DB::table('attendances')->insertGetId([
            'plans_date_list_id' => $s->id, 'plan_id' => $s->plan_id, 'student_id' => $this->student($studentOffset), 'attendance_date' => $s->date,
            'attendance_captured_at' => substr($createdAt, 0, 10), 'attendance_feed_status_id' => $status, 'created_by' => $userId,
            'created_at' => $createdAt, 'updated_at' => $createdAt,
        ]);
    }

    private function scan(): array
    {
        return (new Scanner())->scanTerm($this->term);
    }

    public function test_late_entry_delay_runs_from_the_scheduled_class_end_not_midnight(): void
    {
        $s = $this->sessions(1)[0];
        $user = $this->freshStaff();
        $end = $s->date.' '.substr($s->end_time, 0, 8);
        $this->insertRow($s, $user->id, date('Y-m-d H:i:s', strtotime($end.' +3 days +2 hours')));
        $this->insertRow($s, $user->id, date('Y-m-d H:i:s', strtotime($end.' +3 days +2 hours')), 4, 1);

        $this->scan();
        $f = AimFinding::where('rule', 'retrospective')->where('event_key', 'ses:'.$s->id.':u:'.$user->id.':entry')->first();
        $this->assertNotNull($f, 'late register found');
        $this->assertSame(3 * 1440 + 120, $f->evidence['delay_minutes']);
        $this->assertSame(12, (int) $f->weight, 'over 72 hours tier');
        $this->assertSame(2, (int) $f->rows_count);
        $this->assertSame('fact', $f->basis);

        // Entered during the class: not late.
        $s2 = $this->sessions(2)[1];
        $this->insertRow($s2, $user->id, $s2->date.' '.substr($s2->start_time, 0, 8));
        $this->scan();
        $this->assertFalse(AimFinding::where('rule', 'retrospective')->where('event_key', 'ses:'.$s2->id.':u:'.$user->id.':entry')->whereNull('cleared_at')->exists());
    }

    public function test_status_changes_come_from_the_history_and_rescans_never_duplicate(): void
    {
        $s = $this->sessions(1)[0];
        $user = $this->freshStaff();
        $id = $this->insertRow($s, (int) User::find(7)->id, $s->date.' '.substr($s->end_time, 0, 8), 4);

        $this->actingAs($user);
        AttendanceAudit::context('student_profile', 'Test: student was in class');
        Attendance::find($id)->fill(['attendance_feed_status_id' => 1, 'updated_by' => $user->id])->save();

        $first = $this->scan();
        $f = AimFinding::where('rule', 'amendment')->where('attendance_id', $id)->where('type', 'absent_to_present')->first();
        $this->assertNotNull($f, 'Absent > Present from the history');
        $this->assertSame('fact', $f->basis);
        $this->assertSame($user->id, (int) $f->staff_user_id);
        $this->assertSame('Test: student was in class', $f->evidence['reason']);
        $this->assertSame(25, (int) $f->weight);

        $count = AimFinding::where('term_declaration_id', $this->term)->count();
        $second = $this->scan();
        $this->assertSame($count, AimFinding::where('term_declaration_id', $this->term)->count(), 'a rescan adds nothing');
        $this->assertSame(0, $second['new']);
        $this->assertGreaterThan(0, $first['new'] + $first['updated']);
    }

    public function test_one_register_saved_at_once_is_not_bulk_but_many_classes_in_seconds_is(): void
    {
        $sessions = $this->sessions(14);
        $this->assertCount(14, $sessions);
        $user = $this->freshStaff();

        // A whole register at the same second: normal.
        for ($i = 0; $i < 25; $i++) {
            $this->insertRow($sessions[0], $user->id, '2026-10-10 10:00:00', 1, $i);
        }
        $this->scan();
        $this->assertFalse(AimFinding::where('rule', 'bulk_entry')->where('staff_user_id', $user->id)->whereNull('cleared_at')->exists());

        // Twelve class dates within 22 seconds: a burst.
        foreach (array_slice($sessions, 1, 12) as $i => $s) {
            $this->insertRow($s, $user->id, '2026-10-10 11:00:'.str_pad((string) ($i * 2), 2, '0', STR_PAD_LEFT), 1, $i);
        }
        $this->scan();
        $burst = AimFinding::where('rule', 'bulk_entry')->where('staff_user_id', $user->id)->whereNull('cleared_at')->first();
        $this->assertNotNull($burst);
        $this->assertSame(12, $burst->evidence['entries']);
        $this->assertSame(15, (int) $burst->weight);
    }

    public function test_a_score_counts_each_event_once_at_its_highest_points_and_ignores_dismissed(): void
    {
        $user = $this->freshStaff();
        $now = now('Europe/London')->format('Y-m-d H:i:s');
        $row = fn ($weight, $event, $status = 'open') => ['rule' => 'test', 'type' => 'test', 'basis' => 'anomaly', 'weight' => $weight, 'event_key' => $event,
            'fingerprint' => sha1(uniqid('', true)), 'term_declaration_id' => $this->term, 'staff_user_id' => $user->id, 'summary' => 'test', 'status' => $status,
            'first_detected_at' => $now, 'last_detected_at' => $now, 'event_at' => $now, 'created_at' => $now, 'updated_at' => $now];
        DB::table('aim_findings')->insert([$row(25, 'att:1'), $row(10, 'att:1'), $row(15, 'att:2'), $row(99, 'att:3', 'dismissed')]);

        $scores = Scoring::subjects('staff', Filters::fromRequest(new Request(['terms' => (string) $this->term])));
        $this->assertSame(40, $scores[$user->id]['score']);
        $this->assertSame(2, $scores[$user->id]['events']);
        $this->assertSame('low', $scores[$user->id]['band']);
    }

    public function test_online_classes_are_never_counted_as_overlapping(): void
    {
        $this->scan();
        $online = DB::table('aim_findings as f')
            ->join('plans_date_lists as d', 'd.id', '=', 'f.plans_date_list_id')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->where('f.term_declaration_id', $this->term)->where('f.type', 'overlapping_classes')->whereRaw('TRIM(COALESCE(p.virtual_room, "")) <> ""')->count();
        $this->assertSame(0, $online);
    }

    public function test_deleted_records_leave_the_recorded_count_and_terms_add_up(): void
    {
        $f = fn ($terms) => Filters::fromRequest(new Request(['terms' => $terms]));
        $before = Metrics::dashboard($f((string) $this->term))['cards'];
        $row = Attendance::query()->join('plans_date_lists as d', 'd.id', '=', 'attendances.plans_date_list_id')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->where('p.term_declaration_id', $this->term)->whereNull('attendances.deleted_at')->select('attendances.*')->first();
        $this->actingAs(User::find(7));
        AttendanceAudit::context('profile_delete', 'test');
        $row->delete();
        $after = Metrics::dashboard($f((string) $this->term))['cards'];
        $this->assertSame($before['recorded'] - 1, $after['recorded']);
        $this->assertSame($before['deleted'] + 1, $after['deleted']);

        $other = (int) DB::table('plans')->where('term_declaration_id', '<>', $this->term)->whereExists(fn ($q) => $q->selectRaw(1)->from('plans_date_lists as d')->whereColumn('d.plan_id', 'plans.id'))
            ->orderByDesc('term_declaration_id')->value('term_declaration_id');
        $a = Metrics::dashboard($f((string) $other))['cards']['recorded'];
        $both = Metrics::dashboard($f($this->term.','.$other))['cards']['recorded'];
        $this->assertSame($after['recorded'] + $a, $both, 'cross-term totals add up');
    }

    public function test_findings_are_paged_on_the_server(): void
    {
        $this->scan();
        $this->actingAs(User::find(7));
        $total = AimFinding::where('term_declaration_id', $this->term)->whereNull('cleared_at')->where('status', 'open')->count();
        $res = $this->getJson(route('attendance.integrity.findings.data', ['terms' => $this->term, 'size' => 10, 'page' => 2]))->assertOk();
        $this->assertSame($total, $res->json('total'));
        $this->assertSame((int) ceil($total / 10), $res->json('last_page'));
        $this->assertLessThanOrEqual(10, count($res->json('data')));
    }

    public function test_access_needs_the_attendance_integrity_switch_and_the_area(): void
    {
        $user = $this->freshStaff();
        $this->actingAs(User::find($user->id))->get(route('attendance.integrity.dashboard'))->assertForbidden();

        DB::table('employee_permissions')->insert(['user_id' => $user->id, 'key' => 'attendance_integrity_view_dashboard', 'value' => '1', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs(User::find($user->id))->get(route('attendance.integrity.dashboard'))->assertForbidden(); // no parent switch

        DB::table('employee_permissions')->insert(['user_id' => $user->id, 'key' => 'attendance_integrity', 'value' => '1', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs(User::find($user->id))->get(route('attendance.integrity.dashboard', ['terms' => $this->term]))->assertOk();
        $this->actingAs(User::find($user->id))->get(route('attendance.integrity.staff'))->assertForbidden();
        $this->actingAs(User::find($user->id))->get(route('attendance.integrity.reports.export', ['report' => 'staff', 'format' => 'csv']))->assertForbidden();
        $this->actingAs(User::find($user->id))->get(route('attendance.integrity.cases'))->assertForbidden();
        $this->actingAs(User::find($user->id))->postJson(route('attendance.integrity.cases.store'), ['summary' => 'Should not be allowed'])->assertForbidden();
        $this->assertDatabaseHas('aim_access_logs', ['user_id' => $user->id, 'action' => 'view', 'subject_type' => 'dashboard']);
    }

    public function test_a_case_is_opened_worked_and_closed_with_an_append_only_history(): void
    {
        Storage::fake('local');
        $this->scan();
        $this->actingAs(User::find(7));
        $findings = AimFinding::where('term_declaration_id', $this->term)->whereNull('cleared_at')->where('status', 'open')->whereNotNull('staff_user_id')->limit(3)->get();
        $this->assertCount(3, $findings);

        $res = $this->postJson(route('attendance.integrity.cases.store'), ['finding_ids' => $findings->pluck('id')->all(), 'summary' => 'Review these attendance changes.'])->assertOk();
        $case = AimCase::latest('id')->first();
        $this->assertSame($res->json('url'), route('attendance.integrity.cases.show', $case));
        $this->assertMatchesRegularExpression('/^AIM-\d{4}-\d{4}$/', $case->case_number);
        $this->assertSame('New', $case->investigation_status);
        $this->assertSame(3, AimFinding::where('case_id', $case->id)->where('status', 'case')->count());

        $this->get(route('attendance.integrity.cases.show', $case))->assertOk()->assertSee($case->case_number);
        $this->post(route('attendance.integrity.cases.status', $case), ['investigation_status' => 'Resolved – No Issue'])->assertSessionHasErrors('note');
        $this->post(route('attendance.integrity.cases.status', $case), ['investigation_status' => 'Under Review'])->assertRedirect();
        $this->post(route('attendance.integrity.cases.note', $case), ['body' => 'Asked the tutor for the paper register.'])->assertRedirect();
        $this->post(route('attendance.integrity.cases.evidence', $case), ['title' => 'Paper register', 'file' => UploadedFile::fake()->create('register.pdf', 20, 'application/pdf')])->assertRedirect();
        $evidence = $case->evidenceFiles()->first();
        Storage::disk('local')->assertExists($evidence->file_path);
        $this->assertSame(64, strlen($evidence->sha256));
        $this->get(route('attendance.integrity.cases.evidence.download', [$case, $evidence]))->assertOk();
        $this->post(route('attendance.integrity.cases.status', $case), ['investigation_status' => 'Resolved – Process Error', 'note' => 'Tutor confirmed a mistake on the day.'])->assertRedirect();

        $case->refresh();
        $this->assertSame('Resolved – Process Error', $case->investigation_status);
        $this->assertSame(7, (int) $case->reviewed_by);
        $this->assertSame(['created', 'status', 'note', 'evidence', 'status'], $case->activities->pluck('type')->all());

        $this->expectException(\LogicException::class);
        AimCaseActivity::where('case_id', $case->id)->first()->update(['body' => 'rewritten']);
    }

    public function test_exports_carry_the_filters_time_author_and_notes(): void
    {
        $this->scan();
        $this->actingAs(User::find(7));
        $csv = $this->get(route('attendance.integrity.reports.export', ['report' => 'retrospective', 'format' => 'csv', 'terms' => $this->term]))->assertOk()->streamedContent();
        $this->assertStringContainsString('Generated by', $csv);
        $this->assertStringContainsString('Filter: Terms', $csv);
        $this->assertStringContainsString('Basis', $csv);
        $this->assertStringContainsString('UK time', $csv);

        $this->get(route('attendance.integrity.reports.export', ['report' => 'staff', 'format' => 'xlsx', 'terms' => $this->term]))->assertOk()
            ->assertHeader('content-disposition');
        $this->get(route('attendance.integrity.reports.export', ['report' => 'term_summary', 'format' => 'pdf', 'terms' => $this->term]))->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertDatabaseHas('aim_access_logs', ['user_id' => 7, 'action' => 'export', 'subject_type' => 'retrospective']);
    }

    public function test_the_history_seal_detects_a_row_changed_in_the_database(): void
    {
        $row = Attendance::whereNull('deleted_at')->orderByDesc('id')->first();
        AttendanceAudit::context('system');
        AttendanceAudit::updateRows(Attendance::where('id', $row->id), ['tracking_status' => (int) $row->tracking_status === 1 ? 0 : 1]);

        $this->assertSame(0, Artisan::call('attendance-integrity:seal'));
        $this->assertSame(0, Artisan::call('attendance-integrity:seal', ['--verify' => true]));

        DB::table('attendance_audit_logs')->where('attendance_id', $row->id)->update(['change_reason' => 'tampered']);
        $this->assertSame(1, Artisan::call('attendance-integrity:seal', ['--verify' => true]));
    }

    public function test_older_changes_are_labelled_unknown_never_invented(): void
    {
        $this->actingAs(User::find(7));
        $html = $this->get(route('attendance.integrity.amendments', ['terms' => $this->term]))->assertOk()->getContent();
        $this->assertStringContainsString('Changed, previous value unknown', $html);

        $this->scan();
        // Findings from before the history say how the previous status is known.
        $notice = AimFinding::where('term_declaration_id', $this->term)->where('type', 'absent_to_present_notice')->first();
        if ($notice) {
            $this->assertStringContainsString('absence notice', $notice->evidence['how_known']);
            $this->assertStringContainsString('earlier history was not recorded', $notice->evidence['how_known']);
        }
        // No history-based finding exists for a record without history rows.
        $invented = DB::table('aim_findings as f')->where('f.term_declaration_id', $this->term)->where('f.type', 'absent_to_present')
            ->whereNotExists(fn ($q) => $q->selectRaw(1)->from('attendance_audit_logs as l')->whereColumn('l.attendance_id', 'f.attendance_id'))->count();
        $this->assertSame(0, $invented);
    }
}
