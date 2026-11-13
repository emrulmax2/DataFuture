<?php

namespace App\Services\SlcRegistration;

/**
 * The SIS "Registration Confirmation Report" we read: an HH header line, one "~"-delimited line of
 * 30 fields per student and a TT footer. The confirmation file we hand back (build) has its own,
 * shorter layout — see build().
 */
class SisFile
{
    public const FIELDS = ['confirmDate', 'ay', 'title', 'first', 'other', 'last', 'gender', 'dob', 'ucasNo', 'ucasCourse', 'slcCode', 'courseName', 'year', 'start', 'address', 'postcode', 'ssn', 'fee', 'tfl', 'tfg', 'stp', 'f22', 'f23', 'f24', 'f25', 'f26', 'f27', 'f28', 'regConf', 'credits'];

    /**
     * @return array{header: ?array, footer: ?string, rows: array, errors: string[]}
     *         each row: the 30 named fields (whitespace collapsed) + line (1-based) + raw (the line as sent)
     */
    public static function parse(string $text): array
    {
        $text = (string) preg_replace('/^\xEF\xBB\xBF/', '', $text); // a UTF-8 BOM
        $lines = array_values(array_filter(preg_split('/\r?\n/', $text), fn ($l) => trim($l) !== ''));
        $header = null;
        $footer = null;
        $rows = [];
        $errors = [];

        foreach ($lines as $i => $line) {
            if (str_starts_with($line, 'HH')) {
                $header = trim($line);
                continue;
            }
            if (str_starts_with($line, 'TT')) {
                $footer = trim($line);
                continue;
            }
            $parts = explode('~', $line);
            if (count($parts) < 17) {
                $errors[] = 'Line '.($i + 1).' has '.count($parts).' fields (expected 30)';
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

        return ['header' => $header ? self::header($header) : null, 'footer' => $footer, 'rows' => $rows, 'errors' => $errors];
    }

    /** HH26LCHC00001CR → academic year 26, HEI LCHC, sequence 00001, type CR. */
    public static function header(string $raw): array
    {
        return ['raw' => $raw, 'ay' => substr($raw, 2, 2), 'hei' => substr($raw, 4, 4), 'seq' => substr($raw, 8, 5), 'type' => substr($raw, 13, 2)];
    }

    /** The header with another 5-digit number in characters 9–13 (HH26LCHC00005CR → HH26LCHC00001CR). */
    public static function withNumber(string $headerRaw, int $number): string
    {
        return substr($headerRaw, 0, 8).str_pad((string) $number, 5, '0', STR_PAD_LEFT).substr($headerRaw, 13);
    }

    /** The 5-digit number of a header (characters 9–13), 0 when it isn't one. */
    public static function number(?string $headerRaw): int
    {
        $n = substr((string) $headerRaw, 8, 5);

        return ctype_digit($n) ? (int) $n : 0;
    }

    /**
     * The header number of a batch's file: the batch's own number (SLC-B001 → 00001), as in the
     * confirmation file layout the user gave (REG_LCHC_26_B001: HH26LCHC00001CR, 13 Nov 2026).
     */
    public static function batchNumber(string $batchCode): int
    {
        return preg_match('/(\d+)\s*$/', $batchCode, $m) ? max(1, (int) $m[1]) : 1;
    }

    /**
     * The registration confirmation file SLC imports (user, 13 Nov 2026, "the file format should be the
     * same as the attached"): the header, then one line per confirmed student —
     *   confirmation date (ddmmyyyy) ~ academic year ~ HEI code ~ SSN ~ ~ ~
     * e.g. 13112026~26~LCHC~SFDU22528926L~~~ — then TT + the number of lines (6 digits). The academic
     * year and SSN come from the student's SIS line, the HEI code from the header.
     */
    public static function build(string $headerRaw, array $rawLines, \DateTimeInterface $date): string
    {
        $d = $date->format('dmY');
        $hei = substr($headerRaw, 4, 4);
        $headerAy = substr($headerRaw, 2, 2);
        $ayAt = array_search('ay', self::FIELDS, true);
        $ssnAt = array_search('ssn', self::FIELDS, true);
        $details = array_map(function ($raw) use ($d, $hei, $headerAy, $ayAt, $ssnAt) {
            $p = explode('~', (string) $raw);
            $ay = trim((string) ($p[$ayAt] ?? '')) ?: $headerAy;
            $ssn = strtoupper(trim((string) ($p[$ssnAt] ?? '')));

            return implode('~', [$d, $ay, $hei, $ssn, '', '', '']);
        }, $rawLines);
        $footer = 'TT'.str_pad((string) count($details), 6, '0', STR_PAD_LEFT);

        return implode("\r\n", array_merge([$headerRaw], $details, [$footer]))."\r\n";
    }

    /** REG_<HEI>_<academic year>_<batch> (REG_LCHC_26_B001.txt), as the user's file is named. */
    public static function fileName(string $headerRaw, string $batchCode): string
    {
        return 'REG_'.substr($headerRaw, 4, 4).'_'.substr($headerRaw, 2, 2).'_'.preg_replace('/^SLC-/i', '', $batchCode).'.txt';
    }
}
