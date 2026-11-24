<?php

namespace App\Services\Reports;

/**
 * Drawing for the Term Attendance PDF (term-performance/attendance/pdf.blade.php): the week-by-week
 * line chart as an SVG image (DomPDF draws SVG images, not inline SVG, and no gradients) and the
 * heat-cell colours. The same choices as the screen (term-attendance-report-new.js).
 */
class TermAttendanceChart
{
    /** Line colours: "Overall" is ink; the rows cycle through these (prototype palette). */
    const COLOURS = ['#1668a8', '#c8601a', '#7a4fb5', '#2f9e8f', '#b3261e', '#8a6d1b', '#c2478a', '#4a5560'];

    /** More rows than this, and only the overall line shows unless asked (screen default). */
    const MAX_DEFAULT = 7;

    /**
     * The chart's series: "Overall" (key _all), then each row with weekly figures.
     *
     * @param  string[]  $show  keys switched on on screen; empty = the default
     */
    public static function series(array $node, array $show = []): array
    {
        $all = [];
        if (self::has($node['weekly'])) {
            $all[] = ['key' => '_all', 'name' => 'Overall', 'data' => $node['weekly'], 'colour' => '#16242b', 'main' => true];
        }
        foreach (array_values($node['children']) as $i => $child) {
            if (self::has($child['weekly'])) {
                $all[] = ['key' => $child['id'], 'name' => $child['name'], 'data' => $child['weekly'], 'colour' => self::COLOURS[$i % count(self::COLOURS)], 'main' => false];
            }
        }
        $picked = array_values(array_filter($all, fn ($s) => in_array($s['key'], $show, true)));
        if (!empty($picked)) {
            return $picked;
        }

        return array_values(array_filter($all, fn ($s) => $s['main'] || count($all) <= self::MAX_DEFAULT));
    }

    /** ['uri' => SVG data URI or null, 'series' => the lines drawn, for the legend]. */
    public static function svg(array $node, array $weeks, array $show = []): array
    {
        $series = self::series($node, $show);
        $n = count($weeks);
        if (empty($series) || $n === 0) {
            return ['uri' => null, 'series' => $series];
        }

        // Geometry in px at the PDF's width; the right margin holds the end labels.
        $W = 704; $H = 196; $L = 34; $R = 122; $T = 8; $B = 22;
        $x = fn ($i) => $L + ($n > 1 ? $i * ($W - $L - $R) / ($n - 1) : ($W - $L - $R) / 2);
        $y = fn ($v) => $T + (100 - $v) * ($H - $T - $B) / 100;
        $f = fn ($v) => number_format($v, 1, '.', '');
        $esc = fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $g = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$W.'" height="'.$H.'" viewBox="0 0 '.$W.' '.$H.'">';
        foreach ([0, 25, 50, 75, 100] as $v) {
            $g .= '<line x1="'.$L.'" x2="'.($W - $R).'" y1="'.$f($y($v)).'" y2="'.$f($y($v)).'" stroke="#e3e7ea" stroke-width="1"/>';
            $g .= '<text x="'.($L - 6).'" y="'.$f($y($v) + 3).'" text-anchor="end" font-family="Helvetica" font-size="8" fill="#55616b">'.$v.'%</text>';
        }
        foreach ($weeks as $i => $week) {
            $g .= '<text x="'.$f($x($i)).'" y="'.($H - 8).'" text-anchor="middle" font-family="Helvetica" font-size="8" fill="#55616b">'.$esc(date('j M', strtotime($week))).'</text>';
        }
        if ($node['rate'] !== null) {
            $g .= '<line x1="'.$L.'" x2="'.($W - $R).'" y1="'.$f($y($node['rate'])).'" y2="'.$f($y($node['rate'])).'" stroke="#9aa3ab" stroke-width="1" stroke-dasharray="3 3"/>';
        }

        $ends = [];
        // The overall line last, so it sits on top.
        $order = array_merge(array_filter($series, fn ($s) => !$s['main']), array_filter($series, fn ($s) => $s['main']));
        foreach ($order as $s) {
            $d = '';
            $pen = false;
            $last = -1;
            foreach ($s['data'] as $i => $v) {
                if ($v === null) {
                    $pen = false;
                    continue;
                }
                $d .= ($pen ? 'L' : 'M').$f($x($i)).' '.$f($y($v)).' ';
                $pen = true;
                $last = $i;
            }
            $g .= '<path d="'.trim($d).'" fill="none" stroke="'.$s['colour'].'" stroke-width="'.($s['main'] ? 2.2 : 1.4).'" stroke-linejoin="round" stroke-linecap="round"/>';
            foreach ($s['data'] as $i => $v) {
                if ($v !== null) {
                    $g .= '<circle cx="'.$f($x($i)).'" cy="'.$f($y($v)).'" r="'.($s['main'] ? 2.4 : 1.8).'" fill="'.$s['colour'].'"/>';
                }
            }
            if ($last >= 0) {
                $ends[] = ['s' => $s, 'i' => $last, 'y' => $y($s['data'][$last])];
            }
        }
        // End labels, nudged apart, when there are few enough to read.
        if (count($ends) <= 8) {
            // At least 10px apart and inside the plot: pushed down, then back up from the bottom.
            usort($ends, fn ($a, $b) => $a['y'] <=> $b['y']);
            $top = $T + 4;
            $bottom = $H - $B - 2;
            foreach ($ends as $k => $e) {
                $ends[$k]['y'] = max($e['y'], $top, $k ? $ends[$k - 1]['y'] + 10 : $top);
            }
            for ($k = count($ends) - 1; $k >= 0; $k--) {
                $limit = $k === count($ends) - 1 ? $bottom : $ends[$k + 1]['y'] - 10;
                $ends[$k]['y'] = min($ends[$k]['y'], $limit);
            }
            foreach ($ends as $e) {
                $label = mb_strlen($e['s']['name']) > 16 ? mb_substr($e['s']['name'], 0, 15).'…' : $e['s']['name'];
                $g .= '<text x="'.$f($x($e['i']) + 7).'" y="'.$f($e['y'] + 3).'" font-family="'.($e['s']['main'] ? 'Helvetica-Bold' : 'Helvetica').'" font-size="8" fill="'.$e['s']['colour'].'">'
                    .$esc($label.' '.number_format($e['s']['data'][$e['i']], 1).'%').'</text>';
            }
        }
        $g .= '</svg>';

        return ['uri' => 'data:image/svg+xml;base64,'.base64_encode($g), 'series' => $series];
    }

    /**
     * A heat cell's background and text colour: orange below the average, teal above, deeper the
     * further away (up to 30 points), as on screen.
     */
    public static function heat(?float $value, ?float $average): array
    {
        if ($value === null) {
            return ['#f4f6f8', '#9aa3ab'];
        }
        $d = $value - ($average ?? 50);
        $t = min(1, abs($d) / 30);
        $to = $d < 0 ? [200, 96, 26] : [14, 90, 84];
        $k = 0.08 + $t * 0.82;
        $c = array_map(fn ($x) => (int) round(255 + ($x - 255) * $k), $to);
        $lum = (0.299 * $c[0] + 0.587 * $c[1] + 0.114 * $c[2]) / 255;

        return [sprintf('#%02x%02x%02x', ...$c), $lum < 0.6 ? '#ffffff' : '#16242b'];
    }

    /** The Below → Above key as small cells (DomPDF has no gradients). */
    public static function scale(int $steps = 24): array
    {
        $cells = [];
        for ($i = 0; $i < $steps; $i++) {
            $d = (($i + 0.5) / $steps - 0.5) * 60; // -30 … +30 points
            $cells[] = self::heat(50 + $d, 50)[0];
        }

        return $cells;
    }

    public static function has(?array $values): bool
    {
        return !empty($values) && count(array_filter($values, fn ($v) => $v !== null)) > 0;
    }
}
