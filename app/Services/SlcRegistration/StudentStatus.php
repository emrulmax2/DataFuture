<?php

namespace App\Services\SlcRegistration;

use Illuminate\Support\Facades\DB;

/**
 * A student's current status (students.status_id → statuses) as /slc-registration shows it beside
 * the name (user, 6 Nov 2026). Statuses have no colour of their own, so the tone comes from the
 * name and the active flag: still studying green, completed blue, incomplete / suspended amber,
 * left / discarded / terminated and the other inactive ones red, test data / course changed grey.
 */
class StudentStatus
{
    /** @return array{name:string, tone:string}|null */
    public static function of(?string $name, $active): ?array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }
        $n = strtolower($name);
        $tone = match (true) {
            str_contains($n, 'incomplete') || str_contains($n, 'suspend') => 'warn',
            str_contains($n, 'complete') => 'info',
            str_contains($n, 'test') || str_contains($n, 'changed') => 'muted',
            (int) $active === 1 => 'ok',
            default => 'bad',
        };

        return ['name' => $name, 'tone' => $tone];
    }

    /** @return array<int, array{name:string, tone:string}|null> by student id */
    public static function forIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) {
            return [];
        }

        return DB::table('students as s')->leftJoin('statuses as t', 't.id', '=', 's.status_id')->whereIn('s.id', $ids)
            ->get(['s.id', 't.name', 't.active'])
            ->mapWithKeys(fn ($r) => [(int) $r->id => self::of($r->name, $r->active)])->all();
    }
}
