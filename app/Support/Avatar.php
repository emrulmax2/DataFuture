<?php

namespace App\Support;

/**
 * Generates an inline "initials" avatar used as a fallback whenever a
 * person (user / student / applicant / agent / employee) has no uploaded
 * profile photo. Returns a self-contained SVG data URI that drops straight
 * into any existing <img src="..."> (and inherits the container's shape, so
 * the usual `rounded-full` slots render it as a circle).
 *
 *  - initials()  → per-name colour from the palette (lists, tables, cards)
 *  - brand()     → fixed brand-teal gradient (profile headers)
 */
class Avatar
{
    /**
     * Background palette — white text passes WCAG AA on every colour.
     * The brand teal leads so it is the most common tone.
     */
    protected static array $palette = [
        '#0E7C86', // teal (brand)
        '#0D9488', // teal
        '#2563EB', // blue
        '#0891B2', // cyan
        '#4F46E5', // indigo
        '#7C3AED', // violet
        '#9333EA', // purple
        '#DB2777', // pink
        '#DC2626', // red
        '#EA580C', // orange
        '#B45309', // amber
        '#15803D', // green
    ];

    /** Fixed brand-teal gradient (deep → bright) used on profile headers. */
    protected static array $brand = ['#0A5E66', '#159FA0'];

    /**
     * Data-URI SVG avatar with a deterministic, per-name background colour.
     */
    public static function initials(?string $name, int $size = 200): string
    {
        return self::render($name, $size, self::colorFor($name));
    }

    /**
     * Data-URI SVG avatar with the fixed brand-teal gradient (always the
     * same colour regardless of the name) — used for profile pictures.
     */
    public static function brand(?string $name, int $size = 200): string
    {
        return self::render($name, $size, self::$brand);
    }

    /** Deterministic palette colour for a given name. */
    protected static function colorFor(?string $name): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', (string) $name));
        $key   = $clean !== '' ? $clean : 'lcc';

        return self::$palette[abs(crc32($key)) % count(self::$palette)];
    }

    /**
     * Build the SVG. $fill is either a solid colour string or a [from, to]
     * pair rendered as a diagonal linear gradient.
     */
    protected static function render(?string $name, int $size, $fill): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', (string) $name));

        $letters = '?';
        if ($clean !== '') {
            $parts = explode(' ', $clean);
            $first = mb_substr($parts[0], 0, 1);
            $last  = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';
            $letters = mb_strtoupper($first . $last);
        }

        $font = (int) round($size * (mb_strlen($letters) < 2 ? 0.46 : 0.42));

        if (is_array($fill)) {
            $defs = '<defs><linearGradient id="a" x1="0" y1="0" x2="1" y2="1">'
                  . '<stop offset="0" stop-color="' . $fill[0] . '"/>'
                  . '<stop offset="1" stop-color="' . $fill[1] . '"/>'
                  . '</linearGradient></defs>';
            $rectFill = 'url(#a)';
        } else {
            $defs = '';
            $rectFill = $fill;
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">'
             . $defs
             . '<rect width="' . $size . '" height="' . $size . '" fill="' . $rectFill . '"/>'
             . '<text x="50%" y="50%" dy="0.35em" text-anchor="middle" '
             . 'font-family="Arial, Helvetica, sans-serif" font-size="' . $font . '" '
             . 'font-weight="600" fill="#ffffff">' . htmlspecialchars($letters, ENT_QUOTES) . '</text>'
             . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
