<?php

namespace App\Services\SlcRegistration;

/**
 * The SIS "Registration Confirmation Report": an HH header line, one "~"-delimited line of 30
 * fields per student, and (perhaps) a TT footer. Reading and the confirmation file we hand back
 * follow the prototype exactly; the TT + six-digit count footer is its assumption, flagged on the
 * Batches page until it is checked against SLC's import specification.
 */
class SisFile
{
    public const FIELDS = ['confirmDate', 'ay', 'title', 'first', 'other', 'last', 'gender', 'dob', 'ucasNo', 'ucasCourse', 'slcCode', 'courseName', 'year', 'start', 'address', 'postcode', 'ssn', 'fee', 'tfl', 'tfg', 'stp', 'f22', 'f23', 'f24', 'f25', 'f26', 'f27', 'f28', 'regConf', 'credits'];

    public const REG_CONF_INDEX = 28; // field 29

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

    /**
     * The header with another 5-digit number in characters 9–13 (HH26LCHC00005CR → HH26LCHC00006CR).
     * SLC's exports number their files in a running sequence (00003 on a 57-line report, later 00005 on a
     * 47-line one), so the number is not a record count; a file we send takes the next number (user,
     * 12 Nov 2026 — SLC's public guide doesn't define it; the import technical specification does).
     */
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
     * The number the next file we send should carry: one more than the highest number seen for that
     * HEI — on SIS reports uploaded here and on batch files made here (leaving out $exceptBatchId, the
     * batch being made again).
     */
    public static function nextNumber(string $headerRaw, ?int $exceptBatchId = null): int
    {
        $hei = substr($headerRaw, 4, 4);
        $headers = \App\Models\SlcBulkImport::whereNotNull('header')->pluck('header')
            ->merge(\App\Models\SlcBulkBatch::when($exceptBatchId, fn ($q) => $q->where('id', '!=', $exceptBatchId))->whereNotNull('header')->pluck('header'))
            ->filter(fn ($h) => substr((string) $h, 4, 4) === $hei);

        return (int) ($headers->map(fn ($h) => self::number($h))->max() ?? 0) + 1;
    }

    /** Each student's original line with field 1 = the file date (ddmmyyyy) and field 29 = Y. */
    public static function build(string $headerRaw, array $rawLines, \DateTimeInterface $date): string
    {
        $d = $date->format('dmY');
        $details = array_map(function ($raw) use ($d) {
            $p = explode('~', $raw);
            while (count($p) < 30) {
                $p[] = '';
            }
            $p[0] = $d;
            $p[self::REG_CONF_INDEX] = 'Y';

            return implode('~', $p);
        }, $rawLines);
        $footer = 'TT'.str_pad((string) count($details), 6, '0', STR_PAD_LEFT);

        return implode("\r\n", array_merge([$headerRaw], $details, [$footer]))."\r\n";
    }

    public static function fileName(string $headerRaw, string $batchCode): string
    {
        return 'REG_'.substr($headerRaw, 4, 4).'_'.substr($headerRaw, 2, 2).'_'.$batchCode.'.txt';
    }
}
