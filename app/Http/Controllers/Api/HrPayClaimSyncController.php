<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HrPayClaim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Receives paid payment claims from the Operations HR pay portal.
 *
 * Accounts ticks a batch over there and presses Push Claims; this is where the
 * batch lands. Each claim is matched to an employee and stored, so the HR
 * attendance report can show what a person claimed in a month beside what they
 * worked.
 *
 * Authenticated by Passport client-credentials (scope sms.hr-pay-claims.write),
 * so there is no logged-in user — the pusher's email is carried in the payload
 * for the audit trail only, never for authorisation.
 *
 * Idempotent by `reference`. A retried request or a double-pressed button
 * updates the rows it already wrote rather than adding a second copy, because
 * the failure this endpoint must never produce is a doubled figure on a report
 * that someone is paid against.
 */
class HrPayClaimSyncController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'pushed_by_email' => ['nullable', 'email', 'max:191'],
            'claims' => ['required', 'array', 'min:1', 'max:500'],
            'claims.*.source_id' => ['nullable', 'integer'],
            'claims.*.reference' => ['required', 'string', 'max:64'],
            /* Claims only. Invoices are settled a different way and must not
               land in a column headed Claim. */
            'claims.*.doc_type' => ['required', 'string', 'in:claim'],
            'claims.*.staff_email' => ['nullable', 'email', 'max:191'],
            'claims.*.staff_name' => ['nullable', 'string', 'max:191'],
            'claims.*.payroll_number' => ['nullable', 'string', 'max:32'],
            /* The month payroll ran the money in, as a date. Sent resolved by
               the caller, which owns the cutoff rule that decides it. */
            'claims.*.pay_cycle' => ['required', 'date'],
            'claims.*.pay_cycle_label' => ['nullable', 'string', 'max:32'],
            'claims.*.period' => ['nullable', 'string', 'max:191'],
            'claims.*.for_summary' => ['nullable', 'string'],
            'claims.*.subtotal' => ['required', 'numeric', 'min:0'],
            'claims.*.lines' => ['nullable', 'array'],
            'claims.*.submitted_at' => ['nullable', 'date'],
            'claims.*.paid_at' => ['nullable', 'date'],
        ]);

        $pushedBy = strtolower(trim((string) ($data['pushed_by_email'] ?? ''))) ?: null;

        $results = [];
        $stored = 0;
        $unmatched = 0;

        foreach ($data['claims'] as $claim) {
            $employeeId = $this->employeeIdFor($claim['staff_email'] ?? null);

            if (!$employeeId) {
                $unmatched++;
                Log::warning('[HrPayClaims] Claim stored without an employee match.', [
                    'reference' => $claim['reference'],
                    'staff_email' => $claim['staff_email'] ?? null,
                ]);
            }

            /* updateOrCreate on the reference is what makes the push safe to
               repeat. Everything else is overwritten from the payload, because
               Operations is the source of truth for a claim — this copy must
               never drift away from it. */
            $row = HrPayClaim::updateOrCreate(
                ['reference' => $claim['reference']],
                [
                    'source_id' => $claim['source_id'] ?? null,
                    'doc_type' => $claim['doc_type'],
                    'employee_id' => $employeeId,
                    'payroll_number' => $claim['payroll_number'] ?? null,
                    'staff_name' => $claim['staff_name'] ?? null,
                    'staff_email' => $claim['staff_email'] ?? null,
                    'pay_cycle' => date('Y-m-01', strtotime($claim['pay_cycle'])),
                    'pay_cycle_label' => $claim['pay_cycle_label'] ?? date('F Y', strtotime($claim['pay_cycle'])),
                    'period' => $claim['period'] ?? null,
                    'for_summary' => $claim['for_summary'] ?? null,
                    'subtotal' => $claim['subtotal'],
                    'lines' => $claim['lines'] ?? null,
                    'submitted_at' => !empty($claim['submitted_at']) ? date('Y-m-d H:i:s', strtotime($claim['submitted_at'])) : null,
                    'paid_at' => !empty($claim['paid_at']) ? date('Y-m-d H:i:s', strtotime($claim['paid_at'])) : null,
                    'pushed_at' => now(),
                    'pushed_by_email' => $pushedBy,
                ]
            );

            $stored++;
            $results[] = [
                'reference' => $row->reference,
                'id' => $row->id,
                'employee_id' => $employeeId,
                'matched' => (bool) $employeeId,
                'pay_cycle' => $row->pay_cycle->format('Y-m-d'),
            ];
        }

        return response()->json([
            'ok' => true,
            'stored' => $stored,
            'unmatched' => $unmatched,
            'claims' => $results,
        ]);
    }

    /**
     * The employee a claim belongs to, found by email.
     *
     * Email is the only key the two systems genuinely share: the payroll number
     * exists solely in Operations, and the user ids are unrelated — Operations
     * user 341 is DataFuture user 356 for the same person. Checked against all
     * 23 outstanding submissions at build time and matched every one.
     */
    private function employeeIdFor(?string $email): ?int
    {
        $email = strtolower(trim((string) $email));

        if ($email === '') {
            return null;
        }

        $userId = User::whereRaw('LOWER(email) = ?', [$email])->value('id');

        if (!$userId) {
            return null;
        }

        return Employee::where('user_id', $userId)->value('id');
    }
}
