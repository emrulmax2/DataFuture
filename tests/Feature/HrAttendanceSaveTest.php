<?php

namespace Tests\Feature;

use App\Models\EmployeeAttendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Drives the real save endpoints with a payload built the way the browser builds it
 * (from the rendered editor), and checks what lands in the database.
 *
 * Everything runs inside a transaction that is rolled back, so no row is changed.
 */
class HrAttendanceSaveTest extends TestCase
{
    private const DAY = 1777590000; // Fri 1 May 2026

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function user(): User
    {
        $user = User::find(1);
        $this->assertNotNull($user);

        return $user;
    }

    /** Pulls one row's editor out of the page and serialises it like jQuery would. */
    private function editorPayload(string $html, int $id): array
    {
        $start = strpos($html, 'data-editor="'.$id.'"');
        $this->assertNotFalse($start, "no editor rendered for attendance {$id}");
        $editor = substr($html, $start, strpos($html, 'class="att-editor__foot"', $start) - $start);

        $fields = [];

        preg_match_all('/<input\b[^>]*>/i', $editor, $tags);
        foreach ($tags[0] as $tag) {
            if (!preg_match('/name="attendance\['.$id.'\]\[(\w+)\]"/', $tag, $name)) {
                continue;
            }

            // An unchecked radio posts nothing, exactly as in the browser.
            $isRadio = str_contains($tag, 'type="radio"');
            if ($isRadio && !preg_match('/\bchecked\b/', $tag)) {
                continue;
            }

            preg_match('/value="([^"]*)"/', $tag, $value);
            $fields[$name[1]] = $value[1] ?? '';
        }

        preg_match('/name="attendance\['.$id.'\]\[note\]"[^>]*>([^<]*)</', $editor, $note);
        $fields['note'] = $note[1] ?? '';

        return $fields;
    }

    public function test_saving_a_row_writes_the_hours_and_marks_it_reviewed(): void
    {
        $html = $this->actingAs($this->user())->get('/hr/attendance/show/'.self::DAY)->getContent();

        // A normal worked row: has both punches and no leave.
        $row = EmployeeAttendance::where('date', date('Y-m-d', self::DAY))
            ->where('leave_status', 0)
            ->where('clockin_system', '!=', '')
            ->whereNotNull('clockin_system')
            ->first();
        $this->assertNotNull($row, 'expected at least one worked row on this day');

        $fields = $this->editorPayload($html, $row->id);

        foreach (['attendance_id', 'clockin_system', 'clockout_system', 'total_work_hour', 'total_break', 'paid_break', 'unpadi_break', 'leave_status'] as $key) {
            $this->assertArrayHasKey($key, $fields, "the editor did not render {$key}");
        }

        $rowData = http_build_query(['attendance' => [$row->id => $fields]]);

        $this->actingAs($this->user())
            ->post(route('hr.attendance.update'), [
                'rowData'    => $rowData,
                'rowNote'    => 'set by the save test',
                'leaveData'  => '',
                'isLeaveRow' => 0,
            ])
            ->assertOk();

        $saved = EmployeeAttendance::find($row->id);

        $this->assertSame((int) $fields['total_work_hour'], (int) $saved->total_work_hour, 'the hours the page showed are the hours that got stored');
        $this->assertSame($fields['clockin_system'], $saved->clockin_system);
        $this->assertSame($fields['clockout_system'], $saved->clockout_system);
        $this->assertSame('set by the save test', $saved->note);
        $this->assertSame(0, (int) $saved->user_issues, 'saving clears the issue flag');
        $this->assertSame(1, (int) $saved->updated_by, 'saving records who reviewed it');
        $this->assertSame(0, (int) $saved->leave_status, 'a non-leave row stays non-leave');
    }

    public function test_saving_an_absence_keeps_its_reason_and_leave_hours(): void
    {
        $html = $this->actingAs($this->user())->get('/hr/attendance/show/'.self::DAY)->getContent();

        $row = EmployeeAttendance::where('date', date('Y-m-d', self::DAY))
            ->where('leave_status', '>', 1)
            ->first();
        $this->assertNotNull($row, 'expected at least one absence on this day');

        $fields = $this->editorPayload($html, $row->id);

        // The reason must survive the round trip: update() reads a missing leave_status
        // as 0, which would turn the absence into a normal working day.
        $this->assertArrayHasKey('leave_status', $fields);
        $this->assertSame((int) $row->leave_status, (int) $fields['leave_status']);
        $this->assertArrayHasKey('leave_hour', $fields);
        $this->assertArrayHasKey('leave_adjustment', $fields);

        $rowData = http_build_query(['attendance' => [$row->id => $fields]]);

        $this->actingAs($this->user())
            ->post(route('hr.attendance.update'), [
                'rowData'    => $rowData,
                'rowNote'    => '',
                'leaveData'  => '',
                'isLeaveRow' => 1,
            ])
            ->assertOk();

        $saved = EmployeeAttendance::find($row->id);

        $this->assertSame((int) $row->leave_status, (int) $saved->leave_status, 'the absence reason is preserved');
        $this->assertSame((int) $fields['leave_hour'], (int) $saved->leave_hour);
        $this->assertSame(1, (int) $saved->updated_by);
    }

    public function test_bulk_save_updates_every_row_it_is_given(): void
    {
        $html = $this->actingAs($this->user())->get('/hr/attendance/show/'.self::DAY)->getContent();

        $rows = EmployeeAttendance::where('date', date('Y-m-d', self::DAY))
            ->where('overtime_status', 0)->where('leave_status', '<', 2)->where('user_issues', 0)
            ->limit(3)->get();
        $this->assertCount(3, $rows);

        $payload = [];
        foreach ($rows as $row) {
            $fields = $this->editorPayload($html, $row->id);
            // updateAll() only touches rows carrying an id; the browser appends it.
            $fields['id'] = (string) $row->id;
            $payload[$row->id] = $fields;
        }

        $this->actingAs($this->user())
            ->post(route('hr.attendance.update.all'), [
                'allData' => http_build_query(['attendance' => $payload]),
            ])
            ->assertOk();

        foreach ($rows as $row) {
            $saved = EmployeeAttendance::find($row->id);
            $this->assertSame(1, (int) $saved->updated_by, "row {$row->id} was not saved by the bulk action");
            $this->assertSame((int) $payload[$row->id]['total_work_hour'], (int) $saved->total_work_hour);
        }
    }
}
