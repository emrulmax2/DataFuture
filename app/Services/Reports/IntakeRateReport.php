<?php

namespace App\Services\Reports;

use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use Illuminate\Support\Facades\DB;

/**
 * Shared by the new Continuation Rate and Retention Rate reports (Reports ›
 * Intake Performance, "New version" tabs). Both follow the old reports' rules:
 * per course of the intake (latest creation per course),
 *   intake      = students with an active course relation on the creation
 *   registered  = those with an awarding body reference on that relation
 *   dropped out = registered students who left (each report's own rule)
 *   kept        = registered - dropped out;  rate = kept / registered
 * A course nobody started is left out, as in the old reports.
 */
abstract class IntakeRateReport
{
    use Concerns\NamesIntakeCourses;

    const DROPPED_OUT_STATUSES = [22, 27, 30, 31, 42, 43, 45, 14, 17, 33, 36, 47, 50];
    const REFUND_DAYS = 28;
    const SMALL_COHORT = 30;
    /**
     * Rates below this are shown in orange. The designs show 64.7% and 71.4%
     * orange, and 73.4% and 76.0% in the normal colour (23 Nov 2026 designs).
     */
    const LOW_RATE = 72;
    /** What the report calls a registered student who did not drop out. */
    const KEPT_LABEL = 'Kept';

    /** Whether the course is counted yet. */
    abstract protected function isDue(?string $start, ?string $end, string $today): bool;

    /** When a course that is not due yet will be (shown as "Not due until …"). */
    protected function dueDate(?string $start, ?string $end): ?string
    {
        return null;
    }

    /** Registered students who dropped out, as [student id => status change date]. */
    abstract protected function droppedOut(array $registered, ?string $start, ?string $end): array;

    public function build(int $semesterId): array
    {
        $semester = Semester::find($semesterId);
        $courses = array_map(function ($row) {
            unset($row['student_ids'], $row['registered_ids'], $row['dropped']);
            return $row;
        }, $this->courses($semesterId));

        // Lowest rate first; courses without a rate, then those not due yet, at the end.
        usort($courses, function ($a, $b) {
            $rank = function ($row) { return ($row['due'] ? ($row['rate'] !== null ? 0 : 1) : 2); };
            return [$rank($a), $a['rate'] ?? 0, $a['title']] <=> [$rank($b), $b['rate'] ?? 0, $b['title']];
        });

        $due = array_values(array_filter($courses, function ($row) { return $row['due']; }));
        $totals = ['intake' => 0, 'registered' => 0, 'kept' => 0, 'dropped_out' => 0, 'never_registered' => 0];
        foreach ($due as $row) {
            foreach ($totals as $key => $value) {
                $totals[$key] += $row[$key];
            }
        }
        $totals['rate'] = $this->rate($totals['kept'], $totals['registered']);
        $notDue = array_filter(array_column(array_filter($courses, function ($row) { return !$row['due']; }), 'due_date'));

        return [
            'semester' => (string) optional($semester)->name,
            'intake' => $this->intakeLabel((string) optional($semester)->name),
            'courses' => $courses,
            'totals' => $totals,
            'has_due' => !empty($due),
            'next_due' => (!empty($notDue) ? min($notDue) : null),
            'small_note' => $this->smallCohortNote($due),
        ];
    }

    /**
     * Several intakes, each counted on its own as build() does, in intake
     * order, plus totals over the courses that are due. The old reports took
     * several semesters the same way: per semester, then overall.
     */
    public function buildMany(array $semesterIds): array
    {
        $intakes = [];
        foreach ($this->orderSemesters($semesterIds) as $id) {
            $intakes[] = ['id' => $id] + $this->build($id);
        }

        $totals = ['intake' => 0, 'registered' => 0, 'kept' => 0, 'dropped_out' => 0, 'never_registered' => 0];
        $due = [];
        $nextDue = [];
        foreach ($intakes as $intake) {
            foreach ($totals as $key => $value) {
                $totals[$key] += $intake['totals'][$key];
            }
            foreach ($intake['courses'] as $course) {
                if ($course['due']) {
                    $due[] = $course;
                }
            }
            if (!$intake['has_due'] && !empty($intake['next_due'])) {
                $nextDue[] = $intake['next_due'];
            }
        }
        $totals['rate'] = $this->rate($totals['kept'], $totals['registered']);
        $names = array_column($intakes, 'semester');

        return [
            'intakes' => $intakes,
            'many' => (count($intakes) > 1),
            'semester' => implode(', ', $names),
            'intake' => $this->intakesLabel($names),
            'file' => $this->intakesFileStem($names),
            'totals' => $totals,
            'has_courses' => (bool) array_filter($intakes, function ($intake) { return !empty($intake['courses']); }),
            'has_due' => !empty($due),
            'next_due' => (!empty($nextDue) ? min($nextDue) : null),
            'small_note' => $this->smallCohortNote($due),
        ];
    }

    /** students() for several intakes, each row naming its intake. */
    public function studentsMany(array $semesterIds): array
    {
        $rows = [];
        foreach ($this->orderSemesters($semesterIds) as $id) {
            $intake = $this->intakeName((string) Semester::whereKey($id)->value('name'));
            foreach ($this->students($id) as $row) {
                $rows[] = ['intake' => $intake] + $row;
            }
        }

        return $rows;
    }

    /**
     * One row per student of the counted courses, for the Excel download:
     * registration no, name, course, awarding body reference, outcome, date
     * of the drop-out status, current status.
     */
    public function students(int $semesterId): array
    {
        $courses = array_filter($this->courses($semesterId), function ($row) { return $row['due']; });
        $ids = [];
        foreach ($courses as $row) {
            $ids = array_merge($ids, $row['student_ids']);
        }
        if (empty($ids)) {
            return [];
        }
        $people = DB::table('students as s')->leftJoin('statuses as st', 'st.id', '=', 's.status_id')
            ->whereIn('s.id', array_unique($ids))
            ->select('s.id', 's.registration_no', 's.first_name', 's.last_name', 'st.name as status')
            ->get()->keyBy('id');
        $references = StudentAwardingBodyDetails::whereIn('student_id', array_unique($ids))->whereNotNull('reference')
            ->pluck('reference', 'student_id');

        $rows = [];
        foreach ($courses as $course) {
            foreach ($course['student_ids'] as $id) {
                $person = $people->get($id);
                $registered = in_array($id, $course['registered_ids'], true);
                $dropped = array_key_exists($id, $course['dropped']);
                $rows[] = [
                    'registration_no' => (string) optional($person)->registration_no,
                    'name' => trim(ucwords(strtolower(optional($person)->first_name.' '.optional($person)->last_name))),
                    'course' => $course['name'],
                    'reference' => ($registered ? (string) ($references[$id] ?? '') : ''),
                    'outcome' => (!$registered ? 'Never registered' : ($dropped ? 'Dropped out' : static::KEPT_LABEL)),
                    'dropped_on' => ($dropped && !empty($course['dropped'][$id]) ? date('Y-m-d', strtotime($course['dropped'][$id])) : null),
                    'status' => (string) optional($person)->status,
                ];
            }
        }
        usort($rows, function ($a, $b) {
            return [$a['course'], $a['registration_no']] <=> [$b['course'], $b['registration_no']];
        });

        return $rows;
    }

    /** The intake's courses with their counts and the student ids behind them. */
    protected function courses(int $semesterId): array
    {
        $today = now()->toDateString();
        $rows = [];
        $creations = CourseCreation::with(['course', 'available'])->where('semester_id', $semesterId)->orderBy('id', 'DESC')->get()->unique('course_id');
        foreach ($creations as $creation) {
            $start = $this->date(optional($creation->available)->course_start_date);
            $end = $this->date(optional($creation->available)->course_end_date);
            $row = $this->courseNames((string) optional($creation->course)->name) + [
                'due' => $this->isDue($start, $end, $today),
                'due_date' => $this->dueDate($start, $end),
                'end_date' => $end,
                'running' => ($end !== null && $end >= $today),
                'intake' => 0, 'registered' => 0, 'kept' => 0, 'dropped_out' => 0, 'never_registered' => 0,
                'rate' => null, 'small' => false, 'low' => false,
                'student_ids' => [], 'registered_ids' => [], 'dropped' => [],
            ];

            if ($row['due']) {
                $students = StudentCourseRelation::where('course_creation_id', $creation->id)->where('active', 1)
                    ->pluck('student_id')->map(function ($id) { return (int) $id; })->unique()->values()->all();
                if (empty($students)) {
                    continue;
                }
                $registered = StudentAwardingBodyDetails::whereIn('student_id', $students)->whereNotNull('reference')
                    ->whereHas('studentcrel', function ($q) use ($creation) {
                        $q->where('course_creation_id', $creation->id);
                    })->pluck('student_id')->map(function ($id) { return (int) $id; })->unique()->values()->all();
                $dropped = (empty($registered) ? [] : $this->droppedOut($registered, $start, $end));

                $row['student_ids'] = $students;
                $row['registered_ids'] = $registered;
                $row['dropped'] = $dropped;
                $row['intake'] = count($students);
                $row['registered'] = count($registered);
                $row['dropped_out'] = count($dropped);
                $row['kept'] = $row['registered'] - $row['dropped_out'];
                $row['never_registered'] = $row['intake'] - $row['registered'];
                $row['rate'] = $this->rate($row['kept'], $row['registered']);
                $row['small'] = ($row['registered'] > 0 && $row['registered'] < self::SMALL_COHORT);
                $row['low'] = ($row['rate'] !== null && $row['rate'] < self::LOW_RATE);
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /** Keeps the earliest date per student from [student id, date] pairs. */
    protected function firstDates($pairs): array
    {
        $out = [];
        foreach ($pairs as $pair) {
            $id = (int) $pair->student_id;
            $date = $pair->status_change_date;
            if (!array_key_exists($id, $out) || ($date !== null && ($out[$id] === null || $date < $out[$id]))) {
                $out[$id] = $date;
            }
        }

        return $out;
    }

    protected function date($value): ?string
    {
        return (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }

    protected function rate(int $kept, int $registered): ?float
    {
        return ($registered > 0 ? round($kept / $registered * 100, 1) : null);
    }

    /**
     * How far one student moves a small cohort's rate:
     * "With 7 registered, one student changes the rate by 14 percentage points"
     * or, for several, "One student changes the rate by 4 to 6 percentage points".
     */
    protected function smallCohortNote(array $due): ?string
    {
        $small = array_values(array_filter($due, function ($row) { return $row['small']; }));
        if (empty($small)) {
            return null;
        }
        $lead = 'Fewer than '.self::SMALL_COHORT.' registered students. ';
        if (count($small) === 1) {
            return $lead.'With '.$small[0]['registered'].' registered, one student changes the rate by '
                .(int) round(100 / $small[0]['registered']).' percentage points, so read this rate with care.';
        }
        $swings = array_map(function ($row) { return (int) round(100 / $row['registered']); }, $small);
        $low = min($swings);
        $high = max($swings);

        return $lead.'One student changes the rate by '.($low === $high ? 'about '.$low : $low.' to '.$high)
            .' percentage points, so read these rates with care.';
    }
}
