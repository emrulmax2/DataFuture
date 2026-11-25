<?php

namespace App\Services\Reports;

/**
 * "Retention by term of study" for the Retention PDF (term-performance/retention/pdf.blade.php):
 * one line per term, x = term of study. An SVG image (DomPDF draws SVG images, not inline SVG).
 * The same rules as the screen (term-retention-new.js): the axis starts at 80% unless a point is
 * lower; values are printed when there are at most two lines, the higher above its point.
 */
class TermRetentionChart
{
    /** Lowest and highest axis value, and the grid step. */
    public static function axis(array $series): array
    {
        $min = 100;
        foreach ($series as $s) {
            foreach ($s['points'] as $v) {
                $min = min($min, $v);
            }
        }
        $lo = max(0, min(80, (int) floor(($min - 1) / 5) * 5));

        return [$lo, 100, 100 - $lo > 30 ? 10 : 5];
    }

    /** SVG data URI, or null with nothing to draw. */
    public static function svg(array $series, int $W = 1000, int $H = 200): ?string
    {
        if (empty($series)) {
            return null;
        }
        $n = max(2, max(array_map(fn ($s) => max(array_keys($s['points'])), $series)));
        [$lo, $hi, $step] = self::axis($series);
        $L = 42; $R = 16; $T = 18; $B = 38; $PADX = 40;
        $x = fn ($i) => $L + $PADX + ($i - 1) * ($W - $L - $R - 2 * $PADX) / ($n - 1);
        $y = fn ($v) => $T + ($hi - $v) * ($H - $T - $B) / ($hi - $lo);
        $f = fn ($v) => number_format($v, 1, '.', '');
        $labels = count($series) <= 2;

        $g = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$W.'" height="'.$H.'" viewBox="0 0 '.$W.' '.$H.'">';
        for ($v = $lo; $v <= $hi; $v += $step) {
            $g .= '<line x1="'.$L.'" x2="'.($W - $R).'" y1="'.$f($y($v)).'" y2="'.$f($y($v)).'" stroke="#e3e7ea" stroke-width="1"/>';
            $g .= '<text x="'.($L - 7).'" y="'.$f($y($v) + 3).'" text-anchor="end" font-family="Helvetica" font-size="9" fill="#55616b">'.$v.'%</text>';
        }
        for ($i = 1; $i <= $n; $i++) {
            $g .= '<text x="'.$f($x($i)).'" y="'.($H - 21).'" text-anchor="middle" font-family="Helvetica-Bold" font-size="9" fill="#16242b">'.TermRetention::ordinal($i).' term</text>';
            $sub = $i === 1 ? 'newest intake' : ($i === $n ? 'oldest intake' : '');
            if ($sub !== '') {
                $g .= '<text x="'.$f($x($i)).'" y="'.($H - 8).'" text-anchor="middle" font-family="Helvetica" font-size="8" fill="#55616b">'.$sub.'</text>';
            }
        }

        foreach ($series as $k => $s) {
            $d = '';
            $prev = null;
            foreach ($s['points'] as $i => $v) {
                $d .= ($prev !== null && $prev === $i - 1 ? 'L' : 'M').$f($x($i)).' '.$f($y($v)).' ';
                $prev = $i;
            }
            $g .= '<path d="'.trim($d).'" fill="none" stroke="'.$s['colour'].'" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"/>';
            foreach ($s['points'] as $i => $v) {
                $g .= '<circle cx="'.$f($x($i)).'" cy="'.$f($y($v)).'" r="3.4" fill="#ffffff" stroke="'.$s['colour'].'" stroke-width="1.8"/>';
                if ($labels) {
                    $other = $series[1 - $k]['points'][$i] ?? null;
                    $above = $other === null || $v >= $other;
                    $g .= '<text x="'.$f($x($i)).'" y="'.$f($y($v) + ($above ? -8 : 15)).'" text-anchor="middle" font-family="Helvetica-Bold" font-size="9" fill="'.$s['colour'].'">'.number_format($v, 1).'</text>';
                }
            }
        }

        return 'data:image/svg+xml;base64,'.base64_encode($g.'</svg>');
    }
}
