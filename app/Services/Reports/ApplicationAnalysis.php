<?php

namespace App\Services\Reports;

use App\Models\CourseCreation;
use App\Models\CourseCreationVenue;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

/**
 * Admission Report (/reports/applicant-analysis) for one intake semester:
 * accepted against target, where the applications stand, the split by
 * delivery pattern, and who accepted (gender, entry route, nationality, age).
 *
 * Applications are applicant_proposed_courses rows of the semester's course
 * creations whose applicant has left "Submitting" (status > 1). Statuses fall
 * into five groups: accepted (Offer Accepted), rejected by the college,
 * declined by the applicant (Offer Rejected), canceled, and pending (every
 * other status, still in the pipeline).
 */
class ApplicationAnalysis
{
    const SUBMITTING = 1;
    const ACCEPTED = [7];
    const REJECTED = [8];
    const DECLINED = [9];
    const CANCELED = [46];

    public function build(int $semesterId): array
    {
        $semester = Semester::find($semesterId);
        $creations = CourseCreation::with('course')->where('semester_id', $semesterId)->get();
        $applications = $this->applications($semesterId, $creations->pluck('id')->all());
        $statusNames = DB::table('statuses')->pluck('name', 'id')->all();

        $courses = $this->courses($creations, $applications);
        $totals = $this->totals($courses, $applications);

        return [
            'semester' => (string) optional($semester)->name,
            'intake' => $this->intakeLabel((string) optional($semester)->name),
            'has_data' => ($totals['target'] > 0 || $totals['applied'] > 0),
            'totals' => $totals,
            'pending_parts' => $this->pendingParts($applications, $statusNames),
            'summary' => $this->summary($courses, $totals),
            'courses' => $this->courseRows($courses),
            'delivery' => $this->deliveryRows($courses),
            'cohort' => $this->cohort($courses, $applications),
        ];
    }

    /**
     * One row per application, with what the report needs about the applicant.
     * Subqueries rather than joins for the one-to-many details, so an applicant
     * with two detail rows is still one application.
     */
    protected function applications(int $semesterId, array $creationIds): array
    {
        if (empty($creationIds)) {
            return [];
        }

        $rows = DB::table('applicant_proposed_courses as apc')
            ->join('applicants as ap', 'ap.id', '=', 'apc.applicant_id')
            ->leftJoin('countries as ct', 'ct.id', '=', 'ap.nationality_id')
            ->whereIn('apc.course_creation_id', $creationIds)
            ->where('apc.semester_id', $semesterId)
            ->where('ap.status_id', '>', self::SUBMITTING)
            ->whereNull('apc.deleted_at')
            ->whereNull('ap.deleted_at')
            ->select(
                'apc.course_creation_id', 'apc.full_time', 'ap.id as applicant_id', 'ap.status_id',
                'ap.sex_identifier_id', 'ap.nationality_id', 'ct.name as nationality',
                DB::raw('TIMESTAMPDIFF(YEAR, ap.date_of_birth, CURDATE()) as age'),
                DB::raw('EXISTS(SELECT 1 FROM applicant_other_details o WHERE o.applicant_id = ap.id AND o.deleted_at IS NULL AND o.is_edication_qualification = 1) as has_qualification'),
                DB::raw('EXISTS(SELECT 1 FROM applicant_other_details o WHERE o.applicant_id = ap.id AND o.deleted_at IS NULL AND o.employment_status IS NOT NULL) as has_employment_status'),
                DB::raw('EXISTS(SELECT 1 FROM applicant_employments e WHERE e.applicant_id = ap.id AND e.deleted_at IS NULL) as has_employment')
            )
            ->get();

        return $rows->map(function ($row) {
            $row->group = $this->statusGroup((int) $row->status_id);

            return $row;
        })->all();
    }

    protected function statusGroup(int $statusId): string
    {
        if (in_array($statusId, self::ACCEPTED, true)) {
            return 'accepted';
        }
        if (in_array($statusId, self::REJECTED, true)) {
            return 'rejected';
        }
        if (in_array($statusId, self::DECLINED, true)) {
            return 'declined';
        }
        if (in_array($statusId, self::CANCELED, true)) {
            return 'canceled';
        }

        return 'pending';
    }

    /**
     * Courses of the intake with their targets and applications. A course run
     * under more than one creation in the same semester is added up.
     */
    protected function courses($creations, array $applications): array
    {
        $courses = [];
        foreach ($creations as $creation) {
            $key = (int) $creation->course_id;
            if (!isset($courses[$key])) {
                $names = $this->courseNames((string) optional($creation->course)->name);
                $courses[$key] = [
                    'name' => $names['name'],
                    'subject' => $names['subject'],
                    'venues' => [],
                    'creation_ids' => [],
                    'target' => ['weekdays' => 0, 'weekends' => 0],
                    'apps' => [],
                ];
            }
            $courses[$key]['creation_ids'][] = (int) $creation->id;

            foreach (CourseCreationVenue::with('venue')->where('course_creation_id', $creation->id)->get() as $venue) {
                $courses[$key]['target']['weekdays'] += max(0, (int) $venue->weekdays);
                $courses[$key]['target']['weekends'] += max(0, (int) $venue->weekends);
                $venueName = ucwords(trim((string) optional($venue->venue)->name));
                if ($venueName !== '' && !in_array($venueName, $courses[$key]['venues'], true)) {
                    $courses[$key]['venues'][] = $venueName;
                }
            }
        }

        $byCreation = [];
        foreach ($courses as $key => $course) {
            foreach ($course['creation_ids'] as $creationId) {
                $byCreation[$creationId] = $key;
            }
        }
        foreach ($applications as $app) {
            if (isset($byCreation[(int) $app->course_creation_id])) {
                $courses[$byCreation[(int) $app->course_creation_id]]['apps'][] = $app;
            }
        }

        // A course with neither a target nor an application has nothing to report.
        return array_values(array_filter($courses, function ($course) {
            return ($course['target']['weekdays'] + $course['target']['weekends']) > 0 || !empty($course['apps']);
        }));
    }

    /** Applied / accepted / pending / rejected / declined / canceled for a set of applications. */
    protected function counts(array $apps): array
    {
        $counts = ['applied' => count($apps), 'accepted' => 0, 'pending' => 0, 'rejected' => 0, 'declined' => 0, 'canceled' => 0];
        foreach ($apps as $app) {
            $counts[$app->group]++;
        }

        return $counts;
    }

    protected function totals(array $courses, array $applications): array
    {
        $target = 0;
        foreach ($courses as $course) {
            $target += $course['target']['weekdays'] + $course['target']['weekends'];
        }
        $counts = $this->counts($applications);

        return $counts + [
            'target' => $target,
            'gap' => $counts['accepted'] - $target,
            'of_target' => ($target > 0 ? (int) round($counts['accepted'] / $target * 100) : null),
            'accepted_share' => ($counts['applied'] > 0 ? (int) round($counts['accepted'] / $counts['applied'] * 100) : 0),
        ];
    }

    /** "6 new · 15 in progress": the statuses inside Pending, in status order. */
    protected function pendingParts(array $applications, array $statusNames): array
    {
        $byStatus = [];
        foreach ($applications as $app) {
            if ($app->group === 'pending') {
                $byStatus[(int) $app->status_id] = ($byStatus[(int) $app->status_id] ?? 0) + 1;
            }
        }
        ksort($byStatus);

        $parts = [];
        foreach ($byStatus as $statusId => $count) {
            $label = strtolower(trim((string) ($statusNames[$statusId] ?? 'other')));
            $parts[] = $count.' '.str_replace('awating', 'awaiting', $label);
        }

        return $parts;
    }

    /** Target-vs-accepted rows for the course bars, on one shared scale. */
    protected function courseRows(array $courses): array
    {
        $rows = [];
        $largest = 0;
        foreach ($courses as $course) {
            $counts = $this->counts($course['apps']);
            $target = $course['target']['weekdays'] + $course['target']['weekends'];
            $gap = $counts['accepted'] - $target;
            $largest = max($largest, $target, $counts['accepted']);

            $rows[] = [
                'name' => $course['name'],
                'subject' => $course['subject'],
                'venues' => implode(' · ', $course['venues']),
                'target' => $target,
                'accepted' => $counts['accepted'],
                'pending' => $counts['pending'],
                'gap' => $gap,
                'note' => $this->courseNote($target, $gap, $counts['pending']),
                'note_warn' => ($gap < 0 && $counts['pending'] < abs($gap)),
            ];
        }

        $scale = $this->niceCeiling($largest * 1.05);
        foreach ($rows as &$row) {
            $row['fill'] = round(min(100, $row['accepted'] / $scale * 100), 1);
            $row['tick'] = round(min(100, $row['target'] / $scale * 100), 1);
        }
        unset($row);

        return $rows;
    }

    protected function courseNote(int $target, int $gap, int $pending): string
    {
        $stillPending = ($pending > 0 ? $pending.' still pending' : 'Nothing pending');
        if ($target < 1) {
            return 'No target set. '.$stillPending.'.';
        }
        if ($gap > 0) {
            return 'Over target. '.$stillPending.'.';
        }
        if ($gap === 0) {
            return 'On target. '.$stillPending.'.';
        }
        if ($pending < 1) {
            return abs($gap).' short. Nothing pending.';
        }

        return abs($gap).' short. '.$stillPending.', '.($pending >= abs($gap) ? 'enough' : 'not enough').' to close the gap.';
    }

    /**
     * The sentence under the headline figure: is the overall target met, which
     * courses carry it, and which course is furthest behind.
     */
    protected function summary(array $courses, array $totals): string
    {
        if ($totals['target'] < 1) {
            return 'No targets are set for this intake.';
        }

        $over = [];
        $under = [];
        foreach ($courses as $course) {
            $target = $course['target']['weekdays'] + $course['target']['weekends'];
            if ($target < 1) {
                continue;
            }
            $accepted = $this->counts($course['apps'])['accepted'];
            if ($accepted > $target) {
                $over[] = $course['subject'];
            } elseif ($accepted < $target) {
                $under[] = ['subject' => $course['subject'], 'pct' => (int) round($accepted / $target * 100)];
            }
        }
        usort($under, function ($a, $b) {
            return $a['pct'] <=> $b['pct'];
        });
        $weakest = (!empty($under) ? ' '.$under[0]['subject'].' is at '.$under[0]['pct'].'%.' : '');
        $overText = $this->joinNames($over).(count($over) === 1 ? ' is' : ' are').' over';

        if ($totals['gap'] >= 0) {
            if (empty($under)) {
                return 'Overall target met, and every course is on or over target.';
            }

            return 'Overall target met, but only '.$overText.'.'.$weakest;
        }

        $text = abs($totals['gap']).' short of the overall target, '.($totals['pending'] > 0 ? 'with '.$totals['pending'].' still pending.' : 'with nothing pending.');
        if (!empty($over)) {
            $text .= ' '.$overText.'.';
        }

        return $text.$weakest;
    }

    protected function joinNames(array $names): string
    {
        if (count($names) < 2) {
            return implode('', $names);
        }
        $last = array_pop($names);

        return implode(', ', $names).' and '.$last;
    }

    /** One row per course and delivery pattern that has a target or an application. */
    protected function deliveryRows(array $courses): array
    {
        $rows = [];
        foreach ($courses as $course) {
            foreach (['weekdays' => [0, 'Weekdays'], 'weekends' => [1, 'Evening / weekend']] as $key => [$fullTime, $label]) {
                $apps = array_values(array_filter($course['apps'], function ($app) use ($fullTime) {
                    return (int) $app->full_time === $fullTime;
                }));
                $target = $course['target'][$key];
                if ($target < 1 && empty($apps)) {
                    continue;
                }
                $counts = $this->counts($apps);
                $rows[] = ['label' => $course['subject'].' · '.$label, 'target' => $target, 'gap' => $counts['accepted'] - $target] + $counts;
            }
        }

        return $rows;
    }

    /**
     * Who accepted: everyone with an accepted application, counted once even
     * if they accepted on two courses.
     */
    protected function cohort(array $courses, array $applications): array
    {
        $people = [];
        foreach ($applications as $app) {
            if ($app->group === 'accepted') {
                $people[(int) $app->applicant_id] = $app;
            }
        }
        $total = count($people);

        return [
            'total' => $total,
            'gender' => $this->gender($people, $total),
            'entry' => $this->entryRoutes($courses),
            'nationality' => $this->nationality($people, $total),
            'age' => $this->age($people, $total),
        ];
    }

    protected function gender(array $people, int $total): array
    {
        $counts = ['male' => 0, 'female' => 0, 'other' => 0, 'unknown' => 0];
        foreach ($people as $person) {
            $sex = (int) $person->sex_identifier_id;
            $counts[$sex === 2 ? 'male' : ($sex === 1 ? 'female' : ($sex === 3 ? 'other' : 'unknown'))]++;
        }

        $caption = [];
        if ($total > 0) {
            $caption[] = $this->pct($counts['male'], $total).'% male';
            $caption[] = $this->pct($counts['female'], $total).'% female';
            $caption[] = ($counts['other'] > 0 ? $this->pct($counts['other'], $total).'% other' : 'no other recorded');
            if ($counts['unknown'] > 0) {
                $caption[] = $counts['unknown'].' not recorded';
            }
        }

        return $counts + ['caption' => implode(' · ', $caption)];
    }

    /**
     * Each accepted student is counted once: Academic when they declared an
     * educational qualification, otherwise Mature when they gave an employment
     * status and at least one job, otherwise Unknown. (The old report counted
     * an applicant with both under both columns, so the two did not add up.)
     */
    protected function entryRoutes(array $courses): array
    {
        $rows = [];
        foreach ($courses as $course) {
            $row = ['subject' => $course['subject'], 'mature' => 0, 'academic' => 0, 'unknown' => 0, 'unknown_ids' => []];
            $seen = [];
            foreach ($course['apps'] as $app) {
                if ($app->group !== 'accepted' || isset($seen[(int) $app->applicant_id])) {
                    continue;
                }
                $seen[(int) $app->applicant_id] = true;
                if ($app->has_qualification) {
                    $row['academic']++;
                } elseif ($app->has_employment_status && $app->has_employment) {
                    $row['mature']++;
                } else {
                    $row['unknown']++;
                    $row['unknown_ids'][] = (int) $app->applicant_id;
                }
            }
            if (!empty($seen)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /** Top five countries, then the rest as "Other (N countries)", then "Not recorded". */
    protected function nationality(array $people, int $total): array
    {
        $byCountry = [];
        $unknown = 0;
        foreach ($people as $person) {
            $name = trim((string) $person->nationality);
            if ($name === '') {
                $unknown++;
                continue;
            }
            $byCountry[$name] = ($byCountry[$name] ?? 0) + 1;
        }
        uksort($byCountry, function ($a, $b) use ($byCountry) {
            return [$byCountry[$b], $a] <=> [$byCountry[$a], $b];
        });

        $countries = count($byCountry);
        $grouped = ($countries > 6);
        $rows = [];
        foreach (($grouped ? array_slice($byCountry, 0, 5, true) : $byCountry) as $name => $count) {
            $rows[] = $this->shareRow($name, $count, $total);
        }
        if ($grouped) {
            $rest = array_slice($byCountry, 5, null, true);
            $rows[] = $this->shareRow('Other ('.count($rest).' countries)', array_sum($rest), $total, true);
        }
        if ($unknown > 0) {
            $rows[] = $this->shareRow('Not recorded', $unknown, $total, true);
        }

        return [
            'meta' => $countries.' '.($countries === 1 ? 'country' : 'countries').($grouped ? ' · top five shown' : ''),
            'rows' => $rows,
        ];
    }

    /** Age today in whole years, in bands that neither overlap nor leave gaps. */
    protected function age(array $people, int $total): array
    {
        $bands = ['Under 18' => 0, '18–20' => 0, '21–29' => 0, '30–39' => 0, '40–49' => 0, '50–59' => 0, '60+' => 0, 'Not recorded' => 0];
        $ages = [];
        foreach ($people as $person) {
            if ($person->age === null) {
                $bands['Not recorded']++;
                continue;
            }
            $age = (int) $person->age;
            $ages[] = $age;
            $band = ($age < 18 ? 'Under 18' : ($age <= 20 ? '18–20' : ($age <= 29 ? '21–29' : ($age <= 39 ? '30–39' : ($age <= 49 ? '40–49' : ($age <= 59 ? '50–59' : '60+'))))));
            $bands[$band]++;
        }

        $rows = [];
        foreach ($bands as $label => $count) {
            // The open-ended bands only appear when someone is in them.
            if ($count < 1 && in_array($label, ['Under 18', '60+', 'Not recorded'], true)) {
                continue;
            }
            $rows[] = $this->shareRow($label, $count, $total, $label === 'Not recorded');
        }

        return [
            'mean' => (!empty($ages) ? (int) round(array_sum($ages) / count($ages)) : null),
            'rows' => $rows,
        ];
    }

    protected function shareRow(string $label, int $count, int $total, bool $muted = false): array
    {
        return ['label' => $label, 'count' => $count, 'pct' => $this->pct($count, $total), 'muted' => $muted];
    }

    protected function pct(int $count, int $total): int
    {
        return ($total > 0 ? (int) round($count / $total * 100) : 0);
    }

    /**
     * "HND in Business (Entrepreneurship And Small Business Management)" →
     * name "HND Business", subject "Business". The bracketed pathway and the
     * "in" after the award are dropped, and "and" becomes "&".
     */
    protected function courseNames(string $full): array
    {
        $plain = trim(preg_replace('/\s+/', ' ', preg_replace('/\([^)]*\)/', ' ', $full)));
        $award = '';
        $subject = $plain;
        if (preg_match('/^(\S+)\s+in\s+(.+)$/i', $plain, $match)) {
            $award = $match[1];
            $subject = $match[2];
        }
        $subject = preg_replace('/\s+and\s+/i', ' & ', $subject);
        if ($subject === '') {
            $subject = ($plain !== '' ? $plain : 'Unnamed course');
        }

        return ['name' => trim($award.' '.$subject), 'subject' => $subject];
    }

    /** "2026 September" → "September 2026 intake". */
    protected function intakeLabel(string $semester): string
    {
        $semester = trim($semester);
        if ($semester === '') {
            return 'Intake';
        }
        if (preg_match('/^(\d{4})\s+(.+)$/', $semester, $match)) {
            return $match[2].' '.$match[1].' intake';
        }

        return $semester.' intake';
    }

    /** A round number at or above the value (1, 2, 2.5 or 5 × a power of ten), for the bar scale. */
    protected function niceCeiling(float $value): float
    {
        if ($value <= 0) {
            return 1;
        }
        $magnitude = 10 ** floor(log10($value));
        foreach ([1, 2, 2.5, 5, 10] as $step) {
            if ($step * $magnitude >= $value) {
                return $step * $magnitude;
            }
        }

        return 10 * $magnitude;
    }
}
