<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reports › SLC Reports › SLC Registration History, new version (user's slc-registration-history
 * design, 25 Nov 2026): the registration confirmations sent to the Student Loans Company in a date
 * range. As SLC Attendance History: the screen gets every confirmation of the range and filters it
 * in the browser (status, registration year, academic year, course, day); Excel applies the same
 * filters here. The rows are the old export's (SlcDataReportController::SlcRegistrationHistoryExcelDownload):
 * slc_registrations not deleted, by confirmation date; course, intake and class dates from the
 * confirmation's course relation.
 */
class SlcRegistrationHistory
{
    /** Status names: the design's for Yes and No; the others keep their code. */
    const NAMES = ['Yes' => 'Registered', 'No' => 'Not registered'];

    const COLOURS = ['Yes' => '#0e5a54', 'No' => '#b3261e', 'N/R' => '#7a4fb5', 'RCNR' => '#e0a030'];

    /** Confirmations dated $from to $to (Y-m-d), newest first. */
    public static function rows(string $from, string $to): Collection
    {
        return DB::table('slc_registrations as g')
            ->leftJoin('student_course_relations as scr', function ($j) {
                $j->on('scr.id', '=', 'g.student_course_relation_id')->whereNull('scr.deleted_at');
            })
            ->leftJoin('course_creations as cc', function ($j) {
                $j->on('cc.id', '=', 'scr.course_creation_id')->whereNull('cc.deleted_at');
            })
            ->leftJoin('students as s', 's.id', '=', 'g.student_id')
            ->whereNull('g.deleted_at')
            ->whereBetween('g.confirmation_date', [$from, $to])
            ->orderByDesc('g.confirmation_date')->orderByDesc('g.id')
            ->get([
                'g.id', 'g.confirmation_date', 'g.slc_registration_status_id', 'g.registration_year', 'g.academic_year_id', 'g.student_id',
                'cc.course_id', 'cc.semester_id', 's.status_id',
                DB::raw('(scr.course_start_date IS NOT NULL AND scr.course_end_date IS NOT NULL) AS dated'),
            ]);
    }

    /**
     * The screen's data: compact rows and the lookups they point into.
     * rows = [[id, date, status id, registration year, academic year id, course id, intake id,
     *          student status id, has class dates 1/0, student id], ...]
     */
    public static function build(string $from, string $to): array
    {
        $rows = self::rows($from, $to);
        $ids = fn ($col) => $rows->pluck($col)->filter()->unique()->values()->all();

        $students = [];
        foreach (array_chunk($ids('student_id'), 5000) as $chunk) {
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
            'statuses' => self::statuses(),
            'years' => DB::table('academic_years')->whereIn('id', $ids('academic_year_id'))->pluck('name', 'id'),
            'courses' => DB::table('courses')->whereIn('id', $ids('course_id'))->pluck('name', 'id'),
            'intakes' => DB::table('semesters')->whereIn('id', $ids('semester_id'))->pluck('name', 'id'),
            'studentStatuses' => DB::table('statuses')->whereIn('id', $ids('status_id'))->pluck('name', 'id'),
            'students' => (object) $students,
            'rows' => $rows->map(fn ($r) => [
                (int) $r->id, (string) $r->confirmation_date, $r->slc_registration_status_id !== null ? (int) $r->slc_registration_status_id : null,
                $r->registration_year ? (int) $r->registration_year : null, $r->academic_year_id !== null ? (int) $r->academic_year_id : null,
                $r->course_id !== null ? (int) $r->course_id : null, $r->semester_id !== null ? (int) $r->semester_id : null,
                $r->status_id !== null ? (int) $r->status_id : null, (int) $r->dated, (int) $r->student_id,
            ])->values(),
            // Student statuses the "Worth checking" list compares registrations with.
            'checks' => ['left' => $statusId('SLC Left'), 'enrolled' => $statusId('SLC Enrolled'), 'registered' => $statusId('SLC REGISTERED')],
        ];
    }

    /** Every registration status, Yes, No, N/R first: [[id, code, name, colour], ...]. */
    public static function statuses(): array
    {
        $order = array_flip(['Yes', 'No', 'N/R']);

        return DB::table('slc_registration_statuses')->get(['id', 'name'])
            ->sortBy(fn ($s) => [($order[$s->name] ?? 9), $s->name])
            ->map(fn ($s) => ['id' => (int) $s->id, 'code' => (string) $s->name, 'name' => self::NAMES[$s->name] ?? 'Status '.$s->name, 'colour' => self::COLOURS[$s->name] ?? '#4a5b6b'])
            ->values()->all();
    }

    /** Academic years, newest first: [[id, name], ...] (the search form's list). */
    public static function academicYears(): array
    {
        return DB::table('academic_years')->whereNull('deleted_at')->orderByDesc('id')->get(['id', 'name'])
            ->map(fn ($y) => [(int) $y->id, (string) $y->name])->all();
    }

    /** Courses that have SLC registrations, A–Z: [[id, name], ...]. */
    public static function courses(): array
    {
        return DB::table('slc_registrations as g')
            ->join('student_course_relations as scr', 'scr.id', '=', 'g.student_course_relation_id')
            ->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->join('courses as c', 'c.id', '=', 'cc.course_id')
            ->whereNull('g.deleted_at')->distinct()->orderBy('c.name')
            ->get(['c.id', 'c.name'])->map(fn ($c) => [(int) $c->id, (string) $c->name])->all();
    }

    /**
     * The screen's filters on rows from rows() (Excel): statuses, years (registration), academic
     * years (lists; empty = all) and course (id or null). Dates are already the range's.
     */
    public static function filter(Collection $rows, array $f): Collection
    {
        $statuses = array_map('intval', $f['statuses'] ?? []);
        $years = array_map('intval', $f['years'] ?? []);
        $academic = array_map('intval', $f['academic'] ?? []);
        $course = isset($f['course']) && $f['course'] !== '' && $f['course'] !== null ? (int) $f['course'] : null;

        return $rows->filter(fn ($r) => (empty($statuses) || in_array((int) $r->slc_registration_status_id, $statuses, true))
            && (empty($years) || in_array((int) $r->registration_year, $years, true))
            && (empty($academic) || in_array((int) $r->academic_year_id, $academic, true))
            && ($course === null || (int) $r->course_id === $course))->values();
    }
}
