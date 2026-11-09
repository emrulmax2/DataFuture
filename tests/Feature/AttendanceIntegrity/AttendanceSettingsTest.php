<?php

namespace Tests\Feature\AttendanceIntegrity;

use App\Models\Option;
use App\Models\User;
use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\IntegrityAccess;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Attendance Integrity > Settings (moved from Site Settings, 3 Nov 2026). Rolled back after each test. */
class AttendanceSettingsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        AttendanceSettings::flush();
    }

    /** Every setting as the form posts it, from the current values. */
    private function form(array $override = []): array
    {
        $post = [];
        foreach (AttendanceSettings::all() as $name => $value) {
            $post[$name] = $value;
        }

        return array_merge($post, $override);
    }

    public function test_defaults_apply_before_anything_is_saved(): void
    {
        $this->assertSame(48, AttendanceSettings::int('att_correction_deadline_hours'));
        $this->assertTrue(AttendanceSettings::bool('att_require_late_reason'));
        $this->assertTrue(AttendanceSettings::bool('att_enforce_class_ownership'));
        $this->assertContains(1, AttendanceSettings::ids('att_attended_statuses'));
        $this->assertNotContains(4, AttendanceSettings::ids('att_attended_statuses'), 'Absent never counts as attended');
        $this->assertSame([[24, 5], [48, 8], [72, 12], [168, 20], [336, 25]], AttendanceSettings::get('att_retro_tiers'));
    }

    public function test_the_page_needs_manage_settings(): void
    {
        $this->actingAs(User::find(7))->get(route('attendance.integrity.settings'))->assertOk()
            ->assertSee('Rules &amp; thresholds', false)->assertSee('Correction deadline')->assertSee('Attendance statuses')->assertSee('Save changes');
        $this->get(route('attendance.integrity.settings', ['tab' => 'scans']))->assertOk()->assertSee('Scan terms')->assertSee('Recent scans');

        // The old Site Settings address still works: it sends people here.
        $this->get('/site-settings/attendance-settings')->assertRedirect('/attendance-integrity/settings');

        $other = User::whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderBy('id')->limit(80)->get()
            ->first(fn ($u) => ! IntegrityAccess::can('manage_settings', $u));
        $this->actingAs($other)->get(route('attendance.integrity.settings'))->assertForbidden();
        $this->actingAs($other)->post(route('attendance.integrity.settings.save'), $this->form())->assertForbidden();
    }

    public function test_saving_stores_only_what_changed_and_logs_it(): void
    {
        $this->actingAs(User::find(7));
        $this->post(route('attendance.integrity.settings.save'), $this->form([
            'att_correction_deadline_hours' => 72,
            'att_retro_tiers' => [[24, 5], [168, 20], ['', '']],
            'att_working_days' => [1, 2, 3, 4, 5],
        ]))->assertRedirect(route('attendance.integrity.settings'))->assertSessionHas('aim_success', 'Settings saved (3 changed).');

        AttendanceSettings::flush();
        $this->assertSame(72, AttendanceSettings::int('att_correction_deadline_hours'));
        $this->assertSame([[24, 5], [168, 20]], AttendanceSettings::get('att_retro_tiers'));
        $this->assertSame([1, 2, 3, 4, 5], AttendanceSettings::get('att_working_days'));
        $this->assertSame(3, Option::where('category', AttendanceSettings::CATEGORY)->count());
        $this->assertDatabaseHas('aim_access_logs', ['user_id' => 7, 'action' => 'settings']);
    }

    public function test_bad_values_are_refused_and_nothing_is_saved(): void
    {
        $this->actingAs(User::find(7));
        $this->post(route('attendance.integrity.settings.save'), $this->form([
            'att_correction_deadline_hours' => 'abc',
            'att_night_start' => '25:00',
            'att_attended_statuses' => [],
            'att_band_staff_medium' => 800,
            'att_band_staff_high' => 600,
        ]))->assertSessionHasErrors(['att_correction_deadline_hours', 'att_night_start', 'att_attended_statuses', 'att_band_staff_high']);
        $this->assertSame(0, Option::where('category', AttendanceSettings::CATEGORY)->count());
    }
}
