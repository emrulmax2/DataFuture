<?php

namespace App\Services\SlcRegistration;

use App\Services\Slc\RemittanceFile;

/**
 * The SLC remittance file (Bulk payment upload, user 2 Nov 2026) — the `.dat` SLC issues, or a
 * `.csv`/`.txt` saved from it (RemittanceFile reads both). A header line: HEI code, HEI name,
 * number of payments, remittance date (ddmmyyyy; Excel drops the leading zero), type (T), total.
 * Then one line per payment: term (AUT/SPR/SUM/WIN), SSN, surname, forename, date of birth, –,
 * SLC course code, course name, year of study, –, amount (negative = refund), …, R.
 *
 * Reports › Accounts › Payments Upload reads the same file but files field 3 as the first name;
 * in the file it is the surname (ADI TIBOR = Tibor Adi).
 */
class PaymentFile
{
    /** @return array{header: ?array, lines: array, errors: string[]} */
    public static function parse(string $path): array
    {
        $rows = RemittanceFile::rows($path);
        if (!$rows) {
            return ['header' => null, 'lines' => [], 'errors' => []];
        }
        $h = $rows[0];
        $header = count($h) >= 6 && self::date($h[3] ?? '') && is_numeric($h[2] ?? null) ? [
            'hei' => $h[0], 'heiName' => $h[1], 'count' => (int) $h[2], 'date' => self::date($h[3]), 'type' => $h[4] ?? '',
            'total' => round((float) ($h[5] ?? 0), 2), 'raw' => implode('~', array_slice($h, 0, 6)),
        ] : null;
        $lines = [];
        $errors = [];
        foreach (array_slice($rows, $header ? 1 : 0) as $k => $r) {
            $n = $k + ($header ? 2 : 1);
            if (count($r) < 11 || trim($r[1]) === '') {
                $errors[] = 'Line '.$n.' has '.count($r).' fields (expected 16 or 17) or no SSN';
                continue;
            }
            $lines[] = [
                'line' => $n, 'raw' => implode('~', $r),
                'term' => strtoupper($r[0]), 'ssn' => strtoupper($r[1]), 'surname' => $r[2], 'forename' => $r[3], 'dob' => self::date($r[4]) ?: '',
                'slcCode' => strtoupper($r[6]), 'courseName' => $r[7], 'year' => (int) $r[8], 'amount' => round((float) $r[10], 2), 'flag' => $r[15] ?? '',
            ];
        }

        return ['header' => $header, 'lines' => $lines, 'errors' => $errors];
    }

    /** ddmmyyyy, or dmmyyyy when a spreadsheet dropped the leading zero → Y-m-d ('' if not a date). */
    public static function date(string $v): string
    {
        $v = preg_replace('/\D/', '', $v);
        if (strlen($v) === 7) {
            $v = '0'.$v;
        }
        if (strlen($v) !== 8 || !checkdate((int) substr($v, 2, 2), (int) substr($v, 0, 2), (int) substr($v, 4))) {
            return '';
        }

        return substr($v, 4).'-'.substr($v, 2, 2).'-'.substr($v, 0, 2);
    }

    public const TERMS = ['AUT' => 'Autumn', 'SPR' => 'Spring', 'SUM' => 'Summer', 'WIN' => 'Winter'];

    public static function termName(string $code): string
    {
        return self::TERMS[strtoupper($code)] ?? $code;
    }
}
