<?php

namespace Tests\Feature\AttendanceIntegrity;

use App\Models\Attendance;
use App\Models\AttendanceAuditLog;
use App\Models\Option;
use App\Models\User;
use App\Services\AttendanceIntegrity\AttendanceAudit;
use App\Services\AttendanceIntegrity\AttendanceGuard;
use App\Services\AttendanceIntegrity\AttendanceSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Attendance Integrity (user, 3 Nov 2026): the server-side checks on the screens
 * that write attendance, the late-correction reason, and the attendance history.
 * Runs on the dev database in a rolled-back transaction. No SMS or email leaves:
 * outgoing HTTP is faked and stray requests fail the test.
 */
class AttendanceSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Http::preventStrayRequests();
        Bus::fake();
        AttendanceAudit::reset();
        AttendanceSettings::flush();
    }

    private function setting(string $name, $value): void
    {
        Option::updateOrCreate(['category' => AttendanceSettings::CATEGORY, 'name' => $name],
            ['value' => AttendanceSettings::encode($value), 'created_by' => 1, 'updated_by' => 1]);
        AttendanceSettings::flush();
    }

    /** A live attendance row of a class that ended more than 3 days ago (one student's only row on that date). */
    private function oldRow(): Attendance
    {
        $row = Attendance::query()
            ->join('plans_date_lists as d', 'd.id', '=', 'attendances.plans_date_list_id')
            ->where('d.date', '<', now()->subDays(4)->toDateString())
            ->whereNull('attendances.deleted_at')->whereNull('attendances.note')
            ->orderByDesc('attendances.id')->select('attendances.*')->first();
        $this->assertNotNull($row, 'needs an attendance row older than 4 days');

        return $row;
    }

    private function userWithout(string $key): User
    {
        $user = User::whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderBy('id')->limit(80)->get()
            ->first(fn ($u) => empty($u->priv()[$key]));
        $this->assertNotNull($user, "needs an active staff user without $key");

        return $user;
    }

    public function test_profile_edit_needs_the_attendance_edit_privilege(): void
    {
        $row = $this->oldRow();
        $this->actingAs($this->userWithout('attendance_edit'))
            ->postJson(route('attendance.update.all'), ['id' => [$row->id], 'attendance_feed' => [$row->id => 1]])
            ->assertForbidden();
        $this->assertSame((int) $row->attendance_feed_status_id, (int) $row->fresh()->attendance_feed_status_id);
    }

    public function test_a_late_correction_asks_for_a_reason_and_keeps_it_in_the_history(): void
    {
        $row = $this->oldRow();
        $new = (int) $row->attendance_feed_status_id === 1 ? 4 : 1;
        $this->actingAs(User::find(7));

        $res = $this->postJson(route('attendance.update.all'), ['id' => [$row->id], 'attendance_feed' => [$row->id => $new]])
            ->assertStatus(422)->assertJson(['requires_reason' => true]);
        $this->assertStringContainsString('more than 48 hours after the class ended', $res->json('message'));
        $this->assertSame((int) $row->attendance_feed_status_id, (int) $row->fresh()->attendance_feed_status_id, 'nothing saved without a reason');

        $this->postJson(route('attendance.update.all'), ['id' => [$row->id], 'attendance_feed' => [$row->id => $new], 'change_reason' => 'ok'])
            ->assertStatus(422)->assertJson(['requires_reason' => true]); // too short

        $this->postJson(route('attendance.update.all'), ['id' => [$row->id], 'attendance_feed' => [$row->id => $new], 'change_reason' => 'Student was in class; tutor confirmed by email.'])
            ->assertOk();
        $this->assertSame($new, (int) $row->fresh()->attendance_feed_status_id);

        $log = AttendanceAuditLog::where('attendance_id', $row->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame('updated', $log->action);
        $this->assertSame('student_profile', $log->source);
        $this->assertSame((int) $row->attendance_feed_status_id, (int) $log->old_status_id);
        $this->assertSame($new, (int) $log->new_status_id);
        $this->assertTrue($log->is_late_correction);
        $this->assertSame('Student was in class; tutor confirmed by email.', $log->change_reason);
        $this->assertSame(7, (int) $log->changed_by);
        $this->assertGreaterThan(48 * 60, (int) $log->minutes_after_class);
    }

    public function test_no_reason_is_asked_when_the_setting_is_off_or_within_the_deadline(): void
    {
        $row = $this->oldRow();
        $this->actingAs(User::find(7));

        $this->setting('att_require_late_reason', 0);
        $this->postJson(route('attendance.update.all'), ['id' => [$row->id], 'attendance_feed' => [$row->id => (int) $row->attendance_feed_status_id === 1 ? 4 : 1]])
            ->assertOk();

        $this->setting('att_require_late_reason', 1);
        $this->setting('att_correction_deadline_hours', 2160); // 90 days
        $fresh = Attendance::query()->join('plans_date_lists as d', 'd.id', '=', 'attendances.plans_date_list_id')
            ->where('d.date', '>', now()->subDays(80)->toDateString())->where('d.date', '<', now()->subDays(2)->toDateString())
            ->whereNull('attendances.deleted_at')->whereNull('attendances.note')->select('attendances.*')->first();
        $this->assertNotNull($fresh);
        $this->postJson(route('attendance.update.all'), ['id' => [$fresh->id], 'attendance_feed' => [$fresh->id => (int) $fresh->attendance_feed_status_id === 1 ? 4 : 1]])
            ->assertOk();
        $this->assertFalse(AttendanceAuditLog::where('attendance_id', $fresh->id)->latest('id')->first()->is_late_correction);
    }

    public function test_profile_edit_refuses_rows_of_two_students(): void
    {
        $row = $this->oldRow();
        $other = Attendance::whereNull('deleted_at')->whereNull('note')->where('student_id', '!=', $row->student_id)->first();
        $this->actingAs(User::find(7))
            ->postJson(route('attendance.update.all'), ['id' => [$row->id, $other->id], 'attendance_feed' => [$row->id => 1, $other->id => 1], 'change_reason' => 'a long enough reason'])
            ->assertStatus(422)->assertJsonPath('errors.permission.0', 'These attendance records do not belong to one student.');
    }

    public function test_delete_needs_the_privilege_and_a_reason_and_restore_works(): void
    {
        $row = $this->oldRow();
        $this->actingAs($this->userWithout('attendance_delete'))->deleteJson(route('attendance.destory', $row->id))->assertForbidden();
        $this->assertNull($row->fresh()->deleted_at);

        $this->actingAs(User::find(7));
        $this->deleteJson(route('attendance.destory', $row->id))->assertStatus(422)->assertJson(['requires_reason' => true]);
        $this->deleteJson(route('attendance.destory', $row->id), ['change_reason' => 'Duplicate record entered twice.'])->assertOk();
        $this->assertNotNull(Attendance::withTrashed()->find($row->id)->deleted_at);
        $deleted = AttendanceAuditLog::where('attendance_id', $row->id)->where('action', 'deleted')->first();
        $this->assertNotNull($deleted);
        $this->assertSame('Duplicate record entered twice.', $deleted->change_reason);
        $this->assertSame((int) $row->attendance_feed_status_id, (int) $deleted->old_status_id);

        // The restore route used to have no id and return nothing.
        $this->postJson(route('attendance.restore', $row->id))->assertOk();
        $this->assertNull(Attendance::find($row->id)->deleted_at);
        $this->assertTrue(AttendanceAuditLog::where('attendance_id', $row->id)->where('action', 'restored')->exists());
    }

    public function test_only_the_class_tutors_can_save_a_register_unless_the_setting_is_off(): void
    {
        $row = $this->oldRow();
        $session = DB::table('plans_date_lists')->find($row->plans_date_list_id);
        $outsider = User::whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderBy('id')->limit(200)->get()
            ->first(fn ($u) => empty($u->priv()['edit_attendance']) && ! AttendanceGuard::isLinked((int) $u->id, (int) $session->id));
        $this->assertNotNull($outsider);

        $payload = ['plan_id' => $session->plan_id, 'plan_date_list_id' => $session->id, 'tutor_id' => 0,
            'attendances' => [$session->id => [['student_id' => $row->student_id, 'attendance_feed_status_id' => $row->attendance_feed_status_id]]]];

        $this->actingAs($outsider)->postJson(route('attendance.store'), $payload)->assertForbidden()
            ->assertJsonPath('errors.permission.0', fn ($m) => str_contains($m, 'Only the tutor, personal tutor or cover tutor'));

        // The register of another class date cannot be posted under this class.
        $this->actingAs(User::find(7))->postJson(route('attendance.store'), array_merge($payload, ['plan_id' => $session->plan_id + 999999]))
            ->assertStatus(422);

        // Setting off: the ownership check no longer refuses (the ended-and-fed lock still may).
        $this->setting('att_enforce_class_ownership', 0);
        $res = $this->actingAs($outsider)->postJson(route('attendance.store'), $payload);
        $this->assertStringNotContainsString('Only the tutor', (string) $res->json('message'));
    }

    public function test_the_linked_check_knows_the_class_tutor_and_cover_tutor(): void
    {
        $session = DB::table('plans_date_lists as d')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->where('p.tutor_id', '>', 0)->orderByDesc('d.id')->first(['d.id', 'p.tutor_id']);
        $this->assertTrue(AttendanceGuard::isLinked((int) $session->tutor_id, (int) $session->id));
        $proxy = DB::table('plans_date_lists')->where('proxy_tutor_id', '>', 0)->first(['id', 'proxy_tutor_id']);
        if ($proxy) {
            $this->assertTrue(AttendanceGuard::isLinked((int) $proxy->proxy_tutor_id, (int) $proxy->id));
        }
        $this->assertFalse(AttendanceGuard::isLinked(0, (int) $session->id));
    }

    public function test_student_visit_routes_need_login(): void
    {
        // Not signed in: sent to the login page (or 401), never run.
        foreach ([$this->postJson(route('student.visits.store')), $this->deleteJson(route('student.visits.destroy', 1)), $this->postJson(route('student.visits.restore', 1))] as $res) {
            $this->assertContains($res->status(), [302, 401, 404]); // 404: no such visit (looked up before the login check)
        }
    }

    public function test_history_rows_cannot_be_changed_or_deleted(): void
    {
        $row = $this->oldRow();
        AttendanceAudit::context('system');
        AttendanceAudit::updateRows(Attendance::where('id', $row->id), ['tracking_status' => (int) $row->tracking_status === 1 ? 0 : 1]);
        $log = AttendanceAuditLog::where('attendance_id', $row->id)->latest('id')->first();
        $this->assertSame('tracking_status', $log->changed_fields);

        $this->expectException(\LogicException::class);
        $log->update(['change_reason' => 'tampered']);
    }

    public function test_history_is_skipped_not_fatal_when_the_table_is_missing(): void
    {
        $row = $this->oldRow();
        // Simulate code deployed before the migration ran.
        $prop = new \ReflectionProperty(AttendanceAudit::class, 'enabled');
        $prop->setAccessible(true);
        $prop->setValue(null, false);

        $count = AttendanceAudit::updateRows(Attendance::where('id', $row->id), ['tracking_status' => (int) $row->tracking_status === 1 ? 0 : 1]);
        $this->assertSame(1, $count);
        $this->assertFalse(AttendanceAuditLog::where('attendance_id', $row->id)->exists());
        AttendanceAudit::reset();
    }
}
