<?php

namespace App\Services\Reports;

use App\Models\Assign;
use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use Illuminate\Support\Facades\DB;

/**
 * Attendance Rate for one intake (Reports › Intake Performance, new version).
 *
 * Same rules as the old report (AttendanceRateReportController::refineResult):
 * per course of the intake (latest creation per course), the registered
 * students' attendance marks on every class plan they are assigned to,
 * grouped by term. Rate = (marks - Absent) / marks. A term's "registered" is
 * the course's registered students less those given a drop-out status since
 * the previous term began.
 *
 * Terms go into columns by when they start: January-April, May-August,
 * September-December (so a June term sits under May). Terms before the
 * intake's first column are "earlier terms": they count in a course's
 * Overall rate and are listed in the detail, as in the old report.
 */
class AttendanceRate
{
    use Concerns\NamesIntakeCourses;

    /** attendance_feed_status_id => code, in the order the detail lists them. */
    const CODES = [1 => 'P', 2 => 'O', 5 => 'L', 3 => 'LE', 6 => 'E', 7 => 'M', 8 => 'H', 4 => 'A'];
    const DROPPED_OUT_STATUSES = [22, 27, 30, 31, 42, 43, 45, 14, 17, 33, 36, 47, 50];
    const SEASONS = [['Jan', 'January'], ['May', 'May'], ['Sep', 'September']];
    /** Course rates below this are shown in orange (the design: 62.1% orange, 66.4% teal). */
    const LOW_RATE = 65;

    public function build(int $semesterId): array
    {
        $semester = Semester::find($semesterId);
        $courses = [];
        $starts = [];
        $creations = CourseCreation::with(['course', 'available'])->where('semester_id', $semesterId)->orderBy('id', 'DESC')->get()->unique('course_id');
        foreach ($creations as $creation) {
            $students = StudentCourseRelation::where('course_creation_id', $creation->id)->where('active', 1)->pluck('student_id')->unique()->values()->all();
            if (empty($students)) {
                continue; // as the old report: a course nobody started is left out
            }
            $registered = StudentAwardingBodyDetails::whereIn('student_id', $students)->whereNotNull('reference')
                ->whereHas('studentcrel', function ($q) use ($creation) {
                    $q->where('course_creation_id', $creation->id);
                })->pluck('student_id')->unique()->values()->all();
            $start = optional($creation->available)->course_start_date;
            $start = (!empty($start) ? date('Y-m-d', strtotime($start)) : null);
            if ($start) {
                $starts[] = $start;
            }

            $courses[] = $this->courseNames((string) optional($creation->course)->name) + [
                'registered' => count($registered),
                'terms' => $this->terms($registered, $start),
            ];
        }

        $firstSlot = (!empty($starts) ? $this->slotKey(min($starts)) : null);
        return $this->summarise((string) optional($semester)->name, $courses, $firstSlot);
    }

    /**
     * Several intakes, each worked out on its own as build() does, in intake
     * order. The term columns are every intake's columns together (calendar
     * terms: an intake shows "No term" where it has none), and the totals add
     * the intakes' sessions up per column.
     */
    public function buildMany(array $semesterIds): array
    {
        $intakes = [];
        foreach ($this->orderSemesters($semesterIds) as $id) {
            $intakes[] = ['id' => $id] + $this->build($id);
        }

        $blankCodes = array_fill_keys(array_values(self::CODES), 0);
        $months = [];
        $totals = ['registered' => 0, 'codes' => $blankCodes, 'total' => 0, 'cells' => []];
        $early = ['marks' => 0, 'terms' => 0, 'years' => []];
        foreach ($intakes as $intake) {
            foreach ($intake['slot_months'] as $key => $list) {
                foreach ($list as $month) {
                    $months[$key][$month] = true;
                }
            }
            $t = $intake['totals'];
            $totals['registered'] += $t['registered'];
            $totals['total'] += $t['total'];
            foreach ($t['codes'] as $code => $n) {
                $totals['codes'][$code] += $n;
            }
            foreach ($t['cells'] as $slot => $cell) {
                $totals['cells'][$slot]['total'] = ($totals['cells'][$slot]['total'] ?? 0) + $cell['total'];
                $totals['cells'][$slot]['absent'] = ($totals['cells'][$slot]['absent'] ?? 0) + $cell['absent'];
            }
            $early['marks'] += $intake['early']['marks'];
            $early['terms'] += $intake['early']['terms'];
            foreach ($intake['early']['years'] as $year) {
                $early['years'][$year] = true;
            }
        }
        ksort($months);
        $slots = [];
        foreach ($months as $key => $list) {
            $slots[$key] = $this->slotLabel($key, array_keys($list));
        }
        ksort($totals['cells']);
        $first = null;
        foreach ($totals['cells'] as &$cell) {
            $cell += $this->rateOf($cell['total'], $cell['absent']);
            $first = ($first === null && $cell['exact'] !== null ? $cell['exact'] : $first);
            $cell['vs_first'] = ($cell['exact'] !== null && $first !== null ? round($cell['exact'] - $first, 1) : null);
        }
        unset($cell);
        $totals += $this->rateOf($totals['total'], $totals['codes']['A']);
        $totals['present'] = $totals['total'] - $totals['codes']['A'];
        $years = array_keys($early['years']);
        sort($years);
        $early['years'] = $years;
        $names = array_column($intakes, 'semester');

        return [
            'intakes' => $intakes,
            'many' => (count($intakes) > 1),
            'semester' => implode(', ', $names),
            'intake' => $this->intakesLabel($names),
            'file' => $this->intakesFileStem($names),
            'slots' => $slots,
            'totals' => $totals,
            'early' => $early,
            'trend' => $this->trend($totals['cells'], $slots),
            'has_courses' => (bool) array_filter($intakes, function ($intake) { return !empty($intake['courses']); }),
            'has_marks' => ($totals['total'] > 0),
        ];
    }

    /** The registered students' marks per term, with each term's registered count. */
    protected function terms(array $registered, ?string $courseStart): array
    {
        if (empty($registered)) {
            return [];
        }
        $plans = Assign::whereIn('student_id', $registered)->pluck('plan_id')->unique()->values()->all();
        if (empty($plans)) {
            return [];
        }
        $select = ['trm.id as term_id', 'trm.name as term_name', 'trm.start_date', DB::raw('COUNT(atn.attendance_feed_status_id) as total')];
        foreach (self::CODES as $id => $code) {
            $select[] = DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = '.$id.' THEN 1 ELSE 0 END) as c_'.$code);
        }
        $rows = DB::table('attendances as atn')
            ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
            ->leftJoin('term_declarations as trm', 'pln.term_declaration_id', 'trm.id')
            ->whereIn('atn.plan_id', $plans)
            ->whereIn('atn.student_id', $registered)
            ->whereNull('atn.deleted_at')
            ->groupBy('pln.term_declaration_id', 'trm.id', 'trm.name', 'trm.start_date')
            ->orderBy('pln.term_declaration_id', 'ASC')
            ->select($select)->get();

        // Drop-outs between term starts, as get_last_term_dropped_out_students did (both ends inclusive).
        $changes = StudentAttendanceTermStatus::whereIn('student_id', $registered)->whereIn('status_id', self::DROPPED_OUT_STATUSES)
            ->whereNotNull('status_change_date')->get(['student_id', 'status_change_date'])
            ->map(function ($row) { return [(int) $row->student_id, date('Y-m-d', strtotime($row->status_change_date))]; })->all();
        $droppedBetween = function ($from, $to) use ($changes) {
            $from = date('Y-m-d', strtotime((string) $from));
            $to = date('Y-m-d', strtotime((string) $to));
            $ids = [];
            foreach ($changes as [$id, $date]) {
                if ($date >= $from && $date <= $to) {
                    $ids[$id] = true;
                }
            }
            return count($ids);
        };

        $terms = [];
        $left = 0;
        $previousStart = $courseStart;
        foreach ($rows as $i => $row) {
            if ($i > 0) {
                $left += $droppedBetween($previousStart, $row->start_date);
            }
            $codes = [];
            foreach (self::CODES as $code) {
                $codes[$code] = (int) $row->{'c_'.$code};
            }
            $terms[] = [
                'id' => (int) $row->term_id,
                'name' => preg_replace('/\s*\(/', ' (', trim((string) $row->term_name)),
                'start' => ($row->start_date ? date('Y-m-d', strtotime($row->start_date)) : null),
                'slot' => ($row->start_date ? $this->slotKey($row->start_date) : null),
                'registered' => count($registered) - $left,
                'codes' => $codes,
                'total' => (int) $row->total,
            ] + $this->rateOf((int) $row->total, $codes['A']);
            $previousStart = $row->start_date;
        }

        return $terms;
    }

    protected function summarise(string $semesterName, array $courses, ?int $firstSlot): array
    {
        $blankCodes = array_fill_keys(array_values(self::CODES), 0);
        $slots = [];
        $months = [];
        $early = ['marks' => 0, 'terms' => 0, 'years' => []];

        foreach ($courses as &$course) {
            $course['codes'] = $blankCodes;
            $course['total'] = 0;
            $course['cells'] = [];
            foreach ($course['terms'] as &$term) {
                foreach ($term['codes'] as $code => $n) {
                    $course['codes'][$code] += $n;
                }
                $course['total'] += $term['total'];
                $term['early'] = ($firstSlot !== null && $term['slot'] !== null && $term['slot'] < $firstSlot);
                if ($term['early']) {
                    $early['marks'] += $term['total'];
                    $early['terms']++;
                    $early['years'][(int) substr((string) $term['start'], 0, 4)] = true;
                    continue;
                }
                if ($term['slot'] === null) {
                    continue;
                }
                $slots[$term['slot']] = true;
                if ($term['start']) {
                    $months[$term['slot']][(int) date('n', strtotime($term['start']))] = true;
                }
                $cell = $course['cells'][$term['slot']] ?? ['total' => 0, 'absent' => 0];
                $cell['total'] += $term['total'];
                $cell['absent'] += $term['codes']['A'];
                $course['cells'][$term['slot']] = $cell;
            }
            unset($term);
            $course += $this->rateOf($course['total'], $course['codes']['A']);
            foreach ($course['cells'] as &$cell) {
                $cell += $this->rateOf($cell['total'], $cell['absent']);
            }
            unset($cell);
            ksort($course['cells']);
            $course['low'] = ($course['rate'] !== null && $course['rate'] < self::LOW_RATE);
        }
        unset($course);

        // Lowest overall rate first; courses with no marks at the end.
        usort($courses, function ($a, $b) {
            return [$a['rate'] === null ? 1 : 0, $a['exact'] ?? 0, $a['title']] <=> [$b['rate'] === null ? 1 : 0, $b['exact'] ?? 0, $b['title']];
        });

        ksort($slots);
        $slotList = [];
        foreach (array_keys($slots) as $key) {
            $slotList[$key] = $this->slotLabel($key, array_keys($months[$key] ?? []));
        }

        $totals = ['registered' => 0, 'codes' => $blankCodes, 'total' => 0, 'cells' => []];
        foreach ($courses as $course) {
            $totals['registered'] += $course['registered'];
            $totals['total'] += $course['total'];
            foreach ($course['codes'] as $code => $n) {
                $totals['codes'][$code] += $n;
            }
            foreach ($course['cells'] as $slot => $cell) {
                $totals['cells'][$slot]['total'] = ($totals['cells'][$slot]['total'] ?? 0) + $cell['total'];
                $totals['cells'][$slot]['absent'] = ($totals['cells'][$slot]['absent'] ?? 0) + $cell['absent'];
            }
        }
        ksort($totals['cells']);
        $first = null;
        foreach ($totals['cells'] as &$cell) {
            $cell += $this->rateOf($cell['total'], $cell['absent']);
            // Points up or down on the intake's first term, for the term cards.
            $first = ($first === null && $cell['exact'] !== null ? $cell['exact'] : $first);
            $cell['vs_first'] = ($cell['exact'] !== null && $first !== null ? round($cell['exact'] - $first, 1) : null);
        }
        unset($cell);
        $totals += $this->rateOf($totals['total'], $totals['codes']['A']);
        $totals['present'] = $totals['total'] - $totals['codes']['A'];

        $years = array_keys($early['years']);
        sort($years);
        $early['years'] = $years;

        return [
            'semester' => $semesterName,
            'intake' => $this->intakeLabel($semesterName),
            'slots' => $slotList,
            'slot_months' => array_map('array_keys', $months),
            'courses' => $courses,
            'totals' => $totals,
            'early' => $early,
            'trend' => $this->trend($totals['cells'], $slotList),
            'has_marks' => ($totals['total'] > 0),
        ];
    }

    /** Rate as shown (1 decimal) and exact (for sorting and change). */
    protected function rateOf(int $total, int $absent): array
    {
        if ($total < 1) {
            return ['rate' => null, 'exact' => null];
        }
        $exact = ($total - $absent) / $total * 100;

        return ['rate' => round($exact, 1), 'exact' => $exact];
    }

    /** First and latest all-courses term rates, for the headline sentence and card. */
    protected function trend(array $cells, array $slots): ?array
    {
        $rated = array_filter($cells, function ($cell) { return $cell['exact'] !== null; });
        if (empty($rated)) {
            return null;
        }
        ksort($rated);
        $firstKey = array_key_first($rated);
        $lastKey = array_key_last($rated);

        return [
            'first_rate' => $rated[$firstKey]['rate'],
            'first_month' => self::SEASONS[$firstKey % 10][1],
            'latest_rate' => $rated[$lastKey]['rate'],
            'latest_label' => $slots[$lastKey]['short'] ?? '',
            'latest_month' => self::SEASONS[$lastKey % 10][1],
            'single' => ($firstKey === $lastKey),
            'change' => round($rated[$lastKey]['exact'] - $rated[$firstKey]['exact'], 1),
        ];
    }

    /** Column of a date: year × 10 + 0 (Jan-Apr), 1 (May-Aug) or 2 (Sep-Dec). */
    protected function slotKey(string $date): int
    {
        $month = (int) date('n', strtotime($date));

        return (int) date('Y', strtotime($date)) * 10 + ($month <= 4 ? 0 : ($month <= 8 ? 1 : 2));
    }

    /**
     * Column labels: "Jan 2026 term" (cards), "Jan 2026" (chart), and the
     * table heading naming every month the column's terms start in, so a
     * June term under May reads "May / June 2026".
     */
    protected function slotLabel(int $key, array $months = []): array
    {
        $year = intdiv($key, 10);
        [$short, $month] = self::SEASONS[$key % 10];
        sort($months);
        $names = array_map(function ($n) {
            $full = date('F', mktime(0, 0, 0, $n, 1));
            return (strlen($full) <= 5 ? $full : date('M', mktime(0, 0, 0, $n, 1)));
        }, $months);

        return [
            'label' => $short.' '.$year.' term',
            'short' => $short.' '.$year,
            'heading' => (count($names) > 1 ? implode(' / ', $names).' '.$year : $short.' '.$year),
            'month' => $month,
        ];
    }
}
