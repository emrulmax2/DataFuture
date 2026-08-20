<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Exposes staff mobile numbers for external synchronisation.
 *
 * The number lives on the employment record (user -> employee -> employment),
 * so an employee with more than one is represented by their current employment:
 * the one still open, most recently started.
 *
 * Protected by Passport client_credentials with `sms.user-mobiles.read`.
 * Read-only, and deliberately narrow — it returns an email and a number, not a
 * staff record.
 */
class UserMobileSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 200), 1);

        $rows = DB::table('users')
            ->join('employees', 'employees.user_id', '=', 'users.id')
            ->join('employments', 'employments.employee_id', '=', 'employees.id')
            ->whereNull('employments.deleted_at')
            ->whereNotNull('users.email')
            ->whereNotNull('employments.mobile')
            ->where('employments.mobile', '!=', '')
            ->select([
                'users.email',
                'employments.mobile',
                'employments.started_on',
                'employments.ended_on',
                'employments.updated_at',
                'employments.id as employment_id',
            ])
            // Current employment first, then the most recently started.
            ->orderBy('users.email')
            ->orderByRaw('employments.ended_on IS NULL DESC')
            ->orderByDesc('employments.started_on')
            ->orderByDesc('employments.id')
            ->get();

        // One number per person: the first row for each email is the current one.
        $unique = $rows->unique('email')->values();

        $page = max((int) $request->integer('page', 1), 1);
        $slice = $unique->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $slice->map(fn ($r) => [
                'email'      => $r->email,
                'mobile'     => $r->mobile,
                'updated_at' => $r->updated_at ? (string) $r->updated_at : null,
            ])->all(),
            'meta' => [
                'current_page' => $page,
                'last_page'    => max((int) ceil($unique->count() / $perPage), 1),
                'per_page'     => $perPage,
                'total'        => $unique->count(),
            ],
        ]);
    }
}
