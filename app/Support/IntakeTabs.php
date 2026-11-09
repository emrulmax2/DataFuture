<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Picks the intake tabs for the admission list hero from the semesters
 * table. Semesters are named "2027 January", "2011 Sept HND", ... so the
 * month is read from the name; the tabs are the semesters nearest to today.
 */
class IntakeTabs
{
    private const MONTHS = ['jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12];

    /** First day of the semester's month, or null when the name carries no year and month. */
    public static function dateOf(?string $name): ?Carbon
    {
        if (!preg_match('/(\d{4})\s+([A-Za-z]{3,})/', (string) $name, $m)) {
            return null;
        }
        $month = self::MONTHS[strtolower(substr($m[2], 0, 3))] ?? null;

        return $month ? Carbon::create((int) $m[1], $month, 1)->startOfDay() : null;
    }

    /**
     * The $count semesters nearest to $today, newest first. A past and a
     * future semester at the same distance resolve in favour of the future
     * one. Semesters whose name carries no date are left out.
     *
     * @param  Collection<int, object>  $semesters  objects with id and name
     * @return Collection<int, array{id:int,name:string,at:Carbon}>
     */
    public static function pick(Collection $semesters, Carbon $today, int $count = 2): Collection
    {
        $today = $today->copy()->startOfMonth();
        $months = fn (Carbon $at) => abs(($at->year - $today->year) * 12 + ($at->month - $today->month));

        return $semesters
            ->map(fn ($s) => ['id' => (int) $s->id, 'name' => (string) $s->name, 'at' => self::dateOf($s->name)])
            ->filter(fn ($s) => $s['at'] !== null)
            ->sort(fn ($a, $b) => [$months($a['at']), $b['at']->timestamp] <=> [$months($b['at']), $a['at']->timestamp])
            ->take($count)
            ->sortByDesc(fn ($s) => $s['at']->timestamp)
            ->values();
    }
}
