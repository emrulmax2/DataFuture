<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\TermDeclaration;
use App\Services\Reports\NonTutorAttendanceUpdates;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Reports > Non-Tutor Attendance Updates: attendance now Present that was last changed
 * by staff with no link to the class, searched by attendance term and/or a date range
 * (on the class date or on the date changed). The Excel files are
 * built in the browser from list/details (a busy member of staff can have thousands of
 * rows in one term, too many for an in-memory spreadsheet here).
 */
class NonTutorAttendanceUpdateController extends Controller
{
    /** Printed above the table in the Excel files. */
    private const ROWS_SHOWN = 'Attendance now Present, last changed by staff who are not the class tutor, personal tutor, cover tutor or tutor of the parent theory class, and did not start the class';

    private const NOTHING_CHOSEN = 'Please select an attendance term or a date range.';

    public function index()
    {
        return view('pages.reports.non-tutor-attendance.index', [
            'title' => 'Non-Tutor Attendance Updates - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Non-Tutor Attendance Updates', 'href' => 'javascript:void(0);'],
            ],
            'terms' => TermDeclaration::orderBy('id', 'DESC')->get(['id', 'name']),
        ]);
    }

    /** The summary list: one row per member of staff. */
    public function list(Request $request)
    {
        [$terms, $dates] = [$this->terms($request), $this->dates($request)];
        if (! $terms && ! $dates) {
            return response()->json(['message' => self::NOTHING_CHOSEN], 422);
        }

        $report = new NonTutorAttendanceUpdates($terms ? $terms->keys()->all() : [], $dates);

        return response()->json([
            'rows' => $report->summary(),
            'meta' => $this->meta($terms, $dates),
        ]);
    }

    /**
     * One member of staff's changes, for the details window:
     *   group=student  "By student" tab: one row per student with the rows changed;
     *   group=tutor    "By tutor" tab: who teaches the class (tutor, else personal tutor);
     *   group=sessions that tutor's class dates (tutor_id);
     *   group=review   "Needs review" tab: students whose absence this person changed to Present;
     *   group=review_timeline  those students' class dates in the classes involved
     *                  (student_id for one student, none = all of them for the Excel file);
     *   student_id=N   that student's rows (opened under the student's line);
     *   session_id=N   the rows changed in that class (opened under the class date);
     *   all=1          every row (the "All changes" Excel file);
     *   page/size      "All changes" tab, Tabulator's remote pagination.
     */
    public function details(Request $request)
    {
        [$terms, $dates] = [$this->terms($request), $this->dates($request)];
        $userId = (int) $request->input('user_id');
        if ((! $terms && ! $dates) || $userId < 1) {
            return response()->json(['message' => self::NOTHING_CHOSEN], 422);
        }

        $report = new NonTutorAttendanceUpdates($terms ? $terms->keys()->all() : [], $dates);
        $search = (string) $request->input('search', '');

        if ($request->input('group') === 'review') {
            return response()->json(['data' => $report->reviewStudents($userId, $search)]);
        }

        if ($request->input('group') === 'review_timeline') {
            $studentId = $request->filled('student_id') ? (int) $request->input('student_id') : null;

            return response()->json(['data' => $report->reviewTimeline($userId, $studentId, $search)]);
        }

        if ($request->input('group') === 'student') {
            return response()->json(['data' => $report->students($userId, $search)]);
        }

        if ($request->input('group') === 'tutor') {
            return response()->json(['data' => $report->tutors($userId, $search)]);
        }

        if ($request->input('group') === 'sessions') {
            return response()->json(['data' => $report->sessions($userId, $search, (int) $request->input('tutor_id'))]);
        }

        if ($request->filled('student_id') || $request->filled('session_id') || $request->boolean('all')) {
            $filters = array_filter([
                'student_id' => $request->filled('student_id') ? (int) $request->input('student_id') : null,
                'session_id' => $request->filled('session_id') ? (int) $request->input('session_id') : null,
            ], fn ($value) => $value !== null);
            $result = $report->details($userId, $search, null, 50, $filters);

            return response()->json(['data' => $result['rows'], 'total' => $result['total'], 'meta' => $this->meta($terms, $dates)]);
        }

        $size = min(max((int) $request->input('size', 50), 10), 250);
        $page = max((int) $request->input('page', 1), 1);
        $result = $report->details($userId, $search, $page, $size);

        return response()->json([
            'last_page' => max(1, (int) ceil($result['total'] / $size)),
            'total' => $result['total'],
            'data' => $result['rows'],
        ]);
    }

    /** The chosen term_declarations as id => name, from "terms=1,2" or terms[]. */
    private function terms(Request $request)
    {
        $ids = $request->input('terms', []);
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        $ids = array_filter(array_map('intval', $ids));

        if (! $ids) {
            return null;
        }

        $terms = TermDeclaration::whereIn('id', $ids)->orderBy('id', 'DESC')->pluck('name', 'id');

        return $terms->isEmpty() ? null : $terms;
    }

    /**
     * The date range from date_from / date_to (Y-m-d) and date_on (class|changed), or
     * null when either end is missing or not a date. A reversed range is put in order.
     */
    private function dates(Request $request): ?array
    {
        $parse = function ($value) {
            $value = (string) $value;
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            $date = Carbon::createFromFormat('!Y-m-d', $value);

            return $date && $date->format('Y-m-d') === $value ? $value : null;
        };

        $from = $parse($request->input('date_from'));
        $to = $parse($request->input('date_to'));
        if (! $from || ! $to) {
            return null;
        }
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $on = $request->input('date_on') === 'changed' ? 'changed' : 'class';

        return ['on' => $on, 'from' => $from, 'to' => $to];
    }

    /** "Class date 01/10/2026 - 31/10/2026" */
    private function dateLabel(?array $dates): string
    {
        if (! $dates) {
            return '';
        }
        $uk = fn ($ymd) => Carbon::createFromFormat('!Y-m-d', $ymd)->format('d/m/Y');
        $range = $dates['from'] === $dates['to'] ? $uk($dates['from']) : $uk($dates['from']).' - '.$uk($dates['to']);

        return NonTutorAttendanceUpdates::DATE_ON[$dates['on']].' '.$range;
    }

    /** What the page and the Excel files show about the search. */
    private function meta($terms, ?array $dates): array
    {
        $termNames = $terms ? $terms->values()->implode(', ') : 'All terms';
        $dateLabel = $this->dateLabel($dates);

        return [
            'terms' => $termNames,
            'dates' => $dateLabel,
            'label' => $dateLabel ? $termNames.' · '.$dateLabel : $termNames,
            'rows_shown' => self::ROWS_SHOWN,
            'review_rule' => NonTutorAttendanceUpdates::REVIEW_RULE,
            'generated_at' => now('Europe/London')->format('d/m/Y H:i').' (UK time)',
            'generated_by' => optional(auth()->user())->name ?? '',
        ];
    }
}
