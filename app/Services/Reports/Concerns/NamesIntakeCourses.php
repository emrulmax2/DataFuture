<?php

namespace App\Services\Reports\Concerns;

/** Course and intake names as the intake performance reports print them. */
trait NamesIntakeCourses
{
    /**
     * "HND in Business (Entrepreneurship And Small Business Management)" →
     * title "HND Business", pathway "Entrepreneurship and Small Business
     * Management", name "HND Business (Entrepreneurship and Small Business Management)".
     */
    protected function courseNames(string $full): array
    {
        $full = trim(preg_replace('/\s+/', ' ', $full));
        $title = $full;
        $pathway = '';
        if (preg_match('/^(.*\S)\s*\(([^()]*)\)$/', $full, $match)) {
            $title = $match[1];
            $pathway = trim(str_replace(' And ', ' and ', $match[2]));
        }
        $title = preg_replace('/^(\S+)\s+in\s+/i', '$1 ', $title);
        if ($title === '') {
            $title = 'Unnamed course';
        }

        return ['title' => $title, 'pathway' => $pathway, 'name' => $title.($pathway !== '' ? ' ('.$pathway.')' : '')];
    }

    /** "2025 September" → "September 2025 intake". */
    protected function intakeLabel(string $semester): string
    {
        $name = $this->intakeName($semester);

        return ($name !== '' ? $name.' intake' : 'Intake');
    }

    /** "2025 September" → "September 2025". */
    protected function intakeName(string $semester): string
    {
        $semester = trim($semester);
        if (preg_match('/^(\d{4})\s+(.+)$/', $semester, $match)) {
            return $match[2].' '.$match[1];
        }

        return $semester;
    }

    /**
     * Header line for one or more intakes: "January 2025 intake",
     * "January 2025 and May 2025 intakes", "January 2025, May 2025 and
     * September 2025 intakes".
     */
    protected function intakesLabel(array $semesterNames): string
    {
        $names = array_values(array_filter(array_map([$this, 'intakeName'], $semesterNames), 'strlen'));
        if (count($names) < 2) {
            return $this->intakeLabel((string) ($semesterNames[0] ?? ''));
        }

        return implode(', ', array_slice($names, 0, -1)).' and '.end($names).' intakes';
    }

    /** File name part: "2025_January", "2025_January_2025_May", or "2025_January_and_3_more". */
    protected function intakesFileStem(array $semesterNames): string
    {
        $names = array_values(array_filter(array_map('trim', $semesterNames), 'strlen'));
        if (empty($names)) {
            return 'Intake';
        }
        $parts = (count($names) > 3 ? [$names[0], 'and', (count($names) - 1).' more'] : $names);

        return trim(preg_replace('/[^A-Za-z0-9]+/', '_', implode(' ', $parts)), '_');
    }

    /**
     * Semester ids in intake order: by the year and month in the name
     * ("2025 January", "2015 April HND"), then by id. Ids that are not
     * semesters are dropped; repeats count once.
     */
    protected function orderSemesters(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($id) { return $id > 0; })));
        if (empty($ids)) {
            return [];
        }
        $names = \App\Models\Semester::whereIn('id', $ids)->pluck('name', 'id')->all();
        $months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
        $key = function ($id) use ($names, $months) {
            $name = strtolower(trim((string) $names[$id]));
            $year = (preg_match('/\b(\d{4})\b/', $name, $m) ? (int) $m[1] : 9999);
            $month = 13;
            foreach ($months as $i => $month_name) {
                if (preg_match('/\b'.$month_name.'\b/', $name)) {
                    $month = $i + 1;
                    break;
                }
            }

            return [$year, $month, $id];
        };
        $found = array_values(array_filter($ids, function ($id) use ($names) { return array_key_exists($id, $names); }));
        usort($found, function ($a, $b) use ($key) { return $key($a) <=> $key($b); });

        return $found;
    }
}
