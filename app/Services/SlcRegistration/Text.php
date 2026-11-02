<?php

namespace App\Services\SlcRegistration;

use Normalizer;

/**
 * The prototype's text helpers (SLC Registration Check.html), unchanged in behaviour: SLC sends
 * upper-case, space-padded values, so names, addresses and postcodes are compared normalised.
 */
class Text
{
    private const ABBR = ['RD' => 'ROAD', 'ST' => 'STREET', 'AVE' => 'AVENUE', 'AV' => 'AVENUE', 'LN' => 'LANE', 'GDNS' => 'GARDENS', 'CT' => 'COURT', 'CRES' => 'CRESCENT', 'PL' => 'PLACE', 'DR' => 'DRIVE', 'SQ' => 'SQUARE'];

    /** Upper case, accents dropped, anything but A-Z 0-9 turned into one space. */
    public static function norm(?string $s): string
    {
        $s = mb_strtoupper((string) $s, 'UTF-8');
        if (class_exists(Normalizer::class)) {
            $s = (string) Normalizer::normalize($s, Normalizer::FORM_D);
            $s = (string) preg_replace('/\p{Mn}/u', '', $s);
        }
        $s = (string) preg_replace('/[^A-Z0-9 ]/', ' ', $s);

        return trim((string) preg_replace('/\s+/', ' ', $s));
    }

    public static function addrNorm(?string $s): string
    {
        $words = array_filter(explode(' ', self::norm($s)), 'strlen');

        return implode(' ', array_map(fn ($t) => self::ABBR[$t] ?? $t, $words));
    }

    public static function pcNorm(?string $s): string
    {
        return str_replace(' ', '', self::norm($s));
    }

    /** SLC's "MOHAMMED  ALI" → "Mohammed Ali" (also after - and '). */
    public static function titleCase(?string $s): string
    {
        $s = trim((string) preg_replace('/\s+/', ' ', mb_strtolower((string) $s, 'UTF-8')));

        return (string) preg_replace_callback("/(^|[\\s\\-'])(\\p{Ll})/u", fn ($m) => $m[1].mb_strtoupper($m[2], 'UTF-8'), $s);
    }

    public static function nameSim(?string $a, ?string $b): float
    {
        $a = self::norm($a);
        $b = self::norm($b);
        if ($a === '' || $b === '') {
            return 0;
        }
        if ($a === $b || str_replace(' ', '', $a) === str_replace(' ', '', $b)) {
            return 1;
        }
        if (explode(' ', $a)[0] === explode(' ', $b)[0]) {
            return 0.8;
        }
        if (levenshtein($a, $b) <= (max(strlen($a), strlen($b)) > 5 ? 2 : 1)) {
            return 0.6;
        }
        if (str_contains($a, $b) || str_contains($b, $a)) {
            return 0.6;
        }

        return $a[0] === $b[0] ? 0.15 : 0;
    }

    public static function addrSim(?string $a, ?string $b): float
    {
        $x = array_unique(array_filter(explode(' ', self::addrNorm($a)), 'strlen'));
        $y = array_unique(array_filter(explode(' ', self::addrNorm($b)), 'strlen'));
        if (!$x || !$y) {
            return 0;
        }

        return count(array_intersect($x, $y)) / count(array_unique(array_merge($x, $y)));
    }

    public static function pcSim(?string $a, ?string $b): float
    {
        $x = self::pcNorm($a);
        $y = self::pcNorm($b);
        if ($x === '' || $y === '') {
            return 0;
        }
        if ($x === $y) {
            return 1;
        }

        return substr($x, 0, -3) === substr($y, 0, -3) ? 0.4 : 0;
    }

    /** SLC dates are ddmmyyyy. */
    public static function slcToIso(?string $s): string
    {
        $s = (string) $s;

        return strlen($s) === 8 && ctype_digit($s) ? substr($s, 4).'-'.substr($s, 2, 2).'-'.substr($s, 0, 2) : '';
    }

    public static function fmtIso(?string $iso): string
    {
        $iso = (string) $iso;

        return strlen($iso) >= 10 ? substr($iso, 8, 2).'/'.substr($iso, 5, 2).'/'.substr($iso, 0, 4) : '–';
    }

    public static function fmtSlc(?string $s): string
    {
        return self::fmtIso(self::slcToIso($s));
    }

    /** yyyy-mm-dd with day and month swapped (the usual DOB typo). */
    public static function swapDM(string $iso): string
    {
        return strlen($iso) === 10 ? substr($iso, 0, 5).substr($iso, 8, 2).'-'.substr($iso, 5, 2) : '';
    }

    public static function money($n): string
    {
        return '£'.number_format((float) $n, 2);
    }

    public static function genderWord(?string $g): string
    {
        return $g === 'M' ? 'Male' : ($g === 'F' ? 'Female' : ($g ?: '–'));
    }

    public static function plural(int $n, string $w): string
    {
        return $n.' '.$w.($n === 1 ? '' : 's');
    }
}
