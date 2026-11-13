<?php

namespace App\Services\SlcRegistration;

/**
 * The SIS "Attendance Confirmation Report" and the Attendance Confirmation import file: one layout, SLC's
 * "Attendance and Student Information Import & Export Files" technical specification v9.0, Appendix B
 * (user, 13 Nov 2026). A "~"-delimited header of 7 columns —
 *   1 HEI code · 2 HEI name · 3 schedule issue date (DDMMYYYY) · 4 number of records ·
 *   5 total Tuition Fee Loan · 6 total Tuition Fee Grant · 7 total Tuition Fees Student Pays
 * — then one line of 21 columns (spec columns 8–28) per student, in FIELDS order below; column 28 (field 21)
 * is the attendance confirmation code, empty in the export. No footer (§6.12: that is the registration file's).
 * Each student's line is kept exactly as SIS sent it, for the confirmation file.
 */
class AttendanceFile
{
    public const FIELDS = [
        'term',          // 8  Course Start Period (AUT, WIN, SPR, SUM)
        'ssn',           // 9  Student Support Number
        'surname',       // 10
        'forename',      // 11
        'dob',           // 12 Birth Date (may be empty)
        'ucasNumber',    // 13 UCAS Number (may be empty)
        'slcCode',       // 14 Course Code (SLC)
        'courseName',    // 15
        'year',          // 16 Course Year
        'fee',           // 17 Course Fee Amount
        'tfl',           // 18 Tuition Fee Loan
        'tfg',           // 19 Tuition Fee Grant
        'tfs',           // 20 Tuition Fee Student (paid by the student)
        'studentStatus', // 21 latest Support Notification status (F, P, S, N)
        'issueDate',     // 22 Issue Date of the Support Notification
        'newRecord',     // 23 NEW or blank
        'revision',      // 24 Revision Indicator (R, N)
        'bursary',       // 25 Bursary Student flag (Y, N)
        'validNino',     // 26 Valid NINO flag (Y, N)
        'disabledDistance', // 27 Disabled Distance Learner (Y, N)
        'attendanceCode',   // 28 Attendance Confirmation Code
    ];

    public const CODE_INDEX = 20; // field 21 = spec column 28

    public const AMOUNT_INDEXES = [10, 11, 12]; // fields 11–13 = spec columns 18–20, totalled in header columns 5–7

    public const MAX_RECORDS = 50000; // spec §6.12 / §8

    public const TERMS = ['AUT' => 'Autumn', 'WIN' => 'Winter', 'SPR' => 'Spring', 'SUM' => 'Summer']; // column 8's values (Appendix B)

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
            // SIS checks every line has the right number of columns (spec v9.0 §7.2.2): 21 here (Appendix B columns 8–28);
            // a line that hasn't would fail the whole confirmation file, so it is left out and listed
            if (count($parts) !== count(self::FIELDS)) {
                $errors[] = 'Line '.($i + 1).' has '.count($parts).' fields, not the 21 SLC’s layout has – left out';
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
     * The Attendance Confirmation import file for a batch (spec v9.0 Appendix B, §6.12, §7.2):
     *  - header: HEI code ~ HEI name ~ schedule issue date (DDMMYYYY, the file date) ~ number of records ~
     *    total TFL ~ total TFG ~ total Tuition Fees Student Pays, 2 decimals — SLC's File Header Validation
     *    checks the count and the three totals against the detail records, so they are recounted for the batch
     *    (summed in pence, so they can't drift);
     *  - each student's line exactly as SIS exported it — SLC matches HEP, course, course year, academic year
     *    and student, and the TFL / TFG / Student Pays against its most recent tuition fee data — with column 28
     *    = the code (A, C, D, F, L, N, S or X; AttendanceCodes::IMPORTABLE);
     *  - no footer; lines end CR LF, as SIS's own export; UTF-8 (§7.2.2 footnote); a .txt file (accepted
     *    with .out, §7.2.1).
     *
     * @param array $lines [raw line => code]
     */
    public static function build(string $headerRaw, array $lines, \DateTimeInterface $date): string
    {
        $h = explode('~', $headerRaw);
        $out = [];
        $pence = [0, 0, 0];
        foreach ($lines as $raw => $code) {
            $p = explode('~', $raw);
            while (count($p) < count(self::FIELDS)) {
                $p[] = '';
            }
            if (!AttendanceCodes::importable($code)) {
                throw new \InvalidArgumentException('Attendance code '.$code.' is not accepted by SLC’s import (A, C, D, F, L, N, S, X).');
            }
            $p[self::CODE_INDEX] = strtoupper($code);
            foreach (self::AMOUNT_INDEXES as $k => $i) {
                $pence[$k] += (int) round(((float) ($p[$i] ?? 0)) * 100);
            }
            $out[] = implode('~', $p);
        }
        $money = fn (int $p) => number_format($p / 100, 2, '.', '');
        $header = implode('~', [$h[0] ?? '', $h[1] ?? '', $date->format('dmY'), count($out), $money($pence[0]), $money($pence[1]), $money($pence[2])]);
        $text = implode("\r\n", array_merge([$header], $out))."\r\n";

        // SLC reads UTF-8 (§7.2.2): a report saved by a Windows editor may come back as Windows-1252
        return mb_check_encoding($text, 'UTF-8') ? $text : mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
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
