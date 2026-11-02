<?php

namespace App\Services\SlcRegistration;

/**
 * The SIS "Attendance Confirmation Report" (the user's sample of 2 Nov 2026): a header line —
 * HEI code ~ HEI name ~ report date (ddmmyyyy) ~ number of students ~ three amount totals — then one
 * "~"-delimited line of 21 fields per student. The header totals are the sums of fields 11–13.
 *
 * Fields whose meaning the sample makes plain are named; the others keep a neutral name until it is
 * confirmed (field 21 is empty in every line: the attendance code SIS expects back, as field 29 is for
 * registration). Each line is kept as sent, for the confirmation file to come.
 */
class AttendanceFile
{
    public const FIELDS = [
        'term', 'ssn', 'surname', 'forename', 'dob', 'f6', 'slcCode', 'courseName', 'year',
        'fee', 'tfl', 'f12', 'f13', 'mode', 'f15', 'newStudent', 'f17', 'f18', 'f19', 'f20', 'attendanceCode',
    ];

    public const CODE_INDEX = 20; // field 21

    public const TERMS = ['AUT' => 'Autumn', 'SPR' => 'Spring', 'SUM' => 'Summer'];

    /** @return array{header: ?array, rows: array, errors: string[]} */
    public static function parse(string $text): array
    {
        $text = (string) preg_replace('/^\xEF\xBB\xBF/', '', $text);
        $lines = array_values(array_filter(preg_split('/\r?\n/', $text), fn ($l) => trim($l) !== ''));
        $header = null;
        $rows = [];
        $errors = [];
        foreach ($lines as $i => $line) {
            $parts = explode('~', rtrim($line, "\r"));
            if (!$header && count($parts) <= 8 && isset($parts[2]) && preg_match('/^\d{8}$/', trim($parts[2]))) {
                $header = self::header($parts, rtrim($line, "\r"));
                continue;
            }
            if (count($parts) < 9) {
                $errors[] = 'Line '.($i + 1).' has '.count($parts).' fields (expected 21)';
                continue;
            }
            $row = [];
            foreach (self::FIELDS as $j => $f) {
                $row[$f] = trim((string) preg_replace('/\s+/', ' ', $parts[$j] ?? ''));
            }
            $row['line'] = $i + 1;
            $row['raw'] = rtrim($line, "\r");
            $rows[] = $row;
        }

        return ['header' => $header, 'rows' => $rows, 'errors' => $errors];
    }

    /** LCHC~LONDON CHURCHILL COLLEGE~02112026~55~311150.00~0.00~38100.00 */
    public static function header(array $p, string $raw): array
    {
        return [
            'raw' => $raw, 'hei' => trim($p[0] ?? ''), 'heiName' => trim($p[1] ?? ''), 'date' => trim($p[2] ?? ''),
            'count' => (int) ($p[3] ?? 0), 'totals' => array_map(fn ($v) => (float) $v, array_slice($p, 4, 3)),
        ];
    }

    /**
     * The SIS attendance import file for a batch: the header line recounted for these lines (count and
     * the totals of fields 11–13, date = today) and each student's line as sent with field 21 set to the
     * confirmed code. The header's recount is an assumption, flagged on the page until checked against
     * SLC's import specification.
     *
     * @param array $lines [raw line => code]
     */
    public static function build(string $headerRaw, array $lines, \DateTimeInterface $date): string
    {
        $h = explode('~', $headerRaw);
        $out = [];
        $totals = [0.0, 0.0, 0.0];
        foreach ($lines as $raw => $code) {
            $p = explode('~', $raw);
            while (count($p) < count(self::FIELDS)) {
                $p[] = '';
            }
            $p[self::CODE_INDEX] = $code;
            foreach ([10, 11, 12] as $k => $i) {
                $totals[$k] += (float) ($p[$i] ?? 0);
            }
            $out[] = implode('~', $p);
        }
        $header = implode('~', [$h[0] ?? '', $h[1] ?? '', $date->format('dmY'), count($out), number_format($totals[0], 2, '.', ''), number_format($totals[1], 2, '.', ''), number_format($totals[2], 2, '.', '')]);

        return implode("\r\n", array_merge([$header], $out))."\r\n";
    }

    public static function fileName(string $hei, string $term, string $batchCode): string
    {
        return 'ATT_'.$hei.'_'.$term.'_'.$batchCode.'.txt';
    }

    public static function termName(string $term): string
    {
        return self::TERMS[strtoupper($term)] ?? $term;
    }
}
