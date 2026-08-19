<?php

namespace App\Support;

/**
 * Presentation rules for the Group Leader dashboard.
 *
 * These live in one place because they are the screen's whole vocabulary: a
 * percentage has to be coloured identically on a card, a KPI, a table row and
 * the drawer, or the reader learns nothing from the colour. A class rather
 * than blade helpers — an `@include` renders in its own scope, so closures
 * defined in a partial never reach the view that included it.
 */
class GroupLeaderPresenter
{
    /**
     * Red / amber / green for a percentage.
     *
     * Attendance and completion share the 85/75 scale; submissions run
     * stricter, so the caller passes its own bounds. Null is "grey": no data
     * is not the same as nought, and must never read as a failure.
     */
    public static function tone($value, $good = 85, $warn = 75): string
    {
        if ($value === null) {
            return 'grey';
        }

        return $value >= $good ? 'green' : ($value >= $warn ? 'amber' : 'red');
    }

    /** The words beside the dot on a KPI card. */
    public static function flag(string $tone): string
    {
        return [
            'green' => 'On target',
            'amber' => 'Watch',
            'red' => 'At risk',
            'grey' => 'No data',
        ][$tone] ?? '';
    }

    /** Two letters for an avatar; titles stripped, so "Mr Chris Powell" is CP. */
    public static function initials($name): string
    {
        $clean = preg_replace('/^(?:(Mr|Mrs|Ms|Miss|Dr|Md)\.?\s+)+/i', '', trim((string) $name));
        $parts = preg_split('/\s+/', $clean ?: '?');
        $first = $parts[0] ?? '?';
        $last = count($parts) > 1 ? $parts[count($parts) - 1] : $first;

        return strtoupper(substr($first, 0, 1).substr($last, 0, 1));
    }

    /** The chip colour for a class type. */
    public static function typeClass($type): string
    {
        return [
            'theory' => 'is-theory',
            'tutorial' => 'is-tutorial',
            'seminar' => 'is-seminar',
            'practical' => 'is-seminar',
        ][strtolower(trim((string) $type))] ?? 'is-plain';
    }

    /** A percentage clamped to a bar width, treating null as empty. */
    public static function width($value): int
    {
        return (int) min(100, max(0, (int) $value));
    }
}
