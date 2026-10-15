<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Exposes each employee's contracted hours for external synchronisation.
 *
 * The figure lives on the working pattern (user -> employee -> working
 * pattern), and a person can have several rows over the years. The one that
 * counts is the rule the HR screens use:
 *
 *   · active = 1
 *   · the end date is empty, or still in the future
 *   · and if more than one row answers to both, the latest — by the date it
 *     takes effect, then by id, which breaks a same-day tie by whichever was
 *     entered last
 *
 * `contracted_hour` is stored as "HH:MM" ("37:30" is thirty-seven and a half
 * hours), so it goes out both as it is written and parsed into whole minutes.
 * Whole minutes because that is a number that cannot be read two ways, and
 * because "37:30" turns into 37.3 the first time something treats it as a
 * decimal.
 *
 * Protected by Passport client_credentials with `sms.working-patterns.read`.
 * Read-only, and deliberately narrow: an email, the hours, and enough about
 * the row they came from to trace a wrong number back to it.
 */
class WorkingPatternSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 200), 1);
        $today   = now()->toDateString();

        $rows = DB::table('users')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->join('employee_working_patterns as p', 'p.employee_id', '=', 'employees.id')
            ->whereNull('p.deleted_at')
            ->where('p.active', 1)
            ->where(function ($q) use ($today) {
                $q->whereNull('p.end_to')->orWhere('p.end_to', '>=', $today);
            })
            ->whereNotNull('users.email')
            ->where('users.email', '!=', '')
            ->select([
                'users.email',
                'employees.id as employee_id',
                'p.id as pattern_id',
                'p.contracted_hour',
                'p.effective_from',
                'p.end_to',
                'p.updated_at',
            ])
            ->orderBy('users.email')
            ->orderByDesc('p.effective_from')
            ->orderByDesc('p.id')
            ->get();

        /* One pattern per person: ordered above, so the first row for each
           email is the one the rule chooses. */
        $unique = $rows->unique('email')->values();

        $page  = max((int) $request->integer('page', 1), 1);
        $slice = $unique->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $slice->map(fn ($r) => [
                'email'             => $r->email,
                'employee_id'       => (int) $r->employee_id,
                'pattern_id'        => (int) $r->pattern_id,
                'contracted_hour'   => $r->contracted_hour,
                'contracted_minutes' => $this->minutes($r->contracted_hour),
                'effective_from'    => $r->effective_from ? (string) $r->effective_from : null,
                'end_to'            => $r->end_to ? (string) $r->end_to : null,
                'updated_at'        => $r->updated_at ? (string) $r->updated_at : null,
            ])->all(),
            'meta' => [
                'current_page' => $page,
                'last_page'    => max((int) ceil($unique->count() / $perPage), 1),
                'per_page'     => $perPage,
                'total'        => $unique->count(),
                'as_of'        => $today,
            ],
        ]);
    }

    /**
     * "37:30" into 2250.
     *
     * Also takes a plain number, in case somebody ever types 37.5 into a field
     * that has always held HH:MM. Anything else — an empty cell, a typo, more
     * than sixty minutes, a week longer than anybody works — comes back null
     * rather than a guess, and the far end skips the row.
     */
    private function minutes(?string $raw): ?int
    {
        $value = trim((string) $raw);

        if ($value === '') {
            return null;
        }

        if (str_contains($value, ':')) {
            [$h, $m] = array_pad(explode(':', $value, 2), 2, '0');

            if (! is_numeric($h) || ! is_numeric($m) || (int) $m > 59 || (int) $m < 0 || (int) $h < 0) {
                return null;
            }

            $minutes = ((int) $h * 60) + (int) $m;
        } elseif (is_numeric($value)) {
            $minutes = (int) round(((float) $value) * 60);
        } else {
            return null;
        }

        return $minutes > 0 && $minutes <= 80 * 60 ? $minutes : null;
    }
}
