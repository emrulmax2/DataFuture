<?php

namespace App\Services\Reports;

use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;

/**
 * Award Rate for one intake (Reports › Intake Performance, new version).
 *
 * Same rules as the old report (AwardRateReportController::refineResult): per
 * course of the intake (latest creation per course),
 *   registered       = students on the creation with an awarding body
 *                      reference on that course relation
 *   completed        = registered students now at Course Completed (13) or
 *                      SLC Course Completed (21)
 *   did not complete = registered - completed
 *   rate             = completed / registered
 * As in the old report, the course's students are not limited to active
 * course relations, and a course nobody started is left out. A course that
 * has not ended yet still counts; it is marked "Still running".
 */
class AwardRate
{
    use Concerns\NamesIntakeCourses;

    const COMPLETED_STATUSES = [13, 21];
    const SMALL_COHORT = 30;
    /** Rates below this are shown in orange: the design shows 63.6% orange and 76.5% in ink. */
    const LOW_RATE = 75;

    public function build(int $semesterId): array
    {
        $semester = Semester::find($semesterId);
        $today = now()->toDateString();
        $courses = [];
        $creations = CourseCreation::with(['course', 'available'])->where('semester_id', $semesterId)->orderBy('id', 'DESC')->get()->unique('course_id');
        foreach ($creations as $creation) {
            $students = StudentCourseRelation::where('course_creation_id', $creation->id)->pluck('student_id')->unique()->values()->all();
            if (empty($students)) {
                continue;
            }
            $registered = StudentAwardingBodyDetails::whereIn('student_id', $students)->whereNotNull('reference')
                ->whereHas('studentcrel', function ($q) use ($creation) {
                    $q->where('course_creation_id', $creation->id);
                })->pluck('student_id')->unique()->values()->all();
            $completed = (empty($registered) ? 0 : Student::whereIn('id', $registered)->whereIn('status_id', self::COMPLETED_STATUSES)->pluck('id')->unique()->count());
            $end = optional($creation->available)->course_end_date;
            $end = (!empty($end) ? date('Y-m-d', strtotime($end)) : null);

            $courses[] = $this->courseNames((string) optional($creation->course)->name) + $this->figures(count($registered), $completed) + [
                'running' => ($end !== null && $end >= $today),
                'end_date' => $end,
            ];
        }

        // Lowest rate first; courses without registered students at the end.
        usort($courses, function ($a, $b) {
            return [$a['rate'] === null ? 1 : 0, $a['exact'] ?? 0, $a['title']] <=> [$b['rate'] === null ? 1 : 0, $b['exact'] ?? 0, $b['title']];
        });

        $rated = array_values(array_filter($courses, function ($row) { return $row['registered'] > 0; }));
        $totals = $this->figures(array_sum(array_column($courses, 'registered')), array_sum(array_column($courses, 'completed')));
        $totals['courses'] = count($rated);

        return [
            'semester' => (string) optional($semester)->name,
            'intake' => $this->intakeLabel((string) optional($semester)->name),
            'courses' => $courses,
            'totals' => $totals,
            'small_note' => $this->smallCohortNote($rated),
        ];
    }

    /** Several intakes, each worked out on its own as build() does, in intake order, plus totals. */
    public function buildMany(array $semesterIds): array
    {
        $intakes = [];
        foreach ($this->orderSemesters($semesterIds) as $id) {
            $intakes[] = ['id' => $id] + $this->build($id);
        }
        $rated = [];
        foreach ($intakes as $intake) {
            foreach ($intake['courses'] as $course) {
                if ($course['registered'] > 0) {
                    $rated[] = $course;
                }
            }
        }
        $totals = $this->figures(
            array_sum(array_map(function ($intake) { return $intake['totals']['registered']; }, $intakes)),
            array_sum(array_map(function ($intake) { return $intake['totals']['completed']; }, $intakes))
        );
        $totals['courses'] = count($rated);
        $totals['intakes'] = count(array_filter($intakes, function ($intake) { return $intake['totals']['registered'] > 0; }));
        $names = array_column($intakes, 'semester');

        return [
            'intakes' => $intakes,
            'many' => (count($intakes) > 1),
            'semester' => implode(', ', $names),
            'intake' => $this->intakesLabel($names),
            'file' => $this->intakesFileStem($names),
            'totals' => $totals,
            'has_courses' => (bool) array_filter($intakes, function ($intake) { return !empty($intake['courses']); }),
            'small_note' => $this->smallCohortNote($rated),
        ];
    }

    protected function figures(int $registered, int $completed): array
    {
        $exact = ($registered > 0 ? $completed / $registered * 100 : null);
        $rate = ($exact !== null ? round($exact, 1) : null);

        return [
            'registered' => $registered,
            'completed' => $completed,
            'not_completed' => $registered - $completed,
            'rate' => $rate,
            'exact' => $exact,
            'low' => ($rate !== null && $rate < self::LOW_RATE),
            'small' => ($registered > 0 && $registered < self::SMALL_COHORT),
        ];
    }

    /**
     * The design's footnote: "Small cohort = fewer than 30 registered students;
     * with 22 registered, one student changes the rate by 4.5 percentage points."
     */
    protected function smallCohortNote(array $rated): ?string
    {
        $small = array_values(array_filter($rated, function ($row) { return $row['small']; }));
        if (empty($small)) {
            return null;
        }
        $swing = function ($registered) {
            return rtrim(rtrim(number_format(100 / $registered, 1), '0'), '.');
        };
        $lead = 'Small cohort = fewer than '.self::SMALL_COHORT.' registered students; ';
        if (count($small) === 1) {
            return $lead.'with '.$small[0]['registered'].' registered, one student changes the rate by '.$swing($small[0]['registered']).' percentage points.';
        }
        $sizes = array_column($small, 'registered');
        $low = $swing(max($sizes));
        $high = $swing(min($sizes));

        return $lead.'one student changes these rates by '.($low === $high ? $low : $low.' to '.$high).' percentage points.';
    }
}
