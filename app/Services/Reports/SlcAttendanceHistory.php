<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reports › SLC Reports › SLC Attendance History, new version (user's slc-attendance-history
 * design, 25 Nov 2026): the attendance confirmations sent to the Student Loans Company in a date
 * range. The screen gets every confirmation of the range at once and filters it in the browser
 * (code, year, term, course, day); the Excel export applies the same filters here.
 *
 * The rows are the old export's (SlcDataReportController::SLCAttendanceExcelDownload):
 * slc_attendances not deleted, by confirmation date; course and intake from the confirmation's
 * course relation; the claim amount is its first instalment (the old `currentClaimAmount`).
 */
class SlcAttendanceHistory
{
    /** Code names: X and S as the SLC bulk attendance screen describes them, A from the design. */
    const NAMES = ['A' => 'In attendance', 'S' => 'Studies suspended', 'X' => 'Not in attendance'];

    const COLOURS = ['A' => '#0e5a54', 'S' => '#e0a030', 'X' => '#b3261e', 'F' => '#7a4fb5', 'C' => '#1668a8', 'D' => '#a3456b', 'L' => '#8a5a2b', 'N' => '#4a5b6b'];

    /** Confirmations dated $from to $to (Y-m-d), newest first, with the claim amount. */
    public static function rows(string $from, string $to): Collection
    {
        $rows = DB::table('slc_attendances as a')
            ->leftJoin('student_course_relations as scr', function ($j) {
                $j->on('scr.id', '=', 'a.student_course_relation_id')->whereNull('scr.deleted_at');
            })
            ->leftJoin('course_creations as cc', function ($j) {
                $j->on('cc.id', '=', 'scr.course_creation_id')->whereNull('cc.deleted_at');
            })
            ->leftJoin('students as s', 's.id', '=', 'a.student_id')
            ->whereNull('a.deleted_at')
            ->whereBetween('a.confirmation_date', [$from, $to])
            ->orderByDesc('a.confirmation_date')->orderByDesc('a.id')
            ->get(['a.id', 'a.confirmation_date', 'a.attendance_code_id', 'a.attendance_year', 'a.session_term', 'a.student_id', 'cc.course_id', 'cc.semester_id', 's.status_id']);

        // The first instalment of each confirmation, in one pass (slc_installments has no index
        // on slc_attendance_id, so a lookup per row took 23 s for a year).
        $amount = [];
        foreach (array_chunk($rows->pluck('id')->all(), 5000) as $chunk) {
            DB::table('slc_installments')->whereNull('deleted_at')->whereIn('slc_attendance_id', $chunk)->orderBy('id')
                ->get(['slc_attendance_id', 'amount'])
                ->each(function ($i) use (&$amount) {
                    $amount[$i->slc_attendance_id] ??= $i->amount;
                });
        }

        return $rows->map(function ($r) use ($amount) {
            $r->amount = isset($amount[$r->id]) && $amount[$r->id] !== null ? (float) $amount[$r->id] : null;

            return $r;
        });
    }

    /**
     * The screen's data: compact rows and the lookups they point into.
     * rows = [[id, date, code id, year, term, course id, intake id, status id, amount, student id], ...]
     */
    public static function build(string $from, string $to): array
    {
        $rows = self::rows($from, $to);

        $courseIds = $rows->pluck('course_id')->filter()->unique()->values()->all();
        $intakeIds = $rows->pluck('semester_id')->filter()->unique()->values()->all();
        $statusIds = $rows->pluck('status_id')->filter()->unique()->values()->all();
        $studentIds = $rows->pluck('student_id')->unique()->values()->all();

        $students = [];
        foreach (array_chunk($studentIds, 5000) as $chunk) {
            DB::table('students')->whereIn('id', $chunk)->get(['id', 'registration_no', 'application_no', 'first_name', 'last_name'])
                ->each(function ($s) use (&$students) {
                    $students[$s->id] = [(string) ($s->registration_no ?: $s->application_no), trim($s->first_name.' '.$s->last_name)];
                });
        }

        $statusId = fn ($name) => (int) DB::table('statuses')->where('name', $name)->value('id');

        return [
            'from' => $from,
            'to' => $to,
            'today' => now()->toDateString(),
            'codes' => self::codes(),
            'courses' => DB::table('courses')->whereIn('id', $courseIds)->pluck('name', 'id'),
            'intakes' => DB::table('semesters')->whereIn('id', $intakeIds)->pluck('name', 'id'),
            'statuses' => DB::table('statuses')->whereIn('id', $statusIds)->pluck('name', 'id'),
            'students' => (object) $students,
            'rows' => $rows->map(fn ($r) => [
                (int) $r->id, (string) $r->confirmation_date, (int) $r->attendance_code_id,
                $r->attendance_year !== null ? (int) $r->attendance_year : null, $r->session_term !== null ? (int) $r->session_term : null,
                $r->course_id !== null ? (int) $r->course_id : null, $r->semester_id !== null ? (int) $r->semester_id : null,
                $r->status_id !== null ? (int) $r->status_id : null, $r->amount, (int) $r->student_id,
            ])->values(),
            // Statuses the "Worth checking" list compares codes with.
            'checks' => ['suspended' => $statusId('SLC Suspended'), 'enrolled' => $statusId('SLC Enrolled'), 'left' => $statusId('SLC Left')],
        ];
    }

    /** Every attendance code, A, S and X first: [[id, code, name, colour], ...]. */
    public static function codes(): array
    {
        $order = array_flip(['A', 'S', 'X']);

        return DB::table('attendance_codes')->whereNull('deleted_at')->get(['id', 'code'])
            ->sortBy(fn ($c) => [($order[$c->code] ?? 9), $c->code])
            ->map(fn ($c) => ['id' => (int) $c->id, 'code' => (string) $c->code, 'name' => self::NAMES[$c->code] ?? 'Code '.$c->code, 'colour' => self::COLOURS[$c->code] ?? '#4a5b6b'])
            ->values()->all();
    }

    /** Courses that have SLC confirmations, A–Z: [[id, name], ...] (the search form's list). */
    public static function courses(): array
    {
        return DB::table('slc_attendances as a')
            ->join('student_course_relations as scr', 'scr.id', '=', 'a.student_course_relation_id')
            ->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->join('courses as c', 'c.id', '=', 'cc.course_id')
            ->whereNull('a.deleted_at')->distinct()->orderBy('c.name')
            ->get(['c.id', 'c.name'])->map(fn ($c) => [(int) $c->id, (string) $c->name])->all();
    }

    /**
     * The screen's filters on rows from rows() (Excel): codes, years, terms (lists of ids or
     * numbers; empty = all) and course (id or null). Dates are already the range's.
     */
    public static function filter(Collection $rows, array $f): Collection
    {
        $codes = array_map('intval', $f['codes'] ?? []);
        $years = array_map('intval', $f['years'] ?? []);
        $terms = array_map('intval', $f['terms'] ?? []);
        $course = isset($f['course']) && $f['course'] !== '' && $f['course'] !== null ? (int) $f['course'] : null;

        return $rows->filter(fn ($r) => (empty($codes) || in_array((int) $r->attendance_code_id, $codes, true))
            && (empty($years) || ($r->attendance_year !== null && in_array((int) $r->attendance_year, $years, true)))
            && (empty($terms) || ($r->session_term !== null && in_array((int) $r->session_term, $terms, true)))
            && ($course === null || (int) $r->course_id === $course))->values();
    }
}
