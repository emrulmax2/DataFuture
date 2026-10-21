<?php

namespace App\Services\Slc;

/**
 * Reads an SLC remittance file into rows, whichever way it was saved.
 *
 * The SLC issues the file tilde-separated with a `.dat` extension. The
 * office used to open that in Excel and save a comma-separated copy, which
 * drops leading zeros from dates and trailing zeros from amounts; the
 * matching code copes with both. Here both shapes become the same thing: a
 * summary row first (college code, name, transaction count, date, type,
 * total), then one row per transaction, with the values at the same indexes
 * either way. Nothing is interpreted - that stays with the caller.
 */
class RemittanceFile
{
    /** Extensions the upload accepts, lower-case. */
    public const EXTENSIONS = ['dat', 'csv', 'txt'];

    /**
     * Rows as arrays of trimmed strings. Blank lines are skipped and a UTF-8
     * BOM on the first cell is removed. An unreadable or empty file gives [].
     */
    public static function rows(string $path): array
    {
        $handle = @fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $delimiter = self::delimiter($handle);
        $rows = [];
        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($line === [null] || (count($line) === 1 && trim((string) $line[0]) === '')) {
                continue; /* a blank line */
            }
            $rows[] = array_map(fn($v) => trim((string) $v), $line);
        }
        fclose($handle);

        if (isset($rows[0][0])) {
            $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]);
        }

        return $rows;
    }

    /**
     * Tilde when the first line is tilde-separated with no commas (the SLC
     * file), comma otherwise. The handle is left at the start of the file.
     */
    public static function delimiter($handle): string
    {
        $first = (string) fgets($handle);
        rewind($handle);

        return (str_contains($first, '~') && !str_contains($first, ',')) ? '~' : ',';
    }
}
