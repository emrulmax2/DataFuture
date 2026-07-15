<?php

namespace Tests\Feature;

use App\Models\EmployeeAttendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Read-only guard rails for the HR daily attendance screen.
 *
 * hr.attendance.update stores whatever total_work_hour the browser posts, so the
 * page must present every field the endpoints read - if one goes missing the save
 * silently writes a null into payroll. These tests pin that contract.
 */
class HrAttendanceShowTest extends TestCase
{
    /** A day with a real spread of rows: absents, overtime and clean shifts. */
    private const DAY = 1777590000; // Fri 1 May 2026

    private function html(): string
    {
        $user = User::find(1);
        $this->assertNotNull($user, 'expected user 1 to exist');

        $response = $this->actingAs($user)->get('/hr/attendance/show/'.self::DAY);
        $response->assertOk();

        return $response->getContent();
    }

    public function test_it_renders_one_row_per_attendance_and_no_duplicates(): void
    {
        $html = $this->html();

        $expected = EmployeeAttendance::where('date', date('Y-m-d', self::DAY))
            ->where(function ($q) {
                $q->where(fn ($q) => $q->where('user_issues', '>', 0)->where('overtime_status', '!=', 1))
                  ->orWhere('leave_status', '>', 1)
                  ->orWhere('overtime_status', 1)
                  ->orWhere(fn ($q) => $q->where('overtime_status', 0)->where('leave_status', '<', 2)->where('user_issues', 0));
            })
            ->count();

        $this->assertSame($expected, substr_count($html, 'class="att-row '), 'one .att-row per visible attendance');
        $this->assertSame($expected, substr_count($html, 'class="att-editor"'), 'one editor per row');
    }

    public function test_every_row_posts_every_field_the_endpoints_read(): void
    {
        $html = $this->html();

        // One chunk per editor. A field may legitimately appear more than once in a
        // chunk (an absent row carries four leave_status radios), so this asserts
        // presence per row rather than a total across the page.
        $editors = array_slice(explode('class="att-editor"', $html), 1);
        $this->assertGreaterThan(0, count($editors));

        // update() and updateAll() dereference each of these unconditionally: a
        // missing one is a PHP 8 undefined-key error, or worse a null into payroll.
        $required = [
            'attendance_id', 'clockin_system', 'clockout_system', 'adjustment',
            'total_work_hour', 'total_break', 'paid_break', 'unpadi_break',
            'allowed_br', 'leave_status', 'leave_adjustment', 'leave_hour', 'note',
        ];

        foreach ($editors as $index => $editor) {
            // Cut at the editor's closing marker so fields are not counted twice.
            $editor = explode('class="att-editor__foot"', $editor)[0];

            foreach ($required as $field) {
                $this->assertStringContainsString(
                    '['.$field.']',
                    $editor,
                    "editor #{$index} does not post {$field} - the save endpoints read it without an isset()"
                );
            }
        }
    }

    public function test_clock_out_shows_its_own_venue_not_the_clock_in_one(): void
    {
        $html = $this->html();

        // The old screen printed clock_in_location in both cells. If the two sides
        // ever resolve differently the page must now say so.
        $this->assertStringContainsString('att-venue', $html);
        $this->assertStringNotContainsString('clock_in_location', $html);
    }

    public function test_it_does_not_run_a_query_per_row(): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries) { $queries++; });

        $this->html();

        // Old page: ~2 location queries per row plus lazy employee/pay/leave loads.
        $this->assertLessThan(40, $queries, "expected a bounded query count, ran {$queries}");
    }
}
