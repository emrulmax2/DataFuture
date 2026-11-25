<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reports › SLC Reports › SLC COC History, new version (user's slc-coc-history design, 25 Nov
 * 2026): the changes of circumstances reported to the Student Loans Company. Few rows (about 300 a
 * year), so the design's "All dates" is the default; the screen gets every change of the range and
 * filters it in the browser (type, actioned, course); Excel applies the same filters here. The rows
 * are the old export's (SlcDataReportController::SlcCocHistoryExcelDownload): slc_cocs not deleted,
 * by confirmation date; course and intake from the change's course relation.
 * Actioned is "Yes" or not: "no" and the 22 blank ones are not actioned.
 */
class SlcCocHistory
{
    /** Types in the design's order (then the two the design lacked) with its colours. */
    const TYPES = [
        'Withdrawal' => '#b3261e', 'Suspension' => '#b9770e', 'Repetition' => '#7a4fb5', 'Resumption' => '#0e5a54',
        'Transfer' => '#1668a8', 'Fee' => '#4a5560', 'Free' => '#2f8f84', 'Outstanding' => '#a3456b',
    ];

    /** Changes dated $from to $to (Y-m-d; both null = every date), newest first. */
    public static function rows(?string $from, ?string $to): Collection
    {
        $q = DB::table('slc_cocs as c')
            ->leftJoin('student_course_relations as scr', function ($j) {
                $j->on('scr.id', '=', 'c.student_course_relation_id')->whereNull('scr.deleted_at');
            })
            ->leftJoin('course_creations as cc', function ($j) {
                $j->on('cc.id', '=', 'scr.course_creation_id')->whereNull('cc.deleted_at');
            })
            ->leftJoin('students as s', 's.id', '=', 'c.student_id')
            ->whereNull('c.deleted_at');
        if ($from !== null && $to !== null) {
            $q->whereBetween('c.confirmation_date', [$from, $to]);
        }

        return $q->orderByDesc('c.confirmation_date')->orderByDesc('c.id')
            ->get(['c.id', 'c.confirmation_date', 'c.coc_type', 'c.actioned', 'c.student_id', 'cc.course_id', 'cc.semester_id', 's.status_id']);
    }

    /**
     * The screen's data: rows = [[id, date, type, actioned 1/0, course id, intake id, student
     * status id, student id], ...] and the lookups they point into.
     */
    public static function build(?string $from, ?string $to): array
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

        return [
            'from' => $from,
            'to' => $to,
            'today' => now()->toDateString(),
            'first' => $rows->min('confirmation_date'),
            'types' => self::types(),
            'courses' => DB::table('courses')->whereIn('id', $ids('course_id'))->pluck('name', 'id'),
            'intakes' => DB::table('semesters')->whereIn('id', $ids('semester_id'))->pluck('name', 'id'),
            'statuses' => DB::table('statuses')->whereIn('id', $ids('status_id'))->pluck('name', 'id'),
            'students' => (object) $students,
            'rows' => $rows->map(fn ($r) => [
                (int) $r->id, (string) $r->confirmation_date, (string) $r->coc_type, self::actioned($r->actioned) ? 1 : 0,
                $r->course_id !== null ? (int) $r->course_id : null, $r->semester_id !== null ? (int) $r->semester_id : null,
                $r->status_id !== null ? (int) $r->status_id : null, (int) $r->student_id,
            ])->values(),
        ];
    }

    /** [[name, colour], ...] in the design's order. */
    public static function types(): array
    {
        return array_map(fn ($name, $colour) => [$name, $colour], array_keys(self::TYPES), self::TYPES);
    }

    public static function actioned(?string $value): bool
    {
        return strcasecmp((string) $value, 'Yes') === 0;
    }

    /** Courses that have changes of circumstances, A–Z: [[id, name], ...]. */
    public static function courses(): array
    {
        return DB::table('slc_cocs as c')
            ->join('student_course_relations as scr', 'scr.id', '=', 'c.student_course_relation_id')
            ->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->join('courses as co', 'co.id', '=', 'cc.course_id')
            ->whereNull('c.deleted_at')->distinct()->orderBy('co.name')
            ->get(['co.id', 'co.name'])->map(fn ($c) => [(int) $c->id, (string) $c->name])->all();
    }

    /** The screen's filters (Excel): types (names; empty = all), actioned (all|yes|no), course (id or null). */
    public static function filter(Collection $rows, array $f): Collection
    {
        $types = array_values(array_filter((array) ($f['types'] ?? []), 'strlen'));
        $act = $f['actioned'] ?? 'all';
        $course = isset($f['course']) && $f['course'] !== '' && $f['course'] !== null ? (int) $f['course'] : null;

        return $rows->filter(fn ($r) => (empty($types) || in_array((string) $r->coc_type, $types, true))
            && ($act === 'all' || ($act === 'yes') === self::actioned($r->actioned))
            && ($course === null || (int) $r->course_id === $course))->values();
    }
}
